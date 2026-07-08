document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('.form');
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const formData = new FormData(form);
        fetch('login.php', {
            method: 'POST',
            body: formData
        }).then(resolve => resolve.json()).then(resolve => console.log(resolve)).catch(reject => console.log(reject));
    });
});