<?php
// src/View/resources/create.php
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
input[type="file"] {
    background-color: var(--input-bg);
    border-color: var(--input-border);
    color: var(--text-color);
}

input[type="file"]::file-selector-button {
    background-color: var(--accent-color);
    color: var(--text-color);
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    cursor: pointer;
}

input[type="file"]::file-selector-button:hover {
    background-color: #0284c7;
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
</style>

<div class="container mt-5">
    <?php
    $rid = $resource['resource_id'] ?? $resource['id'] ?? null;
    ?>
    <h2><?php echo ($rid) ? '리소스 수정' : '리소스 등록'; ?></h2>
    
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
            <label for="visibility" class="form-label">공개 범위</label>
            <select class="form-control" id="visibility" name="visibility">
                <option value="public" <?php if (($resource['visibility'] ?? 'public') === 'public') echo 'selected'; ?>>공개</option>
                <option value="private" <?php if (($resource['visibility'] ?? '') === 'private') echo 'selected'; ?>>비공개</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label for="language_code" class="form-label">언어</label>
            <select class="form-control" id="language_code" name="language_code">
                <option value="ko" <?php if (($resource['language_code'] ?? 'ko') === 'ko') echo 'selected'; ?>>한국어</option>
                <option value="en" <?php if (($resource['language_code'] ?? 'ko') === 'en') echo 'selected'; ?>>English</option>
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
            <label for="file" class="form-label">첨부 파일 (이미지, PDF)</label>
            <input type="file" class="form-control" id="file" name="file" accept="image/jpeg,image/png,application/pdf">
        </div>
        
        <button type="submit" class="btn btn-primary"><?php echo ($rid) ? '수정' : '등록'; ?></button>
    </form>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
let ckeditorInstance, descriptionEditorInstance;

// CKEditor 로드 실패 시 대체 에디터 사용
function initFallbackEditor(elementId) {
    const textarea = document.getElementById(elementId);
    if (textarea) {
        textarea.style.height = '300px';
        textarea.style.width = '100%';
        textarea.style.padding = '10px';
        textarea.style.backgroundColor = 'var(--input-bg)';
        textarea.style.color = 'var(--text-color)';
        textarea.style.border = '1px solid var(--input-border)';
        textarea.style.borderRadius = '4px';
    }
}

// CKEditor 초기화 함수
function initCKEditor(elementId, config) {
    if (typeof ClassicEditor === 'undefined') {
        console.error('CKEditor failed to load. Using fallback editor.');
        initFallbackEditor(elementId);
        return;
    }

    ClassicEditor
        .create(document.querySelector(`#${elementId}`), config)
        .then(editor => {
            if (elementId === 'content') {
                ckeditorInstance = editor;
            } else {
                descriptionEditorInstance = editor;
            }
            console.log(`${elementId} editor initialized successfully`);
        })
        .catch(error => {
            console.error(`${elementId} editor initialization failed:`, error);
            initFallbackEditor(elementId);
        });
}

// 이미지 업로드 핸들러
function uploadImage(file) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');

    return fetch('/upload/image', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return {
                default: data.url
            };
        } else {
            throw new Error(data.error || '이미지 업로드에 실패했습니다.');
        }
    });
}

// CKEditor 이미지 업로드 어댑터 설정
class CustomUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file
            .then(file => uploadImage(file));
    }

    abort() {
        // 업로드 중단 처리
    }
}

function CustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new CustomUploadAdapter(loader);
    };
}

// 공통 에디터 설정
const editorConfig = {
    extraPlugins: [CustomUploadAdapterPlugin],
    toolbar: {
        items: [
            'heading',
            '|',
            'bold',
            'italic',
            'link',
            'bulletedList',
            'numberedList',
            '|',
            'outdent',
            'indent',
            '|',
            'imageUpload',
            'blockQuote',
            'insertTable',
            'undo',
            'redo'
        ]
    },
    image: {
        toolbar: [
            'imageTextAlternative',
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side'
        ],
        styles: [
            'full',
            'side',
            'alignLeft',
            'alignCenter',
            'alignRight'
        ],
        resizeOptions: [
            {
                name: 'imageResize:original',
                value: null,
                label: 'Original'
            },
            {
                name: 'imageResize:50',
                value: '50',
                label: '50%'
            },
            {
                name: 'imageResize:75',
                value: '75',
                label: '75%'
            }
        ],
        resizeUnit: '%',
        upload: {
            types: ['jpeg', 'png', 'gif', 'jpg']
        },
        insert: {
            type: 'block'
        },
        styles: {
            options: [
                'inline',
                'block',
                'side'
            ]
        }
    },
    table: {
        contentToolbar: [
            'tableColumn',
            'tableRow',
            'mergeTableCells'
        ]
    },
    language: 'ko'
};

// content 에디터 초기화
initCKEditor('content', editorConfig);

// description 에디터 초기화
initCKEditor('description', editorConfig);

// 폼 submit 시 에디터 내용 textarea에 복사
document.getElementById('resource-form').addEventListener('submit', function(e) {
    if (ckeditorInstance) {
        document.getElementById('content').value = ckeditorInstance.getData();
        // CKEditor 내용이 비어 있으면 제출 막기
        if (!ckeditorInstance.getData().trim()) {
            alert('내용을 입력하세요.');
            e.preventDefault();
            return false;
        }
    }
    if (descriptionEditorInstance) {
        document.getElementById('description').value = descriptionEditorInstance.getData();
        // CKEditor 내용이 비어 있으면 제출 막기
        if (!descriptionEditorInstance.getData().trim()) {
            alert('설명을 입력하세요.');
            e.preventDefault();
            return false;
        }
    }
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
</script>