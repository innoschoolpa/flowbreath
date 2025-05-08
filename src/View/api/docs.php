<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="container mt-4">
    <h1>API 문서</h1>
    <p class="lead">FlowBreath.io API 문서에 오신 것을 환영합니다. 여기서 모든 사용 가능한 엔드포인트와 사용 방법에 대한 정보를 찾을 수 있습니다.</p>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">기본 URL</h2>
        </div>
        <div class="card-body">
            <code>https://flowbreath.io/api</code>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">인증</h2>
        </div>
        <div class="card-body">
            <p>모든 API 엔드포인트는 JWT 토큰을 사용한 인증이 필요합니다. Authorization 헤더에 토큰을 포함하세요:</p>
            <pre><code>Authorization: Bearer &lt;your_jwt_token&gt;</code></pre>
            <p class="mt-3">토큰은 로그인 후 발급되며, 1시간 동안 유효합니다.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">사용 가능한 엔드포인트</h2>
        </div>
        <div class="card-body">
            <h3 class="h6">상태 확인</h3>
            <pre><code>GET /api/health</code></pre>
            <p>API 서버의 상태를 확인합니다.</p>
            <div class="mt-3">
                <h4 class="h6">응답 예시:</h4>
                <pre><code>{
    "success": true,
    "data": {
        "status": "healthy",
        "version": "1.0.0",
        "uptime": 3600,
        "memory_usage": "50MB",
        "database": "connected"
    }
}</code></pre>
            </div>

            <h3 class="h6 mt-4">리소스</h3>
            <pre><code>GET /resources</code></pre>
            <p>모든 사용 가능한 리소스를 목록으로 가져옵니다.</p>
            <div class="mt-3">
                <h4 class="h6">쿼리 파라미터:</h4>
                <ul>
                    <li><code>page</code>: 페이지 번호 (기본값: 1)</li>
                    <li><code>limit</code>: 페이지당 항목 수 (기본값: 10)</li>
                    <li><code>sort</code>: 정렬 기준 (created_at, updated_at, title)</li>
                    <li><code>order</code>: 정렬 순서 (asc, desc)</li>
                </ul>
            </div>

            <pre><code>GET /resources/{id}</code></pre>
            <p>ID로 특정 리소스를 가져옵니다.</p>

            <pre><code>POST /resources</code></pre>
            <p>새로운 리소스를 생성합니다.</p>
            <div class="mt-3">
                <h4 class="h6">요청 본문:</h4>
                <pre><code>{
    "title": "리소스 제목",
    "content": "리소스 내용",
    "tags": ["태그1", "태그2"],
    "visibility": "public"
}</code></pre>
            </div>

            <h3 class="h6 mt-4">태그</h3>
            <pre><code>GET /api/tags/suggest</code></pre>
            <p>입력에 기반한 태그 제안을 가져옵니다.</p>
            <div class="mt-3">
                <h4 class="h6">쿼리 파라미터:</h4>
                <ul>
                    <li><code>query</code>: 검색어</li>
                    <li><code>limit</code>: 최대 결과 수 (기본값: 10)</li>
                </ul>
            </div>

            <h3 class="h6 mt-4">댓글</h3>
            <pre><code>GET /api/resources/{id}/comments</code></pre>
            <p>특정 리소스의 댓글을 가져옵니다.</p>

            <pre><code>POST /api/resources/{id}/comments</code></pre>
            <p>리소스에 댓글을 추가합니다.</p>
            <div class="mt-3">
                <h4 class="h6">요청 본문:</h4>
                <pre><code>{
    "content": "댓글 내용"
}</code></pre>
            </div>

            <h3 class="h6 mt-4">좋아요</h3>
            <pre><code>POST /api/resources/{id}/like</code></pre>
            <p>리소스의 좋아요 상태를 토글합니다.</p>

            <pre><code>GET /api/resources/{id}/like</code></pre>
            <p>리소스의 좋아요 상태를 확인합니다.</p>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">응답 형식</h2>
        </div>
        <div class="card-body">
            <p>모든 API 응답은 다음 형식을 따릅니다:</p>
            <pre><code>{
    "success": true,
    "data": {
        // 응답 데이터
    },
    "error": null,
    "message": "작업이 성공적으로 완료되었습니다"
}</code></pre>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">오류 처리</h2>
        </div>
        <div class="card-body">
            <p>오류 응답은 다음 형식을 따릅니다:</p>
            <pre><code>{
    "success": false,
    "data": null,
    "error": {
        "code": "ERROR_CODE",
        "message": "오류 설명",
        "details": {
            // 추가 오류 세부 정보
        }
    }
}</code></pre>
            <div class="mt-3">
                <h4 class="h6">일반적인 오류 코드:</h4>
                <ul>
                    <li><code>UNAUTHORIZED</code>: 인증되지 않은 요청</li>
                    <li><code>FORBIDDEN</code>: 권한이 없는 요청</li>
                    <li><code>NOT_FOUND</code>: 리소스를 찾을 수 없음</li>
                    <li><code>VALIDATION_ERROR</code>: 입력 데이터 검증 실패</li>
                    <li><code>RATE_LIMIT_EXCEEDED</code>: 요청 한도 초과</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h2 class="h5 mb-0">요청 제한</h2>
        </div>
        <div class="card-body">
            <ul>
                <li>인증된 요청: 시간당 1000회</li>
                <li>인증되지 않은 요청: 시간당 100회</li>
                <li>파일 업로드: 시간당 10회</li>
            </ul>
            <p>모든 응답에 요청 제한 헤더가 포함됩니다:</p>
            <pre><code>X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1616236800</code></pre>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?> 