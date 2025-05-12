<?php
// src/Model/User.php

// 네임스페이스 사용 시 (composer.json에 App\\: src/ 설정 필요)
// namespace App\Model;
// use PDO;
// use PDOException;

declare(strict_types=1);

namespace App\Models;

use PDO;
use PDOException;
use Exception;
use App\Models\BaseModel;
use App\Core\Model;
use App\Core\Database;

/**
 * User 모델 클래스
 * users 테이블 관련 데이터베이스 작업을 처리합니다.
 */
class User extends Model {
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password', 'role', 'google_id', 'status', 'bio', 'profile_image'];

    /**
     * 생성자
     * @param \App\Core\Database $db 데이터베이스 객체 주입
     */
    public function __construct($db = null) {
        parent::__construct($db ?: Database::getInstance());
    }

    /**
     * 이메일 또는 사용자 이름으로 사용자 정보 조회
     * @param string $identifier 이메일 주소 또는 사용자 이름
     * @return array|null 사용자 정보 배열 또는 null (사용자 없음)
     */
    public function findByEmailOrUsername(string $identifier): ?array {
        try {
            $sql = "SELECT * FROM users WHERE (email = :identifier OR name = :identifier) AND status = 'active' LIMIT 1";
            return $this->db->fetch($sql, ['identifier' => $identifier]);
        } catch (PDOException $e) {
            error_log("Error in findByEmailOrUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 사용자 ID로 사용자 정보 조회
     * @param int $id 사용자 ID
     * @return array|null 사용자 정보 배열 또는 null
     */
    public function findById(int $id): ?array
    {
        try {
            // 디버그 로그 추가
            error_log("Finding user with ID: " . $id);
            
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, \PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            // 디버그 로그 추가
            error_log("Query result: " . ($result ? json_encode($result) : 'null'));
            
            return $result ?: null;
        } catch (\PDOException $e) {
            // 오류 로그 추가
            error_log("Error in findById: " . $e->getMessage());
            error_log("SQL: " . $sql);
            error_log("Parameters: " . print_r(['id' => $id], true));
            error_log("Stack trace: " . $e->getTraceAsString());
            throw new \Exception('사용자를 찾는 중 오류가 발생했습니다: ' . $e->getMessage());
        }
    }

    /**
     * Google ID로 사용자 정보 조회
     * @param string $googleId Google 사용자 고유 ID
     * @return array|null 사용자 정보 배열 또는 null
     */
    public function findByGoogleId(string $googleId): ?array {
        try {
            $sql = "SELECT * FROM users WHERE google_id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$googleId]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (\PDOException $e) {
            error_log("Error in findByGoogleId: " . $e->getMessage());
            throw new \Exception('사용자를 찾는 중 오류가 발생했습니다.');
        }
    }

    /**
     * 데이터베이스 연결 상태 확인
     */
    private function checkDatabaseConnection(): bool {
        try {
            echo "Checking database connection...\n";
            $this->db->query("SELECT 1");
            echo "Database connection successful.\n";
            return true;
        } catch (\Exception $e) {
            echo "Database connection failed: " . $e->getMessage() . "\n";
            error_log("Database connection check failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 새로운 사용자 생성 (회원가입 또는 Google 최초 로그인 시)
     * @param array $data 사용자 데이터
     * @return int|false 생성된 사용자의 ID 또는 실패 시 false
     */
    public function createUser(array $data): int|false
    {
        try {
            echo "\n=== Starting User Creation Process ===\n";
            
            // Check database connection first
            if (!$this->checkDatabaseConnection()) {
                echo "Cannot proceed with user creation due to database connection failure.\n";
                return false;
            }

            echo "Creating user with:\n";
            echo "- Name: " . $data['name'] . "\n";
            echo "- Email: " . $data['email'] . "\n";
            echo "- Google ID: " . ($data['google_id'] ?? 'none') . "\n";
            
            // Start transaction
            echo "Starting database transaction...\n";
            $this->db->beginTransaction();
            
            try {
                // Check if email already exists
                echo "Checking for existing email...\n";
                $existingUser = $this->findByEmail($data['email']);
                if ($existingUser) {
                    echo "Email already exists in the system.\n";
                    $this->db->rollBack();
                    return false;
                }
                echo "Email is available.\n";

                // Check if Google ID already exists
                if (!empty($data['google_id'])) {
                    echo "Checking for existing Google ID...\n";
                    $existingGoogleUser = $this->findByGoogleId($data['google_id']);
                    if ($existingGoogleUser) {
                        echo "Google ID already exists in the system.\n";
                        $this->db->rollBack();
                        return false;
                    }
                    echo "Google ID is available.\n";
                }

                // Prepare user data
                echo "Preparing user data...\n";
                $currentTime = date('Y-m-d H:i:s');
                $userData = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => isset($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null,
                    'role' => $data['role'] ?? 'user',
                    'google_id' => $data['google_id'] ?? null,
                    'profile_image' => $data['avatar'] ?? null,
                    'status' => $data['status'] ?? 'active',
                    'email_verified_at' => !empty($data['google_id']) ? $currentTime : null,
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
                    'last_login_at' => $currentTime,
                    'login_count' => 1,
                    'failed_login_attempts' => 0,
                    'locked_until' => null,
                    'bio' => null,
                    'last_password_change' => isset($data['password']) ? $currentTime : null
                ];

                // Insert user data
                echo "Inserting user data into database...\n";
                $sql = "INSERT INTO users (
                    name, email, password, role, google_id, status,
                    email_verified_at, created_at, updated_at, last_login_at,
                    login_count, failed_login_attempts, locked_until,
                    bio, last_password_change, profile_image
                ) VALUES (
                    :name, :email, :password, :role, :google_id, :status,
                    :email_verified_at, :created_at, :updated_at, :last_login_at,
                    :login_count, :failed_login_attempts, :locked_until,
                    :bio, :last_password_change, :profile_image
                )";
                
                echo "Executing SQL with data: " . json_encode($userData) . "\n";
                $result = $this->db->query($sql, $userData);
                if (!$result) {
                    echo "Failed to insert user data.\n";
                    $this->db->rollBack();
                    return false;
                }

                $userId = $this->db->lastInsertId();
                if (!$userId) {
                    echo "Failed to get new user ID.\n";
                    $this->db->rollBack();
                    return false;
                }
                echo "User created with ID: " . $userId . "\n";

                // Commit transaction
                echo "Committing transaction...\n";
                $this->db->commit();
                echo "User creation completed successfully.\n";
                return (int)$userId;
            } catch (\Exception $e) {
                echo "Error during user creation: " . $e->getMessage() . "\n";
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            echo "Fatal error in user creation: " . $e->getMessage() . "\n";
            error_log("Failed to create user: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * 기존 사용자의 Google ID 업데이트 (이메일로 찾은 사용자를 Google 계정과 연결 시)
     * @param int $userId 사용자 ID
     * @param string $googleId 연결할 Google ID
     * @param string|null $profileImage 프로필 이미지 URL
     * @return bool 성공 여부
     */
    public function updateGoogleId(int $userId, string $googleId, ?string $profileImage = null): bool {
        try {
            echo "\n=== Starting Google ID Update Process ===\n";
            
            // Check database connection first
            if (!$this->checkDatabaseConnection()) {
                echo "Cannot proceed with Google ID update due to database connection failure.\n";
                return false;
            }

            echo "Updating Google ID for user ID: " . $userId . "\n";
            echo "New Google ID: " . $googleId . "\n";
            
            // Start transaction
            echo "Starting database transaction...\n";
            $this->db->beginTransaction();
            
            try {
                // Check if Google ID is already used by another user
                echo "Checking for existing Google ID...\n";
                $existingUser = $this->findByGoogleId($googleId);
                if ($existingUser && $existingUser['id'] !== $userId) {
                    echo "Google ID is already in use by another user.\n";
                    $this->db->rollBack();
                    return false;
                }
                echo "Google ID is available.\n";

                // First update basic info
                echo "Updating user information...\n";
                $data = [
                    'google_id' => $googleId,
                    'email_verified_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                    'id' => $userId
                ];

                $sql = "UPDATE users SET 
                        google_id = :google_id,
                        email_verified_at = :email_verified_at,
                        updated_at = :updated_at
                        WHERE id = :id";
                        
                $result = $this->db->query($sql, $data);
                $success = $result && $result->rowCount() > 0;
                
                if (!$success) {
                    echo "Failed to update Google ID.\n";
                    $this->db->rollBack();
                    return false;
                }
                echo "User information updated successfully.\n";

                // If profile image exists, update it separately
                if ($profileImage) {
                    echo "Updating profile image...\n";
                    $updateSql = "UPDATE users SET profile_image = :profile_image WHERE id = :id";
                    $updateResult = $this->db->query($updateSql, [
                        'profile_image' => $profileImage,
                        'id' => $userId
                    ]);
                    
                    if (!$updateResult) {
                        echo "Warning: Failed to update profile image.\n";
                    } else {
                        echo "Profile image updated successfully.\n";
                    }
                }

                echo "Committing transaction...\n";
                $this->db->commit();
                echo "Google ID update completed successfully.\n";
                return true;
            } catch (\Exception $e) {
                echo "Error during Google ID update: " . $e->getMessage() . "\n";
                $this->db->rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            echo "Fatal error in Google ID update: " . $e->getMessage() . "\n";
            error_log("Failed to update Google ID: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * 마지막 로그인 타임스탬프 업데이트
     * @param int $userId 사용자 ID
     * @return bool 성공 여부
     */
    public function updateLastLogin(int $userId): bool {
        try {
            $data = [
                'last_login_at' => date('Y-m-d H:i:s'),
                'login_count' => 1,
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'id' => $userId
            ];

            $sql = "UPDATE users SET 
                    last_login_at = :last_login_at,
                    login_count = login_count + :login_count,
                    failed_login_attempts = :failed_login_attempts,
                    locked_until = :locked_until,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = :id";
                    
            $result = $this->db->query($sql, $data);
            $success = $result && $result->rowCount() > 0;
            
            if ($success) {
                error_log("Last login updated successfully for user: " . $userId);
            } else {
                error_log("Failed to update last login for user: " . $userId);
            }
            
            return $success;
        } catch (\Exception $e) {
            error_log("Failed to update last login: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * 이메일로 사용자 찾기
     */
    public function findByEmail($email) {
        try {
            error_log("Finding user by email: " . $email);
            $sql = "SELECT * FROM users WHERE email = :email AND status = 'active'";
            $result = $this->db->fetch($sql, ['email' => $email]);
            error_log("User found: " . ($result ? "yes" : "no"));
            return $result;
        } catch (\Exception $e) {
            error_log("Failed to find user by email: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * 로그인 시도
     */
    public function attemptLogin($email, $password) {
        try {
            $user = $this->findByEmail($email);
            if (!$user) {
                return false;
            }

            // Check if account is locked
            if ($user['locked_until'] && strtotime($user['locked_until']) > time()) {
                return false;
            }

            // Verify password
            if (!password_verify($password, $user['password'])) {
                // Increment failed login attempts
                $this->incrementFailedLoginAttempts($user['id']);
                return false;
            }

            // Reset failed login attempts and update last login
            $this->updateLastLogin($user['id']);
            return $user;
        } catch (\Exception $e) {
            error_log("Failed to attempt login: " . $e->getMessage());
            return false;
        }
    }

    private function incrementFailedLoginAttempts($userId)
    {
        try {
            $sql = "UPDATE users SET 
                    failed_login_attempts = failed_login_attempts + 1,
                    locked_until = CASE 
                        WHEN failed_login_attempts + 1 >= 5 THEN DATE_ADD(NOW(), INTERVAL 30 MINUTE)
                        ELSE NULL 
                    END
                    WHERE id = :id";
            $this->db->query($sql, ['id' => $userId]);
        } catch (\Exception $e) {
            error_log("Failed to increment failed login attempts: " . $e->getMessage());
        }
    }

    /**
     * 사용자 생성
     */
    public function create(array $data): ?int
    {
        try {
            $this->db->beginTransaction();

            $fields = array_keys($data);
            $values = array_values($data);
            $placeholders = array_fill(0, count($fields), '?');

            $sql = sprintf(
                "INSERT INTO users (%s) VALUES (%s)",
                implode(', ', $fields),
                implode(', ', $placeholders)
            );

            $stmt = $this->db->prepare($sql);
            $stmt->execute($values);
            
            $userId = (int)$this->db->lastInsertId();
            
            $this->db->commit();
            return $userId;
        } catch (\PDOException $e) {
            $this->db->rollBack();
            error_log("Failed to create user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 사용자 정보 업데이트
     * @param int $id 사용자 ID
     * @param array $data 업데이트할 데이터
     * @return array|null 업데이트된 사용자 정보 또는 null
     */
    public function update(int $id, array $data): ?array
    {
        try {
            // 업데이트할 필드와 값 준비
            $fields = [];
            $values = [];
            
            foreach ($data as $key => $value) {
                if (in_array($key, $this->fillable)) {
                    $fields[] = "`$key` = :$key";
                    $values[$key] = $value;
                }
            }
            
            if (empty($fields)) {
                return null;
            }
            
            // updated_at 필드 추가
            $fields[] = "`updated_at` = CURRENT_TIMESTAMP";
            
            // SQL 쿼리 생성
            $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = :id";
            $values['id'] = $id;
            
            // 쿼리 실행
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute($values);
            
            if (!$result) {
                error_log("Error updating user: " . json_encode($stmt->errorInfo()));
                return null;
            }
            
            // 업데이트된 사용자 정보 조회
            return $this->findById($id);
        } catch (\PDOException $e) {
            error_log("Database error in User::update: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the user's resources
     */
    public function resources()
    {
        $sql = "SELECT * FROM resources WHERE user_id = :user_id";
        return $this->db->fetchAll($sql, ['user_id' => $this->getAuthIdentifier()]);
    }

    /**
     * Get the user's comments
     */
    public function comments()
    {
        $sql = "SELECT * FROM comments WHERE user_id = :user_id";
        return $this->db->fetchAll($sql, ['user_id' => $this->getAuthIdentifier()]);
    }

    /**
     * Get the user's likes
     */
    public function likes()
    {
        $sql = "SELECT * FROM likes WHERE user_id = :user_id";
        return $this->db->fetchAll($sql, ['user_id' => $this->getAuthIdentifier()]);
    }

    /**
     * Get the user's ID
     */
    public function getAuthIdentifier()
    {
        return $this->id ?? null;
    }

    /**
     * Get the name of the unique identifier for the user
     */
    public function getAuthIdentifierName()
    {
        return 'id';
    }

    /**
     * Get the password for the user
     */
    public function getAuthPassword()
    {
        return $this->password ?? null;
    }

    /**
     * Get the remember token for the user
     */
    public function getRememberToken()
    {
        $sql = "SELECT remember_token FROM users WHERE id = :id";
        $result = $this->db->fetch($sql, ['id' => $this->getAuthIdentifier()]);
        return $result['remember_token'] ?? null;
    }

    /**
     * Set the remember token for the user
     */
    public function setRememberToken($value)
    {
        $sql = "UPDATE users SET remember_token = :token WHERE id = :id";
        return $this->db->query($sql, [
            'token' => $value,
            'id' => $this->getAuthIdentifier()
        ]);
    }

    /**
     * Get the column name for the remember token
     */
    public function getRememberTokenName()
    {
        return 'remember_token';
    }

    // --- 필요한 경우 다른 메소드 추가 ---
    // 예: 사용자 정보 수정, 비밀번호 변경, 사용자 삭제 등
    // public function updateUserProfile(...) { ... }
    // public function changePassword(...) { ... }
    // public function deleteUser(...) { ... }

}
?>