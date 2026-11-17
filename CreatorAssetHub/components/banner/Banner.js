// Creator Asset Hub(c) 2023. All rights reserved.
//
// The banner component loader file

export class BannerBuilder {
    url = 'http://' + window.location.host + '/components/banner/';
    items = [];

    addTitle = () => this.items.push(this.getResource('title/Title.html'));
    addSearch = () => this.items.push(this.getResource('search/Search.html'));
    
    getResource = (resource) => {
        return new Promise(async (resolve, reject) => {
            const response = await fetch(this.url + resource, {
                credentials: 'omit',
                headers: {
                    'Content-Type': 'text/html',
                },
            });
            
            const result = await response.text().catch(reason => console.log('Ошибка Promise: ', reason));

            if (response.ok)
                resolve(result);
            else
                console.log('Сервер вернул ошибку запроса (' + response.status + ' ' + response.statusText + ')');
        });
    };

    async loadBannerGrand() {
        const results = await Promise.all(this.items)
            .catch(reason => console.log('Ошибка Promise: ' + reason));

        const main = document.getElementsByTagName('main')[0];

        const div = document.createElement('div');
        div.innerHTML = Array.isArray(results) ? results.join('') : null;
        main.before(div);

        console.log('Загружен главный баннер');
    }
}