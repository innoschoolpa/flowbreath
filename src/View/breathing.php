<?php require_once __DIR__ . '/layouts/header.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <!-- 호흡 패턴 선택 -->
                    <div class="mb-4">
                        <label class="form-label"><?= __('breathing.patterns.title') ?></label>
                        <select class="form-select" id="breathingPattern">
                            <option value="danjeon"><?= __('breathing.patterns.danjeon') ?></option>
                            <option value="4-7-8"><?= __('breathing.patterns.478') ?></option>
                            <option value="box"><?= __('breathing.patterns.box') ?></option>
                        </select>
                    </div>

                    <!-- 호흡 시간 설정 -->
                    <div class="mb-4" id="breathingTimeSettings">
                        <label class="form-label"><?= __('breathing.timer.title') ?></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label"><?= __('breathing.timer.inhale') ?></label>
                                    <input type="number" class="form-control" id="inhaleTime" value="4" min="2" max="10">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label"><?= __('breathing.timer.exhale') ?></label>
                                    <input type="number" class="form-control" id="exhaleTime" value="4" min="2" max="10">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 운동 시간 설정 -->
                    <div class="mb-4">
                        <label class="form-label"><?= __('breathing.timer.duration') ?></label>
                        <input type="number" class="form-control" id="duration" value="300" min="60" max="3600">
                    </div>

                    <!-- 시각적 가이드 -->
                    <div class="text-center mb-4 position-relative">
                        <div id="breathingCircle" class="mx-auto" style="width: 200px; height: 200px; border-radius: 50%; background-color: #4CAF50; transition: all 4s cubic-bezier(0.4, 0, 0.2, 1);"></div>
                        <div class="position-absolute top-50 start-50 translate-middle text-center" style="width: 100%;">
                            <div id="timer" class="h3 mb-2" style="color: white; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">05:00</div>
                            <div id="phaseText" class="text-white mb-3" style="text-shadow: 1px 1px 2px rgba(0,0,0,0.5);"><?= __('breathing.controls.ready') ?></div>
                            <!-- 컨트롤 -->
                            <div class="d-flex justify-content-center gap-3">
                                <button class="btn btn-primary" id="startButton"><?= __('breathing.controls.start') ?></button>
                                <button class="btn btn-secondary" id="stopButton" disabled><?= __('breathing.controls.stop') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 설정 -->
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="h5 mb-0"><?= __('breathing.settings.title') ?></h3>
                </div>
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="soundEnabled" checked>
                        <label class="form-check-label" for="soundEnabled"><?= __('breathing.settings.sound') ?></label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="vibrationEnabled" checked>
                        <label class="form-check-label" for="vibrationEnabled"><?= __('breathing.settings.vibration') ?></label>
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
let audioContext = null;
let oscillator = null;
let lastPhase = null;
let lastCircleSize = 1;

// 오디오 컨텍스트 초기화
function initAudio() {
    if (!audioContext) {
        audioContext = new (window.AudioContext || window.webkitAudioContext)();
    }
}

// 소리 재생
function playSound(frequency = 440, duration = 0.5) {
    if (!document.getElementById('soundEnabled').checked) return;
    
    try {
        initAudio();
        oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(frequency, audioContext.currentTime);
        
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + duration);
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        oscillator.start();
        oscillator.stop(audioContext.currentTime + duration);
    } catch (error) {
        console.error('Error playing sound:', error);
    }
}

// 진동 실행
function vibrate(duration = 500) {
    if (!document.getElementById('vibrationEnabled').checked) return;
    
    if (navigator.vibrate) {
        navigator.vibrate(duration);
    }
}

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

// 호흡 패턴 변경 시 시간 설정 표시/숨김
document.getElementById('breathingPattern').addEventListener('change', function() {
    const timeSettings = document.getElementById('breathingTimeSettings');
    timeSettings.style.display = this.value === 'danjeon' ? 'block' : 'none';
});

// 세션 시작
async function startSession() {
    try {
        const pattern = document.getElementById('breathingPattern').value;
        const duration = parseInt(document.getElementById('duration').value);
        const sound = document.getElementById('soundEnabled').checked;
        const vibration = document.getElementById('vibrationEnabled').checked;

        let sessionData = {
            pattern,
            duration,
            sound,
            vibration
        };

        // 단전 호흡인 경우 시간 설정 추가
        if (pattern === 'danjeon') {
            sessionData.inhaleTime = parseInt(document.getElementById('inhaleTime').value);
            sessionData.exhaleTime = parseInt(document.getElementById('exhaleTime').value);
        }

        console.log('Starting session with:', sessionData);

        const response = await fetch('/api/breathing/sessions', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(sessionData)
        });

        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}, body: ${errorText}`);
        }

        const data = await response.json();
        console.log('Response data:', data);

        if (!data.success) {
            throw new Error(data.message || 'Failed to start session');
        }

        currentSession = data.data.session_id;
        document.getElementById('startButton').disabled = true;
        document.getElementById('stopButton').disabled = false;
        
        startTimer(duration);
        startStatusUpdates();
        
        // 시작 소리
        playSound(440, 0.5);
        vibrate(500);
    } catch (error) {
        console.error('Error starting session:', error);
        alert('세션을 시작하는 중 오류가 발생했습니다: ' + error.message);
    }
}

// 세션 상태 업데이트
async function updateSessionStatus() {
    if (!currentSession) return;

    try {
        const response = await fetch(`/api/breathing/sessions/${currentSession}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const data = await response.json();
        if (data.success) {
            updateVisualGuide(data.data);
        } else {
            throw new Error(data.message || 'Failed to update session status');
        }
    } catch (error) {
        console.error('Error updating session status:', error);
        if (error.message.includes('404')) {
            stopSession();
        }
    }
}

