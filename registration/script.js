document.addEventListener('DOMContentLoaded', () => {
    const registrationForm = document.getElementById('registration_form');
    const errorContainer = document.getElementById('error_container');
    const avatarUploadBox = document.getElementById('avatar_upload_box');
    const avatarInput = document.getElementById('avatar_input');
    const avatarPlaceholder = document.getElementById('avatar_placeholder');

    const nameInput = document.getElementById('name_input');
    const surnameInput = document.getElementById('surname_input');
    const loginInput = document.getElementById('login_input');
    const passwordInput = document.getElementById('password_input');

    if (!registrationForm) {
        return;
    }

    const errorBox = document.createElement('div');
    errorBox.className = 'error_message_box';
    errorBox.style.display = 'none';
    errorContainer.appendChild(errorBox);

    function showErrorMessage(message) {
        errorBox.textContent = message;
        errorBox.style.display = 'block';
    }

    function clearErrorMessage() {
        errorBox.textContent = '';
        errorBox.style.display = 'none';
    }

    if (avatarUploadBox && avatarInput) {
        avatarUploadBox.addEventListener('click', () => {
            avatarInput.click();
        });

        avatarInput.addEventListener('change', () => {
            const files = avatarInput.files;
            if (files.length > 0) {
                const selectedFile = files[0];
                const objectUrl = URL.createObjectURL(selectedFile);

                avatarPlaceholder.innerHTML = '';
                const previewImage = document.createElement('img');
                previewImage.src = objectUrl;
                previewImage.className = 'avatar_preview_image';

                avatarPlaceholder.appendChild(previewImage);
            }
        });
    }

    function validateFormFields() {
        if (avatarInput.files.length === 0) {
            showErrorMessage('Пожалуйста, загрузите аватарку');
            return false;
        }

        const nameValue = nameInput.value.trim();
        if (nameValue.length === 0) {
            showErrorMessage('Пожалуйста, введите имя');
            return false;
        }

        const surnameValue = surnameInput.value.trim();
        if (surnameValue.length === 0) {
            showErrorMessage('Пожалуйста, введите фамилию');
            return false;
        }

        const loginValue = loginInput.value.trim();
        if (loginValue.length === 0) {
            showErrorMessage('Пожалуйста, введите логин');
            return false;
        }

        if (loginValue.length < 4 || loginValue.length > 50) {
            showErrorMessage('Логин должен содержать от 4 до 50 символов');
            return false;
        }

        const loginPattern = /^[a-zA-Z0-9]+$/;
        if (!loginPattern.test(loginValue)) {
            showErrorMessage('Логин может содержать только английские буквы и цифры');
            return false;
        }

        const passwordValue = passwordInput.value.trim();
        if (passwordValue.length === 0) {
            showErrorMessage('Пожалуйста, введите пароль');
            return false;
        }

        if (passwordValue.length < 8) {
            showErrorMessage('Пароль должен содержать минимум 8 символов');
            return false;
        }

        return true;
    }

    registrationForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrorMessage();

        if (!validateFormFields()) {
            return;
        }

        const submitButton = registrationForm.querySelector('.submit_button');
        if (submitButton) {
            submitButton.disabled = true;
        }

        const formData = new FormData(registrationForm);

        const fetchResponse = await fetch('/registration/registration.php', {
            method: 'POST',
            body: formData
        });

        const responseText = await fetchResponse.text();

        try {
            const parsedJsonData = JSON.parse(responseText);

            if (fetchResponse.status !== 200) {
                showErrorMessage(parsedJsonData.error || 'Произошла ошибка при регистрации');
            } else if (parsedJsonData.redirect) {
                window.location.href = parsedJsonData.redirect;
            }
        } catch (errorParsingJson) {
            console.error('Ошибка парсинга. Сервер вернул:', responseText);
            showErrorMessage('Системная ошибка сервера. Повторите позже.');
        } finally {
            if (submitButton) {
                submitButton.disabled = false;
            }
        }
    });
});