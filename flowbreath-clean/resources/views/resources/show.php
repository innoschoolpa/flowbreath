<?php $currentPage = 'resources'; ?>

<div class="container">
    <div class="resource-header">
        <div class="resource-title">
            <h2><?php echo htmlspecialchars($resource['name']); ?></h2>
            <span class="status-badge status-<?php echo $resource['status']; ?>">
                <?php echo $statusLabels[$resource['status']]; ?>
            </span>
        </div>
        <div class="resource-actions">
            <button class="btn btn-primary" id="executeButton">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M5 3l14 9-14 9V3z"></path>
                </svg>
                실행
            </button>
            <button class="btn btn-secondary" id="downloadButton">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                다운로드
            </button>
            <button class="btn btn-danger" id="deleteButton">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
                삭제
            </button>
        </div>
    </div>

    <div class="resource-info">
        <div class="info-grid">
            <div class="info-item">
                <div class="info-label">크기</div>
                <div class="info-value"><?php echo formatBytes($resource['size']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">생성일</div>
                <div class="info-value"><?php echo formatDate($resource['created_at']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">수정일</div>
                <div class="info-value"><?php echo formatDate($resource['updated_at']); ?></div>
            </div>
            <div class="info-item">
                <div class="info-label">SQL 문장 수</div>
                <div class="info-value"><?php echo formatStatementCount($resource['statement_count']); ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>SQL 내용</h3>
            <div class="sql-actions">
                <button class="btn btn-secondary btn-sm" id="copyButton">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                    </svg>
                    복사
                </button>
                <button class="btn btn-secondary btn-sm" id="formatButton">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                    포맷
                </button>
            </div>
        </div>
        <div class="sql-content">
            <pre><code class="language-sql"><?php echo htmlspecialchars($resource['content']); ?></code></pre>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>실행 기록</h3>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>실행 시간</th>
                        <th>상태</th>
                        <th>처리된 문장</th>
                        <th>소요 시간</th>
                        <th>메모리 사용량</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($executions as $execution): ?>
                    <tr>
                        <td><?php echo formatDate($execution['started_at']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo $execution['status']; ?>">
                                <?php echo $executionStatusLabels[$execution['status']]; ?>
                            </span>
                        </td>
                        <td><?php echo formatStatementCount($execution['statements_executed']); ?></td>
                        <td><?php echo formatDuration($execution['duration']); ?></td>
                        <td><?php echo formatBytes($execution['memory_used']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- 실행 옵션 모달 -->
<div class="modal" id="executeModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>실행 옵션</h3>
            <button class="modal-close" id="closeExecuteModal">×</button>
        </div>
        <div class="modal-body">
            <form id="executeForm">
                <div class="form-group">
                    <label class="form-label">트랜잭션 크기</label>
                    <input type="number" class="form-input" name="transaction_size" value="1000" min="1">
                    <div class="form-help">한 번의 트랜잭션에서 처리할 SQL 문장 수</div>
                </div>
                <div class="form-group">
                    <label class="form-label">청크 크기</label>
                    <input type="number" class="form-input" name="chunk_size" value="1048576" min="1">
                    <div class="form-help">파일을 읽을 때 사용할 청크 크기 (바이트)</div>
                </div>
                <div class="form-group">
                    <label class="form-label">최대 메모리 사용량</label>
                    <input type="range" class="form-range" name="max_memory_usage" value="70" min="10" max="90" step="5">
                    <div class="range-value">70%</div>
                    <div class="form-help">허용할 최대 메모리 사용량 비율</div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" id="cancelExecute">취소</button>
            <button class="btn btn-primary" id="confirmExecute">실행</button>
        </div>
    </div>
</div>

<style>
.resource-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--spacing-lg);
}

.resource-title {
    display: flex;
    align-items: center;
    gap: var(--spacing-md);
}

.resource-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--spacing-md);
    margin-bottom: var(--spacing-lg);
}

.info-item {
    background: white;
    padding: var(--spacing-md);
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.info-label {
    color: var(--secondary-color);
    font-size: var(--small-text);
    margin-bottom: var(--spacing-xs);
}

.info-value {
    font-size: var(--heading-3);
    font-weight: 600;
    color: var(--text-color);
}

.sql-content {
    background: #1e1e1e;
    border-radius: 0.5rem;
    padding: var(--spacing-md);
    overflow-x: auto;
}

.sql-content pre {
    margin: 0;
    font-family: 'Fira Code', monospace;
    font-size: 0.875rem;
    line-height: 1.5;
    color: #d4d4d4;
}

.sql-actions {
    display: flex;
    gap: var(--spacing-sm);
}

.form-range {
    width: 100%;
    height: 0.5rem;
    border-radius: 0.25rem;
    background: var(--border-color);
    outline: none;
    -webkit-appearance: none;
}

.form-range::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 1rem;
    height: 1rem;
    border-radius: 50%;
    background: var(--primary-color);
    cursor: pointer;
}

