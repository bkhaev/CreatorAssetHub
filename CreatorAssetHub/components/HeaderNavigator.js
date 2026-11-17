// Creator Asset Hub(c) 2023. All rights reserved.
//
// The header component loader file

import { sys, sysGetData } from "../js/index.js";



export class loadHeaderGrand extends HTMLElement {
    render() {
        // const elements = JSON.parse(this.getAttribute('elements').replace(/&quot;/ig,'"'))['elements'];
        const created = [];

        this.innerHTML = `
        <div class="__wrapper">
            <!-- logo -->
            <div id="welcomeLinks">
                <a id="logoCreatorassethub" class="logo" href="/" title="Главная страница Creator Asset Hub"></a>
            </div>

            <!-- links -->
            <div id="navigationLinksRich" class="spectrum">
                <ul>
                    <li>
                        <a href="http://localhost/phrases/search">Фразы</a>
                    </li>
                    <li>
                        <a href="http://localhost/stock/search?kind=materials%23image">Изображения</a>
                    </li>
                    <li>
                        <a href="http://localhost/stock/search?kind=materials%23video">Футажи</a>
                    </li>
                    <li>
                        <a href="http://localhost/support">Помощь и поддержка</a>
                    </li>
                </ul>
            </div>

            <!-- profile user-photo-->
            <div id="toolsRich">
                <div id="navigationUser" class="s-hidden">
                    <button id="profile" type="button" title="Вход/регистрация">
                        <img width="50" height="50" class="skeleton" alt="Фото профиля" src="../images/user_picture.jpg">
                    </button>
                    <div id="profileDropdown" class="spectrum">
                        <ul class="_actions">
                            <li id="userSignUp"><a href="#">Зарегистрироваться</a></li>
                            <li id="userLogIn"><a href="#">Войти</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        `;

        profile.addEventListener('click', () => {
            navigationUser.classList.remove('s-hidden');
            navigationUser.classList.add('s-expanded');
            navigationUser.preventHideEvent = true;
        });
    
        document.addEventListener('click', e => {
            const isProfileDropDownModal = e.target == profileDropdown || profileDropdown.contains(e.target);
            
            if (!isProfileDropDownModal && navigationUser.preventHideEvent)
                navigationUser.preventHideEvent = false;
            else if (!isProfileDropDownModal) {
                navigationUser.classList.remove('s-expanded');
                navigationUser.classList.add('s-hidden');
            }
        });

        userLogIn.addEventListener('click', e => {
            window.location.href = 'http://localhost/login?redirect=' + encodeURIComponent(document.location.pathname + document.location.search + document.location.hash) + '#login';
            e.preventDefault();
        });
        userSignUp.addEventListener('click', e => {
            window.location.href = 'http://localhost/login?redirect=' + encodeURIComponent(document.location.pathname + document.location.search + document.location.hash) + '#signup';
            e.preventDefault();
        });

        // testSleepAsync(3000);

        console.log('Rendering: главная навигационная панель первого типа');
    }

	connectedCallback() {
        if (!this.rendered) {
            this.render();
            this.rendered = true;
        }
    }

    static get observedAttributes() {
        return ['signed-in'];
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if (name === 'signed-in')
            this.headerLoadProfile();
    }

    headerLoadProfile() {
        if (sys.account) {
            const profileLink = document.createElement('a');
            profileLink.href = 'http://localhost/fans/' + sys.account.id;
            profileLink.className = '_link-user spectrum';

            const greetingBox = document.createElement('div');

            const photo = document.createElement('div');
            photo.className = '__picture skeleton';

            const content = document.createElement('div');
            content.className = '__text';

            const nameRow = document.createElement('div');

            const nameText = document.createElement('h4');
            nameText.textContent = sys.account.name ? sys.account.name : sys.account.email.split('@')[0];

            nameRow.appendChild(nameText);

            if (sys.account.isVerificated) {
                const verificationIcon = document.createElement('img');
                verificationIcon.src = '../images/icons/dark/verification_icon.png';
                verificationIcon.srcset = '../images/icons/dark/verification_icon.svg';
                verificationIcon.alt = '✔';
                verificationIcon.title = 'Верифицирован';
                verificationIcon.width = 15;
                verificationIcon.height = 15;

                nameRow.appendChild(verificationIcon);
            }
            
            if (sys.account.isModerator) {
                const moderationIcon = document.createElement('img');
                moderationIcon.src = '../images/icons/dark/moderation_icon.png';
                moderationIcon.srcset = '../images/icons/dark/moderation_icon.svg';
                moderationIcon.alt = '🔧';
                moderationIcon.title = 'Модератор';
                moderationIcon.width = 15;
                moderationIcon.height = 15;

                nameRow.appendChild(moderationIcon);
            }


            const emailRow = document.createElement('div');

            const emailText = document.createElement('p');
            emailText.textContent = sys.account.email;

            emailRow.appendChild(emailText);

            content.append(nameRow, emailRow);
            greetingBox.append(photo, content);


            const profileLinkText = document.createElement('p');
            profileLinkText.className = '__display-text';
            profileLinkText.textContent = 'Посмотреть учётную запись';

            profileLink.append(greetingBox, profileLinkText);



            const ul = document.createElement('ul');
            ul.className = '_actions';

            const exitAction = document.createElement('li');

            const exitActionLink = document.createElement('a');
            exitActionLink.href = '#';
            exitActionLink.textContent = 'Выйти';

            exitActionLink.addEventListener('click', e => {
                sysGetData('user/logout', 0).then((data) => {
                    if (data.error !== undefined) {
                        alert('Не удалось выйти из аккаунта');
                    } else
                        window.location.reload();
                });
                e.preventDefault();
            });

            exitAction.appendChild(exitActionLink);

            ul.append(exitAction);



            profileDropdown.replaceChildren(profileLink, ul);


            
            sysGetData('user/list?part=picture&mine=true').then((data) => {
                if (data.kind === 'users#account') {
                    const photoImage = document.createElement('img');
                    photoImage.alt = 'photo';
                    photoImage.width = 60;
                    photoImage.height = 60;

                    photoImage.addEventListener('load', () => {
                        if (data.picture.userPictureOriginal.inBytes)
                            profile.querySelector('img').src = data['picture']['userPictureOriginal'];
                        photo.appendChild(photoImage);
                    });
    
                    photoImage.addEventListener('error', () => {
                        console.warn('Главная навигационная панель первого типа: Не удалось отобразить изображение профиля');
                        photo.appendChild(photoImage);
                    });

                    if (data.picture.userPictureOriginal.inBytes)
                        photoImage.src = data['picture']['userPictureOriginal'];
                    else
                        photoImage.src = '../images/user_picture.jpg';
                } else
                    console.warn('Главная навигационная панель первого типа: Не был передан нужный тип объекта.');
            });
        }
    }
}