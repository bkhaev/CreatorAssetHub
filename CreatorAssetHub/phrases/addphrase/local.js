// Creator Asset Hub(c) 2023. All rights reserved.
//
// The Add Phrase modal JS-script file

import { testSleep, testSleepAsync } from '../../js/tests.js';
import { sys, tryLogin, sysHeaderToSticky, sysPostData } from '../../js/index.js';
import { libHmsToSeconds, libRemoveFromArray, libSecondsToHms } from '../../js/bf_library.js';
import { loadHeaderGrand } from '../../components/HeaderNavigator.js';
import { loadFooterMinor } from '../../components/Footer.js';
import { InputTextGrand, state as inputTextGrandState } from '../../components/InputTextGrand.js';
import { TagList, state as tagListState } from '../../components/TagList.js';



customElements.define('bf-header-grand', loadHeaderGrand, {extends: 'nav'});
customElements.define('bf-footer-minor', loadFooterMinor, {extends: 'footer'});
customElements.define('bf-input-text-grand', InputTextGrand);
customElements.define('bf-tag-list', TagList, {extends: 'ul'});
// customElements.define('bf-users-data-list', UsersDataList);



(() => {
    // настройка общих элементов страницы

    sysHeaderToSticky(true);
    
})();



let pageState = {
    phraseCommon: {
        phrase: '',
        synonyms: [],
        author: [],
        tone: ['medium'],
        emotion: 'нет',
        isCamera: true,
        isAnonym: false,
        accessMode: 'public', // public, shared, private
    },
    phraseSources: [],
    phraseFiles: [],
};

let phraseSourceTimeStartMask;
const timeStartMaskOptions = typeof IMask === "function" ? {
	mask: '`HH:MM:MM',
	lazy: false,
	overwrite: true,
	blocks: {
		HH: {
			mask: IMask.MaskedRange,
		  	from: 0,
		  	to: 23
		},
		MM: {
		  	mask: IMask.MaskedRange,
		  	from: 0,
		  	to: 59
		}
  	}
} : null;



///////////////// SECTION 1 --- PHRASE'S COMMON DATA FORM /////////////////



(() => {
    // phraseAppliedAuthors

    tagListState.tagList.phraseAppliedAuthors = {
        list: [],
    };
})();



(() => {
    // newPhraseForm

    newPhraseForm.addEventListener('submit', e => {
        if (phraseFormValidate()) {
            postNewPhrase();
            cleanPhraseForm();
            phraseFormValidate();
        } else {
            phraseSendBtn.disabled = true;
            newPhraseForm.dataset.validate = true;
            phraseSourcesBlock.dataset.validate = true;
        }
        
        e.preventDefault();
    });
})();



(() => {
    // phraseInput

    phraseInput.addEventListener('input', () => {
		phraseInput.value = phraseInput.value.replace(/\n/g,'');
        pageState.phraseCommon.phrase = phraseInput.value.replace(/^ +| +$|( ) +/g,"$1");
        phraseFormValidate();
        updatePhraseCounter();
	});
})();