.range-value {
    text-align: center;
    margin-top: var(--spacing-xs);
    font-size: var(--small-text);
    color: var(--secondary-color);
}

.form-help {
    font-size: var(--small-text);
    color: var(--secondary-color);
    margin-top: var(--spacing-xs);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 실행 모달
    const executeButton = document.getElementById('executeButton');
    const executeModal = document.getElementById('executeModal');
    const closeExecuteModal = document.getElementById('closeExecuteModal');
    const cancelExecute = document.getElementById('cancelExecute');
    const confirmExecute = document.getElementById('confirmExecute');
    const executeForm = document.getElementById('executeForm');
    const memoryRange = executeForm.querySelector('input[name="max_memory_usage"]');
    const rangeValue = executeForm.querySelector('.range-value');
    
    executeButton.addEventListener('click', () => {
        executeModal.style.display = 'block';
    });
    
    [closeExecuteModal, cancelExecute].forEach(button => {
        button.addEventListener('click', () => {
            executeModal.style.display = 'none';
        });
    });
    
    memoryRange.addEventListener('input', () => {
        rangeValue.textContent = memoryRange.value + '%';
    });
    
    // SQL 실행
    confirmExecute.addEventListener('click', async () => {
        const formData = new FormData(executeForm);
        const options = Object.fromEntries(formData.entries());
        
        try {
            const response = await fetch(`/api/resources/<?php echo $resource['id']; ?>/execute`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(options)
            });
            
            if (!response.ok) throw new Error('실행 실패');
            
            const result = await response.json();
            showNotification('success', 'SQL 실행이 시작되었습니다.');
            executeModal.style.display = 'none';
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            showNotification('error', 'SQL 실행 중 오류가 발생했습니다.');
        }
    });
    
    // 다운로드
    const downloadButton = document.getElementById('downloadButton');
    downloadButton.addEventListener('click', async () => {
        try {
            const response = await fetch(`/api/resources/<?php echo $resource['id']; ?>/download`);
            if (!response.ok) throw new Error('다운로드 실패');
            
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = '<?php echo htmlspecialchars($resource['name']); ?>';
            document.body.appendChild(a);
            a.click();
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
        } catch (error) {
            showNotification('error', '파일 다운로드 중 오류가 발생했습니다.');
        }
    });
    
    // 삭제
    const deleteButton = document.getElementById('deleteButton');
    deleteButton.addEventListener('click', async () => {
        if (!confirm('정말 이 리소스를 삭제하시겠습니까?')) return;
        
        try {
            const response = await fetch(`/api/resources/<?php echo $resource['id']; ?>`, {
                method: 'DELETE'
            });
            
            if (!response.ok) throw new Error('삭제 실패');
            
            showNotification('success', '리소스가 삭제되었습니다.');
            setTimeout(() => window.location.href = '/resources', 1500);
        } catch (error) {
            showNotification('error', '리소스 삭제 중 오류가 발생했습니다.');
        }
    });
    
    // SQL 복사
    const copyButton = document.getElementById('copyButton');
    copyButton.addEventListener('click', () => {
        const sqlContent = document.querySelector('.sql-content pre').textContent;
        navigator.clipboard.writeText(sqlContent).then(() => {
            showNotification('success', 'SQL이 클립보드에 복사되었습니다.');
        }).catch(() => {
            showNotification('error', 'SQL 복사 중 오류가 발생했습니다.');
        });
    });
    
    // SQL 포맷
    const formatButton = document.getElementById('formatButton');
    formatButton.addEventListener('click', async () => {
        try {
            const response = await fetch(`/api/resources/<?php echo $resource['id']; ?>/format`, {
                method: 'POST'
            });
            
            if (!response.ok) throw new Error('포맷 실패');
            
            const result = await response.json();
            document.querySelector('.sql-content pre').textContent = result.formatted;
            showNotification('success', 'SQL이 포맷되었습니다.');
        } catch (error) {
            showNotification('error', 'SQL 포맷 중 오류가 발생했습니다.');
        }
    });
});

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