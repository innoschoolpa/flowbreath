<?php $currentPage = 'resources'; ?>

<div class="container">
    <div class="card">
        <div class="card-header">
            <h2>SQL 리소스</h2>
            <button class="btn btn-primary" id="uploadButton">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4"></path>
                </svg>
                SQL 파일 업로드
            </button>
        </div>

        <div class="resource-filters">
            <div class="form-group">
                <input type="text" class="form-input" placeholder="리소스 검색..." id="searchInput">
            </div>
            <div class="form-group">
                <select class="form-input" id="statusFilter">
                    <option value="">모든 상태</option>
                    <option value="pending">대기 중</option>
                    <option value="processing">처리 중</option>
                    <option value="completed">완료</option>
                    <option value="failed">실패</option>
                </select>
            </div>
            <div class="form-group">
                <select class="form-input" id="sortFilter">
                    <option value="created_at-desc">최신순</option>
                    <option value="created_at-asc">오래된순</option>
                    <option value="name-asc">이름순</option>
                    <option value="size-desc">크기순</option>
                </select>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAll" class="form-checkbox">
                        </th>
                        <th>이름</th>
                        <th>크기</th>
                        <th>상태</th>
                        <th>생성일</th>
                        <th>작업</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resources as $resource): ?>
                    <tr>
                        <td>
                            <input type="checkbox" class="form-checkbox resource-checkbox" value="<?php echo $resource['id']; ?>">
                        </td>
                        <td>
                            <a href="/resources/<?php echo $resource['id']; ?>" class="resource-link">
                                <?php echo htmlspecialchars($resource['name']); ?>
                            </a>
                        </td>
                        <td><?php echo formatBytes($resource['size']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $resource['status']; ?>">
                                <?php echo $statusLabels[$resource['status']]; ?>
                            </span>
                        </td>
                        <td><?php echo formatDate($resource['created_at']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-secondary btn-sm" title="실행" onclick="executeResource('<?php echo $resource['id']; ?>')">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M5 3l14 9-14 9V3z"></path>
                                    </svg>
                                </button>
                                <button class="btn btn-secondary btn-sm" title="다운로드" onclick="downloadResource('<?php echo $resource['id']; ?>')">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </button>
                                <button class="btn btn-danger btn-sm" title="삭제" onclick="deleteResource('<?php echo $resource['id']; ?>')">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="pagination">
            <?php if ($currentPage > 1): ?>
            <a href="?page=<?php echo $currentPage - 1; ?>" class="btn btn-secondary">이전</a>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="btn <?php echo $i === $currentPage ? 'btn-primary' : 'btn-secondary'; ?>">
                <?php echo $i; ?>
            </a>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
            <a href="?page=<?php echo $currentPage + 1; ?>" class="btn btn-secondary">다음</a>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- 파일 업로드 모달 -->
<div class="modal" id="uploadModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>SQL 파일 업로드</h3>
            <button class="modal-close" id="closeModal">×</button>
        </div>
        <div class="modal-body">
            <form id="uploadForm" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="form-label">파일 선택</label>
                    <input type="file" class="form-input" name="sql_file" accept=".sql,text/plain" required>
                </div>
                <div class="form-group">
                    <label class="form-label">설명</label>
                    <textarea class="form-input" name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">태그</label>
                    <input type="text" class="form-input" name="tags" placeholder="쉼표로 구분">
                </div>
                <div class="upload-progress" style="display: none;">
                    <div class="progress">
                        <div class="progress-bar" style="width: 0%"></div>
                    </div>
                    <div class="progress-text">0%</div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelUpload">취소</button>
            <button class="btn btn-primary" id="submitUpload">업로드</button>
        </div>
    </div>
</div>

<style>
.resource-filters {
    display: flex;
    gap: 1rem;
    margin-bottom: var(--spacing-lg);
    flex-wrap: wrap;
}

.resource-filters .form-group {
    flex: 1;
    min-width: 200px;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 0.5rem;
    margin-top: var(--spacing-lg);
}

.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background-color: white;
    margin: 10vh auto;
    padding: var(--spacing-lg);
    max-width: 500px;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0.5rem;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: var(--spacing-lg);
}

.form-checkbox {
    width: 1rem;
    height: 1rem;
    border-radius: 0.25rem;
    border: 1px solid var(--border-color);
    cursor: pointer;
}

.upload-progress {
    margin-top: var(--spacing-md);
}

.progress-text {
    text-align: center;
    margin-top: var(--spacing-xs);
    font-size: var(--small-text);
    color: var(--secondary-color);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadButton = document.getElementById('uploadButton');
    const uploadModal = document.getElementById('uploadModal');
    const closeModal = document.getElementById('closeModal');
    const cancelUpload = document.getElementById('cancelUpload');
    const submitUpload = document.getElementById('submitUpload');
    const uploadForm = document.getElementById('uploadForm');
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const sortFilter = document.getElementById('sortFilter');
    const selectAll = document.getElementById('selectAll');
    
    // 모달 제어
    uploadButton.addEventListener('click', () => {
        uploadModal.style.display = 'block';
    });
    
    [closeModal, cancelUpload].forEach(button => {
        button.addEventListener('click', () => {
            uploadModal.style.display = 'none';
            uploadForm.reset();
        });
    });
    
    // 파일 업로드
    submitUpload.addEventListener('click', async () => {
        const formData = new FormData(uploadForm);
        const progressBar = document.querySelector('.progress-bar');
        const progressText = document.querySelector('.progress-text');
        const uploadProgress = document.querySelector('.upload-progress');
        
        uploadProgress.style.display = 'block';
        
        try {
            const response = await fetch('/api/resources/sql', {
                method: 'POST',
                body: formData,
                onUploadProgress: (progressEvent) => {
                    const progress = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                    progressBar.style.width = `${progress}%`;
                    progressText.textContent = `${progress}%`;
                }
            });
            
            if (!response.ok) throw new Error('업로드 실패');
            
            const result = await response.json();
            showNotification('success', '파일이 성공적으로 업로드되었습니다.');
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            showNotification('error', '파일 업로드 중 오류가 발생했습니다.');
        }
    });
    
    // 필터링
    [searchInput, statusFilter, sortFilter].forEach(filter => {
        filter.addEventListener('change', () => {
            const params = new URLSearchParams(window.location.search);
            params.set('search', searchInput.value);
            params.set('status', statusFilter.value);
            params.set('sort', sortFilter.value);
            window.location.search = params.toString();
        });
    });
    
    // 전체 선택
    selectAll.addEventListener('change', () => {
        document.querySelectorAll('.resource-checkbox').forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    });
});

