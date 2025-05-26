// 이미지 업로드 설정
images_upload_url: '/upload/image',
images_upload_base_path: '/',
images_reuse_filename: true,
automatic_uploads: true,
file_picker_types: 'image',

// 개선된 이미지 업로드 핸들러
images_upload_handler: function (blobInfo, progress) {
    return new Promise((resolve, reject) => {
        const formData = new FormData();
        formData.append('image', blobInfo.blob(), blobInfo.filename());
        formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');

        const xhr = new XMLHttpRequest();
        
        xhr.upload.onprogress = function(e) {
            if (e.lengthComputable) {
                progress(e.loaded / e.total * 100);
            }
        };

        xhr.onload = function() {
            if (xhr.status === 200) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        // Ensure the URL is absolute and starts with /uploads/images/
                        let imageUrl = response.url;
                        if (!imageUrl.startsWith('/')) {
                            imageUrl = '/' + imageUrl;
                        }
                        if (!imageUrl.startsWith('/uploads/images/')) {
                            imageUrl = '/uploads/images/' + imageUrl.replace(/^\/+/, '');
                        }
                        resolve(imageUrl);
                    } else {
                        reject(response.error || '이미지 업로드에 실패했습니다.');
                    }
                } catch (e) {
                    reject('서버 응답을 파싱할 수 없습니다.');
                }
            } else {
                reject('서버 오류: ' + xhr.status);
            }
        };

        xhr.onerror = function() {
            reject('네트워크 오류가 발생했습니다.');
        };

        xhr.open('POST', '/upload/image');
        xhr.send(formData);
    });
},

// 파일 선택기 콜백
file_picker_callback: function(callback, value, meta) {
    if (meta.filetype === 'image') {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.setAttribute('accept', 'image/*');
        
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const formData = new FormData();
                formData.append('image', file);
                formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
                
                fetch('/upload/image', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Ensure the URL is absolute and starts with /uploads/images/
                        let imageUrl = data.url;
                        if (!imageUrl.startsWith('/')) {
                            imageUrl = '/' + imageUrl;
                        }
                        if (!imageUrl.startsWith('/uploads/images/')) {
                            imageUrl = '/uploads/images/' + imageUrl.replace(/^\/+/, '');
                        }
                        callback(imageUrl, { alt: file.name });
                    } else {
                        alert('이미지 업로드 실패: ' + (data.error || '알 수 없는 오류'));
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('이미지 업로드 중 오류가 발생했습니다.');
                });
            }
        });
        
        input.click();
    }
}, 