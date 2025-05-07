<?php
/**
 * src/View/home/about.php
 * FlowBreath.io 소개 페이지 뷰
 *
 * Controller로부터 $page_title 변수를 전달받는다고 가정합니다.
 */

// 공통 헤더 파일 포함
//include __DIR__ . '/../layout/header.php';
?>

<div class="static-page-container about-page">

    <h1><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'FlowBreath.io 소개'; ?></h1>

    <section>
        <h2>우리의 이야기</h2>
        <p>
            FlowBreath.io는 호흡의 중요성을 공유하고, 일상 속에서 쉽고 편안하게 복식 호흡을 접하며
            몸과 마음의 건강한 '흐름'을 찾아갈 수 있도록 돕기 위해 만들어졌습니다.
            10대 시절 클라리넷 연주를 통해 복식 호흡을 처음 만났고, 이후 10여 년간의 호흡 수련과
            명상을 통해 몸과 마음의 변화, 그리고 기(氣)의 자연스러운 흐름을 경험했습니다.
            이곳에서는 제가 직접 경험하고 배운 지식과 방법들을 나누고, 여러분의 소중한 경험과
            이야기도 함께 공유하며 성장하는 공간이 되고자 합니다.
        </p>
    </section>

    <section>
        <h2>무엇을 할 수 있나요?</h2>
        <ul>
            <li>호흡에 대한 다양한 정보와 자료(리소스)를 탐색하고 운영자의 주석과 평가를 참고할 수 있습니다.</li>
            <li>복식 호흡의 기초부터 다양한 호흡법에 대한 가이드를 찾아볼 수 있습니다. (추가 예정)</li>
            <li>호흡을 통해 경험한 긍정적인 변화나 이야기를 커뮤니티에서 나눌 수 있습니다. (추가 예정)</li>
            <li><a href="/resources">자료실</a>에서 엄선된 호흡 관련 정보들을 만나보세요.</li>
            <li><a href="/">홈페이지</a>에서 최신 소식을 확인하세요.</li>
        </ul>
    </section>

    <section>
        <h2>함께 호흡해요</h2>
        <p>
            호흡은 우리가 살아가는 매 순간 함께하는 가장 기본적인 행위입니다.
            FlowBreath.io와 함께 의식적인 호흡을 통해 삶의 활력과 평온을 되찾는 여정을 시작해보세요.
        </p>
    </section>

</div>

<style>
    /* 페이지 특정 스타일 (필요한 경우) */
    .static-page-container {
        max-width: 800px; /* 내용 최대 너비 */
        margin: 30px auto; /* 상하 여백 및 가운데 정렬 */
        padding: 20px;
        line-height: 1.7; /* 줄 간격 */
    }
    .static-page-container h1 {
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }
    .static-page-container h2 {
        margin-top: 30px;
        margin-bottom: 15px;
        color: #333;
    }
    .static-page-container section {
        margin-bottom: 30px;
    }
    .static-page-container ul {
        padding-left: 20px;
    }
    .static-page-container li {
        margin-bottom: 8px;
    }
</style>

<?php
// 공통 푸터 파일 포함
include __DIR__ . '/../layout/footer.php';
?>