// Creator Asset Hub(c) 2023. All rights reserved.
//
// The main initializing JS-script file

import { testSleep } from '../../js/tests.js';
import { sysErrorHandler, sysLogControl, sysPromiseRejectionHandler } from './sqr_library.js';

const baseApiUrl = 'http://' + window.location.host + '/api/'; // http://localhost/api/

export const sys = {
    header: document.querySelector('header'),
	account: null,
};



////////////////////////////////////
// A------initializing app------A //
////////////////////////////////////

window.addEventListener('error', sysErrorHandler);
window.addEventListener('unhandledrejection', sysPromiseRejectionHandler);
// console.log = sysLogControl;

if (!self.fetch) {
    document.write('Ваш браузер не поддерживает технологию fetch');
}

console.log('Start initializing was done.');

////////////////////////////////////
// B------initializing app------B //
////////////////////////////////////



export const tryLogin = () => new Promise((resolve, reject) => sysGetData('user/list?part=snippet&mine=true', 0).then((data) => {
    if (data.kind === 'users#account') {
        console.log('Данные от аккаунта получены');

        sys.account = {
            id: data['id'],
            email: data['snippet']['email'],
            name: data['snippet']['name'],
            isVerificated: data['snippet']['isUserVerificated'],
            isModerator: data['snippet']['isUserModerator'],
        };
        resolve(true);
    }

    resolve(false);
}).catch(reason => console.log('Ошибка Promise: ' + reason)));



export async function sysGetData(url, strictMode = 1) {
    const response = await fetch(baseApiUrl + url, {
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json', // можно удалить
        },
    });

    console.log(baseApiUrl + url);
    
    return await getResponseResult(response, strictMode);
}

export async function sysPostData(url, data, strictMode = 1) {
    const response = await fetch(baseApiUrl + url, {
        method: 'post',
        body: JSON.stringify(data),
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json', // можно удалить
        },
    });
    
    console.log(baseApiUrl + url);
    
    return await getResponseResult(response, strictMode);
}

export async function sysPutData(url, data, strictMode = 1) {
    const response = await fetch(baseApiUrl + url, {
        method: 'put',
        body: JSON.stringify(data),
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json', // можно удалить
        },
    });
    
    console.log(baseApiUrl + url);
    
    return await getResponseResult(response, strictMode);
}

export async function sysDeleteData(url, strictMode = 1) {
    const response = await fetch(baseApiUrl + url, {
        method: 'delete',
        credentials: 'same-origin',
        headers: {
            'Content-Type': 'application/json', // можно удалить
        },
    });
    
    console.log(baseApiUrl + url);
    
    return await getResponseResult(response, strictMode);
}



export async function sysSaveResourceInPlaylist(resourceKind, resourceId, playlist) {
    const data = {
        snippet: {
            position: 0,
            resourceId: {
                kind: resourceKind,
            },
            note: '',
        },
    };

    if (typeof(playlist) === 'string')
        data.snippet.playlistTitle = playlist;
    else
        data.snippet.playlistId = playlist;

    if (resourceKind === 'phrases#phrase')
        data.snippet.resourceId.phraseId = resourceId;
    else if (resourceKind === 'materials#image')
        data.snippet.resourceId.imageId = resourceId;
    else if (resourceKind === 'materials#video')
        data.snippet.resourceId.videoId = resourceId;
    else if (resourceKind === 'materials#music')
        data.snippet.resourceId.musicId = resourceId;
    else if (resourceKind === 'materials#sound')
        data.snippet.resourceId.soundId = resourceId;
    

    const result = await sysPostData('playlistItems', data, 0);

    if (result.kind === 'playlists#playlistItem')
        return result.id;
    else
        return false;
}

export async function sysDeleteResourceFromPlaylist(resourceId) {
    const result = await sysDeleteData('playlistItems?id=' + resourceId, 0);

    if (result.isOk)
        return true;
}



export function sysHeaderToSticky(isHideOnScroll = false) {
    sys.header.classList.add('s-sticky');
    console.log('Изменён Header: прикреплён к холсту (скрывающийся)');

    if (isHideOnScroll === true) {
        document.prevScroll = window.pageYOffset;
        window.addEventListener('scroll', onScrollHeader);
    } else if (isHideOnScroll === false) {
        window.removeEventListener('scroll', onScrollHeader);
    }
}

export function sysHeaderToStatic() {
    sys.header.classList.remove('s-sticky');
    console.log('Изменён Header: откреплён от холста');

    window.removeEventListener('scroll', onScrollHeader);
}



