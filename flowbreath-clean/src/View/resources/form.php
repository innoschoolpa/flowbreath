<div class="mb-3">
    <label for="title" class="form-label"><?= $this->lang('resources.title') ?></label>
    <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($resource['title'] ?? '') ?>" required>
</div>

<div class="mb-3">
    <label for="language" class="form-label"><?= $this->lang('resources.language') ?></label>
    <select class="form-select" id="language" name="language">
        <option value="ko" <?= ($resource['language'] ?? 'ko') === 'ko' ? 'selected' : '' ?>>한국어</option>
        <option value="en" <?= ($resource['language'] ?? 'ko') === 'en' ? 'selected' : '' ?>>English</option>
    </select>
</div>

<div class="mb-3">
    <label for="content" class="form-label"><?= $this->lang('resources.content') ?></label>
    <textarea class="form-control" id="content" name="content" rows="10" required><?= htmlspecialchars($resource['content'] ?? '') ?></textarea>
</div> 