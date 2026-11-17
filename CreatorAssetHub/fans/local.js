// Creator Asset Hub(c) 2023. All rights reserved.
//
// The Phrases Search modal JS-script file

import { testSleep, testSleepAsync } from '../js/tests.js';
import { sysPostData, sysGetData, sys, sysGetWebSiteIcon } from '../js/index.js';
import { libNamedMonthDate } from '../js/bf_library.js';
import { loadHeaderGrand } from '../../components/HeaderNavigator.js';
// import { loadFooterMinor } from '../../components/Footer.js';
import { InputTextGrand } from '../components/InputTextGrand.js';
import { UserFavoritesPhrasesPanel } from './moduls/profile/userFavoritesPhrases.js';
import { UserFavoritesImagesPanel } from './moduls/profile/UserFavoritesImages.js';
import { UserFavoritesVideosPanel } from './moduls/profile/UserFavoritesVideos.js';
import { UserMinePhrasesPanel } from './moduls/profile/UserMinePhrases.js';



customElements.define('bf-header-grand', loadHeaderGrand, {extends: 'nav'});
// customElements.define('bf-footer-minor', loadFooterMinor, {extends: 'footer'});
customElements.define('bf-input-text-grand', InputTextGrand);
customElements.define('bf-user-favorites-phrases', UserFavoritesPhrasesPanel);
customElements.define('bf-user-favorites-images', UserFavoritesImagesPanel);
customElements.define('bf-user-favorites-videos', UserFavoritesVideosPanel);
customElements.define('bf-user-mine-phrases', UserMinePhrasesPanel);



