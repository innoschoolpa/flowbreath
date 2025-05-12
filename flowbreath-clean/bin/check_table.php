<?php

try {
    $pdo = new PDO(
        'mysql:host=srv636.hstgr.io;dbname=u573434051_flowbreath',
        'u573434051_flow',
        'Eduispa1712!',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    $stmt = $pdo->query('SHOW CREATE TABLE tags');
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    print_r($row);
} catch(PDOException $e) {
    echo $e->getMessage() . "\n";
} 