// Получает логотип переданного сайта
// Если логотип данного сайта отсутствует, вернётся иконка-заглушка
export function sysGetWebSiteIcon(url) {
	const social = extractHostname(url);
	switch(social) {

		// social web-sites:

		case "youtube.com":
			return 'http://localhost/images/icons/social/youtube.com.svg';
		case "tiktok.com":
			return 'http://localhost/images/icons/social/tiktok.com.svg';
		case "vk.com":
			return 'http://localhost/images/icons/social/vk.com.svg';
		case "t.me":
			return 'http://localhost/images/icons/social/telegram.me.svg';
		case "instagram.com":
			return 'http://localhost/images/icons/social/instagram.com.svg';
		case "discord.com":
			return 'http://localhost/images/icons/social/discord.com.svg';
		case "discord.gg":
			return 'http://localhost/images/icons/social/discord.com.svg';
		case "twitch.tv":
			return 'http://localhost/images/icons/social/twitch.tv.svg';
		case "trovo.live":
			return 'http://localhost/images/icons/social/trovo.live.png';
		case "steamcommunity.com":
			return 'http://localhost/images/icons/social/steam.com.svg';
		case "facebook.com":
			return 'http://localhost/images/icons/social/facebook.com.svg';
		case "twitter.com":
			return 'http://localhost/images/icons/social/twitter.com.svg';
		case "pinterest.com":
			return 'http://localhost/images/icons/social/pinterest.com.svg';
		case "ok.ru":
			return 'http://localhost/images/icons/social/ok.ru.svg';
        
		// other web-sites:

		case "behance.net":
			return 'http://localhost/images/icons/social/behance.net.svg';
		case "donationalerts.com":
			return 'http://localhost/images/icons/social/donationalerts.com.svg';
		case "google.com":
			return 'http://localhost/images/icons/social/google.com.svg';
		case "linkedin.com":
			return 'http://localhost/images/icons/social/linkedin.com.svg';
		case "spotify.com":
			return 'http://localhost/images/icons/social/spotify.com.svg';
		default:
			return 'http://localhost/images/icons/social/globe1.svg';
	}
}



////////////////////////////////////
// A------helper functions------A //
////////////////////////////////////

const onScrollHeader = function () {
    const modals = sys.header.querySelectorAll('.s-expanded, s-hidden');

	const currentScroll = window.pageYOffset;
    if (currentScroll > document.prevScroll + 40) {
        sys.header.classList.add('s-hidden');
        document.prevScroll = currentScroll;
    }
    if (currentScroll < document.prevScroll - 40) {
        sys.header.classList.remove('s-hidden');
        document.prevScroll = currentScroll;

        modals.forEach(modal => {
            modal.classList.remove('s-expanded');
            modal.classList.add('s-hidden');
        });
    }
}


// Обработка ответа от сервера
// strictMode - уровень строгого режим:
//  0 - не строгий режим: возвращает ответ вместе с ошибкой
//  1 - по умолчанию: возвращает ответ, если нет ошибок
//  2 - строгий режим: если есть ошибка, предупреждает пользователя
const getResponseResult = async function (response, strictMode) {
    try {
        let data = null;
        const contentType = response.headers.get("content-type");
        if (contentType && contentType.indexOf("application/json") !== -1)
            data = await response.json().catch(reason => console.log('Ошибка Promise: ', reason));
        
        if (strictMode === 2 && data && data['error'] !== undefined)
            console.log('Сервер вернул ошибку запроса: ' + data['error']['code'] + ', ' + data['error']['message']); // окно предупреждения об ошибке, рекомендуется перезагрузить страницу
        else if (response.ok) {
            if (data) {
                console.log('Сервер вернул тип: ' + data.kind);
                return data;
            } else {
                console.log('Сервер вернул ответ');
                return {
                    'isOk': response.ok,
                    'status': response.status,
                };
            }
        } else {
            if (data)
                if (strictMode === 0 && data && data['error'] !== undefined)
                    return data;
                else if (data && data['error'] !== undefined) {
                    console.log('Сервер вернул ошибку запроса: ' + data['error']['code'] + ', ' + data['error']['message']);
                    return {
                        'isOk': response.ok,
                        'status': response.status,
                    };
                }
                else {
                    console.log('Сервер вернул ошибку запроса (' + response.status + ' ' + response.statusText + ')');
                    return {
                        'isOk': response.ok,
                        'status': response.status,
                    };
                }
        }
    } catch(e) {
        console.log('Ошибка запроса: ' + e);
    }
}

////////////////////////////////////
// B------helper functions------B //
////////////////////////////////////