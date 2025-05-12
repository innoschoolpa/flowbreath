<?php $currentPage = 'settings'; ?>

<div class="container">
    <div class="settings-grid">
        <div class="card">
            <div class="card-header">
                <h2>시스템 설정</h2>
            </div>
            <form id="systemSettingsForm" class="settings-form">
                <div class="form-group">
                    <label class="form-label">메모리 제한</label>
                    <input type="text" class="form-input" name="memory_limit" value="<?php echo $settings['memory_limit']; ?>">
                    <div class="form-help">PHP 메모리 제한 (예: 256M, 1G)</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">최대 실행 시간</label>
                    <input type="number" class="form-input" name="max_execution_time" value="<?php echo $settings['max_execution_time']; ?>">
                    <div class="form-help">스크립트 최대 실행 시간 (초)</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">업로드 제한</label>
                    <input type="text" class="form-input" name="upload_max_filesize" value="<?php echo $settings['upload_max_filesize']; ?>">
                    <div class="form-help">최대 파일 업로드 크기 (예: 10M, 1G)</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">오류 표시</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="display_errors" <?php echo $settings['display_errors'] ? 'checked' : ''; ?>>
                            오류 메시지 표시
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">오류 로깅</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="log_errors" <?php echo $settings['log_errors'] ? 'checked' : ''; ?>>
                            오류 로그 기록
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <button type="button" class="btn btn-secondary" id="resetSystem">기본값으로 재설정</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>데이터베이스 설정</h2>
            </div>
            <form id="dbSettingsForm" class="settings-form">
                <div class="form-group">
                    <label class="form-label">최대 연결 수</label>
                    <input type="number" class="form-input" name="max_connections" value="<?php echo $settings['max_connections']; ?>">
                    <div class="form-help">동시 데이터베이스 연결 제한</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">연결 타임아웃</label>
                    <input type="number" class="form-input" name="connection_timeout" value="<?php echo $settings['connection_timeout']; ?>">
                    <div class="form-help">데이터베이스 연결 타임아웃 (초)</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">쿼리 캐시</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="query_cache_enabled" <?php echo $settings['query_cache_enabled'] ? 'checked' : ''; ?>>
                            쿼리 결과 캐싱 활성화
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">캐시 크기</label>
                    <input type="number" class="form-input" name="query_cache_size" value="<?php echo $settings['query_cache_size']; ?>">
                    <div class="form-help">쿼리 캐시 최대 크기 (MB)</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <button type="button" class="btn btn-secondary" id="resetDB">기본값으로 재설정</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>보안 설정</h2>
            </div>
            <form id="securitySettingsForm" class="settings-form">
                <div class="form-group">
                    <label class="form-label">SQL 검증</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="sql_validation" <?php echo $settings['sql_validation'] ? 'checked' : ''; ?>>
                            SQL 구문 검증 활성화
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">위험한 명령어</label>
                    <textarea class="form-input" name="dangerous_commands" rows="3"><?php echo htmlspecialchars($settings['dangerous_commands']); ?></textarea>
                    <div class="form-help">차단할 SQL 명령어 (줄바꿈으로 구분)</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">IP 차단</label>
                    <textarea class="form-input" name="blocked_ips" rows="3"><?php echo htmlspecialchars($settings['blocked_ips']); ?></textarea>
                    <div class="form-help">차단할 IP 주소 (줄바꿈으로 구분)</div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <button type="button" class="btn btn-secondary" id="resetSecurity">기본값으로 재설정</button>
                </div>
            </form>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>알림 설정</h2>
            </div>
            <form id="notificationSettingsForm" class="settings-form">
                <div class="form-group">
                    <label class="form-label">이메일 알림</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="email_notifications" <?php echo $settings['email_notifications'] ? 'checked' : ''; ?>>
                            이메일 알림 활성화
                        </label>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">알림 이메일</label>
                    <input type="email" class="form-input" name="notification_email" value="<?php echo htmlspecialchars($settings['notification_email']); ?>">
                    <div class="form-help">알림을 받을 이메일 주소</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">알림 설정</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="notify_on_error" <?php echo $settings['notify_on_error'] ? 'checked' : ''; ?>>
                            오류 발생 시 알림
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="notify_on_completion" <?php echo $settings['notify_on_completion'] ? 'checked' : ''; ?>>
                            실행 완료 시 알림
                        </label>
                        <label class="checkbox-label">
                            <input type="checkbox" name="notify_on_warning" <?php echo $settings['notify_on_warning'] ? 'checked' : ''; ?>>
                            경고 발생 시 알림
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">저장</button>
                    <button type="button" class="btn btn-secondary" id="resetNotifications">기본값으로 재설정</button>
                    <button type="button" class="btn btn-info" id="testEmail">테스트 이메일 전송</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: var(--spacing-lg);
}

