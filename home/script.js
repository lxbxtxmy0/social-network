document.addEventListener('DOMContentLoaded', () => {

    const postContainer = document.querySelector('.post_container');
    const memeModal = document.getElementById('meme_modal_window');
    const investModal = document.getElementById('invest_modal_window');

    document.body.addEventListener('click', async (event) => {
        const postImageBox = event.target.closest('.post_image_box');

        if (postImageBox) {
            const currentPost = document.getElementById('current_post');
            let memeIdentifier = null;

            if (currentPost) {
                memeIdentifier = currentPost.getAttribute('data-meme-identifier');
            }

            if (!memeIdentifier) {
                return;
            }
            if (!memeModal) {
                return;
            }

            const fetchResponse = await fetch(`/api_get_meme_details.php?id=${memeIdentifier}`);

            if (!fetchResponse.ok) {
                console.error('API файл не найден. Убедитесь, что api_get_meme_details.php лежит в корне проекта.');
                return;
            }

            const parsedJsonData = await fetchResponse.json();

            if (parsedJsonData.success) {
                const meme = parsedJsonData.data;
                document.getElementById('modal_avatar').src = meme.author_avatar;
                document.getElementById('modal_author_name').textContent = meme.author_name;
                document.getElementById('modal_title').textContent = meme.title;
                document.getElementById('modal_capitalization').textContent = 'cap ' + meme.investments;
                document.getElementById('modal_description').textContent = meme.description;
                document.getElementById('modal_date').textContent = meme.date;

                renderModalSlider(meme.images);
                memeModal.style.display = 'flex';
            }
        }

        const closeButton = event.target.closest('#modal_close_button');
        const modalBackgroundClick = event.target.id === 'meme_modal_window';

        if (closeButton || modalBackgroundClick) {
            if (memeModal) {
                memeModal.style.display = 'none';
            }
        }

        if (event.target.id === 'invest_modal_window') {
            if (investModal) {
                investModal.style.display = 'none';
            }
        }
    });

    function renderModalSlider(images) {
        const modalSliderContainer = document.getElementById('modal_slider_container');
        modalSliderContainer.innerHTML = '';

        if (!images || images.length === 0) {
            return;
        }

        const sliderTrack = document.createElement('div');
        sliderTrack.className = 'modal_slider_track';
        modalSliderContainer.appendChild(sliderTrack);

        images.forEach((imageSource) => {
            const slide = document.createElement('div');
            slide.className = 'modal_slide';

            const image = document.createElement('img');
            image.src = imageSource.trim();

            slide.appendChild(image);
            sliderTrack.appendChild(slide);
        });

        if (images.length > 1) {
            const previousButton = document.createElement('button');
            previousButton.className = 'modal_arrow modal_previous';
            previousButton.textContent = '<';
            modalSliderContainer.appendChild(previousButton);

            const nextButton = document.createElement('button');
            nextButton.className = 'modal_arrow modal_next';
            nextButton.textContent = '>';
            modalSliderContainer.appendChild(nextButton);

            let currentSlideIndex = 0;

            function updateModalSlidePosition() {
                sliderTrack.style.marginLeft = `-${currentSlideIndex * 100}%`;
            }

            previousButton.addEventListener('click', () => {
                if (currentSlideIndex === 0) {
                    currentSlideIndex = images.length - 1;
                } else {
                    currentSlideIndex = currentSlideIndex - 1;
                }
                updateModalSlidePosition();
            });

            nextButton.addEventListener('click', () => {
                if (currentSlideIndex === images.length - 1) {
                    currentSlideIndex = 0;
                } else {
                    currentSlideIndex = currentSlideIndex + 1;
                }
                updateModalSlidePosition();
            });
        }
    }

    if (postContainer) {
        const passButton = document.querySelector('.button_pass');
        const investButton = document.querySelector('.button_invest');
        const investInput = document.getElementById('invest_amount_input');
        const confirmInvestButton = document.getElementById('confirm_invest_button');

        async function fetchNextMeme(actionType = 'pass', amount = null) {
            const currentPost = document.getElementById('current_post');
            const currentMemeId = currentPost.getAttribute('data-meme-identifier');

            const formData = new FormData();
            formData.append('action', actionType);

            if (currentMemeId) {
                formData.append('meme_id', currentMemeId);
            }
            if (amount) {
                formData.append('amount', amount);
            }

            const fetchResponse = await fetch('/api_get_next.php', { method: 'POST', body: formData });

            if (!fetchResponse.ok) {
                console.error('API файл не найден. Убедитесь, что api_get_next.php лежит в корне проекта.');
                return;
            }

            const parsedJsonData = await fetchResponse.json();

            if (parsedJsonData.new_balance !== undefined) {
                const balanceValues = document.querySelectorAll('.balance_value');
                balanceValues.forEach((balance) => {
                    balance.textContent = parsedJsonData.new_balance;
                });
            }

            if (parsedJsonData.success) {
                updateUserInterface(parsedJsonData.meme);
            } else if (parsedJsonData.message === 'no_more_memes') {
                if (currentPost) {
                    currentPost.innerHTML = '<h1 class="meme_title empty_message">Мемы закончились! Приходите позже.</h1>';
                }
                if (passButton) {
                    passButton.disabled = true;
                }
                if (investButton) {
                    investButton.disabled = true;
                }
            } else if (parsedJsonData.error) {
                alert(parsedJsonData.error);
            }
        }

        function updateUserInterface(meme) {
            const currentPost = document.getElementById('current_post');
            currentPost.setAttribute('data-meme-identifier', meme.id);

            const avatar = currentPost.querySelector('.author_avatar');
            if (avatar) {
                avatar.src = meme.avatar_source;
            }

            const authorLink = currentPost.querySelector('.header_information a');
            if (authorLink) {
                authorLink.href = `/${meme.login}`;
                authorLink.textContent = `${meme.name} ${meme.surname}`;
            }

            const investmentsBadges = currentPost.querySelectorAll('.investments_badge');
            investmentsBadges.forEach((badge) => {
                badge.textContent = `cap ${meme.investments}`;
            });

            const titles = currentPost.querySelectorAll('.meme_title:not(.empty_message)');
            titles.forEach((title) => {
                title.textContent = meme.title;
            });

            const mainPhoto = currentPost.querySelector('.main_photo');
            if (mainPhoto) {
                mainPhoto.src = meme.image;
            }
        }

        if (passButton) {
            passButton.addEventListener('click', () => {
                fetchNextMeme('pass');
            });
        }

        if (investButton) {
            if (investModal) {
                investButton.addEventListener('click', () => {
                    investInput.value = '';
                    investInput.style.borderColor = '#3CF385';
                    investModal.style.display = 'flex';
                });

                confirmInvestButton.addEventListener('click', () => {
                    const amountValue = investInput.value.trim();

                    if (!amountValue || isNaN(amountValue) || Number(amountValue) <= 0) {
                        investInput.style.borderColor = '#F95C63';
                        return;
                    }

                    investModal.style.display = 'none';
                    fetchNextMeme('invest', amountValue);
                });
            }
        }
    }
});