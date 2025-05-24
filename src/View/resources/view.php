<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<?php
// src/View/resources/show.php

// Generate meta description from content
$description = !empty($resource['content']) ? 
    substr(strip_tags($resource['content']), 0, 160) . '...' : 
    'Resource details page';

// Get the first image URL from content if exists, otherwise use default
$imageUrl = !empty($resource['featured_image']) ? $resource['featured_image'] : '/assets/images/default-resource.jpg';

// Get absolute URL for the current page
$currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . 
    "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

$title = $title ?? '리소스 상세';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
    :root {
        --background-color: #0f172a;
        --text-color: #f1f5f9;
        --card-bg: #1e293b;
        --border-color: #334155;
        --primary-color: #3b82f6;
        --secondary-color: #64748b;
        --accent-color: #0ea5e9;
        --success-color: #22c55e;
        --warning-color: #f59e0b;
        --error-color: #ef4444;
        --info-bg: rgba(14, 165, 233, 0.1);
    }

    body {
        background-color: var(--background-color);
        color: var(--text-color);
    }

    .card {
        background-color: var(--card-bg);
        border-color: var(--border-color);
    }

    .card-header {
        background-color: rgba(255, 255, 255, 0.05);
        border-bottom-color: var(--border-color);
    }

    .card-header h1 {
        color: var(--text-color);
    }

    .btn-secondary {
        background-color: var(--secondary-color);
        border-color: var(--secondary-color);
        color: var(--text-color);
    }

    .btn-secondary:hover {
        background-color: #475569;
        border-color: #475569;
        color: var(--text-color);
    }

    .btn-primary {
        background-color: var(--accent-color);
        border-color: var(--accent-color);
        color: var(--text-color);
    }

    .btn-primary:hover {
        background-color: #0284c7;
        border-color: #0284c7;
        color: var(--text-color);
    }

    .btn-danger {
        background-color: var(--error-color);
        border-color: var(--error-color);
        color: var(--text-color);
    }

    .btn-danger:hover {
        background-color: #dc2626;
        border-color: #dc2626;
        color: var(--text-color);
    }

    .alert-info {
        background-color: var(--info-bg);
        border-color: var(--accent-color);
        color: var(--text-color);
    }

    .alert-danger {
        background-color: rgba(239, 68, 68, 0.1);
        border-color: var(--error-color);
        color: var(--error-color);
    }

    .badge.bg-secondary {
        background-color: var(--secondary-color) !important;
        color: var(--text-color);
    }

    .text-decoration-none {
        color: var(--accent-color);
    }

    .text-decoration-none:hover {
        color: #0284c7;
    }

    /* 컨텐츠 영역 스타일 */
    .card-text {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        box-sizing: border-box;
        color: var(--text-color);
    }

    /* 이미지 컨테이너 */
    .card-text figure {
        max-width: 100%;
        margin: 1rem 0;
        box-sizing: border-box;
        clear: both;
    }

    /* 이미지 스타일 */
    .card-text img {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 1rem auto;
        box-sizing: border-box;
        object-fit: contain;
    }

    /* 이미지 정렬 */
    .card-text .image-align-left {
        float: left;
        margin: 0.5rem 1rem 0.5rem 0;
        max-width: 45%;
    }

    .card-text .image-align-center {
        margin: 1rem auto;
        max-width: 100%;
    }

    .card-text .image-align-right {
        float: right;
        margin: 0.5rem 0 0.5rem 1rem;
        max-width: 45%;
    }

    /* 반응형 이미지 */
    @media (max-width: 768px) {
        .card-text .image-align-left,
        .card-text .image-align-right {
            max-width: 100%;
            float: none;
            margin: 1rem 0;
        }
    }

    /* 댓글 섹션 스타일 */
    .comments-section {
        margin-top: 2rem;
        padding: 1.5rem;
        background-color: var(--card-bg);
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
    }

    .comments-section h3 {
        color: var(--text-color);
        margin-bottom: 1.5rem;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .comment-form {
        margin-bottom: 2rem;
    }

    .comment-form textarea {
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border-color);
        color: var(--text-color);
        width: 100%;
        padding: 1rem;
        margin-bottom: 1rem;
        border-radius: 0.5rem;
        min-height: 100px;
        resize: vertical;
        transition: all 0.3s ease;
    }

    .comment-form textarea:focus {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: var(--accent-color);
        outline: none;
        box-shadow: 0 0 0 2px rgba(14, 165, 233, 0.25);
    }

    .comment-form button {
        background-color: var(--accent-color);
        color: var(--text-color);
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .comment-form button:hover {
        background-color: #0284c7;
        transform: translateY(-1px);
    }

    .comment-form button:active {
        transform: translateY(0);
    }

    .comment-form button i {
        font-size: 1rem;
    }

    #comments-container {
        margin-top: 2rem;
    }

    .comment {
        padding: 1rem;
        margin-bottom: 1rem;
        background-color: rgba(255, 255, 255, 0.05);
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
    }

    .comment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .comment-author {
        font-weight: 600;
        color: var(--accent-color);
    }

    .comment-date {
        color: var(--secondary-color);
        font-size: 0.875rem;
    }

    .comment-content {
        color: var(--text-color);
        line-height: 1.6;
    }

    .comment-actions {
        margin-top: 0.5rem;
        display: flex;
        gap: 1rem;
    }

    .comment-action-btn {
        background: none;
        border: none;
        color: var(--secondary-color);
        cursor: pointer;
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        transition: all 0.2s ease;
    }

    .comment-action-btn:hover {
        color: var(--accent-color);
        background-color: rgba(255, 255, 255, 0.05);
    }

    .reply-form {
        margin-top: 1rem;
        padding-left: 2rem;
        display: none;
    }

    .reply-form.active {
        display: block;
    }

    .loading {
        color: var(--text-color);
        opacity: 0.7;
    }

    /* DB 정보 섹션 스타일 추가 */
    .list-unstyled.small {
        color: var(--text-color);
    }
    .list-unstyled.small li b {
        color: var(--text-color);
    }
    </style>

    <!-- SEO Meta Tags -->
    <meta name="description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="keywords" content="<?php echo !empty($resource['tags']) ? htmlspecialchars(implode(', ', $resource['tags'])) : ''; ?>">
    <meta name="author" content="<?php echo htmlspecialchars($resource['user_name'] ?? 'FlowBreath'); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo htmlspecialchars($currentUrl); ?>">

    <!-- Open Graph Tags for Social Media -->
    <meta property="og:title" content="<?php echo htmlspecialchars($resource['title'] ?? $title); ?>">
    <meta property="og:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta property="og:image" content="<?php echo htmlspecialchars($imageUrl); ?>">
    <meta property="og:url" content="<?php echo htmlspecialchars($currentUrl); ?>">
    <meta property="og:type" content="article">
    <meta property="og:site_name" content="FlowBreath Resources">

    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($resource['title'] ?? $title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($imageUrl); ?>">

    <!-- Schema.org markup for Google -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "Article",
        "headline": <?php echo json_encode($resource['title'] ?? $title); ?>,
        "author": {
            "@type": "Person",
            "name": <?php echo json_encode($resource['user_name'] ?? 'FlowBreath'); ?>
        },
        "datePublished": <?php echo json_encode($resource['created_at'] ?? date('Y-m-d H:i:s')); ?>,
        "dateModified": <?php echo json_encode($resource['updated_at'] ?? $resource['created_at'] ?? date('Y-m-d H:i:s')); ?>,
        "description": <?php echo json_encode($description); ?>,
        "image": <?php echo json_encode($imageUrl); ?>
    }
    </script>
