<?php
$apiKeys = require __DIR__ . '/../../Config/api_keys.php';
$tinymceApiKey = $apiKeys['tinymce']['api_key'];
?>

<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h2><?= __('diary.edit') ?></h2>
            <form id="diaryForm" method="POST" action="/diary/<?= $diary['id'] ?? '' ?>">
                <div class="mb-3">
                    <label for="title" class="form-label"><?= __('diary.title') ?></label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?= htmlspecialchars($diary['title'] ?? '') ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="content" class="form-label"><?= __('diary.content') ?></label>
                    <textarea id="content" name="content"><?= htmlspecialchars($diary['content'] ?? '') ?></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="tags" class="form-label"><?= __('diary.tags') ?></label>
                    <input type="text" class="form-control" id="tags" name="tags" 
                           value="<?= htmlspecialchars($diary['tags'] ?? '') ?>" 
                           placeholder="<?= __('diary.tags_placeholder') ?>">
                    <div class="form-text"><?= __('diary.tags_help') ?></div>
                </div>
                
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1"
                               <?= isset($diary['is_public']) && $diary['is_public'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_public"><?= __('diary.public') ?></label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <button type="submit" class="btn btn-primary"><?= __('diary.save') ?></button>
                    <a href="/diary" class="btn btn-secondary"><?= __('common.cancel') ?></a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.tiny.cloud/1/<?= $tinymceApiKey ?>/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    tinymce.init({
        selector: '#content',
        plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
        toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
        height: 500,
        images_upload_url: '/upload/image',
        images_upload_handler: function (blobInfo, success, failure) {
            var xhr, formData;
            xhr = new XMLHttpRequest();
            xhr.withCredentials = false;
            xhr.open('POST', '/upload/image');
            xhr.onload = function() {
                var json;
                if (xhr.status != 200) {
                    failure('HTTP Error: ' + xhr.status);
                    return;
                }
                json = JSON.parse(xhr.responseText);
                if (!json || typeof json.location != 'string') {
                    failure('Invalid JSON: ' + xhr.responseText);
                    return;
                }
                success(json.location);
            };
            formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());
            xhr.send(formData);
        }
    });
});

document.getElementById('diaryForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('content', tinymce.get('content').getContent());
    
    fetch(this.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/diary/' + data.id;
        } else {
            alert(data.message || '<?= __('diary.update_error') ?>');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('<?= __('diary.update_error') ?>');
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 