<?php
// src/View/layout/header.php
?>
<!DOCTYPE html>
<html lang="<?= get_language() ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= __('app_name') ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <!-- TinyMCE -->
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <!-- Custom CSS -->
    <style>
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        .badge {
            font-size: 0.8em;
        }
        .select2-container--bootstrap {
            width: 100% !important;
        }
        /* TinyMCE 에디터 스타일 */
        .tox-tinymce {
            border-radius: 0.25rem;
        }
        .tox .tox-edit-area__iframe {
            background-color: white;
        }
        .tox .tox-statusbar {
            border-top: 1px solid #e9ecef;
        }
    </style>

    <!-- TinyMCE 초기화 스크립트 -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // 에디터가 필요한 textarea 찾기
        const editorElements = document.querySelectorAll('.tinymce-editor');
        
        if (editorElements.length > 0) {
            // TinyMCE 설정
            const editorConfig = {
                height: 400,
                menubar: false,
                plugins: [
                    'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'help', 'wordcount', 'paste'
                ],
                toolbar: 'undo redo | formatselect | ' +
                        'bold italic backcolor | alignleft aligncenter ' +
                        'alignright alignjustify | bullist numlist outdent indent | ' +
                        'removeformat | help',
                content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial; font-size: 14px; }',
                paste_data_images: true,
                paste_retain_style_properties: 'all',
                paste_word_valid_elements: 'b,strong,i,em,h1,h2,h3,h4,h5,h6,p,ol,ul,li,table,tr,td,th,tbody,thead,tfoot,div,span,font,a,img',
                paste_webkit_styles: 'all',
                paste_merge_formats: true,
                browser_spellcheck: true,
                contextmenu: false,
                language: '<?= get_language() ?>',
                language_url: '/js/tinymce/langs/<?= get_language() ?>.js'
            };

            // 각 에디터 요소에 TinyMCE 적용
            editorElements.forEach(function(element) {
                tinymce.init({
                    ...editorConfig,
                    selector: '#' + element.id
                });
            });
        }
    });
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="/"><?= __('app_name') ?></a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="/resource/list"><?= __('resource.list') ?></a>
                    </li>
                    <?php if (is_admin()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/resource/create"><?= __('resource.create') ?></a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/tag/manage"><?= __('tag.management') ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center">
                    <!-- 언어 선택기 -->
                    <?= language_selector() ?>
                    
                    <!-- 로그인/로그아웃 -->
                    <?php if (is_logged_in()): ?>
                        <form action="/logout" method="POST" class="ms-3">
                            <button type="submit" class="btn btn-outline-primary">Logout</button>
                        </form>
                    <?php else: ?>
                        <a href="/login" class="btn btn-outline-primary ms-3">Login</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show m-3" role="alert">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show m-3" role="alert">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <main class="container py-4"> 