<?php
/**
 * src/View/resource/edit.php
 * 리소스 수정 페이지
 */

// 디버깅을 위한 로그
error_log("edit.php loaded with resource data: " . print_r($resource, true));
error_log("Tags data: " . print_r($tags, true));

require_once __DIR__ . '/../layout/header.php'; ?>

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
                    </div>
                </div>

                <!-- 상세 내용 -->
                <div class="mb-4">
                    <h4>상세 내용</h4>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="content" class="form-label"><?= __('resource.content') ?></label>
                            <textarea class="form-control tinymce-editor" id="content" name="content" rows="5"><?= htmlspecialchars($resource['content'] ?? '') ?></textarea>
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
                    </div>
                </div>

                <!-- 태그 -->
                <div class="mb-4">
                    <h4>태그</h4>
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="tags" class="form-label">태그</label>
                            <select class="form-select" id="tags" name="tags[]" multiple>
                                <?php foreach ($all_tags as $tag): ?>
                                    <option value="<?= $tag['id'] ?>" 
                                        <?= in_array($tag['id'], $current_tag_ids ?? []) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($tag['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">새로운 태그를 추가하려면 입력 후 Enter를 누르세요.</div>
                        </div>
                    </div>
                </div>

                <!-- 설정 -->
                <div class="mb-4">
                    <h4>설정</h4>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1"
                                    <?= $resource['is_public'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_public">공개</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_pinned" name="is_pinned" value="1"
                                    <?= $resource['is_pinned'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_pinned">상단 고정</label>
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
<script>
document.addEventListener('DOMContentLoaded', function() {
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

<?php require_once __DIR__ . '/../layout/footer.php'; ?> 