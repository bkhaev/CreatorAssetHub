// Creator Asset Hub(c) 2023. All rights reserved.
//
// The input text grand component loader file



export const state = {
    inputTextGrand: [],
};



export class InputTextGrand extends HTMLElement {
    render() {
        const elements = JSON.parse(this.getAttribute('elements').replace(/&quot;/ig,'"'))['elements'];
        const created = [];

        elements.forEach(element => {
            switch (element) {
                case 'search':
                    const search = document.createElement('div');
                    search.className = '_input-text s-primary-input';

                    const searchInput = document.createElement('input');
                    searchInput.type = 'search';
                    searchInput.autocomplete = 'off';
                    searchInput.placeholder = 'Поиск по фразе';
                    searchInput.title = 'Поиск по фразе';

                    // searchInput.addEventListener('focus', () => this.classList.add('s-focus'));
                    // searchInput.addEventListener('blur', () => this.classList.remove('s-focus'));

                    search.appendChild(searchInput);
                    created.push(search);
                    break;
                case 'btn':
                    const btn = document.createElement('div');
                    btn.className = '_btn';

                    const btnButton = document.createElement('button');
                    btnButton.type = 'submit';
                    
                    const btnImg = document.createElement('img');
                    btnImg.src = '../images/icons/black/40pt/magnifier.png';
                    btnImg.srcset = '../images/icons/black/40pt/magnifier.svg';
                    btnImg.alt = '🔍';

                    btnButton.appendChild(btnImg);
                    btn.appendChild(btnButton);
                    created.push(btn);
            }
        });
    
        console.log('Rerendering InputTextGrand');
        this.replaceChildren(...created);
    }

	connectedCallback() {
        if (!this.rendered && this.getAttribute('elements')) {
            this.render();
            this.rendered = true;
        }
    }

    static get observedAttributes() {
        return ['elements'];
    }

    attributeChangedCallback(name, oldValue, newValue) {
        this.render();
    }
}