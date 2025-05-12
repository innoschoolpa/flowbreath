import ClassicEditor from '@ckeditor/ckeditor5-build-classic';

document.addEventListener('DOMContentLoaded', function() {
    const editorElement = document.querySelector('#content');
    if (editorElement) {
        ClassicEditor.create(editorElement)
            .catch(error => { console.error(error); });
    }
}); 