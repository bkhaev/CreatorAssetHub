// Creator Asset Hub(c) 2023. All rights reserved.
//
// The footer component loader file



export class loadFooterGrand extends HTMLElement {
    constructor() {
		super();
	}
	
	connectedCallback() {
		this.innerHTML = `
		<div class="footer-grand spectrum">
            <div>
                <div>
                    <p class="spectrum">Служебные</p>
                    <ul>
                        <li><a href="#">О Creator Asset Hub</a></li>
                        <li><a href="#">Связаться с нами</a></li>
                        <li><a href="#">Пожаловаться</a></li>
                        <li><a href="#">Условия использования</a></li>
                        <li><a href="#">Политика конфиденциальности</a></li>
                    </ul>
                </div>
                <div>
                    <p class="spectrum">Контент</p>
                    <ul>
                        <li><a href="#">Фразы</a></li>
                        <li><a href="#">Футажи</a></li>
                        <li><a href="#">Музыка</a></li>
                        <li><a href="#">Обучение</a></li>
                    </ul>
                </div>
                <div>
                    <p class="spectrum">Фразы</p>
                    <ul>
                        <li><a href="#">[скрыто]</a></li>
                        <li><a href="#">[скрыто]</a></li>
                        <li><a href="#">[скрыто]</a></li>
                    </ul>
                </div>
                <div>
                    <p class="spectrum">&nbsp;</p>
                    <ul>
                        <li><a href="#">[скрыто]</a></li>
                        <li><a href="#">Другие авторы</a></li>
                    </ul>
                </div>
                <div>
                    <p class="spectrum">&nbsp;</p>
                    <ul>
                        <li><a href="#">Статистика сайта</a></li>
                        <li><a href="#">Вход/регистрация</a></li>
                    </ul>
                </div>
            </div>
            <div class="_copyright spectrum">
                <span>Copyright © 2022 Creator Asset Hub. All rights reserved.</span>
                <span>Made and designed by [скрыто]</span>
            </div>
        </div>
    	`;

        console.log('Rendering: главный подвал первого типа');
	}
}



export class loadFooterMinor extends HTMLElement {
    constructor() {
		super();
	}
	
	connectedCallback() {
		this.innerHTML = `
		<div class="footer-minor spectrum">
            <div><a href="#">Политика конфиденциальности</a></div>
            <div><a href="#">Условия использования</a></div>
        </div>
    	`;

        console.log('Rendering: главный подвал первого типа');
	}
}