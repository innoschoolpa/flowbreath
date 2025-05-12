<?php $currentPage = 'dashboard'; ?>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($totalResources); ?></div>
            <div class="stat-label">전체 리소스</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo number_format($processedToday); ?></div>
            <div class="stat-label">오늘 처리된 리소스</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $successRate; ?>%</div>
            <div class="stat-label">성공률</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $averageProcessingTime; ?>s</div>
            <div class="stat-label">평균 처리 시간</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>최근 리소스</h2>
            <a href="/resources" class="btn btn-primary">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4v16m8-8H4"></path>
                </svg>
                새 리소스 추가
            </a>
        </div>
        
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>이름</th>
                        <th>크기</th>
                        <th>상태</th>
                        <th>생성일</th>
                        <th>작업</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentResources as $resource): ?>
                    <tr>
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
                                <button class="btn btn-secondary btn-sm" title="실행">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M5 3l14 9-14 9V3z"></path>
                                    </svg>
                                </button>
                                <button class="btn btn-secondary btn-sm" title="다운로드">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </button>
                                <button class="btn btn-danger btn-sm" title="삭제">
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
    </div>

    <div class="card">
        <div class="card-header">
            <h2>시스템 상태</h2>
        </div>
        
        <div class="system-stats">
            <div class="stat-row">
                <div class="stat-label">메모리 사용량</div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $memoryUsage; ?>%"></div>
                </div>
                <div class="stat-value"><?php echo $memoryUsage; ?>%</div>
            </div>
            
            <div class="stat-row">
                <div class="stat-label">CPU 사용량</div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $cpuUsage; ?>%"></div>
                </div>
                <div class="stat-value"><?php echo $cpuUsage; ?>%</div>
            </div>
            
            <div class="stat-row">
                <div class="stat-label">디스크 사용량</div>
                <div class="progress">
                    <div class="progress-bar" style="width: <?php echo $diskUsage; ?>%"></div>
                </div>
                <div class="stat-value"><?php echo $diskUsage; ?>%</div>
            </div>
        </div>
    </div>
</div>

<style>
.status-badge {
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.875rem;
    font-weight: 500;
}

.status-pending {
    background-color: var(--warning-color);
    color: white;
}

.status-processing {
    background-color: var(--info-color);
    color: white;
}

.status-completed {
    background-color: var(--success-color);
    color: white;
}

.status-failed {
    background-color: var(--danger-color);
    color: white;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.btn-sm {
    padding: 0.25rem;
}

.system-stats {
    display: grid;
    gap: 1rem;
}

.stat-row {
    display: grid;
    grid-template-columns: 150px 1fr 80px;
    align-items: center;
    gap: 1rem;
}

.resource-link {
    color: var(--primary-color);
    text-decoration: none;
}

.resource-link:hover {
    text-decoration: underline;
}
</style> 