<?php
/**
 * src/View/resource/edit.php
 * 리소스 수정 페이지
 */

// 디버깅을 위한 로그
error_log("edit.php loaded with resource data: " . print_r($resource, true));
error_log("Tags data: " . print_r($tags, true));

require_once __DIR__ . '/../layout/header.php'; ?>

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

.card {
    background-color: var(--card-bg);
    border-color: var(--border-color);
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

.form-label {
    color: var(--text-color);
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

<div class="container mt-4">
    <div class="card">
        <div class="card-body">
            <h2 class="card-title mb-4">리소스 수정</h2>

            <form action="/resource/update/<?= $resource['id'] ?>" method="POST" class="needs-validation" novalidate>
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <!-- 기본 정보 -->
                <div class="mb-4">
                    <h4>기본 정보</h4>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="title" class="form-label">제목 *</label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($resource['title']) ?>" required>
                            <div class="invalid-feedback">제목을 입력해주세요.</div>
                        </div>

                        <div class="col-md-12">
                            <label for="url" class="form-label">원본 URL</label>
                            <input type="url" class="form-control" id="url" name="url" 
                                   value="<?= htmlspecialchars($resource['url'] ?? '') ?>">
                        </div>

                        <div class="col-md-12">
                            <label for="summary" class="form-label">요약 *</label>
                            <textarea class="form-control" id="summary" name="summary" rows="3" required><?= htmlspecialchars($resource['summary']) ?></textarea>
                            <div class="invalid-feedback">요약을 입력해주세요.</div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">유형 <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_website" value="Website">
                                    <label class="form-check-label" for="source_type_website">웹사이트</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_paper" value="Paper">
                                    <label class="form-check-label" for="source_type_paper">논문</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_book" value="Book">
                                    <label class="form-check-label" for="source_type_book">도서</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_video" value="Video" checked>
                                    <label class="form-check-label" for="source_type_video">비디오</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_podcast" value="Podcast">
                                    <label class="form-check-label" for="source_type_podcast">팟캐스트</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_personal" value="Personal Experience">
                                    <label class="form-check-label" for="source_type_personal">개인 경험</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_other" value="Other">
                                    <label class="form-check-label" for="source_type_other">기타</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 상세 내용 -->
                <div class="mb-4">
                    <h4>상세 내용</h4>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="content" class="form-label"><?= __('resource.content') ?></label>
                            <textarea class="form-control tinymce-editor" id="content" name="content" rows="5"># 횡격막 호흡과 복식 호흡이 안 되는 5가지 원인과 해결 방안

## 1. 잘못된 자세
- **원인**: 구부정한 자세로 인해 횡격막이 제대로 움직이지 못함
- **해결방안**: 
  - 바른 자세 유지하기
  - 등 스트레칭 정기적으로 하기
  - 자세 교정 운동하기

## 2. 스트레스와 긴장
- **원인**: 스트레스로 인한 근육 긴장이 호흡을 방해
- **해결방안**:
  - 명상과 이완 운동
  - 규칙적인 운동
  - 충분한 휴식

## 3. 잘못된 호흡 습관
- **원인**: 오랜 기간 잘못된 호흡 패턴 형성
- **해결방안**:
  - 호흡 운동 꾸준히 하기
  - 전문가와 함께 올바른 호흡법 배우기
  - 일상생활에서 의식적으로 복식호흡 하기

## 4. 신체적 제한
- **원인**: 횡격막 기능 저하나 신체적 문제
- **해결방안**:
  - 의사와 상담
  - 물리치료
  - 단계적인 호흡 운동

## 5. 환경적 요인
- **원인**: 공기 질 나쁨, 알레르기 등
- **해결방안**:
  - 공기청정기 사용
  - 알레르기 원인 제거
  - 환기 자주하기

## 실천 방법
1. 매일 10분씩 호흡 운동하기
2. 자세 체크 알람 설정하기
3. 스트레스 관리 루틴 만들기
4. 전문가 상담 받기
5. 호흡 일지 작성하기</textarea>
                        </div>

                        <div class="col-md-12">
                            <label for="initial_impression" class="form-label"><?= __('resource.initial_impression') ?></label>
                            <textarea class="form-control tinymce-editor" id="initial_impression" name="initial_impression" rows="3"><?= htmlspecialchars($resource['initial_impression'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-12">
                            <label for="personal_connection" class="form-label"><?= __('resource.personal_connection') ?></label>
                            <textarea class="form-control tinymce-editor" id="personal_connection" name="personal_connection" rows="3"><?= htmlspecialchars($resource['personal_connection'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-12">
                            <label for="reflection_insights" class="form-label"><?= __('resource.reflection_insights') ?></label>
                            <textarea class="form-control tinymce-editor" id="reflection_insights" name="reflection_insights" rows="3"><?= htmlspecialchars($resource['reflection_insights'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-12">
                            <label for="application_ideas" class="form-label"><?= __('resource.application_ideas') ?></label>
                            <textarea class="form-control tinymce-editor" id="application_ideas" name="application_ideas" rows="3"><?= htmlspecialchars($resource['application_ideas'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-12">
                            <label for="description" class="form-label">설명 *</label>
                            <textarea class="form-control tinymce-editor" id="description" name="description" rows="5"><?= htmlspecialchars($resource['description'] ?? '') ?></textarea>
                            <div class="invalid-feedback">설명을 입력해주세요.</div>
                        </div>
                    </div>
                </div>

                <!-- 태그 -->
                <div class="mb-4">
                    <h4>태그</h4>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="tags" class="form-label">태그</label>
                            <input type="text" class="form-control" id="tags" name="tags" value="Laravel,PHP">
                            <small class="text-muted">쉼표(,)로 구분하여 입력하세요.</small>
                        </div>
                    </div>
                </div>

                <!-- 설정 -->
                <div class="mb-4">
                    <h4>설정</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="visibility_public" name="visibility" value="public"
                                <?= ($resource['visibility'] ?? '') === 'public' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="visibility_public">공개</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="radio" class="form-check-input" id="visibility_private" name="visibility" value="private"
                                <?= ($resource['visibility'] ?? '') === 'private' ? 'checked' : '' ?>>
                                <label class="form-check-label" for="visibility_private">비공개</label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 버튼 -->
                <div class="d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $resource['id'] ?>)">
                        삭제
                    </button>
                    <div>
                        <a href="/resource/view/<?= $resource['id'] ?>" class="btn btn-outline-secondary me-2">취소</a>
                        <button type="submit" class="btn btn-primary">저장</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 삭제 확인 모달 -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">리소스 삭제</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>정말로 이 리소스를 삭제하시겠습니까?</p>
                <p class="text-danger">이 작업은 되돌릴 수 없습니다.</p>
            </div>
            <div class="modal-footer">
                <form action="/resource/delete/<?= $resource['id'] ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                    <button type="submit" class="btn btn-danger">삭제</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Select2 및 폼 검증 스크립트 -->
<script src="https://cdn.tiny.cloud/1/3p683d001w10l44tgvyk034uz5nsntitn1eiyjs24ufhx67a/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // TinyMCE 초기화
    tinymce.init({
        selector: '.tinymce-editor',
        height: 400,
        menubar: true,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic | alignleft aligncenter ' +
            'alignright alignjustify | bullist numlist outdent indent | ' +
            'image media table | removeformat | help',
        content_style: `
            body { 
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
                font-size: 14px;
                background-color: var(--input-bg);
                color: var(--text-color);
            }
        `,
        skin: 'oxide-dark',
        content_css: 'dark',
        branding: false,
        promotion: false,
        statusbar: false,
        resize: false,
        images_upload_url: '/upload/image',
        images_upload_handler: function (blobInfo, success, failure) {
            const formData = new FormData();
            formData.append('image', blobInfo.blob(), blobInfo.filename());
            formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');

            fetch('/upload/image', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    success(data.url);
                } else {
                    failure(data.error || '이미지 업로드에 실패했습니다.');
                }
            })
            .catch(error => {
                failure('이미지 업로드 중 오류가 발생했습니다.');
            });
        },
        images_reuse_filename: true,
        automatic_uploads: true,
        file_picker_types: 'image',
        images_upload_base_path: '/'
    });

    // Select2 초기화
    $('#tags').select2({
        tags: true,
        tokenSeparators: [',', ' '],
        placeholder: "태그를 입력하거나 선택하세요",
        allowClear: true,
        width: '100%'
    });

    // 폼 검증
    const form = document.querySelector('.needs-validation');
    form.addEventListener('submit', function(event) {
        if (!form.checkValidity()) {
            event.preventDefault();
            event.stopPropagation();
        }
        form.classList.add('was-validated');
    });
});

// 삭제 확인 모달
function confirmDelete(resourceId) {
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
</body>
</html> 