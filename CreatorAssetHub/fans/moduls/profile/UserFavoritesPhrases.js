// Creator Asset Hub(c) 2023. All rights reserved.
//
// The taglist component loader file

import { libSecondsToHms } from "../../../js/bf_library.js";
import { sysDeleteResourceFromPlaylist, sysGetData, sysPutData, sysSaveResourceInPlaylist } from "../../../js/index.js";



export class UserFavoritesPhrasesPanel extends HTMLElement {
    render() {
        const sectionTitle = document.createElement('h2');
        sectionTitle.textContent = 'Понравившиеся фразы';
        
        this.totalPhrasesCount = document.createElement('p');
        
        this.phrasesList = document.createElement('div');
        this.phrasesList.className = 'local-resources-list';

        this.setSkeletons();
        this.append(sectionTitle, this.totalPhrasesCount, this.phrasesList);

        console.log('Rendering UserFavoritesPhrasesPanel');
    }

	connectedCallback() {
        if (!this.rendered) {
            this.render();
            this.rendered = true;
        }
    }

    static get observedAttributes() {
        return ['updated'];
    }

    attributeChangedCallback(name, oldValue, newValue) {
        this.render();
        this.rendered = true;
    }



    setSkeletons(count = 6) {
        const skeletons = [];
        while (count) {
            const phrase = document.createElement('article');
            phrase.className = 'local-resource-box phrase spectrum';

            const previewVideo = document.createElement('div');
            previewVideo.className = '_preview-source skeleton';

            const previewData = document.createElement('div');
            previewData.className = '_preview-data';

            const titleWrapper = document.createElement('div');

            const title = document.createElement('h3');

            const titleSkeleton = document.createElement('span');
            titleSkeleton.className = 'skeleton s-rounded s-text';

            title.appendChild(titleSkeleton);
            titleWrapper.appendChild(title);

            const phraseSpecs = document.createElement('div');
            phraseSpecs.className = 'skeleton s-rounded s-text';
            
            previewData.append(titleWrapper, phraseSpecs);
            phrase.append(previewVideo, previewData);

            skeletons.push(phrase);
            count--;
        }

        this.phrasesList.replaceChildren(...skeletons);
    }



    async getPlaylistPhrases(playlistId) {
        const data = await sysGetData('phrases/search?part=snippet,contentDetails&playlistId=' + playlistId).catch(reason => {
            console.log('Ошибка Promise: ' + reason);
            return;
        });

        if (data.kind === 'phrases#phrasesListResponse') {
            this.totalPhrasesCount.classList.add('local-section-paragraph');
            if (data.pageInfo.total > 0) {
                this.totalPhrasesCount.textContent = 'Всего фраз: ' + data.pageInfo.total;
                this.setPhrases(data.items);
            } else {
                this.totalPhrasesCount.textContent = 'У вас ещё нет понравившихся фраз';
                this.phrasesList.remove();
            }
        }
        else
            console.warn('Не был передан нужный тип объекта.');
    }

