<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>프로필 완성 - FlowBreath</title>
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="/" class="logo">
                    <img src="/assets/images/logo.png" alt="FlowBreath">
                </a>
                <nav class="main-nav">
                    <ul>
                        <li><a href="/">홈</a></li>
                        <li><a href="/about">소개</a></li>
                        <li><a href="/features">기능</a></li>
                        <li><a href="/contact">문의하기</a></li>
                    </ul>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
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
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">프로필 완성</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>FlowBreath</h3>
                    <p>당신의 일상을 더 건강하게</p>
                </div>
                <div class="footer-section">
                    <h3>바로가기</h3>
                    <ul>
                        <li><a href="/">홈</a></li>
                        <li><a href="/about">소개</a></li>
                        <li><a href="/features">기능</a></li>
                        <li><a href="/contact">문의하기</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3>고객지원</h3>
                    <ul>
                        <li><a href="/faq">자주 묻는 질문</a></li>
                        <li><a href="/privacy">개인정보처리방침</a></li>
                        <li><a href="/terms">이용약관</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> FlowBreath. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
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