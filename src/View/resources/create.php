<?php
// src/View/resources/create.php
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$language = \App\Core\Language::getInstance();
?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <?php 
        echo $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        ?>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<style>
:root {
    --background-color: #0f172a;
    --text-color: #f1f5f9;
    --card-bg: #1e293b;
    --border-color: #334155;
    --primary-color: #3b82f6;
    --secondary-color: #64748b;
    --accent-color: #3b82f6;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
    --input-bg: rgba(255, 255, 255, 0.1);
    --input-border: var(--border-color);
    --input-focus-bg: rgba(255, 255, 255, 0.15);
    --input-focus-border: var(--accent-color);
}

body {
    background-color: var(--background-color);
    color: var(--text-color);
}

.form-control {
    background-color: var(--input-bg);
    border-color: var(--input-border);
    color: var(--text-color);
}

.form-control:focus {
    background-color: var(--input-focus-bg);
    border-color: var(--input-focus-border);
    color: var(--text-color);
    box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25);
}

.form-control::placeholder {
    color: var(--text-color);
    opacity: 0.5;
}

.form-label {
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

.alert-danger {
    background-color: rgba(239, 68, 68, 0.1);
    border-color: var(--error-color);
    color: var(--error-color);
}

/* CKEditor 다크 모드 스타일 */
.ck.ck-editor {
    width: 100%;
}

.ck.ck-content {
    background-color: var(--input-bg) !important;
    color: var(--text-color) !important;
    border-color: var(--input-border) !important;
}

.ck.ck-toolbar {
    background-color: var(--card-bg) !important;
    border-color: var(--input-border) !important;
}

.ck.ck-toolbar .ck-toolbar__items button {
    color: var(--text-color) !important;
}

.ck.ck-toolbar .ck-toolbar__items button:hover {
    background-color: var(--input-focus-bg) !important;
}

.ck.ck-dropdown__panel {
    background-color: var(--card-bg) !important;
    border-color: var(--input-border) !important;
}

.ck.ck-dropdown__panel .ck-button {
    color: var(--text-color) !important;
}

.ck.ck-dropdown__panel .ck-button:hover {
    background-color: var(--input-focus-bg) !important;
}

/* 유튜브 미리보기 스타일 */
#youtube-preview {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.375rem;
    padding: 1rem;
    margin-top: 1rem;
}

/* 파일 업로드 스타일 */
.custom-file-upload {
    position: relative;
    display: block;
}

.custom-file-upload input[type="file"] {
    position: absolute;
    left: -9999px;
    opacity: 0;
    width: 0;
    height: 0;
}

.file-label {
    display: flex;
    align-items: center;
    background-color: var(--input-bg);
    border: 1px solid var(--input-border);
    border-radius: 0.375rem;
    padding: 0.5rem;
    cursor: pointer;
    transition: all 0.2s;
}

.file-label:hover {
    background-color: var(--input-focus-bg);
}

.file-button {
    background-color: var(--accent-color);
    color: var(--text-color);
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    margin-right: 1rem;
    font-weight: 500;
}

.file-name {
    color: var(--text-color);
    opacity: 0.7;
}

/* 파일이 선택되었을 때의 스타일 */
input[type="file"]:not(:placeholder-shown) + .file-label .file-name {
    opacity: 1;
}

/* 선택 상자 스타일 */
select.form-control {
    background-color: var(--input-bg) !important;
    border-color: var(--input-border) !important;
    color: var(--text-color) !important;
    appearance: none !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%23f1f5f9' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.75rem center !important;
    background-size: 16px 12px !important;
    padding-right: 2.5rem !important;
}

select.form-control option {
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
    padding: 0.5rem !important;
}

select.form-control:focus {
    background-color: var(--input-focus-bg) !important;
    border-color: var(--input-focus-border) !important;
    color: var(--text-color) !important;
    box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25) !important;
}

/* 필수 입력 표시 */
.text-danger {
    color: var(--error-color) !important;
}

