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
        ckfinder: {
            uploadUrl: '/upload/image'
        }
    })
    .catch(error => {
        console.error(error);
    }); 