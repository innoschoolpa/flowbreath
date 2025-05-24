<?php require_once __DIR__ . '/../layouts/header.php'; ?>

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
    --code-bg: #1e1e1e;
    --code-text: #d4d4d4;
    --endpoint-bg: rgba(14, 165, 233, 0.1);
    --hover-bg: rgba(255, 255, 255, 0.1);
}

body {
    background-color: var(--background-color);
    color: var(--text-color);
}

h1, h2, h3, h4, h5, h6 {
    color: var(--text-color);
    font-weight: 600;
}

h1 {
    font-size: 2.5rem;
    margin-bottom: 2rem;
    border-bottom: 2px solid var(--border-color);
    padding-bottom: 1rem;
}

h2 {
    font-size: 2rem;
    margin: 2rem 0 1.5rem;
    color: var(--accent-color);
}

h3 {
    font-size: 1.5rem;
    margin: 1.5rem 0 1rem;
}

/* API 엔드포인트 스타일 */
.endpoint {
    background-color: var(--endpoint-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 1.5rem;
    margin: 1.5rem 0;
}

.method {
    font-weight: 600;
    padding: 0.25rem 0.75rem;
    border-radius: 0.25rem;
    margin-right: 1rem;
}

.get {
    background-color: var(--success-color);
    color: var(--text-color);
}

.post {
    background-color: var(--primary-color);
    color: var(--text-color);
}

.put {
    background-color: var(--warning-color);
    color: var(--text-color);
}

.delete {
    background-color: var(--error-color);
    color: var(--text-color);
}

/* 코드 블록 스타일 */
pre {
    background-color: var(--code-bg);
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 1rem 0;
    overflow-x: auto;
}

code {
    color: var(--code-text);
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 0.9rem;
    line-height: 1.5;
}

/* 요청/응답 섹션 스타일 */
.section {
    margin: 1.5rem 0;
    padding: 1rem;
    background-color: var(--card-bg);
    border-radius: 0.5rem;
    border: 1px solid var(--border-color);
}

.section-title {
    color: var(--accent-color);
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 0.75rem;
}

/* 예시 응답 스타일 */
.example-response {
    background-color: var(--code-bg);
    border-radius: 0.5rem;
    padding: 1rem;
    margin-top: 0.75rem;
}

/* 설명 텍스트 스타일 */
.description {
    color: var(--text-color);
    opacity: 0.9;
    line-height: 1.6;
    margin: 1rem 0;
}

/* 반응형 디자인 */
@media (max-width: 768px) {
    .container {
        padding: 1rem;
    }
    
    h1 {
        font-size: 2rem;
    }
    
    h2 {
        font-size: 1.75rem;
    }
    
    .endpoint {
        padding: 1rem;
    }
    
    pre {
        padding: 0.75rem;
    }
}
</style>

<div class="container py-5">
    <h1>FlowBreath 호흡 운동 가이드</h1>
    <p class="description">FlowBreath.io의 호흡 운동 기능을 사용하는 방법을 안내합니다.</p>

    <h2>호흡 패턴</h2>
    <h3>기본 패턴</h3>
    
    <div class="endpoint">
        <div class="method get">GET</div>
        <code>/api/breathing/patterns</code>
        <p class="description">사용 가능한 호흡 패턴 목록을 조회합니다.</p>
        
        <div class="section">
            <div class="section-title">응답 예시:</div>
            <pre><code>{
    "success": true,
    "data": {
        "patterns": [
            {
                "id": "4-7-8",
                "name": "4-7-8 호흡법",
                "description": "4초 들숨, 7초 참기, 8초 날숨",
                "phases": [
                    {"type": "inhale", "duration": 4},
                    {"type": "hold", "duration": 7},
                    {"type": "exhale", "duration": 8}
                ]
            },
            {
                "id": "box",
                "name": "박스 호흡법",
                "description": "4초 들숨, 4초 참기, 4초 날숨, 4초 참기",
                "phases": [
                    {"type": "inhale", "duration": 4},
                    {"type": "hold", "duration": 4},
                    {"type": "exhale", "duration": 4},
                    {"type": "hold", "duration": 4}
                ]
            }
        ]
    }
}</code></pre>
        </div>
    </div>

    <h2>호흡 세션</h2>
    <h3>세션 시작</h3>
    
    <div class="endpoint">
        <div class="method post">POST</div>
        <code>/api/breathing/sessions</code>
        <p class="description">새로운 호흡 운동 세션을 시작합니다.</p>
        
        <div class="section">
            <div class="section-title">요청 본문:</div>
            <pre><code>{
    "pattern": "4-7-8",
    "duration": 300,
    "sound": true,
    "vibration": true
}</code></pre>
        </div>
    </div>

    <h3>세션 상태 조회</h3>
    
    <div class="endpoint">
        <div class="method get">GET</div>
        <code>/api/breathing/sessions/{session_id}</code>
        <p class="description">현재 세션의 상태를 조회합니다.</p>
        
        <div class="section">
            <div class="section-title">응답 예시:</div>
            <pre><code>{
    "success": true,
    "data": {
        "session_id": "123e4567-e89b-12d3-a456-426614174000",
        "status": "in_progress",
        "current_phase": {
            "type": "inhale",
            "duration": 4,
            "time_remaining": 2
        },
        "next_phase": {
            "type": "hold",
            "duration": 7
        },
        "progress": 0.25,
        "visual_guide": {
            "circle_size": 0.5,
            "color": "#4CAF50"
        }
    }
}</code></pre>
        </div>
    </div>

    <h3>세션 종료</h3>
    
    <div class="endpoint">
        <div class="method post">POST</div>
        <code>/api/breathing/sessions/{session_id}/end</code>
        <p class="description">진행 중인 세션을 종료합니다.</p>
    </div>

    <h2>시각적 가이드</h2>
    <h3>가이드 업데이트</h3>
    
    <div class="endpoint">
        <div class="method get">GET</div>
        <code>/api/breathing/sessions/{session_id}/guide</code>
        <p class="description">현재 세션의 시각적 가이드 정보를 조회합니다.</p>
        
        <div class="section">
            <div class="section-title">응답 예시:</div>
            <pre><code>{
    "success": true,
    "data": {
        "session_id": "123e4567-e89b-12d3-a456-426614174000",
        "current_phase": {
            "type": "inhale",
            "duration": 4,
            "time_remaining": 2
        },
        "visual_guide": {
            "circle_size": 0.5,
            "color": "#4CAF50",
            "animation": "expand"
        },
        "timer": {
            "total": 300,
            "remaining": 240,
            "formatted": "04:00"
        }
    }
}</code></pre>
        </div>
    </div>

    <h2>설정</h2>
    <h3>사용자 설정 조회</h3>
    
    <div class="endpoint">
        <div class="method get">GET</div>
        <code>/api/breathing/settings</code>
        <p class="description">사용자의 호흡 운동 설정을 조회합니다.</p>
        
        <div class="section">
            <div class="section-title">응답 예시:</div>
            <pre><code>{
    "success": true,
    "data": {
        "sound": {
            "enabled": true,
            "volume": 0.8,
            "type": "bell"
        },
        "vibration": {
            "enabled": true,
            "intensity": "medium"
        },
        "visual": {
            "theme": "light",
            "animation_speed": "normal",
            "show_timer": true
        }
    }
}</code></pre>
        </div>
    </div>

    <h3>설정 업데이트</h3>
    
    <div class="endpoint">
        <div class="method post">POST</div>
        <code>/api/breathing/settings</code>
        <p class="description">사용자의 호흡 운동 설정을 업데이트합니다.</p>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 