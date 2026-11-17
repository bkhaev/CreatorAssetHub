// Creator Asset Hub(c) 2023. All rights reserved.
//
// The taglist component loader file



export const state = {
    tagList: [],
};



export class TagList extends HTMLUListElement {
    render() {
        // const elements = JSON.parse(this.getAttribute('elements').replace(/&quot;/ig,'"'))['elements'];
        const created = [];
        
        if (state.tagList[this.id]) {
            const list = state.tagList[this.id]['list'];
            list.forEach(element => {
                const li = document.createElement('li');
                li.className = 's-' + element.type;

                if (element.bodyAction && !element.bodyAction.link && element.bodyAction.action) {
                    li.setAttribute('action', '');
                    li.addEventListener('click', () => {
                        if (li.preventEditEvent) {
                            li.preventEditEvent = false;
                            return;
                        }

                        element.bodyAction.action();
                        if (element.bodyAction.isRemove)
                            li.remove();
                    });
                }
                
                const content = document.createElement(element.bodyAction && element.bodyAction.link ? 'a' : 'div');
                content.className = "_content";

                if (element.bodyAction && element.bodyAction.link) {
                    li.setAttribute('action', '');
                    content.href = element.bodyAction.link;

                    if (element.bodyAction.action) {
                        content.addEventListener('click', e => {
                            element.bodyAction.action();
                
                            e.preventDefault();
                        });
                    }
                }

                const text = document.createElement('span');
                text.textContent = element.text;

                content.append(text);

                li.appendChild(content);


                if (element.closeAction) {
                    const closeBtn = document.createElement('div');
                    closeBtn.className = '_close-btn';
                    closeBtn.role = 'button';
                    closeBtn.title = 'Очистить';
        
                    closeBtn.addEventListener('click', () => {
                        element.closeAction();
                        li.remove();
                        li.preventEditEvent = true;
                    });
        
                    li.appendChild(closeBtn);
                }

                created.push(li);
            });

            console.log('Rendering TagList');
            this.replaceChildren(...created);
            return;
        }

        console.log('TagList render fail');
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