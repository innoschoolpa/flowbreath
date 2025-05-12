<?php

namespace App\Batch;

use App\Core\Database;
use App\Core\Logger;

abstract class BatchProcessor
{
    protected $db;
    protected $logger;
    protected $batchSize = 100;
    protected $totalProcessed = 0;
    protected $errors = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->logger = new Logger();
    }

    abstract public function process();

    protected function logError($message, $context = [])
    {
        $this->errors[] = $message;
        $this->logger->error($message, $context);
    }

    protected function logInfo($message, $context = [])
    {
        $this->logger->info($message, $context);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getTotalProcessed()
    {
        return $this->totalProcessed;
    }

    protected function setBatchSize($size)
    {
        $this->batchSize = $size;
    }
} 