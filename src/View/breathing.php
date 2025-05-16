<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                </div>
                <div class="card-body">
                    <!-- 호흡 패턴 선택 -->
                    <div class="mb-4">
                        <label class="form-label">호흡 패턴</label>
                        <select class="form-select" id="breathingPattern">
                            <option value="4-7-8">4-7-8 호흡법</option>
                            <option value="box">박스 호흡법</option>
                        </select>
                    </div>

                    <!-- 운동 시간 설정 -->
                    <div class="mb-4">
                        <label class="form-label">운동 시간 (초)</label>
                        <input type="number" class="form-control" id="duration" value="300" min="60" max="3600">
                    </div>

                    <!-- 시각적 가이드 -->
                    <div class="text-center mb-4">
                        <div id="breathingCircle" class="mx-auto" style="width: 200px; height: 200px; border-radius: 50%; background-color: #4CAF50; transition: all 1s ease-in-out;"></div>
                        <div id="timer" class="mt-3 h3">05:00</div>
                    </div>

                    <!-- 컨트롤 -->
                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn btn-primary" id="startButton">시작</button>
                        <button class="btn btn-secondary" id="stopButton" disabled>정지</button>
                    </div>

                    <!-- 설정 -->
                    <div class="mt-4">
                        <h3 class="h5">설정</h3>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="soundEnabled" checked>
                            <label class="form-check-label" for="soundEnabled">소리</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="vibrationEnabled" checked>
                            <label class="form-check-label" for="vibrationEnabled">진동</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentSession = null;
let timerInterval = null;
let statusInterval = null;

// 호흡 패턴 가져오기
async function getPatterns() {
    try {
        const response = await fetch('/api/breathing/patterns');
        const data = await response.json();
        if (data.success) {
            return data.data.patterns;
        }
        throw new Error('Failed to get patterns');
    } catch (error) {
        console.error('Error getting patterns:', error);
        return [];
    }
}

// 세션 시작
async function startSession() {
    try {
        const pattern = document.getElementById('breathingPattern').value;
        const duration = parseInt(document.getElementById('duration').value);
        const sound = document.getElementById('soundEnabled').checked;
        const vibration = document.getElementById('vibrationEnabled').checked;

        const response = await fetch('/api/breathing/sessions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                pattern,
                duration,
                sound,
                vibration
            })
        });

        const data = await response.json();
        if (!data.success) {
            throw new Error('Failed to start session');
        }

        currentSession = data.data.id;
        document.getElementById('startButton').disabled = true;
        document.getElementById('stopButton').disabled = false;
        
        startTimer(duration);
        startStatusUpdates();
    } catch (error) {
        console.error('Error starting session:', error);
        alert('세션을 시작하는 중 오류가 발생했습니다.');
    }
}

// 세션 상태 업데이트
async function updateSessionStatus() {
    if (!currentSession) return;

    try {
        const response = await fetch(`/api/breathing/sessions/${currentSession}`);
        const data = await response.json();
        if (data.success) {
            updateVisualGuide(data.data);
        }
    } catch (error) {
        console.error('Error updating session status:', error);
    }
}

// 시각적 가이드 업데이트
function updateVisualGuide(data) {
    const circle = document.getElementById('breathingCircle');
    if (!data || !data.visual_guide) {
        circle.style.transform = 'scale(1)';
        circle.style.backgroundColor = '#4CAF50';
        return;
    }

    const guide = data.visual_guide;
    circle.style.transform = `scale(${guide.circle_size})`;
    circle.style.backgroundColor = guide.color;
}

// 타이머 시작
function startTimer(duration) {
    let remaining = duration;

    function updateTimer() {
        const minutes = Math.floor(remaining / 60);
        const seconds = remaining % 60;
        document.getElementById('timer').textContent = 
            `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        if (remaining <= 0) {
            stopSession();
        } else {
            remaining--;
        }
    }

    updateTimer();
    timerInterval = setInterval(updateTimer, 1000);
}

// 상태 업데이트 시작
function startStatusUpdates() {
    if (statusInterval) {
        clearInterval(statusInterval);
    }
    statusInterval = setInterval(updateSessionStatus, 1000);
}

// 세션 정지
async function stopSession() {
    if (!currentSession) return;

    try {
        const response = await fetch(`/api/breathing/sessions/${currentSession}/end`, {
            method: 'POST'
        });
        const data = await response.json();
        if (!data.success) {
            throw new Error('Failed to end session');
        }
    } catch (error) {
        console.error('Error stopping session:', error);
    } finally {
        clearInterval(timerInterval);
        clearInterval(statusInterval);
        currentSession = null;
        document.getElementById('startButton').disabled = false;
        document.getElementById('stopButton').disabled = true;
        document.getElementById('timer').textContent = '00:00';
        document.getElementById('breathingCircle').style.transform = 'scale(1)';
        document.getElementById('breathingCircle').style.backgroundColor = '#4CAF50';
    }
}

// 이벤트 리스너
document.getElementById('startButton').addEventListener('click', startSession);
document.getElementById('stopButton').addEventListener('click', stopSession);

// 초기 상태 설정
updateVisualGuide();
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?> 