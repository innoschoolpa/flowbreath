<?php
declare(strict_types=1);

header('Content-Type: application/json');

function checkApiStatus(string $endpoint): array {
    $ch = curl_init("https://flowbreath.io" . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'status' => $httpCode === 200 ? 'active' : 'inactive',
        'response' => $response,
        'timestamp' => date('Y-m-d H:i:s')
    ];
}

$endpoints = [
    'health' => '/api/health',
    'error' => '/api/test/error',
    'warning' => '/api/test/warning',
    'notice' => '/api/test/notice',
    'memory' => '/api/test/memory',
    'performance' => '/api/test/performance'
];

$status = [];
foreach ($endpoints as $name => $endpoint) {
    $status[$name] = checkApiStatus($endpoint);
}

echo json_encode($status, JSON_PRETTY_PRINT); 