// 시각적 가이드 업데이트
function updateVisualGuide(data) {
    const circle = document.getElementById('breathingCircle');
    const phaseText = document.getElementById('phaseText');
    const pattern = document.getElementById('breathingPattern').value;
    
    if (!data || !data.visual_guide) {
        circle.style.transform = 'scale(1)';
        circle.style.backgroundColor = '#4CAF50';
        phaseText.textContent = '준비';
        lastPhase = null;
        lastCircleSize = 1;
        return;
    }

    const guide = data.visual_guide;
    const currentPhase = data.current_phase.type;
    
    // 단계가 변경되었을 때만 transition 시간 조정
    if (currentPhase !== lastPhase) {
        let transitionDuration;
        if (pattern === '4-7-8') {
            transitionDuration = {
                'inhale': '4s',    // 들숨: 4초
                'hold': '7s',      // 참기: 7초
                'exhale': '8s'     // 날숨: 8초
            }[currentPhase] || '2s';
        } else if (pattern === 'box') {
            transitionDuration = {
                'inhale': '4s',    // 들숨: 4초
                'hold_in': '4s',   // 들숨 후 참기: 4초
                'exhale': '4s',    // 날숨: 4초
                'hold_out': '4s'   // 날숨 후 참기: 4초
            }[currentPhase] || '2s';
        } else { // danjeon
            transitionDuration = `${guide.duration}s`;
        }
        
        // 전환 시작 시 약간의 지연 추가
        setTimeout(() => {
            circle.style.transition = `all ${transitionDuration} cubic-bezier(0.4, 0, 0.2, 1)`;
            lastPhase = currentPhase;
        }, 50);
    }

    // 원의 크기 변화가 너무 급격하지 않도록 조정
    const targetSize = guide.circle_size;
    const sizeDiff = Math.abs(targetSize - lastCircleSize);
    
    if (sizeDiff > 0.1) {
        requestAnimationFrame(() => {
            circle.style.transform = `scale(${targetSize})`;
            circle.style.backgroundColor = guide.color;
        });
        lastCircleSize = targetSize;
    }
    
    // 단계 텍스트 업데이트
    const phaseMap = {
        'inhale': '들숨',
        'hold_in': '들숨 후 참기',
        'exhale': '날숨',
        'hold_out': '날숨 후 참기'
    };
    phaseText.textContent = phaseMap[currentPhase] || '준비';
    
    // 소리와 진동
    if (data.current_phase.time_remaining === data.current_phase.duration) {
        playSound(440 + (currentPhase === 'exhale' ? 220 : 0), 0.3);
        vibrate(300);
    }
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
    if (!currentSession) {
        console.log('No active session to stop');
        return;
    }

    console.log('Stopping session:', currentSession);

    try {
        const response = await fetch(`/api/breathing/sessions/${currentSession}/end`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });

        console.log('Response status:', response.status);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Error response:', errorText);
            throw new Error(`HTTP error! status: ${response.status}, body: ${errorText}`);
        }
        
        const data = await response.json();
        console.log('Response data:', data);

        if (!data.success) {
            throw new Error(data.message || 'Failed to end session');
        }

        // 타이머와 상태 업데이트 중지
        clearInterval(timerInterval);
        clearInterval(statusInterval);
        
        // UI 초기화
        currentSession = null;
        document.getElementById('startButton').disabled = false;
        document.getElementById('stopButton').disabled = true;
        document.getElementById('timer').textContent = '00:00';
        
        // 원 애니메이션 초기화
        const circle = document.getElementById('breathingCircle');
        requestAnimationFrame(() => {
            circle.style.transform = 'scale(1)';
            circle.style.backgroundColor = '#4CAF50';
        });
        document.getElementById('phaseText').textContent = '준비';
        
        // 종료 소리
        playSound(330, 0.5);
        vibrate(500);
        
        // 성공 메시지 표시
        alert('호흡 운동이 종료되었습니다.');
    } catch (error) {
        console.error('Error stopping session:', error);
        alert('세션을 종료하는 중 오류가 발생했습니다: ' + error.message);
    }
}

// 이벤트 리스너 설정
document.getElementById('startButton').addEventListener('click', startSession);
document.getElementById('stopButton').addEventListener('click', stopSession);
</script>

<?php require_once __DIR__ . '/layouts/footer.php'; ?> 