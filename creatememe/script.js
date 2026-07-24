document.addEventListener('DOMContentLoaded', () => {
    const memeForm = document.querySelector('#meme_form');

    if (!memeForm) {
        return;
    }

    const photoInput = document.querySelector('#file_input');
    const addPhotoButton = document.querySelector('.add_button');
    const uploadBoxElement = document.querySelector('.upload_box');
    const uploadLabelElement = document.querySelector('.upload_label');
    const titleInputElement = document.querySelector('#title');
    const descriptionInputElement = document.querySelector('#description');
    const coinsInputElement = document.querySelector('#coins');
    const errorContainerElement = document.querySelector('#error_container');

    const errorBoxElement = document.createElement('div');
    errorBoxElement.className = 'error_box';
    errorContainerElement.appendChild(errorBoxElement);

    const sliderElement = document.createElement('div');
    sliderElement.className = 'slider';
    sliderElement.style.display = 'none';
    uploadBoxElement.appendChild(sliderElement);

    const sliderTrackElement = document.createElement('div');
    sliderTrackElement.className = 'slider_track';
    sliderElement.appendChild(sliderTrackElement);

    const previousButton = document.createElement('button');
    previousButton.type = 'button';
    previousButton.className = 'slider_arrow slider_previous';
    previousButton.textContent = '<';
    sliderElement.appendChild(previousButton);

    const nextButton = document.createElement('button');
    nextButton.type = 'button';
    nextButton.className = 'slider_arrow slider_next';
    nextButton.textContent = '>';
    sliderElement.appendChild(nextButton);

    let filesArray = [];
    let currentSlideIndex = 0;

    function showErrorMessage(messageString) {
        errorBoxElement.textContent = messageString;
        errorBoxElement.style.display = 'block';
    }

    function clearErrorMessage() {
        errorBoxElement.textContent = '';
        errorBoxElement.style.display = 'none';
    }

    function renderImageSlider() {
        sliderTrackElement.innerHTML = '';

        if (filesArray.length === 0) {
            sliderElement.style.display = 'none';
            uploadLabelElement.style.display = 'flex';
            return;
        }

        uploadLabelElement.style.display = 'none';
        sliderElement.style.display = 'block';

        if (filesArray.length > 1) {
            previousButton.style.display = 'block';
            nextButton.style.display = 'block';
        } else {
            previousButton.style.display = 'none';
            nextButton.style.display = 'none';
        }

        filesArray.forEach((fileObject, indexNumber) => {
            const slideElement = document.createElement('div');
            slideElement.className = 'slide';

            const imageElement = document.createElement('img');
            imageElement.src = URL.createObjectURL(fileObject);
            slideElement.appendChild(imageElement);

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'slide_remove';
            removeButton.textContent = '×';

            removeButton.addEventListener('click', () => {
                filesArray.splice(indexNumber, 1);

                if (currentSlideIndex >= filesArray.length) {
                    currentSlideIndex = Math.max(0, filesArray.length - 1);
                }

                renderImageSlider();
            });

            slideElement.appendChild(removeButton);
            sliderTrackElement.appendChild(slideElement);
        });

        updateSliderPosition();
    }

    function updateSliderPosition() {
        sliderTrackElement.style.marginLeft = `-${currentSlideIndex * 100}%`;
    }

    previousButton.addEventListener('click', () => {
        if (filesArray.length <= 1) {
            return;
        }

        if (currentSlideIndex === 0) {
            currentSlideIndex = filesArray.length - 1;
        } else {
            currentSlideIndex = currentSlideIndex - 1;
        }

        updateSliderPosition();
    });

    nextButton.addEventListener('click', () => {
        if (filesArray.length <= 1) {
            return;
        }

        if (currentSlideIndex === filesArray.length - 1) {
            currentSlideIndex = 0;
        } else {
            currentSlideIndex = currentSlideIndex + 1;
        }

        updateSliderPosition();
    });

    addPhotoButton.addEventListener('click', () => {
        photoInput.click();
    });

    photoInput.addEventListener('change', () => {
        clearErrorMessage();
        const selectedFilesArray = Array.from(photoInput.files);
        const allowedFileTypes = ['image/jpeg', 'image/png'];

        for (const fileObject of selectedFilesArray) {
            if (!allowedFileTypes.includes(fileObject.type)) {
                showErrorMessage('Можно загружать только JPEG или PNG');
                continue;
            }
            filesArray.push(fileObject);
        }

        currentSlideIndex = filesArray.length - 1;
        renderImageSlider();
        photoInput.value = '';
    });

    function validateFormFields() {
        if (filesArray.length === 0) {
            showErrorMessage('Добавьте хотя бы одно фото');
            return false;
        }

        if (!titleInputElement.value.trim()) {
            showErrorMessage('Заполните заголовок');
            return false;
        }

        const coinsAmountValue = coinsInputElement.value.trim();

        if (!coinsAmountValue) {
            showErrorMessage('Укажите количество коинов');
            return false;
        }

        if (isNaN(coinsAmountValue) || Number(coinsAmountValue) <= 0) {
            showErrorMessage('Количество коинов должно быть положительным числом');
            return false;
        }

        if (Number(coinsAmountValue) < 5) {
            showErrorMessage('Минимальная цена создания мема - 5 коинов');
            return false;
        }

        return true;
    }

    memeForm.addEventListener('submit', (eventObject) => {
        eventObject.preventDefault();
        clearErrorMessage();

        if (!validateFormFields()) {
            return;
        }

        const submitButtonsArray = memeForm.querySelectorAll('button[type="submit"]');
        submitButtonsArray.forEach((buttonElement) => {
            buttonElement.disabled = true;
        });

        const formDataObject = new FormData();
        formDataObject.append('title', titleInputElement.value.trim());
        formDataObject.append('description', descriptionInputElement.value.trim());
        formDataObject.append('coins', coinsInputElement.value.trim());

        for (const fileObject of filesArray) {
            formDataObject.append('images[]', fileObject);
        }

        fetch('creatememe.php', {
            method: 'POST',
            body: formDataObject
        })
            .then((responseObject) => {
                return responseObject.json().then((parsedData) => {
                    return { status: responseObject.status, data: parsedData };
                });
            })
            .then(({ status, data }) => {
                if (status !== 200) {
                    showErrorMessage(data.error || 'Произошла ошибка, повторите позже');
                    return;
                }
                window.location.href = '../home';
            })
            .finally(() => {
                submitButtonsArray.forEach((buttonElement) => {
                    buttonElement.disabled = false;
                });
            });
    });
});