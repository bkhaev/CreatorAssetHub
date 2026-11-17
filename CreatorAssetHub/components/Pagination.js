// Creator Asset Hub(c) 2023. All rights reserved.
//
// The taglist component loader file



export const state = {
    paginationGrand: [],
};



export class PaginationGrand extends HTMLElement {
    render() {
        const data = state.paginationGrand[this.id];
        const created = [];
        
        if (data) {
            const li = (number) => {
                const li = document.createElement('li');
                const a = document.createElement('a');

                a.addEventListener('click', e => {
                    if (data.action)
                        data.action(number);

                    e.preventDefault();
                });

                if (number === data.current) {
                    a.className = '_btn s-active';
                    a.href = '#';
                }
                else {
                    a.className = '_btn';
                    a.href = data.url(number);
                }

                a.textContent = number;
                li.appendChild(a);
                return li;
            }

            const separator = () => {
                const li = document.createElement('li');
                li.textContent = '...';
                return li;
            }

            created[created.length] = li(1);

            if (data.current > 4)
                created[created.length] = separator();

            let i = data.current - 2;
            if (i < 2) i = 2;
            let len = i + 4;
            for (; i <= len; i++) {
                if (i > data.total)
                    break;
                created[created.length] = li(i);
            }

            if (i + 1 === data.total)
                created[created.length] = li(data.total);
            else if (data.total > i) {
                created[created.length] = separator();
                created[created.length] = li(data.total);
            }

            
            const nav = document.createElement('nav');
            nav.ariaLabel = 'pagination';

            const ul = document.createElement('ul');

            ul.append(...created);
            nav.appendChild(ul);
            this.replaceChildren(nav);

            console.log('Rendering Pagination');
            return;
        }

        console.log('Pagination render fail');
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
}