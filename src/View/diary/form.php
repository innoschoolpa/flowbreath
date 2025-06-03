<?php
$apiKeys = require __DIR__ . '/../../Config/api_keys.php';
$tinymceApiKey = $apiKeys['tinymce']['api_key'];

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
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<style>
// ... existing code ...
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4"><?= $pageTitle ?></h2>
                    
                    <form id="diaryForm" method="POST" action="<?= $formAction ?>">
                        <?php if ($isEdit): ?>
                            <input type="hidden" name="_method" value="PUT">
                        <?php endif; ?>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="mb-3">
                            <label for="title" class="form-label"><?= __('diary.title') ?></label>
                            <input type="text" class="form-control" id="title" name="title" 
                                   value="<?= htmlspecialchars($diary['title']) ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="content" class="form-label"><?= __('diary.content') ?></label>
                            <textarea id="content" name="content"><?= htmlspecialchars($diary['content']) ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="tags" class="form-label"><?= __('diary.tags') ?></label>
                            <input type="text" class="form-control" id="tags" name="tags" 
                                   value="<?= htmlspecialchars($diary['tags']) ?>"
                                   placeholder="<?= __('diary.tags_placeholder') ?>">
                            <div class="form-text"><?= __('diary.tags_help') ?></div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="is_public" name="is_public" 
                                   <?= $diary['is_public'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="is_public">
                                <?= __('diary.public') ?>
                            </label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="<?= $cancelUrl ?>" class="btn btn-outline-secondary">
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
// ... existing TinyMCE configuration ...

document.getElementById('diaryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('content', tinymce.get('content').getContent());
    
    fetch(this.action, {
        method: '<?= $isEdit ? 'PUT' : 'POST' ?>',
        body: formData
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