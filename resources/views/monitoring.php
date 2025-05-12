<?php $currentPage = 'monitoring'; ?>

<div class="container">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $memoryUsage; ?>%</div>
            <div class="stat-label">메모리 사용량</div>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo $memoryUsage; ?>%"></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $cpuUsage; ?>%</div>
            <div class="stat-label">CPU 사용량</div>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo $cpuUsage; ?>%"></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $diskUsage; ?>%</div>
            <div class="stat-label">디스크 사용량</div>
            <div class="progress">
                <div class="progress-bar" style="width: <?php echo $diskUsage; ?>%"></div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo formatNumber($activeConnections); ?></div>
            <div class="stat-label">활성 연결</div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>실시간 모니터링</h2>
            <div class="monitoring-actions">
                <select class="form-input" id="updateInterval">
                    <option value="1000">1초</option>
                    <option value="5000" selected>5초</option>
                    <option value="10000">10초</option>
                    <option value="30000">30초</option>
                </select>
            </div>
        </div>
        <div class="monitoring-grid">
            <div class="monitoring-chart">
                <canvas id="memoryChart"></canvas>
            </div>
            <div class="monitoring-chart">
                <canvas id="cpuChart"></canvas>
            </div>
            <div class="monitoring-chart">
                <canvas id="requestChart"></canvas>
            </div>
            <div class="monitoring-chart">
                <canvas id="errorChart"></canvas>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>활성 실행</h2>
            <div class="execution-actions">
                <button class="btn btn-danger" id="stopAllButton">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    모두 중지
                </button>
            </div>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>리소스</th>
                        <th>시작 시간</th>
                        <th>진행률</th>
                        <th>메모리</th>
                        <th>작업</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeExecutions as $execution): ?>
                    <tr>
                        <td>
                            <a href="/resources/<?php echo $execution['resource_id']; ?>" class="resource-link">
                                <?php echo htmlspecialchars($execution['resource_name']); ?>
                            </a>
                        </td>
                        <td><?php echo formatDate($execution['started_at']); ?></td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?php echo $execution['progress']; ?>%"></div>
                            </div>
                            <div class="progress-text"><?php echo $execution['progress']; ?>%</div>
                        </td>
                        <td><?php echo formatBytes($execution['memory_used']); ?></td>
                        <td>
                            <button class="btn btn-danger btn-sm" onclick="stopExecution('<?php echo $execution['id']; ?>')">
                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                중지
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2>시스템 로그</h2>
            <div class="log-actions">
                <select class="form-input" id="logLevel">
                    <option value="">모든 레벨</option>
                    <option value="error">에러</option>
                    <option value="warning">경고</option>
                    <option value="info">정보</option>
                    <option value="debug">디버그</option>
                </select>
            </div>
        </div>
        <div class="log-container">
            <?php foreach ($systemLogs as $log): ?>
            <div class="log-entry log-<?php echo $log['level']; ?>">
                <div class="log-time"><?php echo formatDate($log['timestamp']); ?></div>
                <div class="log-level"><?php echo strtoupper($log['level']); ?></div>
                <div class="log-message"><?php echo htmlspecialchars($log['message']); ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.monitoring-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-lg);
    margin: var(--spacing-lg) 0;
}

