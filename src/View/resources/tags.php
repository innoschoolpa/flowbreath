<?php
// src/View/resources/tags.php
require __DIR__ . '/../layouts/header.php';
?>

<div class="container mt-4">
    <h1 class="mb-4">태그 관리</h1>

    <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
        <!-- 태그 추가 폼 -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">새 태그 추가</h5>
                <form action="/resources/tags/create" method="POST" class="row g-3 align-items-end">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="col-md-4">
                        <label for="tagName" class="form-label">태그 이름</label>
                        <input type="text" class="form-control" id="tagName" name="tag_name" required>
                    </div>
                    <div class="col-md-4">
                        <label for="tagDescription" class="form-label">설명 (선택사항)</label>
                        <input type="text" class="form-control" id="tagDescription" name="description">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn btn-primary">태그 추가</button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <!-- 태그 목록 -->
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-4">현재 태그</h5>
            <?php if (!empty($tags)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>태그 이름</th>
                                <th>설명</th>
                                <th>사용 횟수</th>
                                <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
                                    <th>작업</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tags as $tag): ?>
                                <tr>
                                    <td>
                                        <a href="/resources?tag_ids[]=<?= $tag['id'] ?>" class="text-decoration-none">
                                            #<?= htmlspecialchars($tag['name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($tag['description'] ?? '') ?></td>
                                    <td><?= number_format($tag['count'] ?? 0) ?></td>
                                    <?php if (isset($_SESSION['user']) && $_SESSION['user']['is_admin']): ?>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" 
                                                        class="btn btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editTagModal<?= $tag['id'] ?>">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button"
                                                        class="btn btn-outline-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#deleteTagModal<?= $tag['id'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>

                                            <!-- 수정 모달 -->
                                            <div class="modal fade" id="editTagModal<?= $tag['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="/resources/tags/update/<?= $tag['id'] ?>" method="POST">
                                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">태그 수정</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label for="editTagName<?= $tag['id'] ?>" class="form-label">태그 이름</label>
                                                                    <input type="text" 
                                                                           class="form-control" 
                                                                           id="editTagName<?= $tag['id'] ?>" 
                                                                           name="tag_name" 
                                                                           value="<?= htmlspecialchars($tag['name']) ?>" 
                                                                           required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="editTagDescription<?= $tag['id'] ?>" class="form-label">설명</label>
                                                                    <input type="text" 
                                                                           class="form-control" 
                                                                           id="editTagDescription<?= $tag['id'] ?>" 
                                                                           name="description" 
                                                                           value="<?= htmlspecialchars($tag['description'] ?? '') ?>">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                                                                <button type="submit" class="btn btn-primary">저장</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 삭제 모달 -->
                                            <div class="modal fade" id="deleteTagModal<?= $tag['id'] ?>" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">태그 삭제 확인</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>정말로 태그 "<?= htmlspecialchars($tag['name']) ?>"를 삭제하시겠습니까?</p>
                                                            <p class="text-danger">이 작업은 되돌릴 수 없으며, 이 태그를 사용하는 모든 리소스에서 태그가 제거됩니다.</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                                                            <form action="/resources/tags/delete/<?= $tag['id'] ?>" method="POST" style="display: inline;">
                                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                                <button type="submit" class="btn btn-danger">삭제</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted">등록된 태그가 없습니다.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?> 