(() => {
    // tagsInput & tagsList

    const getLastSynonym = () => {
        const synonyms = phraseTagsInputBox.getElementsByClassName('__synonym');
        return synonyms.item(synonyms.length - 1);
    }

    phraseTagsInputBox.addEventListener('mousedown', e => {
        if ((!e.target.classList.contains('synonym_btn-clean') && e.target !== tagsInput) || (e.target.classList.contains('synonym_btn-clean') && tagsInput === document.activeElement)) {
            e.preventDefault();
            tagsInput.focus();
        }
    });

    tagsInput.addEventListener('keydown', e => {
        if (tagsInput.value[0] === '#' && [' ', ';', ':', '@', '"', '=', '-', '+', '*', '/', '%', '!', "?", '<', '>', '{', '}', '[', ']', '(', ')'].includes(e.key)) {
			e.preventDefault();
			return;
		} else if (e.keyCode === 13 || e.key === ',') { // Enter || ,
            if (tagsInput.value.length > 2 && tagsInput.value.length <= 80) {
                const synonym = tagsInput.value.replace(/^ +| +$|( ) +/g,"$1").toLowerCase();
                if (!pageState.phraseCommon.synonyms.includes(synonym)) {
                    addPhraseSynonym(synonym);
                    updateSynonymsCounter();
                }

                if (phraseTagsOpenTagsMenu.checked)
                    tagsInput.value = '#';
                else
                    tagsInput.value = '';
                tagsInput.size = 1;
            }
            
            e.preventDefault();
			return;
        } else if (tagsInput.value === '' && e.keyCode === 8) { // Backspace
            const lastSynonym = getLastSynonym();
            if (lastSynonym && lastSynonym.dataset.value) {
                let value = lastSynonym.dataset.value.trim();
                libRemoveFromArray(pageState.phraseCommon.synonyms, value);
                if (value[0] === '#') {
                    value = value.replace(/\s/g, '');
                    tagsInput.value = value;
                    tagsInput.classList.add('s-tag');
                    phraseTagsOpenTagsMenu.checked = true;
                } else {
                    tagsInput.value = value;
                    tagsInput.classList.remove('s-tag');
                    phraseTagsOpenTagsMenu.checked = false;
                }

                tagsInput.size = value.length || 1;
                lastSynonym.remove();
                updateSynonymsCounter();
                phraseFormValidate();
            }

            e.preventDefault();
			return;
        }
    });

    tagsInput.addEventListener('input', () => {
        tagsInput.size = tagsInput.value.length || 1;

        if (tagsInput.value[0] === '#' && tagsInput.value.indexOf(' ') === -1) {
            tagsInput.classList.add('s-tag');
            phraseTagsOpenTagsMenu.checked = true;
        } else {
            tagsInput.classList.remove('s-tag');
            phraseTagsOpenTagsMenu.checked = false;
        }
    });

    phraseTagsOpenTagsMenu.addEventListener('change', () => {
        if (phraseTagsOpenTagsMenu.checked) {
            tagsInput.classList.add('s-tag');
            tagsInput.value = '#';
        } else {
            tagsInput.classList.remove('s-tag');
            tagsInput.value = '';
        }

        tagsInput.focus();
    });
})();



(() => {
    // authorInput & authorInputBtn

    const addPhraseAuthor = () => {
        if (authorInput.value.trim()) {
            const author = authorInput.value.replace(/^ +| +$|( ) +/g,"$1");
            if (!pageState.phraseCommon.author.includes(author)) {
                pageState.phraseCommon.author.push(author);
                updatePhraseAppliedAuthors();
                phraseFormValidate();
            }
        }
        authorInput.value = '';
    }

    authorInput.addEventListener('keydown', e => {
        if ([';', ':', '.', '@', '"', '=', '-', '+', '*', '/', '%', '!', "?", '<', '>', '{', '}', '[', ']', '(', ')', '\\', '|'].includes(e.key)) {
			e.preventDefault();
			return;
		} else if (e.keyCode === 13 || e.key === ',') { // Enter || ,
            addPhraseAuthor();
            e.preventDefault();
			return;
        }
    });

    authorInputBtn.addEventListener('click', () => addPhraseAuthor());
})();



(() => {
    // phraseCamera & phraseUnnamedSwitch

    phrase_camera.addEventListener('change', () => pageState.phraseCommon.isCamera = phrase_camera.checked);
    
})();



(() => {
    // phraseEmotionMenuModal

    const phraseEmotionMenuModal = document.querySelector('#phraseEmotionMenuBtn + ._menu');

    phraseEmotionMenuBtn.addEventListener('click', () => {
        phraseEmotionMenu.classList.add('s-expanded');
        phraseEmotionMenu.preventHideEvent = true;
    });

    document.addEventListener('click', e => {
		const isPhraseEmotionMenuModal = e.target == phraseEmotionMenuModal || phraseEmotionMenuModal.contains(e.target);
		
		if (!isPhraseEmotionMenuModal && phraseEmotionMenu.preventHideEvent)
            phraseEmotionMenu.preventHideEvent = false;
        else if (!isPhraseEmotionMenuModal)
            phraseEmotionMenu.classList.remove('s-expanded');
	});

    Array.prototype.forEach.call(phraseEmotionMenuModal.getElementsByTagName('input'), element => {
        if (element.name === 'emotion') {
            element.addEventListener('change', () => {
                phraseEmotionMenu.classList.remove('s-expanded');
                pageState.phraseCommon.emotion = element.value;
                
                const selectedValue = phraseEmotionMenu.querySelector('[for="phraseEmotionMenuBtn"] span');
                if (selectedValue)
                    selectedValue.textContent = element.value;
            });
        }
    });
})();



