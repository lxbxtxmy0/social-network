document.addEventListener('DOMContentLoaded', () => {

    const postContainerElement = document.querySelector('.post_container');
    const memeModalElement = document.getElementById('meme_modal_window');
    const investModalElement = document.getElementById('invest_modal_window');

    document.body.addEventListener('click', async (eventObject) => {
        const postImageBoxElement = eventObject.target.closest('.post_image_box');

        if (postImageBoxElement) {
            const currentPostElement = document.getElementById('current_post');
            let memeIdentifierValue = null;

            if (currentPostElement) {
                memeIdentifierValue = currentPostElement.getAttribute('data-meme-identifier');
            }

            if (!memeIdentifierValue) {
                return;
            }
            if (!memeModalElement) {
                return;
            }

            const fetchResponse = await fetch(`api_get_meme_details.php?id=${memeIdentifierValue}`);
            const parsedJsonData = await fetchResponse.json();

            if (parsedJsonData.success) {
                const memeData = parsedJsonData.data;
                document.getElementById('modal_avatar').src = memeData.author_avatar;
                document.getElementById('modal_author_name').textContent = memeData.author_name;
                document.getElementById('modal_title').textContent = memeData.title;
                document.getElementById('modal_capitalization').textContent = 'cap ' + memeData.investments;
                document.getElementById('modal_description').textContent = memeData.description;
                document.getElementById('modal_date').textContent = memeData.date;

                renderModalImagesSlider(memeData.images);
                memeModalElement.style.display = 'flex';
            }
        }

        const closeButtonElement = eventObject.target.closest('#modal_close_button');
        const modalBackgroundClick = eventObject.target.id === 'meme_modal_window';

        if (closeButtonElement || modalBackgroundClick) {
            if (memeModalElement) {
                memeModalElement.style.display = 'none';
            }
        }

        if (eventObject.target.id === 'invest_modal_window') {
            if (investModalElement) {
                investModalElement.style.display = 'none';
            }
        }
    });

    function renderModalImagesSlider(imagesArray) {
        const modalSliderContainerElement = document.getElementById('modal_slider_container');
        modalSliderContainerElement.innerHTML = '';

        if (!imagesArray || imagesArray.length === 0) {
            return;
        }

        const sliderTrackElement = document.createElement('div');
        sliderTrackElement.className = 'modal_slider_track';
        modalSliderContainerElement.appendChild(sliderTrackElement);

        imagesArray.forEach((imageSourceString) => {
            const slideElement = document.createElement('div');
            slideElement.className = 'modal_slide';

            const imageElement = document.createElement('img');
            imageElement.src = imageSourceString.trim();

            slideElement.appendChild(imageElement);
            sliderTrackElement.appendChild(slideElement);
        });

        if (imagesArray.length > 1) {
            const previousButtonElement = document.createElement('button');
            previousButtonElement.className = 'modal_arrow modal_previous';
            previousButtonElement.textContent = '<';
            modalSliderContainerElement.appendChild(previousButtonElement);

            const nextButtonElement = document.createElement('button');
            nextButtonElement.className = 'modal_arrow modal_next';
            nextButtonElement.textContent = '>';
            modalSliderContainerElement.appendChild(nextButtonElement);

            let currentSlideIndexNumber = 0;

            function updateModalSlidePosition() {
                sliderTrackElement.style.marginLeft = `-${currentSlideIndexNumber * 100}%`;
            }

            previousButtonElement.addEventListener('click', () => {
                if (currentSlideIndexNumber === 0) {
                    currentSlideIndexNumber = imagesArray.length - 1;
                } else {
                    currentSlideIndexNumber = currentSlideIndexNumber - 1;
                }
                updateModalSlidePosition();
            });

            nextButtonElement.addEventListener('click', () => {
                if (currentSlideIndexNumber === imagesArray.length - 1) {
                    currentSlideIndexNumber = 0;
                } else {
                    currentSlideIndexNumber = currentSlideIndexNumber + 1;
                }
                updateModalSlidePosition();
            });
        }
    }

    if (postContainerElement) {
        const passButtonElement = document.querySelector('.button_pass');
        const investButtonElement = document.querySelector('.button_invest');
        const investInputElement = document.getElementById('invest_amount_input');
        const confirmInvestButtonElement = document.getElementById('confirm_invest_button');

        async function fetchNextMeme(actionTypeString = 'pass', amountValue = null) {
            const currentPostElement = document.getElementById('current_post');
            const currentMemeIdValue = currentPostElement.getAttribute('data-meme-identifier');

            const formDataObject = new FormData();
            formDataObject.append('action', actionTypeString);

            if (currentMemeIdValue) {
                formDataObject.append('meme_id', currentMemeIdValue);
            }
            if (amountValue) {
                formDataObject.append('amount', amountValue);
            }

            const fetchResponse = await fetch('api_get_next.php', { method: 'POST', body: formDataObject });

            if (!fetchResponse.ok) {
                console.error('Ошибка HTTP:', fetchResponse.status);
                return;
            }

            const parsedJsonData = await fetchResponse.json();

            if (parsedJsonData.success) {
                updateUserInterfaceDOM(parsedJsonData.meme);
            } else if (parsedJsonData.message === 'no_more_memes') {
                if (currentPostElement) {
                    currentPostElement.innerHTML = '<h1 class="meme_title empty_message">Мемы закончились! Приходите позже.</h1>';
                }
                if (passButtonElement) {
                    passButtonElement.disabled = true;
                }
                if (investButtonElement) {
                    investButtonElement.disabled = true;
                }
            } else if (parsedJsonData.error) {
                alert(parsedJsonData.error);
            }
        }

        function updateUserInterfaceDOM(memeData) {
            const currentPostBlock = document.getElementById('current_post');
            currentPostBlock.setAttribute('data-meme-identifier', memeData.id);

            const avatarElement = currentPostBlock.querySelector('.author_avatar');
            if (avatarElement) {
                avatarElement.src = memeData.avatar_source;
            }

            const authorLinkElement = currentPostBlock.querySelector('.header_information a');
            if (authorLinkElement) {
                authorLinkElement.href = `../${memeData.login}`;
                authorLinkElement.textContent = `${memeData.name} ${memeData.surname}`;
            }

            const investmentsBadgeElementsArray = currentPostBlock.querySelectorAll('.investments_badge');
            investmentsBadgeElementsArray.forEach((badgeElement) => {
                badgeElement.textContent = `cap ${memeData.investments}`;
            });

            const titleElementsArray = currentPostBlock.querySelectorAll('.meme_title:not(.empty_message)');
            titleElementsArray.forEach((titleElement) => {
                titleElement.textContent = memeData.title;
            });

            const mainPhotoElement = currentPostBlock.querySelector('.main_photo');
            if (mainPhotoElement) {
                let imageUrlString = memeData.image;
                if (typeof imageUrlString === 'string' && imageUrlString.startsWith('[')) {
                    try {
                        const parsedArray = JSON.parse(imageUrlString);
                        if (parsedArray.length > 0) {
                            imageUrlString = parsedArray[0];
                        }
                    } catch (errorObject) {}
                }
                mainPhotoElement.src = imageUrlString;
            }
        }

        if (passButtonElement) {
            passButtonElement.addEventListener('click', () => {
                fetchNextMeme('pass');
            });
        }

        if (investButtonElement) {
            if (investModalElement) {
                investButtonElement.addEventListener('click', () => {
                    investInputElement.value = '';
                    investInputElement.style.borderColor = '#3CF385';
                    investModalElement.style.display = 'flex';
                });

                confirmInvestButtonElement.addEventListener('click', () => {
                    const amountValueString = investInputElement.value.trim();

                    if (!amountValueString || isNaN(amountValueString) || Number(amountValueString) <= 0) {
                        investInputElement.style.borderColor = '#F95C63';
                        return;
                    }

                    investModalElement.style.display = 'none';
                    fetchNextMeme('invest', amountValueString);
                });
            }
        }
    }
});