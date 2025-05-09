<?php

namespace App\Services;

use App\Models\User;
use App\Core\Session;
use Exception;

class LoginService {
    private User $user;
    private Session $session;
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOCKOUT_DURATION = 1800; // 30 minutes

    public function __construct(User $user, Session $session) {
        $this->user = $user;
        $this->session = $session;
    }

    public function attemptLogin(string $email, string $password): array {
        try {
            error_log("Login attempt for email: " . $email);
            
            // 1. 입력값 검증
            if (empty($email) || empty($password)) {
                error_log("Empty email or password");
                return [
                    'success' => false,
                    'error' => '이메일과 비밀번호를 모두 입력해주세요.',
                    'details' => 'Empty email or password'
                ];
            }

            // 2. 사용자 조회
            $user = $this->user->findByEmail($email);
            error_log("User lookup result: " . ($user ? "Found" : "Not found"));
            
            if (!$user) {
                error_log("No user found with email: " . $email);
                return [
                    'success' => false,
                    'error' => '등록되지 않은 이메일입니다.',
                    'details' => 'User not found'
                ];
            }

            // 3. 계정 상태 확인
            if ($user['status'] !== 'active') {
                error_log("Account is not active. Status: " . $user['status']);
                return [
                    'success' => false,
                    'error' => '비활성화된 계정입니다. 관리자에게 문의해주세요.',
                    'details' => 'Account is not active'
                ];
            }

            // 4. 계정 잠금 확인
            if (!empty($user['locked_until'])) {
                $lockedUntil = strtotime($user['locked_until']);
                if ($lockedUntil > time()) {
                    error_log("Account is locked until: " . $user['locked_until']);
                    return [
                        'success' => false,
                        'error' => '계정이 잠겨있습니다. ' . date('Y-m-d H:i:s', $lockedUntil) . ' 이후에 다시 시도해주세요.',
                        'details' => 'Account is locked'
                    ];
                }
            }

            // 5. 비밀번호 검증
            error_log("Attempting password verification");
            
            // 비밀번호 해시 검증
            if (empty($user['password'])) {
                error_log("Error: No password hash found for user");
                return [
                    'success' => false,
                    'error' => '비밀번호 검증에 실패했습니다.',
                    'details' => 'No password hash found'
                ];
            }
            
            // 비밀번호 해시 정보 로깅
            $hashInfo = password_get_info($user['password']);
            error_log("Password hash info: " . json_encode($hashInfo));
            
            // 비밀번호 검증 전 해시 형식 확인
            if ($hashInfo['algo'] === 0) {
                error_log("Warning: Invalid password hash format");
                return [
                    'success' => false,
                    'error' => '비밀번호 검증에 실패했습니다.',
                    'details' => 'Invalid password hash format'
                ];
            }
            
            // 비밀번호 검증 시도
            $passwordVerified = password_verify($password, $user['password']);
            error_log("Password verification result: " . ($passwordVerified ? "success" : "failed"));
            
            if (!$passwordVerified) {
                error_log("Password verification failed");
                error_log("Hash info: " . json_encode($hashInfo));
                
                // 실패한 로그인 시도 증가
                $this->user->incrementFailedLoginAttempts($user['id']);
                
                // 계정 잠금 처리
                if ($user['failed_login_attempts'] + 1 >= self::MAX_LOGIN_ATTEMPTS) {
                    $this->user->lockAccount($user['id'], self::LOCKOUT_DURATION);
                }
                
                return [
                    'success' => false,
                    'error' => '비밀번호가 일치하지 않습니다.',
                    'details' => 'Password verification failed'
                ];
            }

            // 6. 로그인 성공 처리
            $this->handleSuccessfulLogin($user);
            
            return [
                'success' => true,
                'redirect' => '/'
            ];

        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            return [
                'success' => false,
                'error' => '로그인 처리 중 오류가 발생했습니다. 잠시 후 다시 시도해주세요.',
                'details' => $e->getMessage()
            ];
        }
    }

    private function handleSuccessfulLogin(array $user): void {
        // 세션 ID 재생성
        session_regenerate_id(true);

        // 세션 데이터 설정
        $this->session->set('user_id', $user['id']);
        $this->session->set('username', $user['name'] ?? $user['email']);
        $this->session->set('email', $user['email']);
        $this->session->set('role', $user['role'] ?? 'user');
        $this->session->set('last_activity', time());
        $this->session->set('_last_regeneration', time());
        $this->session->set('login_timestamp', time());

        // 마지막 로그인 시간 업데이트
        $this->user->updateLastLogin($user['id']);

        // 실패한 로그인 시도 초기화
        $this->user->resetFailedLoginAttempts($user['id']);

        // 로그인 성공 로깅
        error_log("Login successful for user: " . $user['id']);
        error_log("Session data after login: " . json_encode([
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'] ?? 'user'
        ]));
    }
} 