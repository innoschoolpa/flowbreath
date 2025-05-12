<?php
require_once __DIR__ . '/src/config/database.php';

use Config\Database;

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Set proper character encoding
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    
    // Disable foreign key checks temporarily
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    
    // Drop any existing tables that might interfere
    $pdo->exec("DROP TABLE IF EXISTS resources_new");
    $pdo->exec("DROP TABLE IF EXISTS temp_resources_import");
    $pdo->exec("DROP TABLE IF EXISTS resources");
    
    // Create new resources table with proper structure
    $pdo->exec("
        CREATE TABLE resources_new (
            id int(11) NOT NULL AUTO_INCREMENT,
            user_id int(11) NOT NULL,
            title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            slug varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            description text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            visibility enum('public','private') DEFAULT 'private',
            status enum('draft','published') DEFAULT 'draft',
            published_at timestamp NULL DEFAULT NULL,
            view_count int(11) DEFAULT 0,
            created_at timestamp NULL DEFAULT current_timestamp(),
            updated_at timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            deleted_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (id),
            KEY idx_user_id (user_id),
            KEY idx_slug (slug),
            KEY idx_visibility (visibility),
            KEY idx_status (status),
            KEY idx_published_at (published_at),
            CONSTRAINT fk_resources_user_id FOREIGN KEY (user_id) REFERENCES users (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Create temporary import table with old structure
    $pdo->exec("
        CREATE TABLE temp_resources_import (
            resource_id int(11) NOT NULL,
            title varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
            user_id int(11) NOT NULL DEFAULT 1,
            content text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
            created_at timestamp NULL DEFAULT NULL,
            updated_at timestamp NULL DEFAULT NULL,
            PRIMARY KEY (resource_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    // Read SQL file content
    $sqlContent = file_get_contents(__DIR__ . '/resources.sql');
    
    // Current timestamp for default values
    $currentTimestamp = date('Y-m-d H:i:s');
    
    // Extract and process INSERT statements
    if (preg_match_all('/INSERT INTO\s+`?resources`?\s+\(([^)]+)\)\s+VALUES\s+\(([^)]+)\)/i', $sqlContent, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            $columns = array_map('trim', explode(',', $match[1]));
            $values = str_getcsv($match[2], ',', "'", "\\");
            
            // Convert empty or invalid dates to current timestamp
            $created = preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $values[4]) ? $values[4] : $currentTimestamp;
            $updated = preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $values[5]) ? $values[5] : $currentTimestamp;
            
            try {
                // Prepare insert statement for temp table
                $stmt = $pdo->prepare("
                    INSERT INTO temp_resources_import 
                    (resource_id, title, user_id, content, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE
                    title = VALUES(title),
                    content = VALUES(content),
                    created_at = VALUES(created_at),
                    updated_at = VALUES(updated_at)
                ");
                
                $stmt->execute([
                    (int)$values[0], // resource_id
                    $values[1],      // title
                    1,              // default user_id
                    $values[3],      // content
                    $created,       // created_at
                    $updated        // updated_at
                ]);
            } catch (PDOException $e) {
                echo "Warning: Failed to insert record {$values[0]}: " . $e->getMessage() . "\n";
                continue;
            }
        }
    }
    
    // Function to generate a URL-friendly slug
    function generateSlug($title) {
        // Convert Korean characters to their romanized equivalents
        $romanized = str_replace(
            ['ㄱ', 'ㄴ', 'ㄷ', 'ㄹ', 'ㅁ', 'ㅂ', 'ㅅ', 'ㅇ', 'ㅈ', 'ㅊ', 'ㅋ', 'ㅌ', 'ㅍ', 'ㅎ'],
            ['g', 'n', 'd', 'r', 'm', 'b', 's', 'ng', 'j', 'ch', 'k', 't', 'p', 'h'],
            $title
        );
        
        // Remove any remaining non-ASCII characters
        $slug = preg_replace('/[^\x20-\x7E]/u', '', $romanized);
        
        // Convert to lowercase
        $slug = strtolower($slug);
        
        // Replace non-alphanumeric characters with hyphens
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        
        // Replace multiple hyphens with single hyphen
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');
        
        // If slug is empty or too short, append the title length and a timestamp
        if (empty($slug) || strlen($slug) < 3) {
            $slug = 'resource-' . strlen($title) . '-' . substr(md5($title), 0, 8);
        }
        
        return $slug;
    }
    
    // Migrate data from temp table to new table
    $stmt = $pdo->query("SELECT * FROM temp_resources_import ORDER BY resource_id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $slug = generateSlug($row['title']);
        
        // Check if slug exists and append number if needed
        $baseSlug = $slug;
        $counter = 1;
        while (true) {
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM resources_new WHERE slug = ?");
            $checkStmt->execute([$slug]);
            if ($checkStmt->fetchColumn() == 0) break;
            $slug = $baseSlug . '-' . $counter++;
        }
        
        try {
            $insertStmt = $pdo->prepare("
                INSERT INTO resources_new (
                    id, user_id, title, slug, content,
                    visibility, status, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?,
                    'public', 'published', ?, ?
                )
            ");
            
            $insertStmt->execute([
                $row['resource_id'],
                $row['user_id'],
                $row['title'],
                $slug,
                $row['content'] ?? '',
                $row['created_at'],
                $row['updated_at']
            ]);
        } catch (PDOException $e) {
            echo "Warning: Failed to migrate record {$row['resource_id']}: " . $e->getMessage() . "\n";
            continue;
        }
    }
    
    // Rename new table to resources
    $pdo->exec("RENAME TABLE resources_new TO resources");
    
    // Drop temporary table
    $pdo->exec("DROP TABLE IF EXISTS temp_resources_import");
    
    // Re-enable foreign key checks
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    
    echo "Migration completed successfully!\n";
    
} catch (PDOException $e) {
    // Re-enable foreign key checks even if there's an error
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 