<?php
/**
 * resources/edit.php
 * 리소스 수정 폼
 */

// 디버깅을 위한 데이터 출력
error_log("Resource data in edit form:");
error_log(print_r($resource, true));

// 선택값을 변수로 저장 - 값이 없을 경우 빈 문자열 사용
$source_type = isset($resource['source_type']) ? trim($resource['source_type']) : '';
$reliability = isset($resource['reliability']) ? trim($resource['reliability']) : '';
$usefulness = isset($resource['usefulness']) ? trim($resource['usefulness']) : '';
$is_public = isset($resource['is_public']) ? (int)$resource['is_public'] : 1;
$is_pinned = isset($resource['is_pinned']) ? (int)$resource['is_pinned'] : 0;

// 디버깅을 위한 선택값 출력
error_log("Source type: '" . $source_type . "' (type: " . gettype($source_type) . ")");
error_log("Reliability: '" . $reliability . "' (type: " . gettype($reliability) . ")");
error_log("Usefulness: '" . $usefulness . "' (type: " . gettype($usefulness) . ")");

// 디버깅을 위한 전체 데이터 출력
error_log("Full resource data:");
foreach ($resource as $key => $value) {
    $keyStr = is_array($key) ? json_encode($key, JSON_UNESCAPED_UNICODE) : $key;
    if (is_array($value)) {
        $valueStr = json_encode($value, JSON_UNESCAPED_UNICODE);
    } else {
        $valueStr = is_null($value) ? 'null' : "'" . $value . "'";
    }
    error_log("$keyStr: $valueStr");
}

// 라디오 버튼 체크 함수
function isChecked($currentValue, $optionValue) {
    return strcasecmp((string)$currentValue, (string)$optionValue) === 0 ? 'checked' : '';
}
?>

