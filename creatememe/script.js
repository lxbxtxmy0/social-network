document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.form');
    const inputPhoto = document.querySelector('.file_input');
    const addPhotoButton = document.querySelector('.add_button');
    addPhotoButton.addEventListener('click', () => {
        inputPhoto.click();
    });
    const files = [];
    inputPhoto.addEventListener('change', () => {
        files.push(inputPhoto.files[0]);
    });

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(form);
        for (const file of files) {
            formData.append('images[]', file);
        }
        fetch('creatememe.php', {
            method: 'POST',
            body: formData
        }).then(response => response.json()).then(data => console.log(data)).catch(rej => console.log(rej));
        form.reset();
    });
});