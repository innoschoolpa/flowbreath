<?php

namespace App\Batch;

use App\Models\Resource;
use App\Models\Tag;

class ResourceBatchProcessor extends BatchProcessor
{
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
                'title' => $data[0],
                'content' => $data[1],
                'type' => $data[2],
                'visibility' => $data[3],
                'tags' => explode(',', $data[4])
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
                $resource = new Resource();
                $resource->title = $item['title'];
                $resource->content = $item['content'];
                $resource->type = $item['type'];
                $resource->visibility = $item['visibility'];
                
                if ($resource->save()) {
                    // Process tags
                    foreach ($item['tags'] as $tagName) {
                        $tagName = trim($tagName);
                        if (!empty($tagName)) {
                            $tag = Tag::findOrCreateByName($tagName);
                            $resource->tags()->attach($tag->id);
                        }
                    }
                    $this->totalProcessed++;
                }
            } catch (\Exception $e) {
                $this->logError("Error processing resource: {$item['title']}", ['error' => $e->getMessage()]);
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
        fputcsv($handle, ['Title', 'Content', 'Type', 'Visibility', 'Tags']);

        $offset = 0;
        while (true) {
            $resources = Resource::with('tags')
                ->skip($offset)
                ->take($this->batchSize)
                ->get();

            if ($resources->isEmpty()) {
                break;
            }

            foreach ($resources as $resource) {
                $tags = $resource->tags->pluck('name')->implode(',');
                fputcsv($handle, [
                    $resource->title,
                    $resource->content,
                    $resource->type,
                    $resource->visibility,
                    $tags
                ]);
                $this->totalProcessed++;
            }

            $offset += $this->batchSize;
        }

        fclose($handle);
        return true;
    }

    public function cleanupOrphanedResources()
    {
        $query = "DELETE FROM resources WHERE id NOT IN (SELECT resource_id FROM resource_tags)";
        $this->db->query($query);
        $this->logInfo("Cleaned up orphaned resources");
    }
} 