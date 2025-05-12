<?php
// src/View/about.php
// FlowBreath.io 통합 소개 페이지
// 공통 헤더 포함
include __DIR__ . '/layout/header.php';
?>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0">FlowBreath.io 소개</h2>
                </div>
                <div class="card-body">
                    <p class="lead">FlowBreath.io는 호흡, 복식호흡, 웰빙, 자기성장 등 다양한 주제의 자료와 경험을 공유하고 탐색할 수 있는 오픈 커뮤니티 플랫폼입니다.</p>
                    <hr>
                    <h4>미션</h4>
                    <p>누구나 쉽게 신뢰할 수 있는 호흡 관련 자료를 찾고, 자신의 경험과 인사이트를 나누며, 건강한 삶을 위한 실천을 함께 만들어갑니다.</p>
                    <h4>주요 기능</h4>
                    <ul class="list-group list-group-flush mb-4">
                        <li class="list-group-item">🔍 <strong>리소스 검색/필터</strong> — 키워드, 태그, 유형 등 다양한 조건으로 원하는 자료를 쉽고 빠르게 찾을 수 있습니다.</li>
                        <li class="list-group-item">🏷️ <strong>태그 기반 분류</strong> — 관심 주제별로 자료를 분류하고, 관련 리소스를 한눈에 확인할 수 있습니다.</li>
                        <li class="list-group-item">🤝 <strong>커뮤니티 참여</strong> — 자신의 경험, 의견, 질문을 자유롭게 공유하고, 다른 사람들과 소통할 수 있습니다.</li>
                        <li class="list-group-item">⭐ <strong>리소스 평가/북마크</strong> — 유용한 자료를 평가하고, 나만의 즐겨찾기를 관리할 수 있습니다.</li>
                        <li class="list-group-item">📈 <strong>통계와 대시보드</strong> — 인기 자료, 태그, 활동 현황 등 다양한 통계를 제공합니다.</li>
                    </ul>
                    <div class="alert alert-info text-center mb-0">
                        <strong>지금 바로 다양한 리소스를 탐색하고, 나만의 호흡 여정을 시작해보세요!</strong><br>
                        <a href="/resources" class="btn btn-primary mt-2">리소스 둘러보기</a>
                    </div>
                </div>
            </div>
            <div class="static-page-container about-page bg-white p-4 rounded shadow-sm">
                <h2>우리의 이야기</h2>
                <p>
                    FlowBreath.io는 호흡의 중요성을 공유하고, 일상 속에서 쉽고 편안하게 복식 호흡을 접하며
                    몸과 마음의 건강한 '흐름'을 찾아갈 수 있도록 돕기 위해 만들어졌습니다.<br>
                    10대 시절 클라리넷 연주를 통해 복식 호흡을 처음 만났고, 이후 10여 년간의 호흡 수련과
                    명상을 통해 몸과 마음의 변화, 그리고 기(氣)의 자연스러운 흐름을 경험했습니다.<br>
                    이곳에서는 제가 직접 경험하고 배운 지식과 방법들을 나누고, 여러분의 소중한 경험과
                    이야기도 함께 공유하며 성장하는 공간이 되고자 합니다.
                </p>
                <h3 class="mt-4">무엇을 할 수 있나요?</h3>
                <ul>
                    <li>호흡에 대한 다양한 정보와 자료(리소스)를 탐색하고 운영자의 주석과 평가를 참고할 수 있습니다.</li>
                    <li>복식 호흡의 기초부터 다양한 호흡법에 대한 가이드를 찾아볼 수 있습니다. (추가 예정)</li>
                    <li>호흡을 통해 경험한 긍정적인 변화나 이야기를 커뮤니티에서 나눌 수 있습니다. (추가 예정)</li>
                    <li><a href="/resources">자료실</a>에서 엄선된 호흡 관련 정보들을 만나보세요.</li>
                    <li><a href="/">홈페이지</a>에서 최신 소식을 확인하세요.</li>
                </ul>
                <h3 class="mt-4">함께 호흡해요</h3>
                <p>
                    호흡은 우리가 살아가는 매 순간 함께하는 가장 기본적인 행위입니다.<br>
                    FlowBreath.io와 함께 의식적인 호흡을 통해 삶의 활력과 평온을 되찾는 여정을 시작해보세요.
                </p>
            </div>
        </div>
    </div>
</div>
<style>
.static-page-container {
    max-width: 800px;
    margin: 30px auto;
    padding: 20px;
    line-height: 1.7;
}
.static-page-container h2 {
    margin-top: 30px;
    margin-bottom: 15px;
    color: #333;
}
.static-page-container h3 {
    margin-top: 24px;
    margin-bottom: 12px;
    color: #444;
}
.static-page-container ul {
    padding-left: 20px;
}
.static-page-container li {
    margin-bottom: 8px;
}
</style>
<?php
// 공통 푸터 포함
include __DIR__ . '/layout/footer.php';
?> 