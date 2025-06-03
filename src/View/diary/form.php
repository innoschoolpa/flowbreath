<?php
require_once __DIR__ . '/../../helpers/lang.php';
$apiKeys = require __DIR__ . '/../../Config/api_keys.php';
$tinymceApiKey = $apiKeys['tinymce']['api_key'] ?? '';

// Set default values for new diary
$diary = $diary ?? [
    'id' => null,
    'title' => '',
    'content' => '',
    'tags' => '',
    'is_public' => true
];

$isEdit = isset($diary['id']);
$formAction = $isEdit ? "/diary/{$diary['id']}" : '/diary';
$pageTitle = $isEdit ? __('diary.edit') : __('diary.create');
$cancelUrl = $isEdit ? "/diary/{$diary['id']}" : '/diary';

// Prepare content for TinyMCE
$content = $diary['content'] ?? '';
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<style>
body {
    background: #101522 !important;
    min-height: 100vh;
}
.navbar-custom {
    background: #181c2f;
    border-bottom: 1px solid #23263a;
    padding: 0.75rem 0;
}
.navbar-custom .navbar-brand {
    color: #fff;
    font-weight: bold;
    font-size: 1.5rem;
    letter-spacing: 1px;
}
.navbar-custom .nav-link, .navbar-custom .navbar-text {
    color: #cfd8dc !important;
    font-weight: 500;
    margin-right: 1rem;
}
.navbar-custom .nav-link.active {
    color: #4fc3f7 !important;
}

.footer-custom {
    background: #181c2f;
    color: #b0b8c1;
    font-size: 0.95rem;
    padding: 2rem 0 1rem 0;
    text-align: center;
    border-top: 1px solid #23263a;
    margin-top: 3rem;
}

.center-card {
    max-width: 900px;
    margin: 3rem auto 0 auto;
    background: #23263a;
    border-radius: 1.25rem;
    box-shadow: 0 4px 32px 0 rgba(16,22,34,0.12);
    border: none;
}
.center-card .card-body {
    padding: 2.5rem 2rem;
}

