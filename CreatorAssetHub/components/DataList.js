// Creator Asset Hub(c) 2023. All rights reserved.
//
// The datalist component loader file



export const state = {
    usersDataList: [],
};



export class UsersDataList extends HTMLElement {
    render() {
        if (state.usersDataList[this.id]) {
            const list = state.usersDataList[this.id]['list'];
            if (list.length) {
                const created = [];
                list.forEach(element => {
                

                    created.push(li);
                });

                this.replaceChildren(...created);
            } else {
                this.innerHTML = `
                <div class="_loading">
                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; background: none; display: block; shape-rendering: auto;" width="20" height="20" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">
                        <circle cx="50" cy="50" fill="none" stroke="rgba(112, 112, 112, 0.6)" stroke-width="20" r="35" stroke-dasharray="164.93361431346415 56.97787143782138">
                            <animateTransform attributeName="transform" type="rotate" repeatCount="indefinite" dur="1s" values="0 50 50;360 50 50" keyTimes="0;1"/>
                        </circle>
                    </svg>
                </div>
                `;
            }

            console.log('Rendering UsersDataList');
            return;
        }

        console.log('UsersDataList render fail');
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