(() => {
    // phraseToneList

    Array.prototype.forEach.call(phraseToneList.getElementsByTagName('input'), element => {
        if (element.name === 'tone') {
            element.addEventListener('change', () => {
                if (element.checked) {
                    pageState.phraseCommon.tone.push(element.value);
                    if (pageState.phraseCommon.tone.length > 1)
                        Array.prototype.forEach.call(phraseToneList.querySelectorAll('input:not(:checked)'), element => { element.disabled = true; });
                }
                else {
                    libRemoveFromArray(pageState.phraseCommon.tone, element.value);
                    if (pageState.phraseCommon.tone.length < 2)
                        Array.prototype.forEach.call(phraseToneList.querySelectorAll('input:not(:checked)'), element => { element.disabled = false; });
                }
                phraseFormValidate();
            });
        }
    });
})();



///////////////// SECTION 2 --- PHRASE'S SOURCES /////////////////



(() => {
    // phraseSourceTitleInput

    phraseSourceTitleInput.addEventListener('input', e => {
		phraseSourceTitleInput.value = phraseSourceTitleInput.value.replace(/\n/g,'');
        if (phraseSourceTitleInput.value.length < 3 || phraseSourceTitleInput.value.length > 100)
            phraseSourceTitleInputBox.classList.add('s-invalid');
        else
            phraseSourceTitleInputBox.classList.remove('s-invalid');
        
        updatePhraseSourceTitleCounter();
	});
})();



(() => {
    // phraseSourceAddBtn & phraseSourceUrlInput

    phraseSourceAddBtn.addEventListener('click', () => {
        if (phraseSourceUrlInput.validity.valid && phraseSourceUrlInput.value) {
            const time = libHmsToSeconds(phraseSourceTimeStartInput.value);
            pageState.phraseSources.push({
                url: encodeURIComponent(phraseSourceUrlInput.value),
                title: phraseSourceTitleInput.value,
                time: time === false ? null : time,
            });

            phraseSourceUrlInput.value = '';

            phraseSourceTitleInput.value = '';

            if (timeStartMaskOptions !== null) {
                phraseSourceTimeStartInput.value = '00:__:__';
                phraseSourceTimeStartMask.updateValue();   // update model from view
                phraseSourceTimeStartMask.updateControl(); // update view from new model
            } else
                phraseSourceTimeStartInput.value = '';

            updatePhraseSources();
            phraseFormValidate();

            phraseSourceAddBtn.disabled = true;
        }
	});

    phraseSourceUrlInput.addEventListener('input', () => {
        // phraseSourceUrlInput.value = phraseSourceUrlInput.value.trim();
        if (phraseSourceUrlInput.validity.valid && phraseSourceUrlInput.value)
            phraseSourceAddBtn.disabled = false;
        else
            phraseSourceAddBtn.disabled = true;
	});
})();



(() => {
    // начальные действия

    tryLogin().then(result => {
        if (result) {
            globalnav.setAttribute('signed-in', '');
            
            const phraseUnnamedSwitch = document.createElement('div');
            phraseUnnamedSwitch.className = 'switch-input-minor';

            const phraseUnnamedInput = document.createElement('input');
            phraseUnnamedInput.type = 'checkbox';
            phraseUnnamedInput.id = 'phrase_unnamed';
            // phraseUnnamedInput.name = 'unnamed';
            // phraseUnnamedInput.value = '1';

            phraseUnnamedInput.addEventListener('change', () => pageState.phraseCommon.isAnonym = phraseUnnamedInput.checked);

            const phraseUnnamedLabel = document.createElement('label');
            phraseUnnamedLabel.htmlFor = 'phrase_unnamed';

            const phraseUnnamedLabelContent = document.createElement('span');
            phraseUnnamedLabelContent.textContent = 'Анонимно';

            phraseUnnamedLabel.appendChild(phraseUnnamedLabelContent);
            phraseUnnamedSwitch.append(phraseUnnamedInput, phraseUnnamedLabel);

            const phrasePreReadyControls = document.querySelector('#phrasePreReadyControls > div');
            phrasePreReadyControls.prepend(phraseUnnamedSwitch);
        }
    });

    if (timeStartMaskOptions !== null) { 
        phraseSourceTimeStartInput.value = "00:__:__";
	    phraseSourceTimeStartMask = IMask(phraseSourceTimeStartInput, timeStartMaskOptions);
    } else
        console.warn('Некоторый функционал не работает');
    
    phraseAddResultModalCloseBtn.addEventListener('click', () => {
        modalArea.classList.add('s-hidden');
        const offset = document.documentElement.style.getPropertyValue('--TransitionDurationGrand');

        setTimeout(() => phraseAddResultModal.classList.add('s-hidden'), offset ? offset : 0);
	});
})();



