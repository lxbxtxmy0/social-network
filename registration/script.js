document.addEventListener('DOMContentLoaded', () => {
    const registrationFormElement = document.getElementById('registration_form');
    const errorContainerElement = document.getElementById('error_container');

    if (!registrationFormElement) {
        return;
    }

    const errorBoxElement = document.createElement('div');
    errorBoxElement.className = 'error_message_box';
    errorBoxElement.style.display = 'none';
    errorContainerElement.appendChild(errorBoxElement);

    function showErrorMessage(messageString) {
        errorBoxElement.textContent = messageString;
        errorBoxElement.style.display = 'block';
    }

    function clearErrorMessage() {
        errorBoxElement.textContent = '';
        errorBoxElement.style.display = 'none';
    }

    registrationFormElement.addEventListener('submit', async (eventObject) => {
        eventObject.preventDefault();
        clearErrorMessage();

        const formDataObject = new FormData(registrationFormElement);

        // ИСПРАВЛЕННЫЙ ПУТЬ: Строго от корня сайта в папку registration
        const fetchResponse = await fetch('/registration/registration.php', {
            method: 'POST',
            body: formDataObject
        });

        const parsedJsonData = await fetchResponse.json();

        if (fetchResponse.status !== 200) {
            showErrorMessage(parsedJsonData.error || 'Произошла ошибка');
        } else if (parsedJsonData.redirect) {
            window.location.href = parsedJsonData.redirect;
        }
    });
});