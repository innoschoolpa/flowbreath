<?php
// src/View/resources/create.php
?>
<?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger">
        <?php 
        echo $_SESSION['error_message'];
        unset($_SESSION['error_message']);
        ?>
    </div>
<?php endif; ?>
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">리소스 등록</h3>
                </div>
                <div class="card-body">
                    <form method="post" action="/resources/store" autocomplete="off">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="mb-3">
                            <label for="title" class="form-label">제목 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" value="횡격막 호흡과 복식 호흡이 안 되는 5가지 원인과 해결 방안" required>
                        </div>
                        <div class="mb-3">
                            <label for="url" class="form-label">URL</label>
                            <input type="url" class="form-control" id="url" name="url">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">유형 <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_website" value="Website" checked>
                                    <label class="form-check-label" for="source_type_website">웹사이트</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_paper" value="Paper">
                                    <label class="form-check-label" for="source_type_paper">논문</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_book" value="Book">
                                    <label class="form-check-label" for="source_type_book">도서</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_video" value="Video">
                                    <label class="form-check-label" for="source_type_video">비디오</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_podcast" value="Podcast">
                                    <label class="form-check-label" for="source_type_podcast">팟캐스트</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_personal" value="Personal Experience">
                                    <label class="form-check-label" for="source_type_personal">개인 경험</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="source_type" id="source_type_other" value="Other">
                                    <label class="form-check-label" for="source_type_other">기타</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="tags" class="form-label">태그</label>
                            <input type="text" class="form-control" id="tags" name="tags" value="호흡,건강,스트레스,자세,운동">
                            <small class="text-muted">쉼표(,)로 구분하여 입력하세요.</small>
                        </div>
                        <div class="mb-3">
                            <label for="related_resources" class="form-label">관련 리소스</label>
                            <select multiple class="form-select" id="related_resources" name="related_resources[]"></select>
                            <div class="form-text">Ctrl(또는 Cmd) 키를 누르고 여러 개 선택할 수 있습니다.</div>
                        </div>
                        <div class="mb-3">
                            <label for="author_creator" class="form-label">작가/제작자</label>
                            <input type="text" class="form-control" id="author_creator" name="author_creator">
                        </div>
                        <div class="mb-3">
                            <label for="publication_info" class="form-label">출판 정보</label>
                            <input type="text" class="form-control" id="publication_info" name="publication_info">
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">내용 <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="content" name="content" rows="10" required>
# 횡격막 호흡과 복식 호흡이 안 되는 5가지 원인과 해결 방안

## 1. 잘못된 자세
- **원인**: 구부정한 자세로 인해 횡격막이 제대로 움직이지 못함
- **해결방안**: 
  - 바른 자세 유지하기
  - 등 스트레칭 정기적으로 하기
  - 자세 교정 운동하기

## 2. 스트레스와 긴장
- **원인**: 스트레스로 인한 근육 긴장이 호흡을 방해
- **해결방안**:
  - 명상과 이완 운동
  - 규칙적인 운동
  - 충분한 휴식

## 3. 잘못된 호흡 습관
- **원인**: 오랜 기간 잘못된 호흡 패턴 형성
- **해결방안**:
  - 호흡 운동 꾸준히 하기
  - 전문가와 함께 올바른 호흡법 배우기
  - 일상생활에서 의식적으로 복식호흡 하기

## 4. 신체적 제한
- **원인**: 횡격막 기능 저하나 신체적 문제
- **해결방안**:
  - 의사와 상담
  - 물리치료
  - 단계적인 호흡 운동

## 5. 환경적 요인
- **원인**: 공기 질 나쁨, 알레르기 등
- **해결방안**:
  - 공기청정기 사용
  - 알레르기 원인 제거
  - 환기 자주하기

