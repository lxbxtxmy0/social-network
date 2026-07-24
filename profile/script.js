document.addEventListener('DOMContentLoaded', () => {
    const modalWindowElement = document.getElementById('meme_modal_window');

    document.body.addEventListener('click', async (eventObject) => {
        const clickTargetElement = eventObject.target.closest('.investment_card') || eventObject.target.closest('.post_card');

        if (clickTargetElement) {
            const memeIdentifierValue = clickTargetElement.getAttribute('data-meme-identifier');

            if (!memeIdentifierValue) {
                return;
            }
            if (!modalWindowElement) {
                return;
            }

            const fetchResponse = await fetch(`/api_get_meme_details.php?id=${memeIdentifierValue}`);
            const parsedJsonData = await fetchResponse.json();

            if (parsedJsonData.success) {
                const memeData = parsedJsonData.data;

                document.getElementById('modal_avatar').src = memeData.author_avatar;
                document.getElementById('modal_author_name').textContent = memeData.author_name;
                document.getElementById('modal_title').textContent = memeData.title;
                document.getElementById('modal_capitalization').textContent = 'cap ' + memeData.investments;
                document.getElementById('modal_description').textContent = memeData.description;
                document.getElementById('modal_date').textContent = memeData.date;

                const modalMainImageElement = document.getElementById('modal_main_image');
                if (modalMainImageElement && memeData.images && memeData.images.length > 0) {
                    modalMainImageElement.src = memeData.images[0].trim();
                }

                modalWindowElement.style.display = 'flex';
            }
        }

        const closeButtonElement = eventObject.target.closest('#modal_close_button');
        const isModalBackgroundClick = eventObject.target.id === 'meme_modal_window';

        if (closeButtonElement || isModalBackgroundClick) {
            if (modalWindowElement) {
                modalWindowElement.style.display = 'none';
            }
        }
    });
});