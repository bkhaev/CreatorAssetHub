// Creator Asset Hub(c) 2023. All rights reserved.
//
// The Phrases Search modal JS-script file

import { testSleep, testSleepAsync } from '../js/tests.js';
import { tryLogin, sysPostData } from '../js/index.js';
import { libChangeUrlParameters, libRemoveFromArray } from '../js/bf_library.js';
import { InputTextGrand, state as inputTextGrandState } from '../components/InputTextGrand.js';



customElements.define('bf-input-text-grand', InputTextGrand);



let pageState = {
    login: {
        email: '',
        password: '',
    },
    registration: {
        email: '',
        name: '',
        password: '',
    },
    resetPassword: {
        email: '',
    },
};

let isReg = false;


(() => {
    // beta

    const resetForm = () => {
        logCommonStatus.textContent = logEmailStatus.textContent = regNameStatus.textContent = logPasswordStatus.textContent = '';
        logEmailInp.value = regNameInp.value = logPasswordInp.value = '';
    };

    const toLogForm = () => {
        isReg = false;

        document.querySelector('.local-card h1').textContent = 'Вход';

        const a = document.createElement('a');
        a.className = 'cardSwitcher';
        a.href = '#signup';
        a.textContent = 'Создать учётную запись';

        a.addEventListener('click', e => {
            toRegForm();
            // e.preventDefault();
        });

        document.querySelector('.local-card p').replaceChildren('Нет учётной записи? ', a);

        resetForm();

        logPasswordInp.removeAttribute('minLength');
        logPasswordInp.removeAttribute('maxLength');
        logPasswordInp.removeAttribute('pattern');

        regNameBox.style.display = 'none';
    }

    const toRegForm = () => {
        isReg = true;

        document.querySelector('.local-card h1').textContent = 'Регистрация';

        const a = document.createElement('a');
        a.className = 'cardSwitcher';
        a.href = '#login';
        a.textContent = 'Войти';

        a.addEventListener('click', e => {
            toLogForm();
            // e.preventDefault();
        });

        document.querySelector('.local-card p').replaceChildren('Есть учётная запись? ', a);

        resetForm();

        logPasswordInp.minLength = "8";
        logPasswordInp.maxLength = "30";
        logPasswordInp.pattern = "^[a-zA-Z0-9`~!@#$%^&*()_+-={}|:;<>?,.]+$";

        regNameBox.style.display = 'block';
    }
    
    if (document.location.hash === '#signup') {
        logEmailInp.autocomplete = 'off';
        logPasswordInp.autocomplete = 'off';
        toRegForm();
        setTimeout(() => resetForm(), 1000);
    } else {
        document.querySelector('.local-card .cardSwitcher').addEventListener('click', e => {
            toRegForm();
            e.preventDefault();
        });
    }

    
})();



(() => {
    // Form

    Form.addEventListener('submit', e => {
        if (formValidate()) {
            preloaderArea.classList.remove('s-hidden');
            logCommonStatus.textContent = '';

            const data = {
                snippet: {
                    email: pageState.login.email,
                    password: pageState.login.password,
                },
            };

            let url = 'user/login';

            if (isReg) {
                data.snippet.name = pageState.registration.name;
                url = 'user/signup';
            }

            sysPostData(url, data, 0).then((data) => {
                if (data.kind === 'users#account') {
                    const parameters = new URLSearchParams(window.location.search);
                    const redirect = parameters.get('redirect');
                    if (redirect && redirect.substring(0, 7) === 'http://')         // ВРЕМЕННО ДЛЯ REACT
                        window.location.href = redirect;                            // ВРЕМЕННО ДЛЯ REACT
                    else if (redirect)
                        window.location.href = 'http://localhost' + redirect;
                    else
                        window.location.href = 'http://localhost/phrases/search';
                } else if (data && data.error && data.error.code === '404') {
                    logCommonStatus.classList.add('s-invalid');
                    logCommonStatus.textContent = 'Неверный логин или пароль';
                    logPasswordInp.value = '';
                    logEmailInp.select();
                } else if (data && data.error && data.error.code === '409') {
                    logCommonStatus.classList.add('s-invalid');
                    logCommonStatus.textContent = 'Такой пользователь уже существует';
                    logEmailInp.select();
                } else
                    console.warn('Не был передан нужный тип объекта.');

                preloaderArea.classList.add('s-hidden');
            }).catch(reason => console.log('Ошибка Promise: ' + reason));

            console.log('Данные отправлены');
        } else {
            Form.dataset.validate = true;
            Array.prototype.forEach.call(app.querySelectorAll('#Form [type="submit"]'), element => element.disabled = true);
            formValidate();
        }

        e.preventDefault();
    });
})();



