<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4"><?= __('diary.create') ?></h2>
                    
                    <form id="diaryForm" onsubmit="return submitDiary(event)">
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

<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
tinymce.init({
    selector: '#content',
    plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
    toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
    height: 500,
    images_upload_url: '/upload/image',
    images_upload_handler: function (blobInfo, success, failure) {
        const formData = new FormData();
        formData.append('file', blobInfo.blob(), blobInfo.filename());

        fetch('/upload/image', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.location) {
                success(data.location);
            } else {
                failure('Image upload failed');
            }
        })
        .catch(error => {
            failure('Image upload failed');
        });
    },
    setup: function(editor) {
        editor.on('change', function() {
            editor.save();
        });
    }
});

function submitDiary(event) {
    event.preventDefault();
    
    const form = event.target;
    const formData = new FormData(form);
    
    fetch('/diary', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = `/diary/${data.diary_id}`;
        } else {
            alert(data.error || '<?= __('diary.save_error') ?>');
        }
    })
    .catch(error => {
        alert('<?= __('diary.save_error') ?>');
    });
    
    return false;
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 