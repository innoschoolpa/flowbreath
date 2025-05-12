<?php

namespace App\Core;

class SQLFileProcessor
{
    private $db;
    private $memoryManager;
    private $logger;
    private $chunkSize = 1024 * 1024; // 1MB chunks
    private $maxMemoryUsage = 0.8; // 80% 메모리 사용량 제한
    private $transactionSize = 1000; // 트랜잭션당 처리할 SQL 문장 수
    private $delimiter = ';';
    private $inTransaction = false;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->memoryManager = MemoryManager::getInstance();
        $this->logger = Logger::getInstance();
    }

    public function processFile($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception("SQL file not found: {$filePath}");
        }

        $this->memoryManager->createCheckpoint('sql_processing_start');
        
        try {
            $handle = fopen($filePath, 'r');
            if (!$handle) {
                throw new \Exception("Cannot open file: {$filePath}");
            }

            $buffer = '';
            $lineCount = 0;
            $statementCount = 0;
            $errorCount = 0;
            $startTime = microtime(true);

            // 트랜잭션 시작
            $this->beginTransaction();

            while (!feof($handle)) {
                // 메모리 사용량 체크
                if (!$this->memoryManager->checkMemoryLimit($this->maxMemoryUsage)) {
                    $this->logger->warning("Memory usage threshold exceeded during SQL processing");
                    break;
                }

                $chunk = fread($handle, $this->chunkSize);
                $buffer .= $chunk;

                // SQL 문장 단위로 분리하여 처리
                $statements = $this->splitStatements($buffer);
                
                foreach ($statements as $statement) {
                    if (trim($statement) === '') {
                        continue;
                    }

                    try {
                        $this->db->query($statement);
                        $statementCount++;

                        // 트랜잭션 크기 체크
                        if ($statementCount % $this->transactionSize === 0) {
                            $this->commitTransaction();
                            $this->beginTransaction();
                        }
                    } catch (\Exception $e) {
                        $errorCount++;
                        $this->logger->error(sprintf(
                            "Error executing SQL statement (line %d): %s\nStatement: %s",
                            $lineCount,
                            $e->getMessage(),
                            substr($statement, 0, 100) . '...'
                        ));
                        
                        // 에러가 발생한 경우 현재 트랜잭션 롤백
                        $this->rollbackTransaction();
                        $this->beginTransaction();
                    }
                }

                $lineCount += substr_count($chunk, "\n");
            }

            // 마지막 트랜잭션 커밋
            $this->commitTransaction();
            fclose($handle);

            $this->memoryManager->createCheckpoint('sql_processing_end');
            $stats = $this->memoryManager->getCheckpointDiff('sql_processing_start');
            $processingTime = microtime(true) - $startTime;

            $this->logger->info(sprintf(
                "SQL file processing completed:\n" .
                "- Statements executed: %d\n" .
                "- Lines processed: %d\n" .
                "- Errors encountered: %d\n" .
                "- Memory used: %s\n" .
                "- Processing time: %.2f seconds",
                $statementCount,
                $lineCount,
                $errorCount,
                $this->memoryManager->formatBytes($stats['memory_diff']),
                $processingTime
            ));

            return [
                'statements' => $statementCount,
                'lines' => $lineCount,
                'errors' => $errorCount,
                'memory_used' => $stats['memory_diff'],
                'processing_time' => $processingTime,
                'success_rate' => $statementCount > 0 ? 
                    round(($statementCount - $errorCount) / $statementCount * 100, 2) : 0
            ];

        } catch (\Exception $e) {
            if ($this->inTransaction) {
                $this->rollbackTransaction();
            }
            $this->logger->error("Error processing SQL file: " . $e->getMessage());
            throw $e;
        }
    }

    private function splitStatements(&$buffer)
    {
        $statements = [];
        $inString = false;
        $stringChar = '';
        $statement = '';
        $i = 0;
        $lineNumber = 1;

        while ($i < strlen($buffer)) {
            $char = $buffer[$i];
            $nextChar = $i + 1 < strlen($buffer) ? $buffer[$i + 1] : '';

            // 문자열 처리
            if ($char === "'" || $char === '"') {
                if (!$inString) {
                    $inString = true;
                    $stringChar = $char;
                } elseif ($char === $stringChar && $nextChar !== $stringChar) {
                    $inString = false;
                }
            }

            // 줄 번호 추적
            if ($char === "\n") {
                $lineNumber++;
            }

            // 구분자 처리
            if ($char === $this->delimiter && !$inString) {
                $trimmedStatement = trim($statement);
                if (!empty($trimmedStatement)) {
                    $statements[] = [
                        'sql' => $trimmedStatement,
                        'line' => $lineNumber
                    ];
                }
                $statement = '';
            } else {
                $statement .= $char;
            }

            $i++;
        }

        // 남은 버퍼 처리
        $buffer = $statement;

        return $statements;
    }

    private function beginTransaction()
    {
        if (!$this->inTransaction) {
            $this->db->beginTransaction();
            $this->inTransaction = true;
        }
    }

    private function commitTransaction()
    {
        if ($this->inTransaction) {
            $this->db->commit();
            $this->inTransaction = false;
        }
    }

    private function rollbackTransaction()
    {
        if ($this->inTransaction) {
            $this->db->rollBack();
            $this->inTransaction = false;
        }
    }

    public function setChunkSize($size)
    {
        if ($size > 0) {
            $this->chunkSize = $size;
        }
    }

    public function setMaxMemoryUsage($percentage)
    {
        if ($percentage > 0 && $percentage <= 1) {
            $this->maxMemoryUsage = $percentage;
        }
    }

    public function setTransactionSize($size)
    {
        if ($size > 0) {
            $this->transactionSize = $size;
        }
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }
} 