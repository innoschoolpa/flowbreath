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
document.addEventListener('DOMContentLoaded', function() {
    ClassicEditor
        .create(document.querySelector('#content'), {
            language: 'ko',
            toolbar: [
                'heading', '|', 'bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList', 'blockQuote',
                '|', 'insertTable', 'codeBlock', 'undo', 'redo'
            ]
        })
        .then(editor => {
            editor.ui.view.editable.element.style.height = '400px';
            ckeditorInstance = editor;
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
});
</script> 