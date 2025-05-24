<?php
// src/View/profile.php
require_once __DIR__ . '/layouts/header.php';
?>

<style>
:root {
    --background-color: #0f172a;
    --text-color: #f1f5f9;
    --card-bg: #1e293b;
    --border-color: #334155;
    --primary-color: #3b82f6;
    --secondary-color: #64748b;
    --accent-color: #0ea5e9;
    --success-color: #22c55e;
    --warning-color: #f59e0b;
    --error-color: #ef4444;
    --hover-bg: rgba(255, 255, 255, 0.1);
}

body {
    background-color: var(--background-color);
    color: var(--text-color);
}

.profile-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 2rem;
}

.profile-header {
    background-color: var(--card-bg);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
}

.profile-avatar {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid var(--accent-color);
    margin-bottom: 1.5rem;
}

.profile-name {
    font-size: 2rem;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 0.5rem;
}

.profile-email {
    color: var(--secondary-color);
    font-size: 1.1rem;
    margin-bottom: 1.5rem;
}

.profile-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin-top: 2rem;
}

.stat-card {
    background-color: var(--card-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.75rem;
    padding: 1.5rem;
    text-align: center;
    transition: transform 0.2s ease-in-out;
}

.stat-card:hover {
    transform: translateY(-2px);
}

.stat-value {
    font-size: 2rem;
    font-weight: 600;
    color: var(--accent-color);
    margin-bottom: 0.5rem;
}

.stat-label {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

.profile-section {
    background-color: var(--card-bg);
    border-radius: 1rem;
    padding: 2rem;
    margin-bottom: 2rem;
    border: 1px solid var(--border-color);
}

.section-title {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--text-color);
    margin-bottom: 1.5rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--border-color);
}

.activity-item {
    padding: 1rem;
    border-bottom: 1px solid var(--border-color);
    transition: background-color 0.2s ease-in-out;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-item:hover {
    background-color: var(--hover-bg);
}

.activity-time {
    color: var(--secondary-color);
    font-size: 0.9rem;
}

.activity-title {
    color: var(--text-color);
    font-weight: 500;
    margin: 0.5rem 0;
}

.activity-description {
    color: var(--secondary-color);
    font-size: 0.95rem;
}

.btn-edit {
    background-color: var(--accent-color);
    color: var(--text-color);
    border: none;
    padding: 0.75rem 1.5rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: background-color 0.2s ease-in-out;
}

.btn-edit:hover {
    background-color: #0284c7;
    color: var(--text-color);
}

@media (max-width: 768px) {
    .profile-container {
        padding: 1rem;
    }
    
    .profile-header {
        padding: 1.5rem;
    }
    
    .profile-stats {
        grid-template-columns: 1fr;
    }
    
    .profile-section {
        padding: 1.5rem;
    }
}
</style>

<div class="profile-container">
    <div class="profile-header">
        <div class="text-center">
            <img src="<?= htmlspecialchars($user['avatar_url'] ?? '/assets/images/default-avatar.png') ?>" 
                 alt="프로필 이미지" 
                 class="profile-avatar">
            <h1 class="profile-name"><?= htmlspecialchars($user['name']) ?></h1>
            <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
            <a href="/profile/edit" class="btn btn-edit">프로필 수정</a>
        </div>
        
        <div class="profile-stats">
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_sessions'] ?? 0 ?></div>
                <div class="stat-label">총 호흡 세션</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['total_minutes'] ?? 0 ?></div>
                <div class="stat-label">총 운동 시간</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $stats['streak_days'] ?? 0 ?></div>
                <div class="stat-label">연속 운동일</div>
            </div>
        </div>
    </div>

    <div class="profile-section">
        <h2 class="section-title">최근 활동</h2>
        <?php if (!empty($recent_activities)): ?>
            <?php foreach ($recent_activities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-time"><?= date('Y년 m월 d일 H:i', strtotime($activity['created_at'])) ?></div>
                    <div class="activity-title"><?= htmlspecialchars($activity['title']) ?></div>
                    <div class="activity-description"><?= htmlspecialchars($activity['description']) ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-center text-muted">아직 활동 내역이 없습니다.</p>
        <?php endif; ?>
    </div>

    <div class="profile-section">
        <h2 class="section-title">선호하는 호흡 패턴</h2>
        <?php if (!empty($favorite_patterns)): ?>
            <div class="row">
                <?php foreach ($favorite_patterns as $pattern): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100" style="background-color: var(--card-bg); border-color: var(--border-color);">
                            <div class="card-body">
                                <h5 class="card-title" style="color: var(--text-color);">
                                    <?= htmlspecialchars($pattern['name']) ?>
                                </h5>
                                <p class="card-text" style="color: var(--secondary-color);">
                                    <?= htmlspecialchars($pattern['description']) ?>
                                </p>
                                <div class="text-muted" style="color: var(--secondary-color);">
                                    사용 횟수: <?= $pattern['usage_count'] ?>회
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-muted">아직 선호하는 호흡 패턴이 없습니다.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/layouts/footer.php'; ?> 