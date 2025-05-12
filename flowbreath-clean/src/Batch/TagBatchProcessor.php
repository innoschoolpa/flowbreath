<?php

namespace App\Batch;

use App\Models\Tag;

class TagBatchProcessor extends BatchProcessor
{
    public function process()
    {
        // Default implementation
        return $this->cleanupUnusedTags();
    }

    public function importFromCsv($filePath)
    {
        if (!file_exists($filePath)) {
            $this->logError("CSV file not found: {$filePath}");
            return false;
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->logError("Could not open CSV file: {$filePath}");
            return false;
        }

        // Skip header row
        fgetcsv($handle);

        $batch = [];
        while (($data = fgetcsv($handle)) !== false) {
            $batch[] = [
                'name' => $data[0],
                'description' => $data[1] ?? '',
                'color' => $data[2] ?? '#000000'
            ];

            if (count($batch) >= $this->batchSize) {
                $this->processBatch($batch);
                $batch = [];
            }
        }

        // Process remaining items
        if (!empty($batch)) {
            $this->processBatch($batch);
        }

        fclose($handle);
        return true;
    }

    protected function processBatch($batch)
    {
        foreach ($batch as $item) {
            try {
                $tag = new Tag();
                $tag->name = $item['name'];
                $tag->description = $item['description'];
                $tag->color = $item['color'];
                
                if ($tag->save()) {
                    $this->totalProcessed++;
                }
            } catch (\Exception $e) {
                $this->logError("Error processing tag: {$item['name']}", ['error' => $e->getMessage()]);
            }
        }
    }

    public function exportToCsv($filePath)
    {
        $handle = fopen($filePath, 'w');
        if (!$handle) {
            $this->logError("Could not create CSV file: {$filePath}");
            return false;
        }

        // Write header
        fputcsv($handle, ['Name', 'Description', 'Color', 'Resource Count']);

        $offset = 0;
        while (true) {
            $tags = Tag::withCount('resources')
                ->skip($offset)
                ->take($this->batchSize)
                ->get();

            if ($tags->isEmpty()) {
                break;
            }

            foreach ($tags as $tag) {
                fputcsv($handle, [
                    $tag->name,
                    $tag->description,
                    $tag->color,
                    $tag->resources_count
                ]);
                $this->totalProcessed++;
            }

            $offset += $this->batchSize;
        }

        fclose($handle);
        return true;
    }

    public function cleanupUnusedTags()
    {
        $query = "DELETE FROM tags WHERE id NOT IN (SELECT tag_id FROM resource_tags)";
        $this->db->query($query);
        $this->logInfo("Cleaned up unused tags");
        return true;
    }

    public function mergeTags($sourceTagId, $targetTagId)
    {
        try {
            // Begin transaction
            $this->db->beginTransaction();

            // Move all resource associations from source to target
            $query = "UPDATE resource_tags SET tag_id = ? WHERE tag_id = ?";
            $this->db->query($query, [$targetTagId, $sourceTagId]);

            // Delete the source tag
            $query = "DELETE FROM tags WHERE id = ?";
            $this->db->query($query, [$sourceTagId]);

            $this->db->commit();
            $this->logInfo("Successfully merged tags", [
                'source' => $sourceTagId,
                'target' => $targetTagId
            ]);
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->logError("Error merging tags", [
                'error' => $e->getMessage(),
                'source' => $sourceTagId,
                'target' => $targetTagId
            ]);
            return false;
        }
    }
} 