/* Add dropdown menu styles */
.dropdown-menu {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.dropdown-item {
    color: var(--text-color) !important;
    background-color: var(--card-bg) !important;
}

.dropdown-item:hover {
    background-color: rgba(255, 255, 255, 0.1) !important;
    color: var(--text-color) !important;
}

.dropdown-item.active {
    background-color: var(--primary-color) !important;
    color: white !important;
}

/* Update form-select styles */
.form-select {
    background-color: var(--input-bg) !important;
    color: var(--text-color) !important;
    border-color: var(--input-border) !important;
}

.form-select option {
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
}

/* TinyMCE 다크모드 스타일 */
.tox-tinymce {
    border-color: var(--border-color) !important;
}

.tox .tox-toolbar__primary {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.tox .tox-toolbar__overflow {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.tox .tox-toolbar__group {
    border-color: var(--border-color) !important;
}

.tox .tox-tbtn {
    color: var(--text-color) !important;
}

.tox .tox-tbtn:hover {
    background-color: var(--input-focus-bg) !important;
}

.tox .tox-menu {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.tox .tox-collection__item {
    color: var(--text-color) !important;
}

.tox .tox-collection__item:hover {
    background-color: var(--input-focus-bg) !important;
}

.tox .tox-dialog {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.tox .tox-dialog__header {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.tox .tox-dialog__body {
    background-color: var(--card-bg) !important;
}

.tox .tox-dialog__footer {
    background-color: var(--card-bg) !important;
    border-color: var(--border-color) !important;
}

.tox .tox-textfield {
    background-color: var(--input-bg) !important;
    border-color: var(--border-color) !important;
    color: var(--text-color) !important;
}

.tox .tox-textfield:focus {
    background-color: var(--input-focus-bg) !important;
    border-color: var(--input-focus-border) !important;
}

.tox .tox-dialog__body-content {
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
}

.tox .tox-dialog__body-nav-item {
    color: var(--text-color) !important;
}

.tox .tox-dialog__body-nav-item--active {
    background-color: var(--input-focus-bg) !important;
}

.tox .tox-dialog__footer-end button {
    background-color: var(--accent-color) !important;
    color: var(--text-color) !important;
    border-color: var(--accent-color) !important;
}

.tox .tox-dialog__footer-end button:hover {
    background-color: #0284c7 !important;
    border-color: #0284c7 !important;
}
</style>

<div class="container mt-5">
    <?php
    $rid = $resource['resource_id'] ?? $resource['id'] ?? null;
    ?>
    <h2><?php echo ($rid) ? $language->get('resources.edit') : $language->get('resources.create'); ?></h2>
    
    <form id="resource-form" action="<?php echo ($rid) ? '/resources/' . $rid : '/resources/store'; ?>" method="post" enctype="multipart/form-data">
        <?php if ($rid): ?>
            <input type="hidden" name="_method" value="PUT">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <?php endif; ?>
        
        <div class="mb-3">
            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" required maxlength="150" value="<?php echo htmlspecialchars($resource['title'] ?? ''); ?>">
        </div>
        
        <div class="mb-3">
            <label for="content" class="form-label">내용 <span class="text-danger">*</span></label>
            <textarea class="form-control" id="content" name="content" rows="6"><?php echo htmlspecialchars($resource['content'] ?? ''); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">설명 <span class="text-danger">*</span></label>
            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($resource['description'] ?? ''); ?></textarea>
        </div>
        
        <div class="mb-3">
            <label for="link" class="form-label">링크 (유튜브, 웹사이트 등)</label>
            <input type="url" class="form-control" id="link" name="link" value="<?php echo htmlspecialchars($resource['link'] ?? ''); ?>">
            <div id="youtube-preview" class="mt-2" style="display: none;">
                <div class="ratio ratio-16x9">
                    <iframe id="youtube-iframe" src="" allowfullscreen></iframe>
                </div>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="status" class="form-label">상태</label>
            <select class="form-control" id="status" name="status">
                <option value="draft" <?php if (($resource['status'] ?? 'draft') === 'draft') echo 'selected'; ?>>임시저장</option>
                <option value="published" <?php if (($resource['status'] ?? '') === 'published') echo 'selected'; ?>>발행</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="visibility" class="form-label">공개 여부</label>
            <select class="form-control" id="visibility" name="visibility">
                <option value="public" <?php if (($resource['visibility'] ?? 'public') === 'public') echo 'selected'; ?>>공개</option>
                <option value="private" <?php if (($resource['visibility'] ?? '') === 'private') echo 'selected'; ?>>비공개</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="language_code" class="form-label">언어</label>
            <select class="form-control" id="language_code" name="language_code">
                <option value="ko" <?php if (($resource['language_code'] ?? 'ko') === 'ko') echo 'selected'; ?>>한국어</option>
                <option value="en" <?php if (($resource['language_code'] ?? 'ko') === 'en') echo 'selected'; ?>>영어</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="category" class="form-label">카테고리</label>
            <input type="text" class="form-control" id="category" name="category" maxlength="50" value="<?php echo htmlspecialchars($resource['category'] ?? ''); ?>">
        </div>
        
        <div class="mb-3">
            <label for="tags" class="form-label">태그 (쉼표로 구분)</label>
            <input type="text" class="form-control" id="tags" name="tags" maxlength="100" value="<?php echo htmlspecialchars(implode(',', $resource['tags'] ?? [])); ?>">
        </div>
        
        <div class="mb-3">
            <label for="file" class="form-label">첨부파일 (이미지, PDF)</label>
            <div class="custom-file-upload">
                <input type="file" class="form-control" id="file" name="file" accept="image/jpeg,image/png,application/pdf">
                <label for="file" class="file-label">
                    <span class="file-button">파일 선택</span>
                    <span class="file-name">선택된 파일 없음</span>
                </label>
            </div>
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo ($rid) ? $language->get('resources.save') : $language->get('resources.create'); ?></button>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/3p683d001w10l44tgvyk034uz5nsntitn1eiyjs24ufhx67a/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
let contentEditor, descriptionEditor;

// TinyMCE 초기화 함수
function initTinyMCE(elementId, height = 400) {
    tinymce.init({
        selector: `#${elementId}`,
        height: height,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'image media table link | removeformat | code | help',
        skin: 'oxide-dark',
        content_css: 'dark',
        branding: false,
        promotion: false,
        statusbar: false,
        resize: false,
        
        // 이미지 업로드 설정
        images_upload_url: '/upload/image',
        images_upload_base_path: '/',
        images_reuse_filename: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        
        // 개선된 이미지 업로드 핸들러
        images_upload_handler: function (blobInfo, progress) {
            return new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('image', blobInfo.blob(), blobInfo.filename());
                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

                const xhr = new XMLHttpRequest();
                
                xhr.upload.onprogress = function(e) {
                    if (e.lengthComputable) {
                        progress(e.loaded / e.total * 100);
                    }
                };

                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.success) {
                                // Ensure the URL is absolute and starts with /uploads/images/
                                let imageUrl = response.url;
                                if (!imageUrl.startsWith('/')) {
                                    imageUrl = '/' + imageUrl;
                                }
                                if (!imageUrl.startsWith('/uploads/images/')) {
                                    imageUrl = '/uploads/images/' + imageUrl.replace(/^\/+/, '');
                                }
                                resolve(imageUrl);
                            } else {
                                reject(response.error || '이미지 업로드에 실패했습니다.');
                            }
                        } catch (e) {
                            reject('서버 응답을 파싱할 수 없습니다.');
                        }
                    } else {
                        reject('서버 오류: ' + xhr.status);
                    }
                };

                xhr.onerror = function() {
                    reject('네트워크 오류가 발생했습니다.');
                };

                xhr.open('POST', '/upload/image');
                xhr.send(formData);
            });
        },
        
        // 파일 선택기 콜백
        file_picker_callback: function(callback, value, meta) {
            if (meta.filetype === 'image') {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
                        
                        fetch('/upload/image', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Ensure the URL is absolute and starts with /uploads/images/
                                let imageUrl = data.url;
                                if (!imageUrl.startsWith('/')) {
                                    imageUrl = '/' + imageUrl;
                                }
                                if (!imageUrl.startsWith('/uploads/images/')) {
                                    imageUrl = '/uploads/images/' + imageUrl.replace(/^\/+/, '');
                                }
                                callback(imageUrl, { alt: file.name });
                            } else {
                                alert('이미지 업로드 실패: ' + (data.error || '알 수 없는 오류'));
                            }
                        })
                        .catch(error => {
                            console.error('Upload error:', error);
                            alert('이미지 업로드 중 오류가 발생했습니다.');
                        });
                    }
                });
                
                input.click();
            }
        },
        
        setup: function(editor) {
            editor.on('init', function() {
                if (elementId === 'content') {
                    contentEditor = editor;
                } else {
                    descriptionEditor = editor;
                }
                console.log('TinyMCE initialized for:', elementId);
            });
            
            // 이미지 드래그 앤 드롭 지원
            editor.on('drop', function(e) {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const file = files[0];
                    if (file.type.startsWith('image/')) {
                        e.preventDefault();
                        
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
                        
                        fetch('/upload/image', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                editor.insertContent(`<img src="${data.url}" alt="${file.name}" />`);
                            } else {
                                alert('이미지 업로드 실패: ' + (data.error || '알 수 없는 오류'));
                            }
                        })
                        .catch(error => {
                            console.error('Upload error:', error);
                            alert('이미지 업로드 중 오류가 발생했습니다.');
                        });
                    }
                }
            });
        }
    });
}