window.onload = function() {
    // начальные действия

    // return;

    const pagePathParts = location.pathname.split('/');
    if (pagePathParts.length > 2) {
        sysGetData('user/list?part=snippet&mine=true').then(result => {
            if (result.kind === 'users#account' || result.status === 401) {
                if (result.kind === 'users#account') {
                    sys.account = {
                        id: result.id,
                        email: result.snippet.email,
                        name: result.snippet.name,
                        isVerificated: result.snippet.isUserVerificated,
                        isModerator: result.snippet.isUserModerator,
                    };
    
                    globalnav.setAttribute('signed-in', '');
                }

                const userPictureImg = document.createElement('img');
                userPictureImg.width = 150;
                userPictureImg.height = 150;
                userPictureImg.alt = 'photo';

                const userPictureBox = userCard.querySelector('._picture');

                userPictureImg.addEventListener('load', () => {
                    userPictureBox.appendChild(userPictureImg);
                });

                userPictureImg.addEventListener('error', () => {
                    console.warn('Изображение профиля: Не удалось отобразить изображение');
                    userPictureBox.appendChild(userPictureImg);
                });

                const getVerificationIcon = () => {
                    const verificationIcon = document.createElement('img');
                    verificationIcon.src = 'http://localhost/images/icons/dark/verification_icon.png';
                    verificationIcon.srcset = 'http://localhost/images/icons/dark/verification_icon.svg';
                    verificationIcon.alt = '✔';
                    verificationIcon.title = 'Верифицирован';
                    verificationIcon.width = 15;
                    verificationIcon.height = 15;
                    return verificationIcon;
                };
        
                const getModerationIconIcon = () => {
                    const moderationIcon = document.createElement('img');
                    moderationIcon.src = 'http://localhost/images/icons/dark/moderation_icon.png';
                    moderationIcon.srcset = 'http://localhost/images/icons/dark/moderation_icon.svg';
                    moderationIcon.alt = '🔧';
                    moderationIcon.title = 'Модератор';
                    moderationIcon.width = 15;
                    moderationIcon.height = 15;
                    return moderationIcon;
                };

                const setUserPersonalLinks = (links) => {
                    const userPersonalLinksBoxTitle = document.createElement('h3');
                    userPersonalLinksBoxTitle.textContent = 'Ссылки';

                    const userPersonalLinksList = document.createElement('ul');
                    userPersonalLinksList.className = '__links-list';

                    links.forEach(link => {
                        const li = document.createElement('li');

                        const a = document.createElement('a');
                        a.href = link.url;

                        const linkBox = document.createElement('figure');
                        linkBox.className = '__links-list_link';

                        const linkIcon = document.createElement('blockquote');
                        linkIcon.className = '___icon skeleton';

                        const linkIconImg = document.createElement('img');
                        linkIconImg.width = 30;
                        linkIconImg.height = 30;
                        linkIconImg.alt = 'icon';
                        linkIconImg.src = sysGetWebSiteIcon(link.url);

                        linkIconImg.addEventListener('load', () => {
                            linkIcon.appendChild(linkIconImg);
                        });
        
                        linkIconImg.addEventListener('error', () => {
                            console.warn('Логотип ссылки: Не удалось отобразить изображение');
                            linkIcon.appendChild(linkIconImg);
                        });

                        const linkText = document.createElement('figcaption');
                        linkText.className = '___text';
                        linkText.textContent = link.title;

                        userPersonalLinksList.appendChild(li);
                    });

                    userLinksBox.replaceChildren(userPersonalLinksBoxTitle, userPersonalLinksList);
                };

                const sectionsRemover = (about, linksCount) => {
                    if (!about && !linksCount)
                        commonUserSecondBlock.remove();
                };

                const expandBanner = () => {
                    const animationDuration = Number(getComputedStyle(banner, null).getPropertyValue('--BannerAnimationDuration').match(/[-]{0,1}[\d]*[.]{0,1}[\d]+/g)) * 1000;
                    banner.style.height = userProfileGreeting.clientHeight + 'px';
                    setTimeout(() => banner.style.height = 'unset', animationDuration);
                };
    
                const userId = pagePathParts.pop();
                if (result.kind === 'users#account' && Number(userId) === result.id) {
                    sysGetData('user/list?part=picture,statistics&mine=true').then(data => {
                        if (data.kind === 'users#account') {
                            const userProfileGreetingText = document.createElement('h2');
                            userProfileGreetingText.className = 'spectrum-introductory-text';

                            if (result.snippet.name) {
                                const span = document.createElement('span');
                                span.className = 'attn-text';
                                span.textContent = result.snippet.name;
    
                                userProfileGreetingText.append('С возвращением, ', span, '!');
                            } else
                                userProfileGreetingText.textContent = 'С возвращением!';

                            userProfileGreeting.replaceChildren(userProfileGreetingText);

                            setTimeout(() => expandBanner(), 300);

                            if (data.picture.userPictureOriginal.inBytes)
                                userPictureImg.src = data.picture.userPictureOriginal.inBytes;
                            else
                                userPictureImg.src = 'http://localhost/images/user_picture.jpg';

                            const nameText = document.createElement('h3');
                            nameText.textContent = result.snippet.name ? result.snippet.name : result.snippet.email.split('@')[0];

                            userCardFirstText.replaceChildren(nameText);

                            if (result.snippet.isUserVerificated)
                                userCardFirstText.appendChild(getVerificationIcon());
                            
                            if (result.snippet.isUserModerator)
                                userCardFirstText.appendChild(getModerationIconIcon());

                            const emailText = document.createElement('p');
                            emailText.textContent = result.snippet.email;

                            userCardSecondText.replaceChildren(emailText);

                            const userSettingsLink = document.createElement('a');
                            userSettingsLink.className = 'btn btn-primary s-second';
                            userSettingsLink.textContent = 'Изменить профиль';
                            userSettingsLink.href = 'http://localhost/settings';

                            userSettingsLink.addEventListener('click', e => {
                                e.preventDefault();
                                alert('Данный раздел находится в разработке');
                            })

                            skeletonText1.replaceWith(userSettingsLink);

                            const userSpecsList1 = document.createElement('ul');
                            userSpecsList1.className = '_data-list';

                            const userTotalPoints = document.createElement('li');
                            userTotalPoints.textContent = 'Ваши баллы: ' + data.statistics.totalPoints;

                            userSpecsList1.append(userTotalPoints);

                            const userSpecsList2 = document.createElement('ul');
                            userSpecsList2.className = '_data-list';

                            const userTotalPhrasesCount = document.createElement('li');
                            userTotalPhrasesCount.textContent = 'Добавлено фраз: ' + data.statistics.totalPhrasesCount;

                            userSpecsList2.append(userTotalPhrasesCount);

                            userSpecsBox.querySelector('._data-row').replaceChildren(userSpecsList1, userSpecsList2);

                            if (result.snippet.about) {
                                const userAboutBoxTitle = document.createElement('h3');
                                userAboutBoxTitle.textContent = 'О себе';
    
                                const userAboutBoxText = document.createElement('p');
                                userAboutBoxText.textContent = result.snippet.about;
    
                                userAboutBox.replaceChildren(userAboutBoxTitle, userAboutBoxText);
                            } else
                                userAboutBox.textContent = '';

                            if (result.snippet.personalLinks.length)
                                setUserPersonalLinks(result.snippet.personalLinks);
                            else
                                userLinksBox.textContent = '';

                            sectionsRemover(result.snippet.about, result.snippet.personalLinks.length);
                        } else
                            console.warn('Не был передан нужный тип объекта.');
                    });


                    const userMinePhrasesPanel = new UserMinePhrasesPanel();
                    userMinePhrasesPanel.className = 'section-panel user-mine-phrases-panel';
                    app.appendChild(userMinePhrasesPanel);

                    
                    getUserPlaylists().then(() => {
                        if (pageState.user.favoritesPhrasesId) {
                            const userFavoritesPhrasesPanel = new UserFavoritesPhrasesPanel();
                            userFavoritesPhrasesPanel.className = 'section-panel';
                            userFavoritesPhrasesPanel.getPlaylistPhrases(pageState.user.favoritesPhrasesId);
                            app.appendChild(userFavoritesPhrasesPanel);
                        } else
                            console.log('Адрес плейлиста понравившихся фраз не определён.');

                        if (pageState.user.favoritesImagesId) {
                            const userFavoritesImagesPanel = new UserFavoritesImagesPanel();
                            userFavoritesImagesPanel.className = 'section-panel';
                            userFavoritesImagesPanel.getPlaylistImages(pageState.user.favoritesImagesId);
                            app.appendChild(userFavoritesImagesPanel);
                        } else
                            console.log('Адрес плейлиста понравившихся изображений не определён.');

                        if (pageState.user.favoritesVideoId) {
                            const userFavoritesVideosPanel = new UserFavoritesVideosPanel();
                            userFavoritesVideosPanel.className = 'section-panel';
                            userFavoritesVideosPanel.getPlaylistVideos(pageState.user.favoritesVideoId);
                            app.appendChild(userFavoritesVideosPanel);
                        } else
                            console.log('Адрес плейлиста понравившихся видео не определён.');
                    });
                } else {
                    sysGetData('user/list?part=snippet,picture,statistics&id=' + userId).then(async data => {
                        if (data.kind === 'users#usersListResponse') {
                            if (data.items && data.items.length) {
                                const user = data.items[0];

                                if (user.kind === 'users#user') {
                                    if (user.picture.userPictureOriginal.inBytes)
                                        userPictureImg.src = user.picture.userPictureOriginal.inBytes;
                                    else
                                        userPictureImg.src = 'http://localhost/images/user_picture.jpg';

                                    const nameText = document.createElement('h3');
                                    nameText.textContent = user.snippet.displayName;

                                    userCardFirstText.replaceChildren(nameText);

                                    if (user.snippet.isUserVerificated)
                                        userCardFirstText.appendChild(getVerificationIcon());
                                    
                                    if (user.snippet.isUserModerator)
                                        userCardFirstText.appendChild(getModerationIconIcon());

                                    const registeredDate = document.createElement('p');
                                    const delimeter = document.createElement('br');
                                    registeredDate.append('Присоединился', delimeter, libNamedMonthDate(user.snippet.registeredAt));

                                    skeletonText1.replaceWith(registeredDate);

                                    const userSpecsList1 = document.createElement('ul');
                                    userSpecsList1.className = '_data-list';

                                    const userTotalPoints = document.createElement('li');
                                    userTotalPoints.textContent = 'Набрано баллов: ' + user.statistics.totalPoints;

                                    userSpecsList1.append(userTotalPoints);

                                    const userSpecsList2 = document.createElement('ul');
                                    userSpecsList2.className = '_data-list';

                                    const userTotalPhrasesCount = document.createElement('li');
                                    userTotalPhrasesCount.textContent = 'Добавлено фраз: ' + user.statistics.totalPhrasesCount;

                                    userSpecsList2.append(userTotalPhrasesCount);

                                    userSpecsBox.querySelector('._data-row').replaceChildren(userSpecsList1, userSpecsList2);

                                    if (user.snippet.about.length > 50) {
                                        const userAboutBoxTitle = document.createElement('h3');
                                        userAboutBoxTitle.textContent = 'О себе';

                                        const userAboutBoxText = document.createElement('p');
                                        userAboutBoxText.textContent = user.snippet.about;

                                        userCardSecondText.textContent = '';
                                        userAboutBox.replaceChildren(userAboutBoxTitle, userAboutBoxText);
                                    } else if (user.snippet.about) {
                                        const userAboutText = document.createElement('p');
                                        userAboutText.textContent = user.snippet.about;

                                        userCardSecondText.replaceChildren(userAboutText);
                                        userAboutBox.textContent = '';
                                    } else {
                                        userCardSecondText.textContent = '';
                                        userAboutBox.textContent = '';
                                    }

                                    if (user.snippet.personalLinks.length)
                                        setUserPersonalLinks(user.snippet.personalLinks);
                                    else
                                        userLinksBox.textContent = '';

                                    sectionsRemover(user.snippet.about, user.snippet.personalLinks.length);
                                } else
                                    console.warn('Не был передан нужный тип объекта.');
                            } else
                                window.location.href = 'http://localhost/phrases/search';
                        } else
                            console.warn('Не был передан нужный тип объекта.');
                    });
                }
            } else
                console.warn('Не был передан нужный тип объекта.');
        });
    } else
        window.location.href = 'http://localhost/phrases/search';
}





