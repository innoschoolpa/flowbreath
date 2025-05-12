<?php
// src/View/resources/bookmarks.php
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>북마크 - Flowbreath</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="/resources">리소스 목록</a></li>
                <li class="breadcrumb-item active">북마크</li>
            </ol>
        </nav>
    </div>

    <!-- 폴더 목록 -->
    <div class="row">
        <div class="col-md-3">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">폴더</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="/resources/bookmarks" 
                       class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
                              <?php echo empty($current_folder) ? 'active' : ''; ?>">
                        <span><i class="bi bi-folder"></i> 전체</span>
                        <span class="badge bg-secondary rounded-pill">
                            <?php echo array_sum(array_column($folders, 'count')); ?>
                        </span>
                    </a>
                    <?php foreach ($folders as $folder): ?>
                        <a href="/resources/bookmarks?folder=<?php echo urlencode($folder['folder_name']); ?>" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center
                                  <?php echo ($current_folder === $folder['folder_name']) ? 'active' : ''; ?>">
                            <span>
                                <i class="bi bi-folder"></i>
                                <?php echo htmlspecialchars($folder['folder_name']); ?>
                            </span>
                            <span class="badge bg-secondary rounded-pill">
                                <?php echo $folder['count']; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100" onclick="showNewFolderDialog()">
                        <i class="bi bi-folder-plus"></i> 새 폴더
                    </button>
                </div>
            </div>
        </div>

        <!-- 북마크 목록 -->
        <div class="col-md-9">
            <?php if (!empty($resources)): ?>
                <div class="list-group mb-4">
                <?php foreach ($resources as $resource): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-1">
                                    <a href="/resources/<?php echo $resource['id']; ?>" class="text-decoration-none">
                                        <?php echo htmlspecialchars($resource['title']); ?>
                                    </a>
                                </h5>
                                <p class="mb-1 text-muted small">
                                    <span><i class="bi bi-folder"></i> <?php echo htmlspecialchars($resource['folder_name']); ?></span>
                                    <span class="ms-2"><i class="bi bi-calendar3"></i> <?php echo date('Y-m-d', strtotime($resource['bookmarked_at'])); ?></span>
                                </p>
                                <?php if (!empty($resource['note'])): ?>
                                    <p class="mb-0 text-muted">
                                        <?php echo nl2br(htmlspecialchars($resource['note'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        onclick="editBookmark(<?php echo $resource['id']; ?>)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                        onclick="removeBookmark(<?php echo $resource['id']; ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> 북마크된 리소스가 없습니다.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 새 폴더 다이얼로그 -->
<div class="modal fade" id="newFolderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">새 폴더</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">폴더 이름</label>
                    <input type="text" class="form-control" id="newFolderName">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">취소</button>
                <button type="button" class="btn btn-primary" onclick="createFolder()">생성</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function showNewFolderDialog() {
    const modal = new bootstrap.Modal(document.getElementById('newFolderModal'));
    modal.show();
}

function createFolder() {
    const folderName = document.getElementById('newFolderName').value.trim();
    if (!folderName) {
        alert('폴더 이름을 입력해주세요.');
        return;
    }

    // 새로운 폴더로 이동
    window.location.href = `/resources/bookmarks?folder=${encodeURIComponent(folderName)}`;
}

function editBookmark(resourceId) {
    // 북마크 수정 페이지로 이동
    window.location.href = `/resources/${resourceId}/bookmark/edit`;
}

function removeBookmark(resourceId) {
    if (confirm('이 북마크를 삭제하시겠습니까?')) {
        fetch(`/resources/${resourceId}/bookmark`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.error || '북마크 삭제에 실패했습니다.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('북마크 삭제 중 오류가 발생했습니다.');
        });
    }
}
</script>
</body>
</html> 