// console.log('Local file was executed');



///////////////// FUNCTIONS /////////////////



// Отправляет данные на создание новой фразы
function postNewPhrase() {
    const data = {
        snippet: {
            phrase: pageState.phraseCommon.phrase,
            authors: pageState.phraseCommon.author,
            emotion: pageState.phraseCommon.emotion,
            tone: pageState.phraseCommon.tone,
            synonyms: pageState.phraseCommon.synonyms,
            isCameraUsed: pageState.phraseCommon.isCamera,
            sources: pageState.phraseSources,
            files: pageState.phraseFiles,
        },
        contentDetails: {
            privacyStatus: pageState.phraseCommon.accessMode,
        },
        userDetails: {
            isUserAnonym: pageState.phraseCommon.isAnonym,
        },
    };

    sysPostData('phrases/add', data, 0).then((data) => {
        console.log(data)
        if (data.kind === 'phrases#phrase') {
            phraseAddResultModal.classList.remove('s-hidden');
            modalArea.classList.remove('s-hidden');

            const keyCodeDisplay = document.createElement('span');
            keyCodeDisplay.textContent = data.keyCode;
            // phraseNewDetailsLink.href = 'http://localhost/phrases/details?phrase=' + data.id;
            phraseNewDetailsLink.href = 'http://localhost/phrases/search';

            phraseNewKeyCode.replaceChildren('Ключ редактирования — ', keyCodeDisplay);

            if (!newPhraseForm.dataset.validate)
                phraseSendBtn.disabled = false;
        } else if (data.error !== undefined)
            alert("Ошибка. " + data.error.errors[0].message);
        else
            console.warn('Не был передан нужный тип объекта.');
    }).catch(reason => console.log('Ошибка Promise: ' + reason));
    
    console.log('Данные фразы отправлены');
    // console.log(JSON.stringify(pageState, null, 4));
}



// Очистить форму добавления фразы
function cleanPhraseForm() {

    ////// SECTION 1 - PHRASE'S COMMON DATA FORM //////

    phraseSendBtn.disabled = true;

    phraseInput.value = '';
    pageState.phraseCommon.phrase = '';
    updatePhraseCounter();

    tagsInput.value = '';
    pageState.phraseCommon.synonyms = [];
    updateSynonymsCounter();
    Array.prototype.forEach.call(phraseTagsInputBox.querySelectorAll('._text-area .__synonym'), synonym => synonym.remove());

    authorInput.value = '';
    pageState.phraseCommon.author = [];
    // phraseFeaturedAuthors...
    updatePhraseAppliedAuthors();

    phrase_camera.checked = false;
    pageState.phraseCommon.isCamera = false;

    phraseEmotion0.checked = true;
    const selectedEmotion = phraseEmotionMenu.querySelector('[for="phraseEmotionMenuBtn"] span');
    if (selectedEmotion)
        selectedEmotion.textContent = phraseEmotion0.value;
    pageState.phraseCommon.emotion = 'нет';

    Array.prototype.forEach.call(phraseToneList.getElementsByTagName('input'), tone => {
        if (tone.name === 'tone') {
            tone.checked = false;
            tone.disabled = false;
        }
    });
    pageState.phraseCommon.tone = [];

    if (sys.account) {
        phrase_unnamed.checked = false; // ??? ПРОВЕРИТЬ РАБОТАЕТ ЛИ
        pageState.phraseCommon.isAnonym = false;

        // phrase_access...
        pageState.phraseCommon.accessMode = 'public';
    }

    ////// SECTION 2 - PHRASE'S SOURCES //////

    phraseSourceAddBtn.disabled = true;

    phraseSourceUrlInput.value = '';

    phraseSourceTitleInput.value = '';
    updatePhraseSourceTitleCounter();

    if (timeStartMaskOptions !== null) {
        phraseSourceTimeStartInput.value = '00:__:__';
        phraseSourceTimeStartMask.updateValue();   // update model from view
        phraseSourceTimeStartMask.updateControl(); // update view from new model
    } else
        phraseSourceTimeStartInput.value = '';

    pageState.phraseSources = [];
    updatePhraseSources();
}