// content 에디터 초기화
initTinyMCE('content', 400);

// description 에디터 초기화
initTinyMCE('description', 200);

// 폼 submit 시 에디터 내용 textarea에 복사
document.getElementById('resource-form').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission
    
    if (contentEditor) {
        const content = contentEditor.getContent();
        document.getElementById('content').value = content;
        if (!content.trim()) {
            alert('내용을 입력하세요.');
            return false;
        }
    }
    if (descriptionEditor) {
        const description = descriptionEditor.getContent();
        document.getElementById('description').value = description;
        if (!description.trim()) {
            alert('설명을 입력하세요.');
            return false;
        }
    }
    
    // Add CSRF token to form
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = 'csrf_token';
    csrfInput.value = '<?php echo $_SESSION['csrf_token']; ?>';
    this.appendChild(csrfInput);
    
    // Submit the form
    this.submit();
});

// 태그 입력 처리
const tagsInput = document.getElementById('tags');
tagsInput.addEventListener('input', function() {
    this.value = this.value.replace(/[^ㄱ-ㅎㅏ-ㅣ가-힣a-zA-Z0-9,]/g, '');
});

// 유튜브 링크 미리보기 처리
const linkInput = document.getElementById('link');
const youtubePreview = document.getElementById('youtube-preview');
const youtubeIframe = document.getElementById('youtube-iframe');

