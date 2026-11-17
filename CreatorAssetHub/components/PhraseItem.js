// Creator Asset Hub(c) 2023. All rights reserved.
//
// The phrase block component loader file

import { libExtractHostName, libSecondsToHms } from "../js/bf_library.js";
import { sysDeleteResourceFromPlaylist, sysSaveResourceInPlaylist, sys } from "../js/index.js";
import { TagList, state as tgListState } from "./TagList.js";




export const state = {
    phraseItemGrand: [],
};



export class PhraseItemGrand extends HTMLElement {
    render() {
        const data = state.phraseItemGrand.find(i => i.id === Number(this.getAttribute('phrase-id')));
        if (data !== undefined) {
            const article = document.createElement('article');

            // const articleTitle = document.createElement('h4');
            // articleTitle.textContent = data.phrase ? data.phrase : '';
            // articleTitle.className = 'semantic-hidden';

            // article.appendChild(articleTitle);
        


            // ----- ВИДЕО -----

            const previewVideo = document.createElement('div');
            if (data.video.url !== undefined && libExtractHostName(data.video.url) === 'youtube.com') {
                previewVideo.className = '_preview-video skeleton';

                const iframe = document.createElement('iframe');
                iframe.width = '368'; // 256
                iframe.height = '207'; // 144
                iframe.src = data.video.url;
                iframe.setAttribute('frameborder', '0');
                iframe.setAttribute('allowpart2screen', '');
        
                previewVideo.appendChild(iframe);
            } else {
                previewVideo.className = '_preview-video';
                previewVideo.title = 'Внешний источник';

                const imgfallback = document.createElement('img');
                imgfallback.src = '../images/logo/bflogo.png';
                // imgfallback.srcset = '../images/logo/bflogo.png';
                imgfallback.alt = 'Внешний источник';
                imgfallback.width = '60';
                imgfallback.height = '60';
    
                previewVideo.appendChild(imgfallback);
            }

            // ----- ВИДЕО -----


            
            // ----- ИНФОРМАЦИЯ -----

            const previewData = document.createElement('div');
            previewData.className = '_preview-data';
    

            // ---------- первая строка ----------

            const dataRow1 = document.createElement('div');
            dataRow1.className = '__row';
    

            const dataRow1Column1 = document.createElement('div');
    
            const title = document.createElement('h3');
            title.className = '__phrase spectrum-paragraph-text';
            title.textContent = data.phrase ? data.phrase : '';
    
            dataRow1Column1.appendChild(title);
            

            const dataRow1Column2 = document.createElement('div');
            dataRow1Column2.className = 'menu-btn-options spectrum';
    
            const optionsBtn = document.createElement('button');
            optionsBtn.type = 'button';
            optionsBtn.className = '_btn';

            optionsBtn.addEventListener('click', () => {
                dataRow1Column2.classList.add('s-expanded');
                dataRow1Column2.preventHideEvent = true;
            });
        
            document.addEventListener('click', e => {
                const isPhraseOptionsMenuModal = e.target == optionsMenu || optionsMenu.contains(e.target);
                
                if (!isPhraseOptionsMenuModal && dataRow1Column2.preventHideEvent)
                    dataRow1Column2.preventHideEvent = false;
                else if (!isPhraseOptionsMenuModal) {
                    dataRow1Column2.classList.remove('s-expanded');
                }
            });

            const optionsBtnLabel = document.createElement('span');
            optionsBtnLabel.className = 'semantic-hidden';
            optionsBtnLabel.textContent = 'меню опций';
    
            const optionsIcon = document.createElement('div');
            optionsIcon.className = '__icon';

            optionsBtn.append(optionsBtnLabel, optionsIcon);

            ////////////////////////////////////////////////////////

            const optionsMenu = document.createElement('div');
            optionsMenu.className = '_menu';

            const optionsList = document.createElement('ul');

            const optionsItem = document.createElement('li');


            const savePhraseText = document.createElement('span');
            savePhraseText.textContent = 'Сохранить в закладки';

            // const savePhraseIcon = document.createElement('img');
            // savePhraseIcon.srcset = '../images/icons/dark/30pt/unsaved.svg';
            // savePhraseIcon.width = '12';
            // savePhraseIcon.height = '12';

            const savePhraseIcon = document.createElement('div');
            savePhraseIcon.className = 'li_icon s-save';

            if (sys.account) {
                const savePhraseChbx = document.createElement('input');
                savePhraseChbx.type = 'checkbox';
                savePhraseChbx.id = 'save_phrase_' + data.id;
                savePhraseChbx.name = 'save_phrase';

                if (data.savedId)
                    savePhraseChbx.checked = true;
                
                savePhraseChbx.addEventListener('change', async () => {
                    dataRow1Column2.classList.remove('s-expanded');

                    if (sys.account) {
                        if (savePhraseChbx.checked) {
                            const saveId = await sysSaveResourceInPlaylist(data.kind, data.id, 'Понравившиеся фразы');
                            if (saveId) {
                                data.savedId = saveId;
                                console.log('Фраза сохранена в избранных!');
                            }
                        } else {
                            const result = await sysDeleteResourceFromPlaylist(data.savedId);
                            if (result)
                                console.log('Фраза удалена из избранных.');
                        }
                    } else {
                        alert('войдите в аккаунт');
                    }
                });
                
                const savePhraseContent = document.createElement('label');
                savePhraseContent.className = 'li_content';
                savePhraseContent.htmlFor = 'save_phrase_' + data.id;

                savePhraseContent.append(savePhraseText, savePhraseIcon);
                optionsItem.append(savePhraseChbx, savePhraseContent);
            } else {
                const savePhraseContent = document.createElement('a');
                savePhraseContent.className = 'li_content';
                savePhraseContent.href = '#';

                savePhraseContent.addEventListener('click', e => {
                    dataRow1Column2.classList.remove('s-expanded');
                    window.location.href = 'http://localhost/login?redirect=' + encodeURIComponent(document.location.pathname + document.location.search + document.location.hash) + '#login';

                    e.preventDefault();
                });

                savePhraseContent.append(savePhraseText, savePhraseIcon);
                optionsItem.appendChild(savePhraseContent);
            }

            optionsList.appendChild(optionsItem);

            if (data.video.url !== undefined) {
                const sourceLinkOption = document.createElement('li');


                const sourceLinkOptionText = document.createElement('span');
                sourceLinkOptionText.textContent = 'Перейти к источнику';

                const sourceLinkOptionIcon = document.createElement('div');
                sourceLinkOptionIcon.className = 'li_icon s-link';

                const sourceLinkOptionContent = document.createElement('a');
                sourceLinkOptionContent.className = 'li_content';
                sourceLinkOptionContent.href = data.video.url;
                sourceLinkOptionContent.target = '_blank';

                sourceLinkOptionContent.append(sourceLinkOptionText, sourceLinkOptionIcon);
                sourceLinkOption.appendChild(sourceLinkOptionContent);

                optionsList.appendChild(sourceLinkOption);
            }

            if (data.userId) {
                const sourceLinkOption = document.createElement('li');


                const sourceLinkOptionText = document.createElement('span');
                sourceLinkOptionText.textContent = 'Профиль пользователя';

                const sourceLinkOptionContent = document.createElement('a');
                sourceLinkOptionContent.className = 'li_content';
                sourceLinkOptionContent.href = 'http://localhost/fans/' + data.userId;

                sourceLinkOptionContent.append(sourceLinkOptionText);
                sourceLinkOption.appendChild(sourceLinkOptionContent);

                optionsList.appendChild(sourceLinkOption);
            }
            

            optionsMenu.appendChild(optionsList);

            ////////////////////////////////////////////////////////
    
            dataRow1Column2.append(optionsBtn, optionsMenu);
            dataRow1.append(dataRow1Column1, dataRow1Column2);
    
            // ---------- первая строка ----------


            // ---------- вторая строка ----------
            
            const dataRow2 = document.createElement('div');
            dataRow2.className = '__row';
    

            const dataRow2Column1 = document.createElement('div');
    
            const parametersList1 = document.createElement('div');
            parametersList1.className = '_data-list spectrum';

            if (data.authors && data.authors.length) {
                const wrapper = document.createElement('div');

                const authors = new TagList(); // document.createElement('ul')
                authors.id = 'phrase_' + data.id + '_authors';
                authors.className = '__authors spectrum';
                authors.setAttribute('is', 'bf-tag-list');

                tgListState.tagList['phrase_' + data.id + '_authors'] = {
                    list: [],
                };
                data.authors.forEach(author => {
                    const bodyAction = {};
                    if (author.link) {
                        bodyAction.link = author.link.url;
                        bodyAction.action = author.link.action;
                    } else
                        bodyAction.link = 'http://localhost/phrases/search?author=' + author.name;

                    tgListState.tagList['phrase_' + data.id + '_authors']['list'].push({
                        type: 'author',
                        text: author.name,
                        bodyAction: bodyAction,
                    });
                });

                wrapper.appendChild(authors);
                parametersList1.appendChild(wrapper);

                // authors.setAttribute('updated', Date.now());
            }
            
            if (data.video && data.video.time) {
                const time = document.createElement('div');
                time.textContent = libSecondsToHms(data.video.time);

                parametersList1.appendChild(time);
            }
            
            dataRow2Column1.appendChild(parametersList1);

            dataRow2.appendChild(dataRow2Column1);

            // ---------- вторая строка ----------


            // ---------- третья строка ----------

            const dataRow3 = document.createElement('div');
            dataRow3.className = '__row';
    

            const dataRow3Column1 = document.createElement('div');

            const parametersList2 = document.createElement('div');
            parametersList2.className = '_data-list spectrum-2';

            const tone = document.createElement('span');
            tone.textContent = data.tone;

            parametersList2.appendChild(tone);

            if (data.emotion) {
                const emotion = document.createElement('span');
                emotion.textContent = data.emotion;

                parametersList2.appendChild(emotion);
            }

            dataRow3Column1.appendChild(parametersList2);

            dataRow3.appendChild(dataRow3Column1);

            // ---------- третья строка ----------


            // ---------- четвёртая строка ----------

            const dataRow4Wrapper = document.createElement('div');
            dataRow4Wrapper.className = '__wrapper';

            const dataRow4 = document.createElement('div');
            dataRow4.className = '__row spectrum';
    

            const dataRow4Column1 = document.createElement('div');

            const userDiv = document.createElement('div');
            userDiv.className = '__user';

            const userName = document.createElement('p');

            if (data.userName) {
                userName.textContent = data.userName;
            } else {
                userName.textContent = 'неизвестно';
                userDiv.classList.add('s-undefined');
            }

            userDiv.appendChild(userName);

            if (data.userVerification) {
                const userVerificationIcon = document.createElement('img');
                userVerificationIcon.width = '15';
                userVerificationIcon.height = '15';
                userVerificationIcon.src = '../images/icons/dark/verification_icon.png';
                userVerificationIcon.srcset = '../images/icons/dark/verification_icon.svg';
                userVerificationIcon.alt = '✔';
                userVerificationIcon.title = 'Верифицирован';

                userDiv.appendChild(userVerificationIcon);
            }
            if (data.userModerationStatus) {
                const userModerationStatusIcon = document.createElement('img');
                userModerationStatusIcon.width = '15';
                userModerationStatusIcon.height = '15';
                userModerationStatusIcon.src = '../images/icons/dark/moderation_icon.png';
                userModerationStatusIcon.srcset = '../images/icons/dark/moderation_icon.svg';
                userModerationStatusIcon.alt = '🔧';
                userModerationStatusIcon.title = 'Модератор';

                userDiv.appendChild(userModerationStatusIcon);
            }

            dataRow4Column1.appendChild(userDiv);


            const dataRow4Column2 = document.createElement('div');

            const detailsLink = document.createElement('a');
            detailsLink.className = '__link';
            detailsLink.href = 'details?phrase=' + data.id;
            detailsLink.textContent = 'ПОДРОБНЕЕ';

            detailsLink.addEventListener('click', e => {
                e.preventDefault();
                alert('Данный сервис в разработке');
            });

            dataRow4Column2.appendChild(detailsLink);


            dataRow4.append(dataRow4Column1, dataRow4Column2);

            // ---------- четвёртая строка ----------


            dataRow4Wrapper.appendChild(dataRow4);
            previewData.append(dataRow1, dataRow2, dataRow3, dataRow4Wrapper);

            // ИНФОРМАЦИЯ ----------------


    
            article.append(previewVideo, previewData);
    
            console.log('Rendering PhraseItemGrand');
            this.replaceChildren(article);
            return;
        }

        console.log('PhraseItemGrand render fail');
    }

	connectedCallback() {
        if (!this.rendered) {
            this.render();
            this.rendered = true;
        }
    }

    disconnectedCallback() {
        // delete state.phraseItemGrand.find(i => i.id === Number(this.getAttribute('phrase-id')));
    }

    static get observedAttributes() {
        return ['phrase-id'];
    }

    attributeChangedCallback(name, oldValue, newValue) {
        this.render();
        this.rendered = true;
    }
}