<?php
session_start();

echo "Session Configuration:\n";
echo "Session save path: " . session_save_path() . "\n";
echo "Session name: " . session_name() . "\n";
echo "Session ID: " . session_id() . "\n";
echo "Session status: " . session_status() . "\n";

echo "\nSession Data:\n";
print_r($_SESSION);

echo "\nCookies:\n";
print_r($_COOKIE);

echo "\nPHP Info:\n";
echo "display_errors: " . ini_get('display_errors') . "\n";
echo "error_reporting: " . ini_get('error_reporting') . "\n";
echo "error_log: " . ini_get('error_log') . "\n"; 