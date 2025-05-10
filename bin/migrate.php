<?php

define('ROOT_PATH', realpath(__DIR__ . '/..'));

require_once __DIR__ . '/../vendor/autoload.php';

// 환경 변수 로드
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            putenv(trim($key) . '=' . trim($value));
        }
    }
}

// 데이터베이스 연결
require_once __DIR__ . '/../config/database.php';

// Database 클래스의 인스턴스를 가져옵니다.
$db = \Config\Database::getInstance()->getConnection();

// 마이그레이션 매니저 인스턴스 생성
$manager = new \App\Database\MigrationManager($db, __DIR__ . '/../database/migrations');

// 커맨드라인 인자 처리
$command = $argv[1] ?? 'help';

switch ($command) {
    case 'migrate':
        $manager->runMigrations();
        break;

    case 'rollback':
        $manager->rollbackMigrations();
        break;

    case 'create':
        if (!isset($argv[2])) {
            die("마이그레이션 이름을 지정해주세요.\n");
        }

        $name = $argv[2];
        $timestamp = date('Y_m_d_His');
        $filename = $timestamp . '_' . strtolower($name) . '.php';
        $path = __DIR__ . '/../database/migrations/' . $filename;

        // 마이그레이션 파일 템플릿
        $template = <<<PHP
<?php

namespace App\Database\Migrations;

use App\Database\Migration;

class {$name} extends Migration {
    protected \$tableName = ''; // 테이블 이름을 지정하세요

    public function up() {
        \$sql = "CREATE TABLE IF NOT EXISTS {\$this->tableName} (
            id INT AUTO_INCREMENT PRIMARY KEY,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        
        \$this->createTable(\$sql);
    }

    public function down() {
        \$this->dropTable();
    }
}
PHP;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $template);
        echo "마이그레이션 파일 생성됨: {$filename}\n";
        break;

    case 'help':
    default:
        echo <<<HELP
사용법:
  php bin/migrate.php <command> [options]

사용 가능한 명령어:
  migrate   : 보류 중인 모든 마이그레이션을 실행합니다
  rollback  : 마지막 마이그레이션 배치를 롤백합니다
  create    : 새 마이그레이션 파일을 생성합니다
  help      : 이 도움말을 표시합니다

예시:
  php bin/migrate.php create CreateUsersTable
  php bin/migrate.php migrate
  php bin/migrate.php rollback

HELP;
        break;
} 