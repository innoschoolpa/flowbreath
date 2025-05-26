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
                        // Ensure the URL is absolute
                        const imageUrl = response.url.startsWith('/') ? response.url : '/' + response.url;
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