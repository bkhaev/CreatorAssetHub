// Creator Asset Hub(c) 2023. All rights reserved.
//
// The Materials Search module JS-script file

import { tryLogin } from '../../js/index.js';
import { loadHeaderGrand } from '../../components/HeaderNavigator.js';
import { loadFooterGrand } from '../../components/Footer.js';



customElements.define('bf-header-grand', loadHeaderGrand, {extends: 'nav'});
customElements.define('bf-footer-grand', loadFooterGrand, {extends: 'footer'});



tryLogin().then(result => {
    if (result) globalnav.setAttribute('signed-in', '');
});