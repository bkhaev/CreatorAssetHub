// Creator Asset Hub(c) 2023. All rights reserved.
//
// Sequrity functions JS-library

// кастомный лог-сбор
console.stdlog = console.log.bind(console);
console.logs = [];
// кастомный лог-сбор

// Обработчик кастомного логирования
export function sysLogControl() {
    const timeOffset = +3;
	let date = new Date(new Date().getTime() + timeOffset * 3600 * 1000).toUTCString().replace( / GMT$/, "" );

    console.logs.push(date.replace(/([^\d]*)(\d*)([^\d]*)(\d*)/, (match, p1, p2, p3, p4, timeOffset, string) => {
		return [p2, p4.slice(5)].join('д.');
	}) + " | " + Array.from(arguments));
    console.stdlog.apply(console, arguments);
}


// Глобальный обработчик ошибок // http-запросы с кодом состояния 400 не вызывают этот метод, так что всё ок.
export function sysErrorHandler(message, source, lineno, colno) {
    console.log('Произошла ошибка (' + new Date() + ')');
    fetch('http://' + window.location.host + '/logger/error-log.php', {
        credentials: 'same-origin',
        method: 'post',
        body: JSON.stringify({message, source, lineno, colno}),
    }).then(response => {
        if (response.status === 201)
            console.log('Отчёт об ошибке отправлен');
        else if (!response.ok)
            console.log('Сервер вернул ошибку при отправке отчёта (' + response.status + ' ' + response.statusText + ')');
        else
            console.log('Отчёт не был создан');
    }).catch(error => console.log('Ошибка при отправке отчёта: ' + error));
}

// Глобальный обработчик Promise-исключений // ловит только reject промисы, но не ошибки :(
export function sysPromiseRejectionHandler(event) {
    console.warn('Rejected promise: ' + event.promise + ' | ' + event.reason);
    // произошла ошибка загрузки, рекомендуем перезагрузить страницу...
}