// 리소스 작업
async function executeResource(id) {
    try {
        const response = await fetch(`/api/resources/${id}/execute`, {
            method: 'POST'
        });
        
        if (!response.ok) throw new Error('실행 실패');
        
        showNotification('success', 'SQL 실행이 시작되었습니다.');
    } catch (error) {
        showNotification('error', 'SQL 실행 중 오류가 발생했습니다.');
    }
}

async function downloadResource(id) {
    try {
        const response = await fetch(`/api/resources/${id}/download`);
        if (!response.ok) throw new Error('다운로드 실패');
        
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `resource-${id}.sql`;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    } catch (error) {
        showNotification('error', '파일 다운로드 중 오류가 발생했습니다.');
    }
}

async function deleteResource(id) {
    if (!confirm('정말 이 리소스를 삭제하시겠습니까?')) return;
    
    try {
        const response = await fetch(`/api/resources/${id}`, {
            method: 'DELETE'
        });
        
        if (!response.ok) throw new Error('삭제 실패');
        
        showNotification('success', '리소스가 삭제되었습니다.');
        setTimeout(() => window.location.reload(), 1500);
    } catch (error) {
        showNotification('error', '리소스 삭제 중 오류가 발생했습니다.');
    }
}

function showNotification(type, message) {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script> 