(() => {
    // logEmailInp

    logEmailInp.addEventListener('input', () => {
        pageState.login.email = logEmailInp.value.replace(/^ +| +$|( ) +/g,"$1");
        formValidate();
	});

    logPasswordInp.addEventListener('input', () => {
        pageState.login.password = logPasswordInp.value.replace(/^ +| +$|( ) +/g,"$1");
        formValidate();
	});

    regNameInp.addEventListener('input', () => {
        pageState.registration.name = regNameInp.value.replace(/^ +| +$|( ) +/g,"$1");
        formValidate();
	});
})();



(async () => {
    // начальные действия
    
    document.querySelector('body').style.setProperty('background-image', 'url(\'login/background/login-back3.jpg\')');

    const isAuthorized = await tryLogin();
    if (isAuthorized)
        window.location.href = 'http://localhost/phrases/search';
    else {
        preloaderArea.classList.add('s-hidden');
        Array.prototype.forEach.call(app.querySelectorAll('#Form [type="submit"]'), element => element.disabled = false);
    }

})();




///////////////// FUNCTIONS /////////////////



// Валидация формы (разрешает/запрещает отправку данных)
function formValidate() {
    let isOk = true, isValidate = Form.dataset.validate;


    if (logEmailInp.validity.valid) {
        logEmailBox.classList.remove('s-invalid');
        if (isValidate)
            logEmailStatus.textContent = '';
    } else {
        isOk = false;
        logEmailBox.classList.add('s-invalid');

        if (isValidate) {
            if (logEmailInp.validity.typeMismatch)
                logEmailStatus.textContent = 'Введите адрес в формате ...@domain.tld';
            else if (logEmailInp.validity.tooShort)
                logEmailStatus.textContent = `Количество символов должно быть больше ${logEmailInp.minLength - 1}. Вы ввели ${logEmailInp.value.length}.`;
        }
    }


    if (logPasswordInp.validity.valid) {
        if (isReg && !passCheck()) {
            isOk = false;
            logPasswordBox.classList.add('s-invalid');
        } else {
            logPasswordBox.classList.remove('s-invalid');
            if (isValidate)
                logPasswordStatus.textContent = '';
        }
    } else {
        isOk = false;
        logPasswordBox.classList.add('s-invalid');

        if (isValidate) {
            if (logPasswordInp.validity.patternMismatch) {
                if (logPasswordInp.value.includes(' '))
                    logPasswordStatus.textContent = 'Не используйте пробелы.';
                else
                    logPasswordStatus.textContent = 'Используйте только латинские буквы!';
            } else if (logPasswordInp.validity.tooShort)
                logPasswordStatus.textContent = `Пароль должен содержать как минимум ${logPasswordInp.minLength} символов. Вы ввели ${logPasswordInp.value.length}.`;
            else if (logPasswordInp.validity.tooLong) {
                let sym, excess = logPasswordInp.value.length - logPasswordInp.maxLength;
                if (excess == 1) sym = excess + "символа";
                else if (excess > 1 && excess < 5) sym = excess + "символа";
                else sym = excess + "символов";
                logPasswordStatus.textContent = `Пароль слишком длинный. Уберите ${sym}.`;
            }
        }
    }


    if (isValidate) {
        if (isOk)
            Array.prototype.forEach.call(app.querySelectorAll('#Form [type="submit"]'), element => element.disabled = false);
        else
            Array.prototype.forEach.call(app.querySelectorAll('#Form [type="submit"]'), element => element.disabled = true);
    }

    return isOk;
}




function passCheck() {
    let pass = logPasswordInp.value, lpass = pass.length;
    let lower = false, upper = false, digit = false;

    for (let i = 0; i < lpass; i++) {
        if (pass[i] >= '0' && pass[i] <= '9')
            digit = true;
        else {
            if (pass[i] === pass[i].toLowerCase())
                lower = true;
            if (pass[i] === pass[i].toUpperCase())
                upper = true;
        }

        if (digit && upper && lower)
            return true;
    }

    if (!(lower) || !(upper) || !(digit)) {
        if (!(lower))
            logPasswordStatus.textContent = 'Пароль должен содержать строчную букву';
        else if (!(upper))
            logPasswordStatus.textContent = 'Пароль должен содержать большую букву';
        else if (!(digit))
            logPasswordStatus.textContent = 'Пароль должен содержать цифру';
        return false;
    }
}