function getYoutubeVideoId(url) {
    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return (match && match[2].length === 11) ? match[2] : null;
}

function updateYoutubePreview() {
    const url = linkInput.value.trim();
    const videoId = getYoutubeVideoId(url);
    
    if (videoId) {
        youtubeIframe.src = `https://www.youtube.com/embed/${videoId}`;
        youtubePreview.style.display = 'block';
    } else {
        youtubePreview.style.display = 'none';
        youtubeIframe.src = '';
    }
}

linkInput.addEventListener('input', updateYoutubePreview);
linkInput.addEventListener('change', updateYoutubePreview);

// 초기 로드 시 미리보기 업데이트
updateYoutubePreview();

document.addEventListener('DOMContentLoaded', function() {
    // 이미지 업로드 처리
    function handleImageUpload(file) {
        const formData = new FormData();
        formData.append('file', file);
        
        fetch('/upload/image', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // 절대 경로로 이미지 URL 생성
                const imageUrl = data.url.startsWith('/') ? data.url : '/uploads/images/' + data.url;
                const imageHtml = `<img src="${imageUrl}" alt="${file.name}">`;
                document.querySelector('#content').value += imageHtml;
            } else {
                alert('이미지 업로드 실패: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('이미지 업로드 중 오류가 발생했습니다.');
        });
    }

    // 파일 선택 시 파일명 표시
    document.getElementById('file').addEventListener('change', function(e) {
        const fileName = e.target.files[0] ? e.target.files[0].name : '선택된 파일 없음';
        this.nextElementSibling.querySelector('.file-name').textContent = fileName;
    });
});
</script>