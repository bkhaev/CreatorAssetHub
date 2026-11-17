// Creator Asset Hub(c) 2023. All rights reserved.
//
// The context menu component loader file



export const state = {
    contextMenuGrand: [],
};



export class ContextMenuGrand extends HTMLElement {
    render() {
        const optionsList = state.contextMenuGrand[this.id]['list'];

        const ul = document.createElement('ul');
        const options = [];
        optionsList.forEach((option, index) => {
            const li = document.createElement('li');

            let element = document.createElement('a');;
            if (option.url) {
                element = document.createElement('a');
                element.className = 'li_content';
                element.href = option.url;

                element.addEventListener('click', e => {
                    if (option.clickAction)
                        option.clickAction();

                    this.closeMenu();
                    e.preventDefault();
                });

                const linkText = document.createElement('span');
                linkText.textContent = option.text;

                element.appendChild(linkText);
            } else if (option.isChecked !== undefined) {
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.id = 'option_' + this.id + '_' + index;
                checkbox.checked = option.isChecked;

                checkbox.addEventListener('change', () => {
                    if (option.clickAction) {
                        option.clickAction(checkbox.checked);
                        option.isChecked = checkbox.checked;
                    }

                    this.closeMenu();
                });
                
                element = document.createElement('label');
                element.className = 'li_content';
                element.htmlFor = checkbox.id;
                element.textContent = 'Сохранить в закладки';

                li.appendChild(checkbox);
            } else if (option.clickAction) {
                element = document.createElement('button');
                element.className = 'li_content';
                element.type = 'button';

                element.addEventListener('click', () => {
                    option.clickAction();

                    this.closeMenu();
                });

                const btnText = document.createElement('span');
                btnText.textContent = option.text;
                
                element.appendChild(btnText);
            }
            
            li.appendChild(element);

            if (option.icon) {
                const optionIcon = document.createElement('div');
                optionIcon.className = 'li_icon ' + option.icon;

                element.appendChild(optionIcon);
            }

            options.push(li);
        });

        ul.append(...options);

        console.log('Rendering ContextMenuGrand');
        this.replaceChildren(ul);
        return;
    }

	connectedCallback() {
        const data = state.contextMenuGrand[this.id];
        if (data) {
            if (data.closeOnResize)
                window.addEventListener('resize', () => this.closeMenu(), false);
            if (data.closeOnEsc)
                window.addEventListener('keyup', e => { if (e.key === 'Escape') this.closeMenu(); }, false);
            
            if (data.closeOnOutClick) {
                document.addEventListener('click', e => {
                    const isContextMenu = e.target == this || this.contains(e.target);
                    if (!isContextMenu && this.expanded)
                        this.closeMenu();
                });
            }

            if (data.preventDocumentScroll)
                window.addEventListener('wheel', e => {
                    if (this.expanded)
                        e.preventDefault();
                }, { passive: false });
        }
    }

    static get observedAttributes() {
        return ['updated'];
    }

    attributeChangedCallback(name, oldValue, newValue) {
        if (name === 'updated' && state.contextMenuGrand[this.id]) {
            this.render();
            this.rendered = true;
        }
    }

    showMenu(e) {
        console.log('Меню раскрыто');
        Object.values(state).forEach(menus => {
            Object.values(menus).forEach(menu => {
                if (menu.element !== this)
                    menu.element.closeMenu();
            });
        });

        this.updateMenuPosition(e);
        this.classList.add('s-expanded');
        this.expanded = true;
    }

    closeMenu() {
        console.log('Меню закрыто');
        this.classList.remove('s-expanded');
        this.textContent = '';
        this.expanded = false;
    }

    updateMenuPosition(e) {
        const clickCoords = getPosition(e);
        const menuWidth = this.offsetWidth + 4;
        const menuHeight = this.offsetHeight + 4;

        const windowWidth = window.innerWidth;
        const windowHeight = window.innerHeight;

        if ((windowWidth - clickCoords.x) < menuWidth)
            this.style.left = windowWidth - menuWidth + "px";
        else
            this.style.left = clickCoords.x + "px";

        if ((windowHeight - clickCoords.y) < menuHeight)
            this.style.top = windowHeight - menuHeight + "px";
        else
            this.style.top = clickCoords.y + "px";
    }
}



/**
 * Get's exact position of event.
 * 
 * @param {Object} e The event passed in
 * @return {Object} Returns the x and y position
 */
function getPosition(e) {
    var posx = 0;
    var posy = 0;

    if (!e) var e = window.event;
    
    if (e.pageX || e.pageY) {
        posx = e.pageX;
        posy = e.pageY;
    } else if (e.clientX || e.clientY) {
        posx = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
        posy = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
    }

    return {
        x: posx,
        y: posy
    }
}