<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>프로필 완성 - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="profile-complete-form">
            <h1>프로필 완성</h1>
            <p>서비스를 이용하기 위해 추가 정보를 입력해주세요.</p>
            
            <form id="profileCompleteForm" method="POST" action="/profile/complete">
                <div class="form-group">
                    <label for="name">이름</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="bio">자기소개</label>
                    <textarea id="bio" name="bio" rows="4"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="interests">관심사</label>
                    <input type="text" id="interests" name="interests" value="<?= htmlspecialchars($user['interests'] ?? '') ?>" placeholder="쉼표로 구분하여 입력">
                </div>
                
                <div class="form-group">
                    <label for="notification_preferences">알림 설정</label>
                    <div class="checkbox-group">
                        <label>
                            <input type="checkbox" name="notification_preferences[]" value="email" checked>
                            이메일 알림
                        </label>
                        <label>
                            <input type="checkbox" name="notification_preferences[]" value="push" checked>
                            푸시 알림
                        </label>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">프로필 완성</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('profileCompleteForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            try {
                const formData = new FormData(this);
                const response = await fetch('/profile/complete', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    window.location.href = '/dashboard';
                } else {
                    alert(result.error || '프로필 저장 중 오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('프로필 저장 중 오류가 발생했습니다.');
            }
        });
    </script>
</body>
</html> 