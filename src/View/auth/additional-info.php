<?php require_once __DIR__ . '/../layouts/header.php'; ?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">추가 정보 입력</div>
                <div class="card-body">
                    <form id="additionalInfoForm" method="POST" action="/auth/additional-info">
                        <div class="mb-3">
                            <label for="name" class="form-label">이름</label>
                            <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="bio" class="form-label">자기소개</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3" placeholder="자기소개를 입력해주세요."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">저장하기</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 