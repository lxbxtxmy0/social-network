document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.querySelector('.login_form');

    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const formData = new FormData(loginForm);

        try {
            const response = await fetch('login.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (response.status === 200) {
                window.location.href = '../home';
            } else {
                console.log(data.error);
            }
        } catch (error) {
            console.log(error);
        }
    });
});