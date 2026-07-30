document.addEventListener('DOMContentLoaded', () => {
    const modalWindow = document.getElementById('meme_modal_window');

    document.body.addEventListener('click', async (event) => {
        const clickTarget = event.target.closest('.investment_card') || event.target.closest('.post_card');

        if (clickTarget) {
            const memeIdentifier = clickTarget.getAttribute('data-meme-identifier');

            if (!memeIdentifier) {
                return;
            }
            if (!modalWindow) {
                return;
            }

            const fetchResponse = await fetch(`/api_get_meme_details.php?id=${memeIdentifier}`);
            const parsedJsonData = await fetchResponse.json();

            if (parsedJsonData.success) {
                const meme = parsedJsonData.data;

                document.getElementById('modal_avatar').src = meme.author_avatar;
                document.getElementById('modal_author_name').textContent = meme.author_name;
                document.getElementById('modal_title').textContent = meme.title;
                document.getElementById('modal_capitalization').textContent = 'cap ' + meme.investments;
                document.getElementById('modal_description').textContent = meme.description;
                document.getElementById('modal_date').textContent = meme.date;

                const modalMainImage = document.getElementById('modal_main_image');

                if (modalMainImage) {
                    if (meme.images && meme.images.length > 0) {
                        modalMainImage.src = meme.images[0].trim();
                    }
                }

                modalWindow.style.display = 'flex';
            }
        }

        const closeButton = event.target.closest('#modal_close_button');
        const isModalBackgroundClick = event.target.id === 'meme_modal_window';

        if (closeButton || isModalBackgroundClick) {
            if (modalWindow) {
                modalWindow.style.display = 'none';
            }
        }
    });
});