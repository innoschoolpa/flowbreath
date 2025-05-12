<?php

require_once __DIR__ . '/../../config/database.php';

try {
    $db = \Config\Database::getInstance();
    $pdo = $db->getConnection();

    // 샘플 리소스 데이터
    $resources = [
        [
            'user_id' => 1, // 관리자
            'title' => 'PHP 기초 튜토리얼',
            'slug' => 'php-basic-tutorial',
            'content' => '# PHP 기초 튜토리얼

PHP는 서버 사이드 스크립팅 언어입니다. 이 튜토리얼에서는 PHP의 기본 문법과 사용법을 배워보겠습니다.

## 1. PHP 시작하기

```php
<?php
echo "Hello, World!";
?>
```

## 2. 변수와 데이터 타입

PHP는 동적 타입 언어입니다. 변수는 $ 기호로 시작합니다.

```php
$name = "John";
$age = 25;
$isStudent = true;
```

## 3. 배열

PHP에서 배열을 사용하는 방법입니다.

```php
$fruits = ["apple", "banana", "orange"];
$person = [
    "name" => "John",
    "age" => 25
];
```',
            'description' => 'PHP 프로그래밍 언어의 기초를 배우는 튜토리얼입니다.',
            'visibility' => 'public',
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s')
        ],
        [
            'user_id' => 2, // 테스트 사용자
            'title' => 'JavaScript ES6+ 기능',
            'slug' => 'javascript-es6-features',
            'content' => '# JavaScript ES6+ 기능

ES6(ECMAScript 2015)부터 추가된 주요 기능들을 살펴보겠습니다.

## 1. 화살표 함수

```javascript
const add = (a, b) => a + b;
```

## 2. 템플릿 리터럴

```javascript
const name = "John";
console.log(`Hello, ${name}!`);
```

## 3. 구조 분해 할당

```javascript
const person = { name: "John", age: 25 };
const { name, age } = person;
```',
            'description' => 'JavaScript ES6 이상 버전에서 추가된 주요 기능들을 설명합니다.',
            'visibility' => 'public',
            'status' => 'published',
            'published_at' => date('Y-m-d H:i:s')
        ]
    ];

    // 리소스 삽입
    $stmt = $pdo->prepare("
        INSERT INTO resources (
            user_id, title, slug, content, description, 
            visibility, status, published_at
        ) VALUES (
            :user_id, :title, :slug, :content, :description,
            :visibility, :status, :published_at
        )
    ");
    
    foreach ($resources as $resource) {
        $stmt->execute($resource);
        $resourceId = $pdo->lastInsertId();
        echo "리소스 '{$resource['title']}' 이(가) 생성되었습니다.\n";

        // 태그 연결
        if ($resource['slug'] === 'php-basic-tutorial') {
            $pdo->exec("INSERT INTO resource_tags (resource_id, tag_id) VALUES ($resourceId, 1)"); // PHP
            $pdo->exec("INSERT INTO resource_tags (resource_id, tag_id) VALUES ($resourceId, 7)"); // Laravel
        } else {
            $pdo->exec("INSERT INTO resource_tags (resource_id, tag_id) VALUES ($resourceId, 2)"); // JavaScript
            $pdo->exec("INSERT INTO resource_tags (resource_id, tag_id) VALUES ($resourceId, 8)"); // Vue.js
        }
    }

    echo "모든 리소스가 성공적으로 생성되었습니다.\n";

} catch (PDOException $e) {
    die("리소스 생성 실패: " . $e->getMessage() . "\n");
} 