<div class="container mt-4">
    <h2>리소스 수정</h2>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
            echo $_SESSION['error_message'];
            unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>

    <form action="/resources/<?php echo $resource['resource_id']; ?>" method="POST" class="needs-validation" novalidate>
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        <div class="d-flex justify-content-end mb-3">
            <button type="submit" class="btn btn-primary me-2">저장</button>
            <a href="/resources/view/<?php echo $resource['resource_id']; ?>" class="btn btn-secondary">취소</a>
        </div>
        <div class="mb-3">
            <label for="language_code" class="form-label">언어</label>
            <select class="form-select" id="language_code" name="language_code">
                <option value="ko" <?= ($resource['language_code'] ?? 'ko') === 'ko' ? 'selected' : '' ?>>한국어</option>
                <option value="en" <?= ($resource['language_code'] ?? '') === 'en' ? 'selected' : '' ?>>English</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="status" class="form-label">상태</label>
            <select class="form-select" id="status" name="status">
                <option value="draft" <?= ($resource['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>임시저장</option>
                <option value="published" <?= ($resource['status'] ?? '') === 'published' ? 'selected' : '' ?>>발행</option>
            </select>
        </div>
        <div class="form-group">
            <label for="visibility"><?= __('resources.visibility') ?></label>
            <select name="visibility" id="visibility" class="form-select">
                <option value="public" <?php echo ($resource['visibility'] ?? '') === 'public' ? 'selected' : ''; ?>>공개</option>
                <option value="private" <?php echo ($resource['visibility'] ?? '') === 'private' ? 'selected' : ''; ?>>비공개</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">제목</label>
            <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($resource['title'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label for="url" class="form-label">URL</label>
            <input type="url" class="form-control" id="url" name="url" value="<?php echo htmlspecialchars($resource['url'] ?? ''); ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">유형 <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="source_type" id="source_type_website" value="Website" <?php echo isChecked($source_type, 'Website'); ?> required>
                    <label class="form-check-label" for="source_type_website">웹사이트</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="source_type" id="source_type_paper" value="Paper" <?php echo isChecked($source_type, 'Paper'); ?>>
                    <label class="form-check-label" for="source_type_paper">논문</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="source_type" id="source_type_book" value="Book" <?php echo isChecked($source_type, 'Book'); ?>>
                    <label class="form-check-label" for="source_type_book">도서</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="source_type" id="source_type_video" value="Video" <?php echo isChecked($source_type, 'Video'); ?>>
                    <label class="form-check-label" for="source_type_video">비디오</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="source_type" id="source_type_podcast" value="Podcast" <?php echo isChecked($source_type, 'Podcast'); ?>>
                    <label class="form-check-label" for="source_type_podcast">팟캐스트</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="source_type" id="source_type_personal" value="Personal Experience" <?php echo isChecked($source_type, 'Personal Experience'); ?>>
                    <label class="form-check-label" for="source_type_personal">개인 경험</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="source_type" id="source_type_other" value="Other" <?php echo isChecked($source_type, 'Other'); ?>>
                    <label class="form-check-label" for="source_type_other">기타</label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="tags" class="form-label">태그</label>
            <input type="text" class="form-control" id="tags" name="tags" 
                   value="<?php echo htmlspecialchars(implode(', ', array_column($tags ?? [], 'tag_name'))); ?>"
                   placeholder="태그를 쉼표로 구분하여 입력하세요">
            <div class="form-text">태그를 쉼표로 구분하여 입력하세요. 예: 교육, 기술, 혁신</div>
        </div>

        <div class="mb-3">
            <label for="related_resources" class="form-label">관련 리소스</label>
            <select multiple class="form-select" id="related_resources" name="related_resources[]">
                <?php foreach (($all_resources ?? []) as $res): ?>
                    <?php if ($res['resource_id'] != $resource['resource_id']): ?>
                        <option value="<?php echo $res['resource_id']; ?>"
                            <?php if (!empty($current_related_ids) && in_array($res['resource_id'], $current_related_ids)) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($res['title']); ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
            <div class="form-text">Ctrl(또는 Cmd) 키를 누르고 여러 개 선택할 수 있습니다.</div>
        </div>

        <div class="mb-3">
            <label for="author_creator" class="form-label">작가/제작자</label>
            <input type="text" class="form-control" id="author_creator" name="author_creator" value="<?php echo htmlspecialchars($resource['author_creator'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="publication_info" class="form-label">출판 정보</label>
            <input type="text" class="form-control" id="publication_info" name="publication_info" value="<?php echo htmlspecialchars($resource['publication_info'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label for="content" class="form-label">내용 (서식 지원, 붙여넣기 가능)</label>
            <textarea class="form-control" id="content" name="content" rows="12"><?php echo $resource['content'] ?? ''; ?></textarea>
        </div>

        <div class="mb-3">
            <label for="summary" class="form-label">요약</label>
            <textarea class="form-control" id="summary" name="summary" rows="3"><?php echo htmlspecialchars($resource['summary'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="initial_impression" class="form-label">초기 인상</label>
            <textarea class="form-control" id="initial_impression" name="initial_impression" rows="3"><?php echo htmlspecialchars($resource['initial_impression'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="personal_connection" class="form-label">개인적 연관성</label>
            <textarea class="form-control" id="personal_connection" name="personal_connection" rows="3"><?php echo htmlspecialchars($resource['personal_connection'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">신뢰성 <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="reliability" id="reliability_high" value="High" <?php echo isChecked($reliability, 'High'); ?>>
                    <label class="form-check-label" for="reliability_high">높음</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="reliability" id="reliability_medium" value="Medium" <?php echo isChecked($reliability, 'Medium'); ?>>
                    <label class="form-check-label" for="reliability_medium">중간</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="reliability" id="reliability_low" value="Low" <?php echo isChecked($reliability, 'Low'); ?>>
                    <label class="form-check-label" for="reliability_low">낮음</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="reliability" id="reliability_notassessed" value="Not Assessed" <?php echo isChecked($reliability, 'Not Assessed'); ?>>
                    <label class="form-check-label" for="reliability_notassessed">평가 안함</label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="reliability_rationale" class="form-label">신뢰도 근거</label>
            <textarea class="form-control" id="reliability_rationale" name="reliability_rationale" rows="3"><?php echo htmlspecialchars($resource['reliability_rationale'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">유용성 <span class="text-danger">*</span></label>
            <div class="d-flex flex-wrap gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="usefulness" id="usefulness_high" value="High" <?php echo isChecked($usefulness, 'High'); ?>>
                    <label class="form-check-label" for="usefulness_high">높음</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="usefulness" id="usefulness_medium" value="Medium" <?php echo isChecked($usefulness, 'Medium'); ?>>
                    <label class="form-check-label" for="usefulness_medium">중간</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="usefulness" id="usefulness_low" value="Low" <?php echo isChecked($usefulness, 'Low'); ?>>
                    <label class="form-check-label" for="usefulness_low">낮음</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="usefulness" id="usefulness_notassessed" value="Not Assessed" <?php echo isChecked($usefulness, 'Not Assessed'); ?>>
                    <label class="form-check-label" for="usefulness_notassessed">평가 안함</label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label for="usefulness_context" class="form-label">유용성 맥락</label>
            <textarea class="form-control" id="usefulness_context" name="usefulness_context" rows="3"><?php echo htmlspecialchars($resource['usefulness_context'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="perspective_bias" class="form-label">관점/편향</label>
            <textarea class="form-control" id="perspective_bias" name="perspective_bias" rows="3"><?php echo htmlspecialchars($resource['perspective_bias'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="strengths" class="form-label">강점</label>
            <textarea class="form-control" id="strengths" name="strengths" rows="3"><?php echo htmlspecialchars($resource['strengths'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="weaknesses_limitations" class="form-label">약점/제한사항</label>
            <textarea class="form-control" id="weaknesses_limitations" name="weaknesses_limitations" rows="3"><?php echo htmlspecialchars($resource['weaknesses_limitations'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="flowbreath_relevance" class="form-label">FlowBreath 관련성</label>
            <textarea class="form-control" id="flowbreath_relevance" name="flowbreath_relevance" rows="3"><?php echo htmlspecialchars($resource['flowbreath_relevance'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="reflection_insights" class="form-label">반성/통찰</label>
            <textarea class="form-control" id="reflection_insights" name="reflection_insights" rows="3"><?php echo htmlspecialchars($resource['reflection_insights'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label for="application_ideas" class="form-label">적용 아이디어</label>
            <textarea class="form-control" id="application_ideas" name="application_ideas" rows="3"><?php echo htmlspecialchars($resource['application_ideas'] ?? ''); ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">공개 여부 <span class="text-danger">*</span></label>
            <div class="d-flex flex-row gap-3">
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="is_public" id="is_public_1" value="1" <?php echo $is_public === 1 ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_public_1">공개</label>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="is_public" id="is_public_0" value="0" <?php echo $is_public === 0 ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="is_public_0">비공개</label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label">공지로 고정</label>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="is_pinned" id="is_pinned" value="1" <?php echo $is_pinned === 1 ? 'checked' : ''; ?>>
                <label class="form-check-label" for="is_pinned">상단에 고정(공지)</label>
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary me-2">저장</button>
            <a href="/resources/view/<?php echo $resource['resource_id']; ?>" class="btn btn-secondary">취소</a>
        </div>
    </form>
</div>

<!-- CKEditor 5 CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script>
// 이미지 업로드 핸들러
function uploadImage(file) {
    const formData = new FormData();
    formData.append('image', file);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?>');

    return fetch('/upload/image', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return {
                default: data.url
            };
        } else {
            throw new Error(data.error || '이미지 업로드에 실패했습니다.');
        }
    });
}

// CKEditor 이미지 업로드 어댑터 설정
class CustomUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file
            .then(file => uploadImage(file));
    }

    abort() {
        // 업로드 중단 처리
    }
}

function CustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new CustomUploadAdapter(loader);
    };
}

document.addEventListener('DOMContentLoaded', function() {
    ClassicEditor
        .create(document.querySelector('#content'), {
            extraPlugins: [CustomUploadAdapterPlugin],
            language: 'ko',
            toolbar: [
                'heading', '|', 'bold', 'italic', 'underline', 'link', 'bulletedList', 'numberedList', 'blockQuote',
                '|', 'insertTable', 'codeBlock', 'undo', 'redo', 'imageUpload'
            ],
            image: {
                toolbar: [
                    'imageTextAlternative',
                    'imageStyle:inline',
                    'imageStyle:block',
                    'imageStyle:side',
                    '|',
                    'resizeImage'
                ],
                styles: [
                    'full',
                    'side',
                    'alignLeft',
                    'alignCenter',
                    'alignRight'
                ],
                resizeOptions: [
                    {
                        name: 'imageResize:original',
                        value: null,
                        label: 'Original'
                    },
                    {
                        name: 'imageResize:25',
                        value: '25',
                        label: '25%'
                    },
                    {
                        name: 'imageResize:50',
                        value: '50',
                        label: '50%'
                    },
                    {
                        name: 'imageResize:75',
                        value: '75',
                        label: '75%'
                    }
                ],
                resizeUnit: '%',
                upload: {
                    types: ['jpeg', 'png', 'gif', 'jpg']
                },
                insert: {
                    type: 'block'
                },
                styles: {
                    options: [
                        'inline',
                        'block',
                        'side'
                    ]
                }
            },
            table: {
                contentToolbar: [
                    'tableColumn',
                    'tableRow',
                    'mergeTableCells'
                ]
            }
        })
        .then(editor => {
            // 에디터 컨텐츠 영역에 스타일 적용
            const editorElement = editor.editing.view.document.getRoot();
            editorElement.setStyle('max-width', '100%');
            editorElement.setStyle('overflow-x', 'hidden');
            
            // 이미지 업로드 후 자동 크기 조절
            editor.plugins.get('FileRepository').on('change:isUploading', (evt, data, isUploading) => {
                if (!isUploading) {
                    const images = editor.editing.view.document.getRoot().queryAll('img');
                    images.forEach(img => {
                        const width = img.getStyle('width');
                        if (width && parseInt(width) > 100) {
                            img.setStyle('width', '100%');
                        }
                    });
                }
            });
        })
        .catch(error => {
            console.error(error);
        });
});
</script>