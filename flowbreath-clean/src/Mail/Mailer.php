<?php

namespace App\Mail;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mailer;
    private $config;

    public function __construct() {
        $this->config = require CONFIG_PATH . '/mail.php';
        $this->mailer = new PHPMailer(true);

        // SMTP 설정
        $this->mailer->isSMTP();
        $this->mailer->Host = $this->config['smtp']['host'];
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = $this->config['smtp']['username'];
        $this->mailer->Password = $this->config['smtp']['password'];
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = $this->config['smtp']['port'];
        $this->mailer->CharSet = 'UTF-8';

        // 기본 설정
        $this->mailer->setFrom($this->config['from']['address'], $this->config['from']['name']);
    }

    public function sendPasswordReset(string $email, string $token): bool {
        try {
            $resetUrl = $this->config['app_url'] . '/password/reset/' . $token;

            $this->mailer->addAddress($email);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = '[FlowBreath] 비밀번호 재설정';
            
            // HTML 본문
            $this->mailer->Body = <<<HTML
            <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
                <h2>비밀번호 재설정</h2>
                <p>안녕하세요,</p>
                <p>비밀번호 재설정을 요청하셨습니다. 아래 버튼을 클릭하여 새로운 비밀번호를 설정하실 수 있습니다.</p>
                <p style="margin: 30px 0;">
                    <a href="{$resetUrl}" 
                       style="background-color: #007bff; color: white; padding: 12px 24px; 
                              text-decoration: none; border-radius: 4px; display: inline-block;">
                        비밀번호 재설정
                    </a>
                </p>
                <p>이 링크는 1시간 동안만 유효합니다.</p>
                <p>비밀번호 재설정을 요청하지 않으셨다면 이 이메일을 무시하셔도 됩니다.</p>
                <hr style="margin: 30px 0; border: none; border-top: 1px solid #eee;">
                <p style="color: #666; font-size: 12px;">
                    본 메일은 발신 전용입니다. 문의사항이 있으시면 고객센터를 이용해 주세요.
                </p>
            </div>
            HTML;

            // 플레인 텍스트 본문
            $this->mailer->AltBody = <<<TEXT
            비밀번호 재설정

            비밀번호 재설정을 요청하셨습니다. 아래 링크를 통해 새로운 비밀번호를 설정하실 수 있습니다:

            {$resetUrl}

            이 링크는 1시간 동안만 유효합니다.

            비밀번호 재설정을 요청하지 않으셨다면 이 이메일을 무시하셔도 됩니다.
            TEXT;

            $this->mailer->send();
            return true;
        } catch (Exception $e) {
            error_log("Failed to send password reset email: " . $e->getMessage());
            return false;
        }
    }
} 