// Обновляет список применённых авторов
function updatePhraseAppliedAuthors() {
    tagListState.tagList.phraseAppliedAuthors.list = [];

    pageState.phraseCommon.author.forEach(author => {
        tagListState.tagList.phraseAppliedAuthors.list.push({
            type: 'author',
            text: author,
            bodyAction: {
                action: () => {
                    libRemoveFromArray(pageState.phraseCommon.author, author);
                    authorInput.value = author;
                    authorInput.focus();
                    phraseFormValidate();
                },
                isRemove: true,
            },
            closeAction: () => {
                libRemoveFromArray(pageState.phraseCommon.author, author);
                phraseFormValidate();
            },
        });
    });

    phraseAppliedAuthors.setAttribute('updated', Date.now());
}



// Добавляет синоним/тег к фразе
function addPhraseSynonym(synonym) {
    pageState.phraseCommon.synonyms.push(synonym);
                    
    const synonymBox = document.createElement('div');
    synonymBox.dataset.value = synonym;

    if (synonym[0] === '#' && synonym.indexOf(' ') === -1)
        synonymBox.className = '__synonym s-tag';
    else
        synonymBox.className = '__synonym';
    
    const content = document.createElement('div');
    content.className = 'synonym_content';

    content.addEventListener('click', () => {
        synonymBox.remove();
        if (synonym[0] === '#') {
            tagsInput.value = synonym.replace(/\s/g, '');
            tagsInput.classList.add('s-tag');
            phraseTagsOpenTagsMenu.checked = true;
        } else {
            tagsInput.value = synonym.trim();
            tagsInput.classList.remove('s-tag');
            phraseTagsOpenTagsMenu.checked = false;
        }
        tagsInput.size = synonym.length || 1;
        tagsInput.focus();
        libRemoveFromArray(pageState.phraseCommon.synonyms, synonym);
        updateSynonymsCounter();
        phraseFormValidate();
    });
    
    const contentText = document.createElement('span');
    contentText.textContent = synonym;

    content.appendChild(contentText);

    
    const cleanBtn = document.createElement('div');
    cleanBtn.role = 'button';
    cleanBtn.className = 'synonym_btn-clean';
    cleanBtn.title = 'Очистить';

    cleanBtn.addEventListener('click', () => {
        synonymBox.remove();
        libRemoveFromArray(pageState.phraseCommon.synonyms, synonym);
        updateSynonymsCounter();
        phraseFormValidate();
    });

    synonymBox.append(content, cleanBtn);

    tagsInput.before(synonymBox);

    phraseFormValidate();
}