</head>
<body>
    <div class="container py-5">
        <?php if (isset($resource) && !empty($resource)): ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h1 class="h3 mb-0"><?php echo htmlspecialchars($resource['title'] ?? ''); ?></h1>
                    <div class="d-flex gap-2">
                        <a href="/resources" class="btn btn-secondary btn-sm">
                            <i class="fas fa-list"></i> 목록으로
                        </a>
                        <?php if (isset($_SESSION['user_id']) && isset($resource['user_id']) && $_SESSION['user_id'] === $resource['user_id']): ?>
                            <a href="/resources/edit/<?php echo htmlspecialchars($resource['id']); ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> 수정
                            </a>
                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDelete('<?php echo htmlspecialchars($resource['id']); ?>', '<?php echo htmlspecialchars($resource['translation_language_code'] ?? $resource['language_code'] ?? 'ko'); ?>')">
                                <i class="fas fa-trash"></i> 삭제
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($resource['content'])): ?>
                        <?php
                        // Extract YouTube video ID from link or content
                        $videoId = null;
                        $youtube_pattern = '/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=|live\/)|youtu\.be\/)([^"&?\/\s]{11})/';
                        
                        // First check the link field
                        if (!empty($resource['link'])) {
                            if (preg_match($youtube_pattern, $resource['link'], $matches)) {
                                $videoId = $matches[1];
                            }
                        }
                        
                        // If no video ID found in link, check content
                        if (!$videoId && !empty($resource['content'])) {
                            if (preg_match('/https?:\/\/(www\.)?(youtube\.com|youtu\.be)\/[\w\-?=&#;]+/', $resource['content'], $ytMatch)) {
                                if (preg_match($youtube_pattern, $ytMatch[0], $matches)) {
                                    $videoId = $matches[1];
                                }
                            }
                        }

                        // Display video if found
                        if ($videoId): ?>
                            <div class="mb-4" style="max-width:1280px;margin:24px auto 0 auto;">
                                <div class="ratio ratio-16x9">
                                    <iframe 
                                        src="https://www.youtube.com/embed/<?= $videoId ?>?autoplay=0" 
                                        title="YouTube video player"
                                        frameborder="0" 
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                        allowfullscreen>
                                    </iframe>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="mb-4">
                            <h5>상세 내용</h5>
                            <div class="card-text">
                                <?php
                                // HTML 엔티티가 중첩되어 저장된 경우를 방지하고, 줄바꿈은 <br>로 변환
                                $content = $resource['content'] ?? '';
                                // 1. 중첩된 엔티티를 한 번만 디코딩
                                $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                                // 2. <br>이 아닌 \n은 <br>로 변환
                                $content = nl2br($content, false);
                                echo $content;
                                ?>
                            </div>
                        </div>
                        <?php if (!empty($resource['link'])): ?>
                            <div class="mb-3">
                                <strong>링크:</strong> <a href="<?= htmlspecialchars($resource['link']) ?>" target="_blank" rel="noopener noreferrer" class="text-decoration-none"><?= htmlspecialchars($resource['link']) ?></a>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($resource['description'])): ?>
                        <div class="alert alert-info mb-4">
                            <strong>설명:</strong> <?php echo html_entity_decode($resource['description'], ENT_QUOTES | ENT_HTML5, 'UTF-8'); ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if (!empty($resource['tags'])): ?>
                        <div class="mb-4">
                            <h5 class="card-title">태그</h5>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($resource['tags'] as $tag): ?>
                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($tag); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <h5 class="card-title">DB 원본 정보</h5>
                        <ul class="list-unstyled small">
                            <li><b>ID:</b> <?= htmlspecialchars($resource['id']) ?></li>
                            <li><b>언어 코드:</b> <?= htmlspecialchars($resource['translation_language_code'] ?? $resource['language_code'] ?? 'ko') ?></li>
                            <li><b>작성자:</b> <a href="/profile/<?= htmlspecialchars($resource['user_id']) ?>" class="text-decoration-none"><?= htmlspecialchars($resource['author_name'] ?? '-') ?></a></li>
                            <li><b>작성일:</b> <?= htmlspecialchars($resource['created_at'] ?? '-') ?></li>
                            <li><b>수정일:</b> <?= htmlspecialchars($resource['updated_at'] ?? '-') ?></li>
                            <li><b>공개여부:</b> <?= ($resource['visibility'] ?? 'public') === 'public' ? '공개' : '비공개' ?></li>
                            <li><b>유형:</b> <?= htmlspecialchars($resource['type'] ?? '-') ?></li>
                            <li><b>슬러그:</b> <?= htmlspecialchars($resource['slug'] ?? '-') ?></li>
                            <li><b>파일:</b> <?= htmlspecialchars($resource['file_path'] ?? '-') ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div class="d-flex gap-2">
                    <a href="/resources" class="btn btn-secondary">
                        <i class="fas fa-list"></i> 목록으로
                    </a>
                    <?php if (isset($_SESSION['user_id']) && isset($resource['user_id']) && $_SESSION['user_id'] === $resource['user_id']): ?>
                        <a href="/resources/edit/<?php echo htmlspecialchars($resource['id']); ?>" class="btn btn-primary">
                            <i class="fas fa-edit"></i> 수정
                        </a>
                        <button type="button" class="btn btn-danger" onclick="confirmDelete('<?php echo htmlspecialchars($resource['id']); ?>', '<?php echo htmlspecialchars($resource['translation_language_code'] ?? $resource['language_code'] ?? 'ko'); ?>')">
                            <i class="fas fa-trash"></i> 삭제
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger">
                리소스를 찾을 수 없습니다.
            </div>
            <a href="/resources" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> 목록으로
            </a>
        <?php endif; ?>

        <!-- 댓글 섹션 -->
        <div class="comments-section mt-5">
            <h3><i class="fas fa-comments"></i> 댓글</h3>
            
            <!-- 댓글 작성 폼 -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <form id="comment-form" class="comment-form">
                    <input type="hidden" name="parent_id" value="">
                    <textarea name="content" placeholder="댓글을 입력하세요..." maxlength="1000" required></textarea>
                    <button type="submit">
                        <i class="fas fa-paper-plane"></i>
                        댓글 작성
                    </button>
                </form>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    댓글을 작성하려면 <a href="/login" class="text-decoration-none">로그인</a>이 필요합니다.
                </div>
            <?php endif; ?>

            <!-- 댓글 목록 -->
            <div id="comments-container"></div>

            <!-- 로딩 표시 -->
            <div class="loading" style="display: none;">
                <i class="fas fa-spinner fa-spin"></i>
                댓글을 불러오는 중...
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // 현재 사용자 정보 설정
    window.currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    window.isAdmin = <?php echo isset($_SESSION['is_admin']) && $_SESSION['is_admin'] ? 'true' : 'false'; ?>;
    </script>
    <script src="/js/comments.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const commentForm = document.getElementById('comment-form');
        const commentsContainer = document.getElementById('comments-container');
        const loadingIndicator = document.querySelector('.loading');
        let currentPage = 1;
        let isLoading = false;
        let hasMoreComments = true;
        const resourceId = <?php echo $resource['id']; ?>;

        // 댓글 작성
        commentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const content = formData.get('content').trim();
            const parentId = formData.get('parent_id');

            if (!content) {
                alert('댓글 내용을 입력해주세요.');
                return;
            }

            try {
                const response = await fetch(`/api/resources/${resourceId}/comments`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                    body: JSON.stringify({
                        content: content,
                        parent_id: parentId || null
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    // 폼 초기화
                    this.reset();
                    
                    // 댓글 목록 새로고침
                    currentPage = 1;
                    commentsContainer.innerHTML = '';
                    await loadComments(currentPage);
                    
                    alert(result.message);
                } else {
                    alert(result.message || '댓글 작성 중 오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('댓글 작성 중 오류가 발생했습니다.');
            }
        });

        // 댓글 수정
        async function editComment(commentId, currentContent) {
            const newContent = prompt('댓글을 수정하세요:', currentContent);
            if (newContent === null || newContent.trim() === currentContent.trim()) return;

            try {
                const response = await fetch(`/api/comments/${commentId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                    },
                    body: JSON.stringify({ content: newContent.trim() })
                });

                const data = await response.json();
                if (data.success) {
                    const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
                    if (commentElement) {
                        commentElement.querySelector('.comment-content').textContent = newContent.trim();
                        commentElement.querySelector('.comment-date').textContent = '수정됨 • ' + new Date().toLocaleString();
                    }
                } else {
                    alert(data.error || '댓글 수정 중 오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('댓글 수정 중 오류가 발생했습니다.');
            }
        }

        // 댓글 삭제
        async function deleteComment(commentId) {
            if (!confirm('정말로 이 댓글을 삭제하시겠습니까?')) return;

            try {
                const response = await fetch(`/api/comments/${commentId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?php echo $_SESSION['csrf_token']; ?>'
                    }
                });

                const data = await response.json();
                if (data.success) {
                    const commentElement = document.querySelector(`[data-comment-id="${commentId}"]`);
                    if (commentElement) {
                        commentElement.remove();
                        // 댓글 수 업데이트
                        updateCommentCount();
                    }
                } else {
                    alert(data.error || '댓글 삭제 중 오류가 발생했습니다.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('댓글 삭제 중 오류가 발생했습니다.');
            }
        }

        // 댓글 요소 생성
        function createCommentElement(comment) {
            const div = document.createElement('div');
            div.className = 'comment';
            div.dataset.commentId = comment.id;
            
            const isAuthor = comment.user_id === window.currentUserId;
            const isAdmin = window.isAdmin;

            div.innerHTML = `
                <div class="comment-header">
                    <span class="comment-author">${comment.author_name}</span>
                    <span class="comment-date">${new Date(comment.created_at).toLocaleString()}</span>
                </div>
                <div class="comment-content">${comment.content}</div>
                <div class="comment-actions">
                    <button class="comment-action-btn reply-btn" onclick="showReplyForm(${comment.id})">
                        <i class="fas fa-reply"></i> 답글
                    </button>
                    ${(isAuthor || isAdmin) ? `
                        <button class="comment-action-btn edit-btn" onclick="editComment(${comment.id}, '${comment.content.replace(/'/g, "\\'")}')">
                            <i class="fas fa-edit"></i> 수정
                        </button>
                        <button class="comment-action-btn delete-btn" onclick="deleteComment(${comment.id})">
                            <i class="fas fa-trash"></i> 삭제
                        </button>
                    ` : ''}
                </div>
                <div id="reply-form-${comment.id}" class="reply-form">
                    <form class="comment-form">
                        <input type="hidden" name="parent_id" value="${comment.id}">
                        <textarea name="content" placeholder="답글을 입력하세요..." maxlength="1000" required></textarea>
                        <button type="submit">
                            <i class="fas fa-paper-plane"></i>
                            답글 작성
                        </button>
                    </form>
                </div>
            `;

            return div;
        }

        // 댓글 수 업데이트
        function updateCommentCount() {
            const count = document.querySelectorAll('.comment').length;
            const countElement = document.querySelector('.comments-section h3');
            countElement.innerHTML = `<i class="fas fa-comments"></i> 댓글 (${count})`;
        }

        // 답글 폼 표시/숨김
        window.showReplyForm = function(commentId) {
            const replyForm = document.getElementById(`reply-form-${commentId}`);
            const allReplyForms = document.querySelectorAll('.reply-form');
            
            allReplyForms.forEach(form => {
                if (form.id !== `reply-form-${commentId}`) {
                    form.classList.remove('active');
                }
            });

            replyForm.classList.toggle('active');
            if (replyForm.classList.contains('active')) {
                replyForm.querySelector('textarea').focus();
            }
        };

        // 댓글 목록 로드
        async function loadComments(page = 1) {
            if (isLoading || !hasMoreComments) return;
            
            isLoading = true;
            loadingIndicator.style.display = 'block';

            try {
                const response = await fetch(`/api/resources/${resourceId}/comments?page=${page}`);
                const data = await response.json();

                if (data.success) {
                    data.comments.forEach(comment => {
                        const commentElement = createCommentElement(comment);
                        commentsContainer.appendChild(commentElement);
                    });

                    hasMoreComments = data.comments.length === 10; // 페이지당 10개씩 로드
                    currentPage = page;
                    updateCommentCount();
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                isLoading = false;
                loadingIndicator.style.display = 'none';
            }
        }

        // 무한 스크롤
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && hasMoreComments) {
                    loadComments(currentPage + 1);
                }
            });
        });

        observer.observe(loadingIndicator);

        // 초기 댓글 로드
        loadComments();
    });
    </script>
</body>
</html>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 