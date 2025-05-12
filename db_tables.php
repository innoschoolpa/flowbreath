<?php

/**
 * Database Tables Configuration
 * This file contains the definitions and management functions for database tables
 */

// Table definitions
$tables = [
    'users' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'username' => 'VARCHAR(255) NOT NULL',
        'email' => 'VARCHAR(255) NOT NULL UNIQUE',
        'password' => 'VARCHAR(255) NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'sessions' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'user_id' => 'INT NOT NULL',
        'token' => 'VARCHAR(255) NOT NULL UNIQUE',
        'expires_at' => 'TIMESTAMP NOT NULL',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ],
    'resource' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'title' => 'VARCHAR(255) NOT NULL',
        'description' => 'TEXT',
        'type' => 'VARCHAR(50)',
        'status' => 'VARCHAR(20) DEFAULT "active"',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'updated_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'
    ],
    'resource_backup' => [
        'id' => 'INT AUTO_INCREMENT PRIMARY KEY',
        'resource_id' => 'INT NOT NULL',
        'title' => 'VARCHAR(255) NOT NULL',
        'description' => 'TEXT',
        'content' => 'LONGTEXT',
        'type' => 'VARCHAR(50)',
        'status' => 'VARCHAR(20) DEFAULT "backup"',
        'created_at' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
        'backup_date' => 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP'
    ]
];

/**
 * Get table definition
 * @param string $tableName Name of the table
 * @return array|null Table definition or null if not found
 */
function getTableDefinition($tableName) {
    global $tables;
    return isset($tables[$tableName]) ? $tables[$tableName] : null;
}

/**
 * Check if table exists
 * @param PDO $pdo Database connection
 * @param string $tableName Name of the table to check
 * @return bool True if table exists, false otherwise
 */
function tableExists($pdo, $tableName) {
    try {
        $result = $pdo->query("SHOW TABLES LIKE '$tableName'");
        return $result->rowCount() > 0;
    } catch (PDOException $e) {
        error_log("Error checking table existence: " . $e->getMessage());
        return false;
    }
}

/**
 * Create table if it doesn't exist
 * @param PDO $pdo Database connection
 * @param string $tableName Name of the table to create
 * @return bool True if table was created or already exists, false on error
 */
function createTableIfNotExists($pdo, $tableName) {
    $definition = getTableDefinition($tableName);
    if (!$definition) {
        error_log("Table definition not found for: $tableName");
        return false;
    }

    if (tableExists($pdo, $tableName)) {
        return true;
    }

    try {
        $columns = [];
        foreach ($definition as $column => $type) {
            $columns[] = "$column $type";
        }
        $sql = "CREATE TABLE $tableName (" . implode(', ', $columns) . ")";
        $pdo->exec($sql);
        return true;
    } catch (PDOException $e) {
        error_log("Error creating table: " . $e->getMessage());
        return false;
    }
}

// Export tables configuration
return $tables; 