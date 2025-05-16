<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h1>FlowBreath 호흡 운동 가이드</h1>
    <p class="lead">FlowBreath.io의 호흡 운동 기능을 사용하는 방법을 안내합니다.</p>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">호흡 패턴</h2>
        </div>
        <div class="card-body">
            <h3 class="h6">기본 패턴</h3>
            <pre><code>GET /api/breathing/patterns</code></pre>
            <p>사용 가능한 호흡 패턴 목록을 조회합니다.</p>
            <div class="mt-3">
                <h4 class="h6">응답 예시:</h4>
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
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">호흡 세션</h2>
        </div>
        <div class="card-body">
            <h3 class="h6">세션 시작</h3>
            <pre><code>POST /api/breathing/sessions</code></pre>
            <p>새로운 호흡 운동 세션을 시작합니다.</p>
            <div class="mt-3">
                <h4 class="h6">요청 본문:</h4>
                <pre><code>{
    "pattern": "4-7-8",
    "duration": 300,
    "sound": true,
    "vibration": true
}</code></pre>
            </div>

            <h3 class="h6 mt-4">세션 상태 조회</h3>
            <pre><code>GET /api/breathing/sessions/{session_id}</code></pre>
            <p>현재 세션의 상태를 조회합니다.</p>
            <div class="mt-3">
                <h4 class="h6">응답 예시:</h4>
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

            <h3 class="h6 mt-4">세션 종료</h3>
            <pre><code>POST /api/breathing/sessions/{session_id}/end</code></pre>
            <p>진행 중인 세션을 종료합니다.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">시각적 가이드</h2>
        </div>
        <div class="card-body">
            <h3 class="h6">가이드 업데이트</h3>
            <pre><code>GET /api/breathing/sessions/{session_id}/guide</code></pre>
            <p>현재 세션의 시각적 가이드 정보를 조회합니다.</p>
            <div class="mt-3">
                <h4 class="h6">응답 예시:</h4>
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
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">설정</h2>
        </div>
        <div class="card-body">
            <h3 class="h6">사용자 설정 조회</h3>
            <pre><code>GET /api/breathing/settings</code></pre>
            <p>사용자의 호흡 운동 설정을 조회합니다.</p>
            <div class="mt-3">
                <h4 class="h6">응답 예시:</h4>
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

            <h3 class="h6 mt-4">설정 업데이트</h3>
            <pre><code>POST /api/breathing/settings</code></pre>
            <p>사용자의 호흡 운동 설정을 업데이트합니다.</p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 