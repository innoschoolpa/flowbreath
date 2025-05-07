<?php
// src/View/resources/tags.php
load_view('layout/header', ['title' => '태그 관리']);
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h4 class="mb-0">태그 관리</h4>
                </div>
                <div class="card-body">
                    <h5 class="card-title"><?php echo htmlspecialchars($resource['title']); ?></h5>
                    
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $err): ?>
                                <div><?php echo htmlspecialchars($err); ?></div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- 현재 태그 목록 -->
                    <div class="mb-4">
                        <h6>현재 태그</h6>
                        <?php if (!empty($current_tags)): ?>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($current_tags as $tag): ?>
                                    <span class="badge bg-primary">
                                        <?php echo htmlspecialchars($tag['tag_name']); ?>
                                        <form action="/resources/remove-tag/<?php echo $resource['resource_id']; ?>" method="POST" class="d-inline">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="tag_id" value="<?php echo $tag['tag_id']; ?>">
                                            <button type="submit" class="btn-close btn-close-white ms-1" style="font-size: 0.5rem;"></button>
                                        </form>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">등록된 태그가 없습니다.</p>
                        <?php endif; ?>
                    </div>

                    <!-- 새 태그 추가 폼 -->
                    <form action="/resources/add-tag/<?php echo $resource['resource_id']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label for="new_tag" class="form-label">새 태그 추가</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="new_tag" name="tag_name" placeholder="새 태그 이름">
                                <button type="submit" class="btn btn-primary">추가</button>
                            </div>
                        </div>
                    </form>

                    <!-- 기존 태그 추가 폼 -->
                    <form action="/resources/add-existing-tag/<?php echo $resource['resource_id']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="mb-3">
                            <label for="existing_tag" class="form-label">기존 태그 추가</label>
                            <div class="input-group">
                                <select class="form-select" id="existing_tag" name="tag_id">
                                    <option value="">태그 선택</option>
                                    <?php foreach ($available_tags as $tag): ?>
                                        <option value="<?php echo $tag['tag_id']; ?>">
                                            <?php echo htmlspecialchars($tag['tag_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">추가</button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="card-footer">
                    <a href="/resources" class="btn btn-secondary">목록으로</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
load_view('layout/footer');
?> 