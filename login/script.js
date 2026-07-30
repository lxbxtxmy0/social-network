document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('login_form');
    const errorContainer = document.getElementById('error_container');

    const loginInput = document.getElementById('login_input');
    const passwordInput = document.getElementById('password_input');

    if (!loginForm) {
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

    function validateLoginFields() {
        const loginValue = loginInput.value.trim();
        if (loginValue.length === 0) {
            showErrorMessage('Пожалуйста, введите логин');
            return false;
        }

        const passwordValue = passwordInput.value.trim();
        if (passwordValue.length === 0) {
            showErrorMessage('Пожалуйста, введите пароль');
            return false;
        }

        return true;
    }

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearErrorMessage();

        if (!validateLoginFields()) {
            return;
        }

        const submitButton = loginForm.querySelector('.submit_button');
        if (submitButton) {
            submitButton.disabled = true;
        }

        const formData = new FormData(loginForm);

        const fetchResponse = await fetch('/login/login.php', {
            method: 'POST',
            body: formData
        });

        const parsedJsonData = await fetchResponse.json();

        if (fetchResponse.status !== 200) {
            showErrorMessage(parsedJsonData.error || 'Произошла ошибка при входе');
            if (submitButton) {
                submitButton.disabled = false;
            }
        } else {
            window.location.href = '/home';
        }
    });
});