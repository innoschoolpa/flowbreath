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

/**
 * User 모델 클래스
 * users 테이블 관련 데이터베이스 작업을 처리합니다.
 */
class User extends BaseModel {
    protected string $table = 'users';
    protected array $fillable = [
        'email', 'password', 'name', 'is_admin', 'username', 'role', 
        'google_id', 'created_at', 'last_login'
    ];

    /**
     * PDO 데이터베이스 연결 객체
     * @var PDO
     */
    private $pdo;

    /**
     * 생성자
     * @param PDO $pdo 데이터베이스 연결 객체 주입
     */
    public function __construct(PDO $pdo) {
        parent::__construct($pdo);
    }

    /**
     * 이메일 또는 사용자 이름으로 사용자 정보 조회
     * @param string $identifier 이메일 주소 또는 사용자 이름
     * @return array|null 사용자 정보 배열 또는 null (사용자 없음)
     */
    public function findByEmailOrUsername(string $identifier): ?array {
        try {
            $sql = "SELECT * FROM users WHERE email = :identifier OR username = :identifier LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error in findByEmailOrUsername: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 사용자 ID로 사용자 정보 조회
     * @param int $userId 사용자 ID
     * @return array|null 사용자 정보 배열 또는 null
     */
    public function findById(int $userId): ?array {
        try {
            $sql = "SELECT user_id, username, email, role, google_id, created_at, last_login 
                    FROM users 
                    WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error in findById: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Google ID로 사용자 정보 조회
     * @param string $googleId Google 사용자 고유 ID
     * @return array|null 사용자 정보 배열 또는 null
     */
    public function findByGoogleId(string $googleId): ?array {
        try {
            $sql = "SELECT * FROM users WHERE google_id = :google_id LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':google_id', $googleId, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            error_log("Error in findByGoogleId: " . $e->getMessage());
            return null;
        }
    }

    /**
     * 새로운 사용자 생성 (회원가입 또는 Google 최초 로그인 시)
     * @param string $username 사용자 이름
     * @param string $email 이메일 주소
     * @param string|null $passwordHash 해시된 비밀번호 (Google 가입 시 null 가능)
     * @param string $role 사용자 역할 (기본값 'user')
     * @param string|null $googleId Google ID (Google 가입 시 제공)
     * @return int|false 생성된 사용자의 ID 또는 실패 시 false
     */
    public function createUser(
        string $username,
        string $email,
        ?string $passwordHash,
        string $role = 'user',
        ?string $googleId = null
    ): int|false {
        try {
            $sql = "INSERT INTO users (username, email, password_hash, role, google_id, created_at, last_login)
                    VALUES (:username, :email, :password_hash, :role, :google_id, NOW(), NULL)";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(
                ':password_hash',
                $passwordHash,
                $passwordHash === null ? PDO::PARAM_NULL : PDO::PARAM_STR
            );
            $stmt->bindParam(':role', $role);
            $stmt->bindParam(
                ':google_id',
                $googleId,
                $googleId === null ? PDO::PARAM_NULL : PDO::PARAM_STR
            );

            return $stmt->execute() ? (int)$this->pdo->lastInsertId() : false;
        } catch (PDOException $e) {
            error_log("Error in createUser: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 기존 사용자의 Google ID 업데이트 (이메일로 찾은 사용자를 Google 계정과 연결 시)
     * @param int $userId 사용자 ID
     * @param string $googleId 연결할 Google ID
     * @return bool 성공 여부
     */
    public function updateGoogleId(int $userId, string $googleId): bool {
        try {
            $sql = "UPDATE users SET google_id = :google_id WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':google_id', $googleId, PDO::PARAM_STR);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in updateGoogleId: " . $e->getMessage());
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
            $sql = "UPDATE users SET last_login = NOW() WHERE user_id = :user_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error in updateLastLogin: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 이메일로 사용자 찾기
     */
    public function findByEmail($email) {
        try {
            $stmt = $this->pdo->prepare("SELECT * FROM {$this->table} WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $email]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in findByEmail: " . $e->getMessage());
            throw new Exception("사용자를 찾는 중 오류가 발생했습니다.");
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

            if (!password_verify($password, $user['password'])) {
                return false;
            }

            // 마지막 로그인 시간 업데이트
            $stmt = $this->pdo->prepare("UPDATE {$this->table} SET last_login_at = NOW() WHERE id = :id");
            $stmt->execute(['id' => $user['id']]);

            return $user;
        } catch (PDOException $e) {
            error_log("Error in attemptLogin: " . $e->getMessage());
            throw new Exception("로그인 처리 중 오류가 발생했습니다.");
        }
    }

    /**
     * 사용자 생성
     */
    public function create(array $data) {
        try {
            // 이메일 중복 체크
            if ($this->findByEmail($data['email'])) {
                throw new Exception("이미 사용 중인 이메일입니다.");
            }

            // 비밀번호 해시화
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

            return parent::create($data);
        } catch (PDOException $e) {
            error_log("Error in create: " . $e->getMessage());
            throw new Exception("사용자 생성 중 오류가 발생했습니다.");
        }
    }

    /**
     * 사용자 정보 업데이트
     */
    public function update($id, array $data) {
        try {
            // 비밀번호가 있는 경우에만 해시화
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            return parent::update($id, $data);
        } catch (PDOException $e) {
            error_log("Error in update: " . $e->getMessage());
            throw new Exception("사용자 정보 업데이트 중 오류가 발생했습니다.");
        }
    }

    // --- 필요한 경우 다른 메소드 추가 ---
    // 예: 사용자 정보 수정, 비밀번호 변경, 사용자 삭제 등
    // public function updateUserProfile(...) { ... }
    // public function changePassword(...) { ... }
    // public function deleteUser(...) { ... }

}
?>