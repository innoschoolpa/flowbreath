<?php

namespace App\Controllers;

use App\Batch\ResourceBatchProcessor;
use App\Batch\TagBatchProcessor;
use App\Core\Response;

class BatchController extends BaseController
{
    public function index()
    {
        return $this->view('batch/index');
    }

    public function importResources()
    {
        if (!isset($_FILES['csv_file'])) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'File upload failed'], 400);
        }

        $processor = new ResourceBatchProcessor();
        $result = $processor->importFromCsv($file['tmp_name']);

        if ($result) {
            return $this->json([
                'success' => true,
                'processed' => $processor->getTotalProcessed(),
                'errors' => $processor->getErrors()
            ]);
        }

        return $this->json([
            'success' => false,
            'errors' => $processor->getErrors()
        ], 500);
    }

    public function exportResources()
    {
        $processor = new ResourceBatchProcessor();
        $filePath = sys_get_temp_dir() . '/resources_export_' . date('Y-m-d_His') . '.csv';
        
        if ($processor->exportToCsv($filePath)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            readfile($filePath);
            unlink($filePath);
            exit;
        }

        return $this->json(['error' => 'Export failed'], 500);
    }

    public function importTags()
    {
        if (!isset($_FILES['csv_file'])) {
            return $this->json(['error' => 'No file uploaded'], 400);
        }

        $file = $_FILES['csv_file'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return $this->json(['error' => 'File upload failed'], 400);
        }

        $processor = new TagBatchProcessor();
        $result = $processor->importFromCsv($file['tmp_name']);

        if ($result) {
            return $this->json([
                'success' => true,
                'processed' => $processor->getTotalProcessed(),
                'errors' => $processor->getErrors()
            ]);
        }

        return $this->json([
            'success' => false,
            'errors' => $processor->getErrors()
        ], 500);
    }

    public function exportTags()
    {
        $processor = new TagBatchProcessor();
        $filePath = sys_get_temp_dir() . '/tags_export_' . date('Y-m-d_His') . '.csv';
        
        if ($processor->exportToCsv($filePath)) {
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
            readfile($filePath);
            unlink($filePath);
            exit;
        }

        return $this->json(['error' => 'Export failed'], 500);
    }

    public function cleanupOrphanedResources()
    {
        $processor = new ResourceBatchProcessor();
        $processor->cleanupOrphanedResources();
        
        return $this->json([
            'success' => true,
            'message' => 'Cleanup completed'
        ]);
    }

    public function cleanupUnusedTags()
    {
        $processor = new TagBatchProcessor();
        $processor->cleanupUnusedTags();
        
        return $this->json([
            'success' => true,
            'message' => 'Cleanup completed'
        ]);
    }

    public function mergeTags()
    {
        $sourceId = $_POST['source_id'] ?? null;
        $targetId = $_POST['target_id'] ?? null;

        if (!$sourceId || !$targetId) {
            return $this->json(['error' => 'Source and target tag IDs are required'], 400);
        }

        $processor = new TagBatchProcessor();
        $result = $processor->mergeTags($sourceId, $targetId);

        if ($result) {
            return $this->json([
                'success' => true,
                'message' => 'Tags merged successfully'
            ]);
        }

        return $this->json([
            'success' => false,
            'errors' => $processor->getErrors()
        ], 500);
    }
} 