.settings-form {
    padding: var(--spacing-lg);
}

.checkbox-group {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-sm);
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: var(--spacing-sm);
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 1rem;
    height: 1rem;
    border-radius: 0.25rem;
    border: 1px solid var(--border-color);
    cursor: pointer;
}

.form-actions {
    display: flex;
    gap: var(--spacing-sm);
    margin-top: var(--spacing-lg);
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 시스템 설정
    const systemSettingsForm = document.getElementById('systemSettingsForm');
    systemSettingsForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        await saveSettings('system', new FormData(systemSettingsForm));
    });
    
    // 데이터베이스 설정
    const dbSettingsForm = document.getElementById('dbSettingsForm');
    dbSettingsForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        await saveSettings('database', new FormData(dbSettingsForm));
    });
    
    // 보안 설정
    const securitySettingsForm = document.getElementById('securitySettingsForm');
    securitySettingsForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        await saveSettings('security', new FormData(securitySettingsForm));
    });
    
    // 알림 설정
    const notificationSettingsForm = document.getElementById('notificationSettingsForm');
    notificationSettingsForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        await saveSettings('notifications', new FormData(notificationSettingsForm));
    });
    
    // 설정 저장
    async function saveSettings(type, formData) {
        try {
            const response = await fetch(`/api/settings/${type}`, {
                method: 'POST',
                body: formData
            });
            
            if (!response.ok) throw new Error('저장 실패');
            
            showNotification('success', '설정이 저장되었습니다.');
        } catch (error) {
            showNotification('error', '설정 저장 중 오류가 발생했습니다.');
        }
    }
    
    // 설정 초기화
    document.getElementById('resetSystem').addEventListener('click', () => resetSettings('system'));
    document.getElementById('resetDB').addEventListener('click', () => resetSettings('database'));
    document.getElementById('resetSecurity').addEventListener('click', () => resetSettings('security'));
    document.getElementById('resetNotifications').addEventListener('click', () => resetSettings('notifications'));
    
    async function resetSettings(type) {
        if (!confirm('이 설정을 기본값으로 재설정하시겠습니까?')) return;
        
        try {
            const response = await fetch(`/api/settings/${type}/reset`, {
                method: 'POST'
            });
            
            if (!response.ok) throw new Error('재설정 실패');
            
            showNotification('success', '설정이 기본값으로 재설정되었습니다.');
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            showNotification('error', '설정 재설정 중 오류가 발생했습니다.');
        }
    }
    
    // 테스트 이메일
    document.getElementById('testEmail').addEventListener('click', async () => {
        try {
            const response = await fetch('/api/settings/notifications/test-email', {
                method: 'POST'
            });
            
            if (!response.ok) throw new Error('이메일 전송 실패');
            
            showNotification('success', '테스트 이메일이 전송되었습니다.');
        } catch (error) {
            showNotification('error', '테스트 이메일 전송 중 오류가 발생했습니다.');
        }
    });
});

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script> 