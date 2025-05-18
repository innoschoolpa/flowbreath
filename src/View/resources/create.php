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

<div class="container mt-5">
    <?php
    $rid = $resource['resource_id'] ?? $resource['id'] ?? null;
    ?>
    <h2><?php echo ($rid) ? '리소스 수정' : '리소스 등록'; ?></h2>
    
    <form id="resource-form" action="<?php echo ($rid) ? '/resources/' . $rid : '/resources/store'; ?>" method="post" enctype="multipart/form-data">
        <?php if ($rid): ?>
            <input type="hidden" name="_method" value="PUT">
        <?php endif; ?>
        
        <div class="mb-3">
            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
            <input type="text" class="form-control" id="title" name="title" required maxlength="100" value="<?php echo htmlspecialchars($resource['title'] ?? ''); ?>">
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
                <option value="en" <?php if (($resource['language_code'] ?? '') === 'en') echo 'selected'; ?>>English</option>
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
let ckeditorInstance;

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

// CKEditor 초기화 (한 번만 실행)
ClassicEditor
    .create(document.querySelector('#content'), {
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
    })
    .then(editor => {
        ckeditorInstance = editor;
        console.log('Editor initialized');
    })
    .catch(error => {
        console.error(error);
    });

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
    const descriptionInput = document.getElementById('description');
    if (!descriptionInput.value.trim()) {
        alert('설명을 입력하세요.');
        e.preventDefault();
        return false;
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