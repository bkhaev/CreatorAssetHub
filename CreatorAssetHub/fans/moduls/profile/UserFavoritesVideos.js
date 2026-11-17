// Creator Asset Hub(c) 2023. All rights reserved.
//
// The taglist component loader file

import { libSecondsToHms } from "../../../js/bf_library.js";
import { sysDeleteResourceFromPlaylist, sysGetData, sysPutData, sysSaveResourceInPlaylist } from "../../../js/index.js";



export class UserFavoritesVideosPanel extends HTMLElement {
    render() {
        const sectionTitle = document.createElement('h2');
        sectionTitle.textContent = 'Понравившиеся видео';
        
        this.totalVideosCount = document.createElement('p');
        
        this.videosList = document.createElement('div');
        this.videosList.className = 'local-resources-list';

        this.setSkeletons();
        this.append(sectionTitle, this.totalVideosCount, this.videosList);

        console.log('Rendering UserFavoritesVideosPanel');
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



    setSkeletons(count = 3) {
        const skeletons = [];
        while (count) {
            const video = document.createElement('article');
            video.className = 'local-resource-box image spectrum';

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
            
            previewData.append(titleWrapper);
            video.append(previewVideo, previewData);

            skeletons.push(video);
            count--;
        }

        this.videosList.replaceChildren(...skeletons);
    }



    async getPlaylistVideos(playlistId) {
        const data = await sysGetData('materials/search?part=snippet,contentDetails&kind=materials%23video&playlistId=' + playlistId).catch(reason => {
            console.log('Ошибка Promise: ' + reason);
            return;
        });

        if (data.kind === 'materials#materialsListResponse') {
            this.totalVideosCount.classList.add('local-section-paragraph');
            if (data.pageInfo.total > 0) {
                this.totalVideosCount.textContent = 'Всего видео: ' + data.pageInfo.total;
                this.setImages(data.items);
            } else {
                this.totalVideosCount.textContent = 'У вас ещё нет понравившихся видео';
                this.videosList.remove();
            }
        }
        else
            console.warn('Не был передан нужный тип объекта.');
    }

    setImages(videosList) {
        let invalidKind = 0;
        const videosView = [];
        videosList.forEach(item => {
            if (item.kind === 'materials#video') {
                const material = document.createElement('article');
                material.className = 'local-resource-box video spectrum';

                const headerToolBar = document.createElement('div');
                headerToolBar.className = '_top-right-toolbar';

                const previewVideo = document.createElement('div');
                if (item.snippet.previewFile.fileName !== undefined) {
                    previewVideo.className = '_preview-source skeleton';

                    const video = document.createElement('video');
                    video.width = '256';
                    video.height = '144';
                    video.name = 'media';
                    video.volume = 0;
    
                    const src = document.createElement('source');
                    src.src = 'http://localhost/files/' + item.snippet.previewFile.fileName;
                    src.type = 'video/mp4';
    
                    src.addEventListener('error', () => {
                        console.warn('Ошибка загрузки видео');
                    });
    
                    previewVideo.addEventListener('mouseenter', () => video.play());
                    previewVideo.addEventListener('mouseleave', () => video.pause());
    
                    video.appendChild(src);
                    previewVideo.appendChild(video);
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
                
                const videoLink = document.createElement('a');
                videoLink.href = 'http://localhost/materials/details?id=' + item.id;
                videoLink.textContent = item.snippet.title;

                title.appendChild(videoLink);

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
                    const isVideoOptionsMenuModal = e.target == menuBtnMenu || menuBtnMenu.contains(e.target);
                    
                    if (!isVideoOptionsMenuModal && menuBtnBox.preventHideEvent)
                        menuBtnBox.preventHideEvent = false;
                    else if (!isVideoOptionsMenuModal) {
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

                const saveMaterialInFavoritesOption = document.createElement('li');

                const saveMaterialInFavoritesOptionChbx = document.createElement('input');
                saveMaterialInFavoritesOptionChbx.type = 'checkbox';
                saveMaterialInFavoritesOptionChbx.id = 'save_video_' + item.id;
                saveMaterialInFavoritesOptionChbx.checked = true;

                saveMaterialInFavoritesOptionChbx.addEventListener('change', async () => {
                    menuBtnBox.classList.remove('s-expanded');

                    if (saveMaterialInFavoritesOptionChbx.checked) {
                        const saveId = await sysSaveResourceInPlaylist(item.kind, item.id, 'Понравившиеся видео');
                        if (saveId) {
                            item.contentDetails.savedInFavoritesId.id = saveId;
                            console.log('Видео сохранено в избранные!');
                        }
                    } else {
                        const result = await sysDeleteResourceFromPlaylist(item.contentDetails.savedInFavoritesId.id);
                        if (result) {
                            material.remove();
                            console.log('Видео удалено из избранных.');

                            if (!this.videosList.textContent) {
                                this.totalVideosCount.textContent = 'У вас ещё нет понравившихся видео';
                                this.videosList.remove();
                            }
                        }
                    }
                });


                const saveMaterialInFavoritesOptionLabel = document.createElement('label');
                saveMaterialInFavoritesOptionLabel.className = 'li_content';
                saveMaterialInFavoritesOptionLabel.htmlFor = 'save_video_' + item.id;

                const saveMaterialInFavoritesOptionText = document.createElement('span');
                saveMaterialInFavoritesOptionText.textContent = 'Удалить из закладок';

                const saveMaterialInFavoritesOptionIcon = document.createElement('div');
                saveMaterialInFavoritesOptionIcon.className = 'li_icon s-save';

                saveMaterialInFavoritesOptionLabel.append(saveMaterialInFavoritesOptionText, saveMaterialInFavoritesOptionIcon);
                saveMaterialInFavoritesOption.append(saveMaterialInFavoritesOptionChbx, saveMaterialInFavoritesOptionLabel);
                menuBtnOptionsList.appendChild(saveMaterialInFavoritesOption);


                const downloadMaterialOption = document.createElement('li');

                const downloadMaterialOptionBtn = document.createElement('button');
                downloadMaterialOptionBtn.className = 'li_content';
                downloadMaterialOptionBtn.type = 'button';

                downloadMaterialOptionBtn.addEventListener('click', async () => {
                    menuBtnBox.classList.remove('s-expanded');

                    const a = document.createElement("a");
                    a.href = 'http://localhost/files/' + item.snippet.previewFile.fileName;
                    a.download = item.snippet.previewFile.fileName;
                    a.click();
                });

                const downloadMaterialOptionText = document.createElement('span');
                downloadMaterialOptionText.textContent = 'Скачать';

                downloadMaterialOptionBtn.appendChild(downloadMaterialOptionText);
                downloadMaterialOption.appendChild(downloadMaterialOptionBtn);
                menuBtnOptionsList.appendChild(downloadMaterialOption);
                

                menuBtnMenu.appendChild(menuBtnOptionsList);
                menuBtnBox.append(menuBtn, menuBtnMenu);
                
                previewDataHeaderWrapper.append(title, menuBtnBox);
                
                previewData.append(previewDataHeaderWrapper);


                menuBtnMenu.appendChild(menuBtnOptionsList);
                menuBtnBox.append(menuBtn, menuBtnMenu);
                
                previewDataHeaderWrapper.append(title, menuBtnBox);
                
                previewData.append(previewDataHeaderWrapper);



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
                

                material.append(headerToolBar, previewVideo, previewData, userNote);

                videosView.push(material);
            } else
                invalidKind++;
        });

        if (invalidKind)
            console.warn('Получено ' + invalidKind + ' объектов с неверным типом.');
        
        this.videosList.replaceChildren(...videosView);
    }
}