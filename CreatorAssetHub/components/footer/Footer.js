// Creator Asset Hub(c) 2023. All rights reserved.
//
// The footer component loader file

export async function loadFooterGrand() {
    const url = 'http://' + window.location.host + '/components/footer/footerGrand/';

    const response = await fetch(url + 'FooterGrand.html', {
        credentials: 'omit',
        headers: {
            'Content-Type': 'text/html', // можно удалить
        },
    });

    const data = await response.text().catch(reason => console.log('Ошибка Promise: ', reason));

    if (response.ok) {
        const styles = document.createElement('link');
        styles.rel = 'stylesheet';
        styles.type = 'text/css';
        styles.href = url + 'index.css';
        
        document.head.appendChild(styles);

        await new Promise(async (resolve, reject) => {
            styles.onload = resolve;
        });

        const footer = document.getElementsByTagName('footer')[0];
        footer.innerHTML = data;

        console.log('Загружен главный подвал первого типа');
    } else
        console.log('Сервер вернул ошибку запроса (' + response.status + ' ' + response.statusText + ')');
}