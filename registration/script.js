document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.form');
    const login = document.getElementById('login');

    // login.addEventListener('change', () => {
    //     const value = login.value;
    // }); поработать над длиной и существованием
// логин мин длина 8 макс 50
    form.addEventListener("submit", (event) => {
        event.preventDefault();
        const formData = new FormData(form);
        fetch('registration.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                console.dir(data)
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            }).catch(response => console.dir(response));

    });
});