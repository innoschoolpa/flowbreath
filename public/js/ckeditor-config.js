ClassicEditor
    .create(document.querySelector('#editor'), {
        image: {
            toolbar: [
                'imageTextAlternative',
                'imageStyle:inline',
                'imageStyle:block',
                'imageStyle:side'
            ],
            upload: {
                types: ['jpeg', 'png', 'gif', 'jpg']
            }
        },
        simpleUpload: {
            uploadUrl: '/upload/image',
            withCredentials: true,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        }
    })
    .catch(error => {
        console.error(error);
    }); 