## 실천 방법
1. 매일 10분씩 호흡 운동하기
2. 자세 체크 알람 설정하기
3. 스트레스 관리 루틴 만들기
4. 전문가 상담 받기
5. 호흡 일지 작성하기</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="summary" class="form-label">요약</label>
                            <textarea class="form-control" id="summary" name="summary" rows="3">횡격막 호흡과 복식 호흡이 잘 되지 않는 주요 원인 5가지와 각각의 해결 방안을 상세히 설명합니다.</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="initial_impression" class="form-label">초기 인상</label>
                            <textarea class="form-control" id="initial_impression" name="initial_impression" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="personal_connection" class="form-label">개인적 연관성</label>
                            <textarea class="form-control" id="personal_connection" name="personal_connection" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">신뢰성 <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="reliability" id="reliability_high" value="High" checked>
                                    <label class="form-check-label" for="reliability_high">높음</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="reliability" id="reliability_medium" value="Medium">
                                    <label class="form-check-label" for="reliability_medium">중간</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="reliability" id="reliability_low" value="Low">
                                    <label class="form-check-label" for="reliability_low">낮음</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="reliability" id="reliability_notassessed" value="Not Assessed">
                                    <label class="form-check-label" for="reliability_notassessed">평가 안함</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="reliability_rationale" class="form-label">신뢰도 근거</label>
                            <textarea class="form-control" id="reliability_rationale" name="reliability_rationale" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">유용성 <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="usefulness" id="usefulness_high" value="High" checked>
                                    <label class="form-check-label" for="usefulness_high">높음</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="usefulness" id="usefulness_medium" value="Medium">
                                    <label class="form-check-label" for="usefulness_medium">중간</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="usefulness" id="usefulness_low" value="Low">
                                    <label class="form-check-label" for="usefulness_low">낮음</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="usefulness" id="usefulness_notassessed" value="Not Assessed">
                                    <label class="form-check-label" for="usefulness_notassessed">평가 안함</label>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="usefulness_context" class="form-label">유용성 맥락</label>
                            <textarea class="form-control" id="usefulness_context" name="usefulness_context" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="perspective_bias" class="form-label">관점/편향</label>
                            <textarea class="form-control" id="perspective_bias" name="perspective_bias" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="strengths" class="form-label">강점</label>
                            <textarea class="form-control" id="strengths" name="strengths" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="weaknesses_limitations" class="form-label">약점/제한사항</label>
                            <textarea class="form-control" id="weaknesses_limitations" name="weaknesses_limitations" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="flowbreath_relevance" class="form-label">FlowBreath 관련성</label>
                            <textarea class="form-control" id="flowbreath_relevance" name="flowbreath_relevance" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="reflection_insights" class="form-label">반성/통찰</label>
                            <textarea class="form-control" id="reflection_insights" name="reflection_insights" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="application_ideas" class="form-label">적용 아이디어</label>
                            <textarea class="form-control" id="application_ideas" name="application_ideas" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="visibility" class="form-label">공개 여부</label>
                            <select class="form-select" id="visibility" name="visibility">
                                <option value="public" selected>공개</option>
                                <option value="private">비공개</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">공지로 고정</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1">
                                <label class="form-check-label" for="is_pinned">상단에 고정(공지)</label>
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="/resources" class="btn btn-secondary me-2">취소</a>
                            <button type="submit" class="btn btn-primary">등록</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
let ckeditorInstance;
document.addEventListener('DOMContentLoaded', function() {
    ClassicEditor
        .create(document.querySelector('#content'), {
            language: 'ko',
            toolbar: [
                'heading', '|', 'bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList', 'blockQuote',
                '|', 'insertTable', 'codeBlock', 'undo', 'redo'
            ]
        })
        .then(editor => {
            editor.ui.view.editable.element.style.height = '400px';
            ckeditorInstance = editor;
        })
        .catch(error => {
            console.error(error);
        });

    // 폼 submit 시 에디터 내용 textarea에 복사
    document.querySelector('form').addEventListener('submit', function(e) {
        if (ckeditorInstance) {
            document.getElementById('content').value = ckeditorInstance.getData();
        }
    });

    // 태그 입력 처리
    const tagsInput = document.getElementById('tags');
    tagsInput.addEventListener('input', function() {
        this.value = this.value.replace(/[^ㄱ-ㅎㅏ-ㅣ가-힣a-zA-Z0-9,]/g, '');
    });
});
</script> 