.form-label {
    color: #cfd8dc;
    font-weight: 500;
}
.form-control, #title, #tags {
    background: #181c2f !important;
    color: #fff !important;
    border: 1px solid #23263a !important;
    border-radius: 0.5rem !important;
}
.form-control:focus, #title:focus, #tags:focus {
    background: #23263a !important;
    color: #fff !important;
    border-color: #4fc3f7 !important;
    box-shadow: 0 0 0 0.15rem rgba(79,195,247,0.15) !important;
}
.btn-primary {
    background: linear-gradient(90deg,#4fc3f7 0,#1976d2 100%);
    border: none;
    color: #fff;
    font-weight: 600;
    border-radius: 0.5rem;
    padding: 0.6rem 2.2rem;
    font-size: 1.1rem;
}
.btn-primary:hover {
    background: linear-gradient(90deg,#1976d2 0,#4fc3f7 100%);
}
.btn-outline-secondary {
    border-radius: 0.5rem;
    color: #b0b8c1;
    border: 1px solid #23263a;
    background: transparent;
}
.btn-outline-secondary:hover {
    background: #23263a;
    color: #fff;
}
.form-check-input:checked {
    background-color: #4fc3f7 !important;
    border-color: #4fc3f7 !important;
}
.form-text {
    color: #b0b8c1 !important;
}
.invalid-feedback {
    color: #ff6b81;
}
.tox-tinymce {
    background: #181c2f !important;
    border: 1px solid #23263a !important;
    border-radius: 0.5rem !important;
}
.tox .tox-edit-area__iframe {
    background: #181c2f !important;
    color: #fff !important;
}
.tox .tox-edit-area iframe {
    background: #181c2f !important;
    color: #fff !important;
}
.tox .tox-toolbar__primary {
    background: #23263a !important;
    border-bottom: 1px solid #23263a !important;
}
.tox .tox-tbtn {
    color: #cfd8dc !important;
}
.tox .tox-tbtn:hover {
    background: #181c2f !important;
}
.tox .tox-statusbar {
    background: #23263a !important;
    color: #b0b8c1 !important;
    border-top: 1px solid #23263a !important;
}
.tox .tox-edit-area__iframe {
    background: #181c2f !important;
}
</style>

<!-- 네비게이션 -->
<nav class="navbar navbar-expand-lg navbar-custom">
  <div class="container">
    <a class="navbar-brand" href="/">FlowBreath</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="/resources">자료</a></li>
        <li class="nav-item"><a class="nav-link active" href="/diary">수련 일기</a></li>
        <li class="nav-item"><a class="nav-link" href="/tags">태그</a></li>
        <li class="nav-item"><a class="nav-link" href="/login">로그인</a></li>
        <li class="nav-item"><a class="nav-link" href="/register">회원가입</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- 중앙 카드형 폼 -->
<div class="container">
  <div class="center-card card mt-5">
    <div class="card-body">
      <h2 class="card-title mb-4 text-center" style="color:#fff; font-weight:700; letter-spacing:0.5px; font-size:2.2rem;">
        수련 일기
      </h2>
      <form id="diaryForm" method="POST" action="<?= $formAction ?>">
        <?php if ($isEdit): ?>
          <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
        <div class="mb-3">
          <label for="title" class="form-label">수련 일기</label>
          <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($diary['title']) ?>" required>
        </div>
        <div class="mb-3">
          <label for="content" class="form-label">내용</label>
          <textarea id="content" name="content"><?= $content ?></textarea>
        </div>
        <div class="mb-3">
          <label for="tags" class="form-label">태그</label>
          <input type="text" class="form-control" id="tags" name="tags" value="<?= htmlspecialchars($diary['tags']) ?>" placeholder="예: 수련, 명상, 호흡">
          <div class="form-text">쉼표(,)로 구분해 여러 태그를 입력할 수 있습니다.</div>
        </div>
        <div class="mb-3 form-check">
          <input type="checkbox" class="form-check-input" id="is_public" name="is_public" <?= $diary['is_public'] ? 'checked' : '' ?>>
          <label class="form-check-label" for="is_public">공개</label>
        </div>
        <div class="d-flex justify-content-between align-items-center mt-4">
          <a href="<?= $cancelUrl ?>" class="btn btn-outline-secondary">취소</a>
          <button type="submit" class="btn btn-primary">저장하기</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- 푸터 -->
<footer class="footer-custom mt-5">
  <div class="container">
    <div>본 사이트의 내용은 정보 공유를 위한 것이며, 어떠한 의학적 진단, 치료, 처방도 제공하지 않습니다.<br>건강상의 문제가 있거나 특정 수련법을 시작하기 전에는 반드시 전문 의료인과 상담하십시오.</div>
    <div class="mt-2">© 2025 FlowBreath</div>
  </div>
</footer>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/<?= $tinymceApiKey ?>/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof tinymce !== 'undefined') {
        tinymce.init({
            selector: '#content',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            skin: 'oxide-dark',
            content_css: false,
            height: 400,
            menubar: false,
            language: 'ko_KR',
            setup: function(editor) {
                editor.on('init', function() {
                    // 다크모드 본문 스타일 적용
                    editor.getDoc().body.style.backgroundColor = '#181c2f';
                    editor.getDoc().body.style.color = '#fff';
                    editor.getDoc().body.style.fontFamily = 'inherit';
                });
            },
            content_style: `body { background-color: #181c2f !important; color: #fff !important; font-family: inherit; }
                ::placeholder { color: #b0b8c1 !important; opacity: 1; }
                *::selection { background: #4fc3f7 !important; color: #181c2f !important; }
            `
        });
    }
});

document.getElementById('diaryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Get form data
    const formData = new FormData(this);
    
    // Get TinyMCE content
    const content = tinymce.get('content').getContent();
    formData.set('content', content);
    
    // Log form data for debugging
    console.log('Form data before submission:');
    for (let pair of formData.entries()) {
        console.log(pair[0] + ': ' + pair[1]);
    }
    
    // Add CSRF token to headers
    const headers = new Headers();
    headers.append('X-CSRF-TOKEN', '<?= $_SESSION['csrf_token'] ?>');
    
    // For PUT requests, we need to send the data as application/x-www-form-urlencoded
    const method = '<?= $isEdit ? 'PUT' : 'POST' ?>';
    const url = this.action;
    
    // Convert FormData to URLSearchParams for proper encoding
    const params = new URLSearchParams();
    for (let pair of formData.entries()) {
        params.append(pair[0], pair[1]);
    }
    
    fetch(url, {
        method: method,
        headers: {
            'X-CSRF-TOKEN': '<?= $_SESSION['csrf_token'] ?>',
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: params.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/diary/' + data.id;
        } else {
            alert(data.error || '<?= __('diary.save_error') ?>');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?= __('diary.save_error') ?>');
    });
});

// 태그 입력 처리
const tagsInput = document.getElementById('tags');
tagsInput.addEventListener('input', function() {
    this.value = this.value.replace(/[^ㄱ-ㅎㅏ-ㅣ가-힣a-zA-Z0-9,]/g, '');
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 