///////////////// FUNCTIONS /////////////////




// Получает плейлисты пользователя
async function getUserPlaylists() {
    const data = await sysGetData('playlists?part=snippet%2CcontentDetails&mine=true').catch(reason => console.log('Ошибка Promise: ' + reason));

    if (data.kind === 'playlists#playlistListResponse') {
        let invalidKind = 0;
        data.items.forEach(item => {
            if (item.kind === 'playlists#playlist') {
                if (item.snippet.title === 'Понравившиеся фразы')
                    pageState.user.favoritesPhrasesId = item.id;
                else if (item.snippet.title === 'Понравившиеся изображения')
                    pageState.user.favoritesImagesId = item.id;
                else if (item.snippet.title === 'Понравившиеся видео')
                    pageState.user.favoritesVideoId = item.id;
                else if (item.snippet.title === 'Понравившаяся музыка')
                    pageState.user.favoritesMusicId = item.id;
                else if (item.snippet.title === 'Понравившиеся звуки')
                    pageState.user.favoritesSoundsId = item.id;
            } else
                invalidKind++;
        });

        if (invalidKind)
            console.warn('Получено ' + invalidKind + ' объектов с неверным типом.');

        // searchResults.replaceChildren(...phrasesView);
    } else
        console.warn('Не был передан нужный тип объекта.');
}