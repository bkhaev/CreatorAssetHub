// Creator Asset Hub(c) 2023. All rights reserved.
//
// The taglist component loader file

import { libSecondsToHms } from "../../../js/bf_library.js";
import { sysDeleteResourceFromPlaylist, sysGetData, sysPutData, sysSaveResourceInPlaylist } from "../../../js/index.js";



export class UserFavoritesImagesPanel extends HTMLElement {
    render() {
        const sectionTitle = document.createElement('h2');
        sectionTitle.textContent = 'Понравившиеся изображения';
        
        this.totalImagesCount = document.createElement('p');
        
        this.imagesList = document.createElement('div');
        this.imagesList.className = 'local-resources-list';

        this.setSkeletons();
        this.append(sectionTitle, this.totalImagesCount, this.imagesList);

        console.log('Rendering UserFavoritesImagesPanel');
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



    setSkeletons(count = 4) {
        const skeletons = [];
        while (count) {
            const image = document.createElement('article');
            image.className = 'local-resource-box image spectrum';

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
            image.append(previewVideo, previewData);

            skeletons.push(image);
            count--;
        }

        this.imagesList.replaceChildren(...skeletons);
    }



    async getPlaylistImages(playlistId) {
        const data = await sysGetData('materials/search?part=snippet,contentDetails&kind=materials%23image&playlistId=' + playlistId).catch(reason => {
            console.log('Ошибка Promise: ' + reason);
            return;
        });

        if (data.kind === 'materials#materialsListResponse') {
            this.totalImagesCount.classList.add('local-section-paragraph');
            if (data.pageInfo.total > 0) {
                this.totalImagesCount.textContent = 'Всего изображений: ' + data.pageInfo.total;
                this.setImages(data.items);
            } else {
                this.totalImagesCount.textContent = 'У вас ещё нет понравившихся изображений';
                this.imagesList.remove();
            }
        }
        else
            console.warn('Не был передан нужный тип объекта.');
    }

    setImages(imagesList) {
        let invalidKind = 0;
        const imagesView = [];
        imagesList.forEach(item => {
            if (item.kind === 'materials#image') {
                const material = document.createElement('article');
                material.className = 'local-resource-box image spectrum';

                const headerToolBar = document.createElement('div');
                headerToolBar.className = '_top-right-toolbar';

                const previewImage = document.createElement('div');
                if (item.snippet.previewFile.fileName !== undefined) {
                    previewImage.className = '_preview-source';

                    const image = document.createElement('img');
                    image.src = 'http://localhost/files/' + item.snippet.previewFile.fileName;
                    image.height = '210';
                    image.alt = 'preview';

                    if (item.snippet.previewFile.background === 1) {
                        if (item.snippet.previewFile.width > item.snippet.previewFile.height) {
                            image.style.width = '100%';
                            image.style.height = 'unset';
                        } else {
                            image.style.width = 'unset';
                            image.style.height = '100%';
                        }
                    } else
                        previewImage.classList.add('skeleton');
    
                    image.addEventListener('error', () => {
                        console.warn('Ошибка загрузки изображения');
                    });
    
                    previewImage.appendChild(image);
                } else {
                    previewImage.className = '_preview-source s-no-content';
                    previewImage.title = 'Внешний источник';
    
                    const imgfallback = document.createElement('img');
                    imgfallback.src = 'http://localhost/images/logo/bflogo.png';
                    // imgfallback.srcset = 'images/logo/bflogo.png';
                    imgfallback.alt = 'Внешний источник';
                    imgfallback.width = '60';
                    imgfallback.height = '60';
        
                    previewImage.appendChild(imgfallback);
                }

                const previewData = document.createElement('div');
                previewData.className = '_preview-data';

                const previewDataHeaderWrapper = document.createElement('div');

                const title = document.createElement('h3');
                
                const imageLink = document.createElement('a');
                imageLink.href = 'http://localhost/materials/details?id=' + item.id;
                imageLink.textContent = item.snippet.title;

                title.appendChild(imageLink);

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
                    const isImageOptionsMenuModal = e.target == menuBtnMenu || menuBtnMenu.contains(e.target);
                    
                    if (!isImageOptionsMenuModal && menuBtnBox.preventHideEvent)
                        menuBtnBox.preventHideEvent = false;
                    else if (!isImageOptionsMenuModal) {
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
                saveMaterialInFavoritesOptionChbx.id = 'save_image_' + item.id;
                saveMaterialInFavoritesOptionChbx.checked = true;

                saveMaterialInFavoritesOptionChbx.addEventListener('change', async () => {
                    menuBtnBox.classList.remove('s-expanded');

                    if (saveMaterialInFavoritesOptionChbx.checked) {
                        const saveId = await sysSaveResourceInPlaylist(item.kind, item.id, 'Понравившиеся изображения');
                        if (saveId) {
                            item.contentDetails.savedInFavoritesId.id = saveId;
                            console.log('Изображение сохранено в избранные!');
                        }
                    } else {
                        const result = await sysDeleteResourceFromPlaylist(item.contentDetails.savedInFavoritesId.id);
                        if (result) {
                            material.remove();
                            console.log('Изображение удалено из избранных.');

                            if (!this.imagesList.textContent) {
                                this.totalImagesCount.textContent = 'У вас ещё нет понравившихся изображений';
                                this.imagesList.remove();
                            }
                        }
                    }
                });


                const saveMaterialInFavoritesOptionLabel = document.createElement('label');
                saveMaterialInFavoritesOptionLabel.className = 'li_content';
                saveMaterialInFavoritesOptionLabel.htmlFor = 'save_image_' + item.id;

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
                            // resourceId: {
                            //     kind: resourceKind,
                            // },
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
                

                material.append(headerToolBar, previewImage, previewData, userNote);

                imagesView.push(material);
            } else
                invalidKind++;
        });

        if (invalidKind)
            console.warn('Получено ' + invalidKind + ' объектов с неверным типом.');
        
        this.imagesList.replaceChildren(...imagesView);
    }
}