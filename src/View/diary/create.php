<?php
$apiKeys = require __DIR__ . '/../../Config/api_keys.php';
$tinymceApiKey = $apiKeys['tinymce']['api_key'];
?>

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
    background-color: var(--background-color) !important;
    color: var(--text-color) !important;
}

.card, .card-body, .card-title, .card-header, .card-footer {
    background-color: var(--card-bg) !important;
    color: var(--text-color) !important;
    border-color: var(--border-color) !important;
}

.form-control {
    background-color: var(--input-bg) !important;
    border-color: var(--input-border) !important;
    color: var(--text-color) !important;
}

.form-control:focus {
    background-color: var(--input-focus-bg) !important;
    border-color: var(--input-focus-border) !important;
    color: var(--text-color) !important;
    box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25) !important;
}

.form-control::placeholder {
    color: var(--text-color) !important;
    opacity: 0.5 !important;
}

.form-label {
    color: var(--text-color) !important;
}

.btn-primary {
    background-color: var(--accent-color) !important;
    border-color: var(--accent-color) !important;
    color: var(--text-color) !important;
}

.btn-primary:hover {
    background-color: #0284c7 !important;
    border-color: #0284c7 !important;
    color: var(--text-color) !important;
}

.btn-outline-secondary {
    background-color: transparent !important;
    color: var(--text-color) !important;
    border-color: var(--border-color) !important;
}

.btn-outline-secondary:hover {
    background-color: var(--input-focus-bg) !important;
    color: var(--text-color) !important;
    border-color: var(--accent-color) !important;
}

.form-check-input {
    background-color: var(--input-bg) !important;
    border-color: var(--input-border) !important;
}

.form-check-input:checked {
    background-color: var(--accent-color) !important;
    border-color: var(--accent-color) !important;
}

.form-check-label {
    color: var(--text-color) !important;
}

.form-text {
    color: var(--secondary-color) !important;
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
    border-color: var(--input-border) !important;
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

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4"><?= __('diary.create') ?></h2>
                    
                    <form id="diaryForm" method="POST" action="/diary">
                        <div class="mb-3">
                            <label for="title" class="form-label"><?= __('diary.title') ?></label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label"><?= __('diary.content') ?></label>
                            <textarea id="content" name="content"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label"><?= __('diary.tags') ?></label>
                            <input type="text" class="form-control" id="tags" name="tags" 
                                   placeholder="<?= __('diary.tags_placeholder') ?>">
                            <div class="form-text"><?= __('diary.tags_help') ?></div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_public" name="is_public" checked>
                            <label class="form-check-label" for="is_public">
                                <?= __('diary.public') ?>
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="/diary" class="btn btn-outline-secondary">
                                <?= __('common.cancel') ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <?= __('diary.save') ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- TinyMCE CDN -->
<script src="https://cdn.tiny.cloud/1/<?= $tinymceApiKey ?>/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#content',
        height: 500,
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
        images_upload_url: '/upload/image',
        images_upload_base_path: '/',
        images_reuse_filename: true,
        automatic_uploads: true,
        file_picker_types: 'image',
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
});

document.getElementById('diaryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('content', tinymce.get('content').getContent());
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/diary/' + data.id;
        } else {
            alert(data.message || '<?= __('diary.save_error') ?>');
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