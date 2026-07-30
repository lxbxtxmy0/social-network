document.addEventListener('DOMContentLoaded', () => {
    const memeForm = document.querySelector('#meme_form');

    if (!memeForm) {
        return;
    }

    const photoInput = document.querySelector('#file_input');
    const addPhotoButton = document.querySelector('.add_button');
    const uploadBox = document.querySelector('.upload_box');
    const uploadLabel = document.querySelector('.upload_label');
    const titleInput = document.querySelector('#title');
    const descriptionInput = document.querySelector('#description');
    const coinsInput = document.querySelector('#coins');
    const errorContainer = document.querySelector('#error_container');

    const errorBox = document.createElement('div');
    errorBox.className = 'error_box';
    errorContainer.appendChild(errorBox);

    let selectedFile = null;

    function showErrorMessage(message) {
        errorBox.textContent = message;
        errorBox.style.display = 'block';
    }

    function clearErrorMessage() {
        errorBox.textContent = '';
        errorBox.style.display = 'none';
    }

    function renderImagePreview() {
        const existingPreview = uploadBox.querySelector('.preview_container');

        if (existingPreview) {
            existingPreview.remove();
        }

        if (!selectedFile) {
            uploadLabel.style.display = 'flex';
            return;
        }

        uploadLabel.style.display = 'none';

        const previewContainer = document.createElement('div');
        previewContainer.className = 'preview_container';

        const previewImage = document.createElement('img');
        previewImage.src = URL.createObjectURL(selectedFile);
        previewImage.className = 'preview_img';

        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'preview_remove';
        removeButton.textContent = '×';

        removeButton.addEventListener('click', () => {
            selectedFile = null;
            photoInput.value = '';
            renderImagePreview();
        });

        previewContainer.appendChild(previewImage);
        previewContainer.appendChild(removeButton);
        uploadBox.appendChild(previewContainer);
    }

    addPhotoButton.addEventListener('click', () => {
        photoInput.click();
    });

    photoInput.addEventListener('change', () => {
        clearErrorMessage();

        const files = photoInput.files;

        if (files.length > 0) {
            const uploadedFile = files[0];
            const allowedFileTypes = ['image/jpeg', 'image/png'];

            if (!allowedFileTypes.includes(uploadedFile.type)) {
                showErrorMessage('Можно загружать только JPEG или PNG');
                photoInput.value = '';
                return;
            }

            selectedFile = uploadedFile;
            renderImagePreview();
        }
    });

    function validateMemeForm() {
        if (!selectedFile) {
            showErrorMessage('Добавьте хотя бы одно фото');
            return false;
        }

        const titleValue = titleInput.value.trim();

        if (titleValue.length === 0) {
            showErrorMessage('Заполните заголовок');
            return false;
        }

        if (titleValue.length > 50) {
            showErrorMessage('Заголовок не должен превышать 50 символов');
            return false;
        }

        const descriptionValue = descriptionInput.value.trim();

        if (descriptionValue.length > 300) {
            showErrorMessage('Описание не должно превышать 300 символов');
            return false;
        }

        const coinsValue = coinsInput.value.trim();

        if (!coinsValue) {
            showErrorMessage('Укажите количество коинов');
            return false;
        }

        const parsedCoins = Number(coinsValue);

        if (isNaN(parsedCoins) || !Number.isInteger(parsedCoins)) {
            showErrorMessage('Количество коинов должно быть целым числом');
            return false;
        }

        if (parsedCoins < 5) {
            showErrorMessage('Минимальная цена создания мема - 5 коинов');
            return false;
        }

        return true;
    }

    memeForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrorMessage();

        if (!validateMemeForm()) {
            return;
        }

        const submitButtons = memeForm.querySelectorAll('button[type="submit"]');
        submitButtons.forEach((button) => {
            button.disabled = true;
        });

        const formData = new FormData();
        formData.append('title', titleInput.value.trim());
        formData.append('description', descriptionInput.value.trim());
        formData.append('coins', coinsInput.value.trim());
        formData.append('images[]', selectedFile);

        const fetchResponse = await fetch('/creatememe/creatememe.php', {
            method: 'POST',
            body: formData
        });

        const responseText = await fetchResponse.text();

        try {
            const parsedJsonData = JSON.parse(responseText);

            if (fetchResponse.status !== 200) {
                showErrorMessage(parsedJsonData.error || 'Произошла ошибка при создании');
            } else {
                window.location.href = '/home';
            }
        } catch (errorParsingJson) {
            console.error('Ошибка парсинга сервера:', responseText);
            showErrorMessage('Системная ошибка сервера. Повторите позже.');
        } finally {
            submitButtons.forEach((button) => {
                button.disabled = false;
            });
        }
    });
});