// Обновляет список ссылок фразы
function updatePhraseSources() {
    const created = [];

    pageState.phraseSources.forEach(source => {
        const li = document.createElement('li');

        const url = document.createElement('div');
        url.className = '_url';

        const urlText = document.createElement('span');
        urlText.title = urlText.textContent = decodeURIComponent(source.url);

        url.appendChild(urlText);


        const title = document.createElement('div');
        title.className = '_title';

        const titleText = document.createElement('span');

        if (source.title)
            titleText.title = titleText.textContent = source.title;
        else {
            titleText.textContent = 'без названия';
            title.classList.add('s-undefined');
        }

        title.appendChild(titleText);


        const time = document.createElement('div');
        time.className = '_time';

        const timeText = document.createElement('span');
        
        if (source.time !== null)
            timeText.title = timeText.textContent = libSecondsToHms(source.time);
        else {
            timeText.textContent = 'без времени';
            time.classList.add('s-undefined');
        }

        time.appendChild(timeText);


        const deleteSource = document.createElement('div');

        const deleteSourceBtn = document.createElement('button');
        deleteSourceBtn.type = 'button';
        deleteSourceBtn.name = 'source-delete';
        deleteSourceBtn.textContent = 'Удалить';

        deleteSourceBtn.addEventListener('click', () => {
            li.remove();
            libRemoveFromArray(pageState.phraseSources, source);

            if (!pageState.phraseSources.length) {
                phraseSourcesList.classList.add('s-hidden');
                phraseSourcesList.textContent = '';
            }

            phraseFormValidate();
        });

        deleteSource.appendChild(deleteSourceBtn);


        li.append(url, title, time, deleteSource);
        created.push(li);
    });

    if (created.length) {
        const ul = document.createElement('ul');
        ul.append(...created);

        phraseSourcesList.replaceChildren(ul);
        phraseSourcesList.classList.remove('s-hidden');
    } else {
        phraseSourcesList.classList.add('s-hidden');
        phraseSourcesList.textContent = '';
    }
}



// Валидация формы добавления фразы (разрешает/запрещает отправку данных)
function phraseFormValidate() {
    let isOk = true;


    if (pageState.phraseCommon.phrase.length < 3 || pageState.phraseCommon.phrase.length > 200) {
        isOk = false;
        phraseInputBox.classList.add('s-invalid');
    } else
        phraseInputBox.classList.remove('s-invalid');


    const synonymsLength = pageState.phraseCommon.synonyms.join('').length;
    if (synonymsLength > 300) {
        isOk = false;
        phraseTagsInputBox.classList.add('s-invalid');
    } else
        phraseTagsInputBox.classList.remove('s-invalid');


    if (!pageState.phraseCommon.author.length) {
        isOk = false;
        phraseAuthorsInputBlock.classList.add('s-invalid');
    } else
        phraseAuthorsInputBlock.classList.remove('s-invalid');


    if (pageState.phraseCommon.tone.length < 1 || pageState.phraseCommon.tone.length > 2) {
        isOk = false;
        phraseToneList.classList.add('s-invalid');
    } else
        phraseToneList.classList.remove('s-invalid');


    if (!pageState.phraseSources.length && !pageState.phraseFiles.length) {
        isOk = false;
        phraseSourcesBlock.classList.add('s-invalid');
        // phraseFilesBlock.classList.add('s-invalid');
    } else {
        phraseSourcesBlock.classList.remove('s-invalid');
        // phraseFilesBlock.classList.remove('s-invalid');
    }


    if (newPhraseForm.dataset.validate) {
        if (isOk)
            phraseSendBtn.disabled = false;
        else
            phraseSendBtn.disabled = true;
    }

    return isOk;
}

// Обновляет счётчик символов фразы
function updatePhraseCounter() {
    const counter = phraseInputBox.querySelector('._symbols-count span');
    if (counter)
        counter.textContent = phraseInput.value.length;

    if (phraseInput.value.length > 190)
        phraseInputBox.classList.add('s-too-long');
    else
        phraseInputBox.classList.remove('s-too-long');
}



// Обновляет счётчик символов синонимов/тегов фразы
function updateSynonymsCounter() {
    const synonymsLength = pageState.phraseCommon.synonyms.join('').length;
    const counter = phraseTagsInputBox.querySelector('._symbols-count span');
    if (counter)
        counter.textContent = synonymsLength;

    if (synonymsLength > 270)
        phraseTagsInputBox.classList.add('s-too-long');
    else
        phraseTagsInputBox.classList.remove('s-too-long');
}



// Обновляет счётчик символов названия ссылки фразы
function updatePhraseSourceTitleCounter() {
    const counter = phraseSourceTitleInputBox.querySelector('._symbols-count span');
    if (counter)
        counter.textContent = phraseSourceTitleInput.value.length;

    if (phraseSourceTitleInput.value.length > 90)
        phraseSourceTitleInputBox.classList.add('s-too-long');
    else
        phraseSourceTitleInputBox.classList.remove('s-too-long');
}