.monitoring-chart {
    background: white;
    padding: var(--spacing-md);
    border-radius: 0.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.log-container {
    background: #1e1e1e;
    border-radius: 0.5rem;
    padding: var(--spacing-md);
    max-height: 400px;
    overflow-y: auto;
    font-family: 'Fira Code', monospace;
    font-size: 0.875rem;
    line-height: 1.5;
}

.log-entry {
    display: grid;
    grid-template-columns: 150px 80px 1fr;
    gap: var(--spacing-md);
    padding: var(--spacing-xs) 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.log-time {
    color: #6b7280;
}

.log-level {
    font-weight: 500;
}

.log-error .log-level {
    color: var(--danger-color);
}

.log-warning .log-level {
    color: var(--warning-color);
}

.log-info .log-level {
    color: var(--info-color);
}

.log-debug .log-level {
    color: var(--secondary-color);
}

.log-message {
    color: #d4d4d4;
    white-space: pre-wrap;
    word-break: break-all;
}

.progress-text {
    text-align: center;
    margin-top: var(--spacing-xs);
    font-size: var(--small-text);
    color: var(--secondary-color);
}

@media (max-width: 768px) {
    .monitoring-grid {
        grid-template-columns: 1fr;
    }
    
    .log-entry {
        grid-template-columns: 1fr;
        gap: var(--spacing-xs);
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 차트 설정
    const chartConfig = {
        type: 'line',
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    };
    
    // 메모리 차트
    const memoryChart = new Chart(document.getElementById('memoryChart'), {
        ...chartConfig,
        data: {
            labels: [],
            datasets: [{
                label: '메모리 사용량',
                data: [],
                borderColor: '#2563eb',
                tension: 0.4
            }]
        }
    });
    
    // CPU 차트
    const cpuChart = new Chart(document.getElementById('cpuChart'), {
        ...chartConfig,
        data: {
            labels: [],
            datasets: [{
                label: 'CPU 사용량',
                data: [],
                borderColor: '#22c55e',
                tension: 0.4
            }]
        }
    });
    
    // 요청 차트
    const requestChart = new Chart(document.getElementById('requestChart'), {
        ...chartConfig,
        data: {
            labels: [],
            datasets: [{
                label: '초당 요청 수',
                data: [],
                borderColor: '#f59e0b',
                tension: 0.4
            }]
        }
    });
    
    // 에러 차트
    const errorChart = new Chart(document.getElementById('errorChart'), {
        ...chartConfig,
        data: {
            labels: [],
            datasets: [{
                label: '에러율',
                data: [],
                borderColor: '#ef4444',
                tension: 0.4
            }]
        }
    });
    
    // 실시간 업데이트
    let updateInterval = 5000;
    const updateIntervalSelect = document.getElementById('updateInterval');
    updateIntervalSelect.addEventListener('change', () => {
        updateInterval = parseInt(updateIntervalSelect.value);
    });
    
    async function updateCharts() {
        try {
            const response = await fetch('/api/monitoring/metrics');
            const data = await response.json();
            
            const now = new Date().toLocaleTimeString();
            
            // 메모리 차트 업데이트
            memoryChart.data.labels.push(now);
            memoryChart.data.datasets[0].data.push(data.memory_usage);
            if (memoryChart.data.labels.length > 20) {
                memoryChart.data.labels.shift();
                memoryChart.data.datasets[0].data.shift();
            }
            memoryChart.update();
            
            // CPU 차트 업데이트
            cpuChart.data.labels.push(now);
            cpuChart.data.datasets[0].data.push(data.cpu_usage);
            if (cpuChart.data.labels.length > 20) {
                cpuChart.data.labels.shift();
                cpuChart.data.datasets[0].data.shift();
            }
            cpuChart.update();
            
            // 요청 차트 업데이트
            requestChart.data.labels.push(now);
            requestChart.data.datasets[0].data.push(data.requests_per_second);
            if (requestChart.data.labels.length > 20) {
                requestChart.data.labels.shift();
                requestChart.data.datasets[0].data.shift();
            }
            requestChart.update();
            
            // 에러 차트 업데이트
            errorChart.data.labels.push(now);
            errorChart.data.datasets[0].data.push(data.error_rate);
            if (errorChart.data.labels.length > 20) {
                errorChart.data.labels.shift();
                errorChart.data.datasets[0].data.shift();
            }
            errorChart.update();
        } catch (error) {
            console.error('메트릭 업데이트 실패:', error);
        }
        
        setTimeout(updateCharts, updateInterval);
    }
    
    updateCharts();
    
    // 실행 중지
    async function stopExecution(id) {
        if (!confirm('이 실행을 중지하시겠습니까?')) return;
        
        try {
            const response = await fetch(`/api/executions/${id}/stop`, {
                method: 'POST'
            });
            
            if (!response.ok) throw new Error('중지 실패');
            
            showNotification('success', '실행이 중지되었습니다.');
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            showNotification('error', '실행 중지 중 오류가 발생했습니다.');
        }
    }
    
    // 모든 실행 중지
    const stopAllButton = document.getElementById('stopAllButton');
    stopAllButton.addEventListener('click', async () => {
        if (!confirm('모든 실행을 중지하시겠습니까?')) return;
        
        try {
            const response = await fetch('/api/executions/stop-all', {
                method: 'POST'
            });
            
            if (!response.ok) throw new Error('중지 실패');
            
            showNotification('success', '모든 실행이 중지되었습니다.');
            setTimeout(() => window.location.reload(), 1500);
        } catch (error) {
            showNotification('error', '실행 중지 중 오류가 발생했습니다.');
        }
    });
    
    // 로그 필터링
    const logLevel = document.getElementById('logLevel');
    const logEntries = document.querySelectorAll('.log-entry');
    
    logLevel.addEventListener('change', () => {
        const selectedLevel = logLevel.value;
        logEntries.forEach(entry => {
            if (!selectedLevel || entry.classList.contains(`log-${selectedLevel}`)) {
                entry.style.display = '';
            } else {
                entry.style.display = 'none';
            }
        });
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