    setPhrases(phrasesList) {
        let invalidKind = 0;
        const phrasesView = [];
        phrasesList.forEach(item => {
            if (item.kind === 'phrases#phrase') {
                let authors = '';
                item.snippet.authors.forEach(author => {
                    if (author.kind === 'phrases#author') {
                        authors += author.authorName;
                    } else
                        console.warn('Не был передан нужный тип объекта.');
                });

                const phrase = document.createElement('article');
                phrase.className = 'local-resource-box phrase spectrum';

                const headerToolBar = document.createElement('div');
                headerToolBar.className = '_top-right-toolbar';

                const previewVideo = document.createElement('div');
                if (item.snippet.previewSource.url !== undefined) {
                    previewVideo.className = '_preview-source skeleton';
    
                    const iframe = document.createElement('iframe');
                    iframe.width = '256';
                    iframe.height = '144';
                    iframe.src = item.snippet.previewSource.url;
                    iframe.setAttribute('frameborder', '0');
                    iframe.setAttribute('allowpart2screen', '');
            
                    previewVideo.appendChild(iframe);
                } else {
                    previewVideo.className = '_preview-source s-no-content';
                    previewVideo.title = 'Внешний источник';
    
                    const imgfallback = document.createElement('img');
                    imgfallback.src = 'http://localhost/images/logo/bflogo.png';
                    // imgfallback.srcset = 'images/logo/bflogo.png';
                    imgfallback.alt = 'Внешний источник';
                    imgfallback.width = '60';
                    imgfallback.height = '60';
        
                    previewVideo.appendChild(imgfallback);
                }

                const previewData = document.createElement('div');
                previewData.className = '_preview-data';

                const previewDataHeaderWrapper = document.createElement('div');

                const title = document.createElement('h3');
                
                const phraseLink = document.createElement('a');
                phraseLink.href = 'http://localhost/phrases/details?phrase=' + item.id;
                phraseLink.textContent = item.snippet.phrase;

                title.appendChild(phraseLink);

                const menuBtnBox = document.createElement('div');
                menuBtnBox.className = 'menu-btn-options spectrum';

                const menuBtn = document.createElement('button');
                menuBtn.type = 'button';
                menuBtn.className = '_btn';

                menuBtn.addEventListener('click', () => {
                    menuBtnBox.classList.add('s-expanded');
                    menuBtnBox.preventHideEvent = true;
                });
            
                document.addEventListener('click', e => {
                    const isPhraseOptionsMenuModal = e.target == menuBtnMenu || menuBtnMenu.contains(e.target);
                    
                    if (!isPhraseOptionsMenuModal && menuBtnBox.preventHideEvent)
                        menuBtnBox.preventHideEvent = false;
                    else if (!isPhraseOptionsMenuModal) {
                        menuBtnBox.classList.remove('s-expanded');
                    }
                });

                const menuBtnSemanticText = document.createElement('span');
                menuBtnSemanticText.className = 'semantic-hidden';
                menuBtnSemanticText.textContent = 'меню опций';

                const menuBtnIcon = document.createElement('div');
                menuBtnIcon.className = '__icon';

                menuBtn.append(menuBtnSemanticText, menuBtnIcon);

                const menuBtnMenu = document.createElement('div');
                menuBtnMenu.className = '_menu';

                const menuBtnOptionsList = document.createElement('ul');

                const savePhraseOption = document.createElement('li');

                const savePhraseOptionChbx = document.createElement('input');
                savePhraseOptionChbx.type = 'checkbox';
                savePhraseOptionChbx.id = 'save_phrase_' + item.id;
                savePhraseOptionChbx.checked = true;

                savePhraseOptionChbx.addEventListener('change', async () => {
                    menuBtnBox.classList.remove('s-expanded');

                    if (savePhraseOptionChbx.checked) {
                        const saveId = await sysSaveResourceInPlaylist(item.kind, item.id, 'Понравившиеся фразы');
                        if (saveId) {
                            item.contentDetails.savedInFavoritesId.id = saveId;
                            console.log('Фраза сохранена в избранных!');
                        }
                    } else {
                        const result = await sysDeleteResourceFromPlaylist(item.contentDetails.savedInFavoritesId.id);
                        if (result) {
                            phrase.remove();
                            console.log('Фраза удалена из избранных.');

                            if (!this.phrasesList.textContent) {
                                this.totalPhrasesCount.textContent = 'У вас ещё нет понравившихся фраз';
                                this.phrasesList.remove();
                            }
                        }
                    }
                });

                const savePhraseOptionLabel = document.createElement('label');
                savePhraseOptionLabel.className = 'li_content';
                savePhraseOptionLabel.htmlFor = 'save_phrase_' + item.id;

                const savePhraseOptionText = document.createElement('span');
                savePhraseOptionText.textContent = 'Удалить из закладок';

                const savePhraseOptionIcon = document.createElement('div');
                savePhraseOptionIcon.className = 'li_icon s-save';

                savePhraseOptionLabel.append(savePhraseOptionText, savePhraseOptionIcon);
                savePhraseOption.append(savePhraseOptionChbx, savePhraseOptionLabel);
                menuBtnOptionsList.appendChild(savePhraseOption);
                menuBtnMenu.appendChild(menuBtnOptionsList);
                menuBtnBox.append(menuBtn, menuBtnMenu);
                
                previewDataHeaderWrapper.append(title, menuBtnBox);

                const ul = document.createElement('ul');

                const authorsText = document.createElement('li');
                authorsText.textContent = authors;

                ul.appendChild(authorsText);

                if (item.snippet.previewSource.time !== undefined) {
                    const time = document.createElement('li');
                    time.textContent = libSecondsToHms(item.snippet.previewSource.time);
    
                    ul.appendChild(time);
                }

                previewData.append(previewDataHeaderWrapper, ul);



                const userNote = document.createElement('div');
                userNote.className = '_user-note';

                const userNotePreview = document.createElement('div');
                userNotePreview.className = '__preview-row';

                const userNotePreviewText = document.createElement('p');

                if (item.contentDetails.savedInFavoritesId.note)
                    userNotePreviewText.textContent = item.contentDetails.savedInFavoritesId.note;
                else {
                    userNote.classList.add('s-empty');
                    userNotePreviewText.textContent = 'Добавить примечание';
                }

                const userNotePreviewEditBtn = document.createElement('button');
                userNotePreviewEditBtn.type = 'button';
                userNotePreviewEditBtn.className = '_user-note_btn';

                const userNotePreviewEditBtnSemanticText = document.createElement('span');
                userNotePreviewEditBtnSemanticText.className = 'semantic-hidden';
                userNotePreviewEditBtnSemanticText.textContent = 'Изменить примечание';

                const userNotePreviewEditBtnIcon = document.createElement('div');
                userNotePreviewEditBtnIcon.className = '_user-note_btn_icon';

                userNotePreviewEditBtn.append(userNotePreviewEditBtnSemanticText, userNotePreviewEditBtnIcon);

                userNotePreviewEditBtn.addEventListener('click', () => {
                    userNote.classList.add('s-edit');
                    userNoteInput.focus();
                    userNote.preventCloseForm = true;
                });


                userNotePreview.append(userNotePreviewText, userNotePreviewEditBtn);



                const userNoteEditForm = document.createElement('form');
                userNoteEditForm.method = 'post';

                const userNoteInputBox = document.createElement('bf-input-text-grand');
                userNoteInputBox.className = '_user-note_edit-box';

                const userNoteInputWrapper = document.createElement('div');
                userNoteInputWrapper.className = '_input-text s-primary-input';

                const userNoteInput = document.createElement('input');
                userNoteInput.type = 'text';
                userNoteInput.autocomplete = 'off';
                userNoteInput.placeholder = 'Добавить примечание';
                userNoteInput.value = userNoteInput.fixedValue = item.contentDetails.savedInFavoritesId.note;

                const closeNoteEditForm = () => {
                    userNote.classList.remove('s-edit');

                    if (userNoteInput.value) {
                        userNotePreviewText.textContent = userNoteInput.value;
                        userNoteInput.fixedValue = userNoteInput.value;
                        userNote.classList.remove('s-empty');
                    } else {
                        userNotePreviewText.textContent = 'Добавить примечание';
                        userNote.classList.add('s-empty');
                    }
                };

                userNoteInput.addEventListener('change', () => {
                    closeNoteEditForm();

                    const data = {
                        id: item.contentDetails.savedInFavoritesId.id,
                        snippet: {
                            position: 0,
                            note: userNoteInput.value,
                        },
                    };
                    
                    sysPutData('playlistItems', data).then(result => {
                        if (result.isOk === true) {
                            console.log('Примечание изменено!');
                        }
                    });
                });

                // Закрыть форму изменения примечания через Enter
                userNoteEditForm.addEventListener('submit', e => {
                    closeNoteEditForm();
                    e.preventDefault();
                });

                document.addEventListener('click', e => {
                    if (userNote.preventCloseForm) {
                        userNote.preventCloseForm = false;
                        return;
                    }
                    
                    // Закрыть форму изменения примечания по клику на любое место вне формы
                    if (userNote.classList.contains('s-edit') && !(e.target == userNoteInputBox || userNoteInputBox.contains(e.target)))
                        closeNoteEditForm();
                });

                // Отмена изменений примечания
                document.addEventListener('keydown', e => {
                    if (e.key === 'Escape' && userNote.classList.contains('s-edit')) {
                        userNoteInput.value = userNoteInput.fixedValue ?? '';
                        closeNoteEditForm();
                    }
                });

                userNoteInputWrapper.appendChild(userNoteInput);
                userNoteInputBox.appendChild(userNoteInputWrapper);
                userNoteEditForm.appendChild(userNoteInputBox);

                userNote.append(userNotePreview, userNoteEditForm);
                

                phrase.append(headerToolBar, previewVideo, previewData, userNote);

                phrasesView.push(phrase);
            } else
                invalidKind++;
        });

        if (invalidKind)
            console.warn('Получено ' + invalidKind + ' объектов с неверным типом.');
        
        this.phrasesList.replaceChildren(...phrasesView);
    }
}