// Creator Asset Hub(c) 2023. All rights reserved.
//
// The helper functions library



// Изменяет параметры в переданном URL. Возвращает обновлённую ссылку.
// Если изменений по итогу нет, возвращает якорь по-умолчанию
// Для красивой ссылки:
//  - если по итогу параметров не остаётся, знак ? должен удаляться
//  - если по итогу у некоторых параметров нет значения, знаки = рядом с ними не должно быть
export function libChangeUrlParameters(urlstring, changes) {
    const url = new URL(urlstring);
    const parameters = url.searchParams;
    for (const key in changes) {
        if (changes[key] === null)
            parameters.delete(key);
        else
            parameters.set(key, changes[key]);
    }
    
    const updatedUrl = url.protocol + '//' + url.hostname + url.pathname + (parameters.toString() ? '?' : '') + parameters.toString().replace(/=(?=&|$)/gm, '') + url.hash;
    if (urlstring === updatedUrl)
        return '#';
    else
        return updatedUrl;
}



// Удаляет элемент из массива по значению
export function libRemoveFromArray(array, ...values) {
    values.forEach(value => {
        const phraseIndex = array.indexOf(value);
        if (phraseIndex !== -1)
            array.splice(phraseIndex, 1);
    });
};



// Преобразует строку в виде HH:MM:SS / MM:SS / SS в секунды
// Возвращает false, если передано невалидное значение
export function libHmsToSeconds(time) {
    const p = time.split(':');
    let s = 0, m = 1;

    while (p.length > 0) {
        const cur = p.pop();
        if (!cur || isNaN(cur))
            return false;

        s += m * parseInt(cur, 10);
        m *= 60;
    }

    return s;
}



// Преобразует секунды во время в виде HH:MM:SS / MM:SS
export function libSecondsToHms(seconds) {
    const time = new Date(seconds * 1000).toISOString();
    return seconds < 3600 ? time.substring(14, 19) : time.substring(11, 19);
}



// Возвращает форматированную дату с названием месяца вместо числа
export function libNamedMonthDate(date) {
    date = new Date(date)
    const months = ['января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'];
    return date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear();
}



// Возвращает имя домена URL-ссылки (напр. https://www.vk.com/bulat: vk.com)
// Если tld = false, домен верхнего уровня также будет удалён
export function libExtractHostName(url, tld = false) {
	let hostname;

	// убираем http, ftp и т.п.
	if (url.indexOf("://") > -1)
		hostname = url.split('/')[2];
	else
		hostname = url.split('/')[0];

	// убираем порт
	hostname = hostname.split(':')[0];

	// убираем параметры
	hostname = hostname.split('?')[0];

	// убираем www
	if (!tld) {
	  let hostnames = hostname.split('.');
	  hostname = hostnames[hostnames.length-2] + '.' + hostnames[hostnames.length-1];
	}

	return hostname;
}