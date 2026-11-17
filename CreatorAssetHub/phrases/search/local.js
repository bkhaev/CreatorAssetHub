// Creator Asset Hub(c) 2023. All rights reserved.
//
// The Phrases Search modal JS-script file

import { testSleep, testSleepAsync } from '../../js/tests.js';
import { sys, tryLogin, sysHeaderToSticky, sysGetData } from '../../js/index.js';
import { libChangeUrlParameters, libRemoveFromArray } from '../../js/bf_library.js';
import { loadHeaderGrand } from '../../components/HeaderNavigator.js';
import { loadFooterGrand } from '../../components/Footer.js';
// import { BannerBuilder } from '../../components/banner/Banner.js';
import { InputTextGrand, state as inputTextGrandState } from '../../components/InputTextGrand.js';
import { TagList, state as tagListState } from '../../components/TagList.js';
import { PhraseItemGrand, state as phraseItemState } from '../../components/PhraseItem.js';
import { PaginationGrand, state as paginationState } from '../../components/Pagination.js';



customElements.define('bf-header-grand', loadHeaderGrand, {extends: 'nav'});
customElements.define('bf-footer-grand', loadFooterGrand, {extends: 'footer'});
customElements.define('bf-input-text-grand', InputTextGrand);
customElements.define('bf-tag-list', TagList, {extends: 'ul'});
customElements.define('bf-phrase-item', PhraseItemGrand);
customElements.define('bf-pagination-grand', PaginationGrand);



sysHeaderToSticky(true);



let pageState = {
    search: {
        phrase: [],
        author: [],
        tone: [],
        emotion: [],
        sort: 'latest',
        direction: 'desc',
        page: 1,
        phrasesPerPage: 20,
        totalPages: 0,
        results: [],
    },
};



(() => {
    // searchForm

    searchForm.addEventListener('submit', e => {
        if (searchInput.value.trim()) {
            const phrase = searchInput.value.replace(/^ +| +$|( ) +/g,"$1");
            if (!pageState.search.phrase.includes(phrase)) {
                pageState.search.phrase.push(phrase);
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'phrase': pageState.search.phrase});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;
                
                console.log('Изменены фильтры: Фраза: ' + phrase);
            }
        }

        searchInput.value = '';
        getSearchResults();

        e.preventDefault();
    });
})();



searchFiltersMenu.addEventListener('click', () => {
    alert('Подробная настройка фильтров через интерфейс пока в разработке. \nНо вы можете выбрать автора, нажав на него в одной из карточек фраз! :)');
});



(() => {
    // searchAppliedOptions

    tagListState.tagList.searchAppliedOptions = {
        list: [],
    };
})();



(() => {
    // searchSortMenuModal

    const searchSortMenuModal = document.querySelector('#searchSortMenuBtn + ._menu');

    searchSortMenuBtn.addEventListener('click', () => {
        searchSortMenu.classList.add('s-expanded');
        searchSortMenu.preventHideEvent = true;
    });

    document.addEventListener('click', e => {
		const isSearchSortMenuModal = e.target == searchSortMenuModal || searchSortMenuModal.contains(e.target);
		
		if (!isSearchSortMenuModal && searchSortMenu.preventHideEvent)
            searchSortMenu.preventHideEvent = false;
        else if (!isSearchSortMenuModal)
            searchSortMenu.classList.remove('s-expanded');
	});

    Array.prototype.forEach.call(searchSortMenuModal.getElementsByTagName('input'), element => {
        if (element.name === 'search-sort') {
            element.addEventListener('change', () => {
                searchSortMenu.classList.remove('s-expanded');
                
                pageState.search.sort = element.value;
                const url = libChangeUrlParameters(window.location.href, {'sort': element.value});
                history.pushState(pageState, '', url);

                console.log('Изменены фильтры: Сортировка: ' + element.value);
                getSearchResults();
            });
        } else if (element.name === 'search-direction') {
            element.addEventListener('change', () => {
                searchSortMenu.classList.remove('s-expanded');

                pageState.search.direction = element.value;
                const url = libChangeUrlParameters(window.location.href, {'direction': element.value});
                history.pushState(pageState, '', url);

                console.log('Изменены фильтры: Направление: ' + element.value);
                getSearchResults();
            });
        }
    });
})();



(() => {
    // начальные действия

    tryLogin().then(result => {
        if (result) {
            globalnav.setAttribute('signed-in', '');
            getSearchResults();
        }
    });


    // ПРОБЛЕМА: Если в URL меняется только якорь, onpopstate тоже вызывается, и pageState = null!!!
    // ВРЕМЕННОЕ РЕШЕНИЕ - ДОП. УСЛОВИЕ
    window.onpopstate = function() {
        if (history.state) {
            pageState = history.state;
            getSearchResults();
            updateSearchSort();

            console.log('Состояние данных было изменено');
        }
    }

    history.replaceState(pageState, '');

    if (window.location.search) {
        const parameters = new URLSearchParams(window.location.search);
        for (const [key, value] of parameters.entries()) {
            switch (key) {
                case 'phrase':
                    const phrases = value.split(',');
                    phrases.forEach(phrase => pageState.search.phrase.push(phrase));
                    break;
                case 'page':
                    pageState.search.page = Number(value);
                    break;
                case 'author':
                    const authors = value.split(',');
                    authors.forEach(author => pageState.search.author.push(author));
                    break;
                case 'tone':
                    const tones = value.split(',');
                    tones.forEach(tone => pageState.search.tone.push(tone));
                    break;
                case 'emotion':
                    const emotions = value.split(',');
                    emotions.forEach(emotion => pageState.search.emotion.push(emotion));
                    break;
                case 'sort':
                    pageState.search.sort = value;
                    break;
                case 'direction':
                    pageState.search.direction = value;
                    break;
            }
        }

        console.log('Изначальное состояние данных было переопределено');
        updateSearchSort();
    }
    
    getSearchResults();
})();



// console.log('Local file was executed');



// http://localhost/phrases/search? page=6&phrase=утро%2Bдоброе%2Cпросыпайся%2Cвставай&author=[скрыто]&emotion=средний&direction=asc                      --- URL страницы
// http://localhost/api/phrases/search? part=snippet&page=6&phrase=утро%2Bдоброе%2Cпросыпайся%2Cвставай&author=[скрыто]&emotion=средний&direction=asc     --- URL API запроса

///////////////// FUNCTIONS /////////////////



// Получает результаты поиска
function getSearchResults() {
    const parameters = new URLSearchParams();
    parameters.append('part', 'snippet,contentDetails,userDetails,statistics');

    if (pageState.search.page > 1)
        parameters.append('page', pageState.search.page);
    if (pageState.search.phrasesPerPage !== 20)
        parameters.append('phrasesPerPage', pageState.search.phrasesPerPage);
    if (pageState.search.phrase.length)
        parameters.append('phrase', pageState.search.phrase);
    if (pageState.search.author.length)
        parameters.append('author', pageState.search.author);
    if (pageState.search.tone.length)
        parameters.append('tone', pageState.search.tone);
    if (pageState.search.emotion.length)
        parameters.append('emotion', pageState.search.emotion);
    if (pageState.search.sort !== 'latest')
        parameters.append('sort', pageState.search.sort);
    if (pageState.search.direction !== 'desc')
        parameters.append('direction', pageState.search.direction);

    sysGetData('phrases/search?' + parameters).then((data) => {
        if (data.kind === 'phrases#phrasesListResponse') {
            searchPaginationResult.querySelector('span').textContent = searchPaginationBottomResult.querySelector('span').textContent = data.pageInfo.total;

            pageState.search.totalPages = Math.ceil(data.pageInfo.total / data.pageInfo.resultsPerPage);
            
            let unknownKind = 0;
            const phrasesView = [];
            phraseItemState.phraseItemGrand = [];
            data.items.forEach(item => {
                if (item.kind === 'phrases#phrase') {
                    const authors = [];
                    item.snippet.authors.forEach(author => {
                        if (author.kind === 'phrases#author') {
                            const url = libChangeUrlParameters(window.location.href, {'page': null, 'author': pageState.search.author.length ? pageState.search.author.includes(author.authorName) ? pageState.search.author : pageState.search.author + ',' + author.authorName : author.authorName});
                            authors.push({
                                id: author.authorId,
                                name: author.authorName,
                                link: {
                                    url: url,
                                    action: () => {
                                        if (!pageState.search.author.includes(author.authorName)) {
                                            pageState.search.author.push(author.authorName);
                                            pageState.search.page = 1;
                                            history.pushState(pageState, '', url);
                                        }
                                        getSearchResults();
                                        // updateSearchAppliedOptions();
                                    },
                                },
                            });
                        } else
                            console.warn('Не был передан нужный тип объекта.');
                    });

                    let savedId = null;
                    if (item.contentDetails.savedInFavoritesId)
                        if (item.contentDetails.savedInFavoritesId.id)
                            savedId = item.contentDetails.savedInFavoritesId.id;

                    phraseItemState.phraseItemGrand.push({
                        kind:                   item.kind,
                        id:                     item.id,
                        phrase:                 item.snippet.phrase,
                        authors:                authors,
                        tone:                   item.snippet.tone,
                        emotion:                item.snippet.emotion,
                        video:                  item.snippet.previewSource,
                        savedId:                savedId,
                        userName:               item.userDetails.userName,
                        userId:                 item.snippet.userId,
                        userVerification:       item.userDetails.verificationStatus,
                        userModerationStatus:   item.userDetails.moderationStatus,
                    });

                    const phrase = document.createElement('bf-phrase-item');
                    phrase.className = 'section-panel';
                    phrase.setAttribute('phrase-id', item.id);

                    phrasesView.push(phrase);
                } else
                    unknownKind++;
            });

            if (unknownKind)
                console.warn('Получено ' + unknownKind + ' объектов с неверным типом.');
            
            if (phrasesView.length < 10) {
                searchControlBlockBottom.classList.add('s-hidden');
                if (!phrasesView.length) {
                    const note = document.createElement('h3');
                    note.classList.add('spectrum-heading-text');
                    note.textContent = 'Ни одна фраза не найдена';
                    phrasesView.push(note);
                }
            } else
                searchControlBlockBottom.classList.remove('s-hidden');
            
            searchResults.replaceChildren(...phrasesView);

            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });

            updateSearchAppliedOptions();
            updatePagination();
            // updateSearchSort();
        } else
            console.warn('Не был передан нужный тип объекта.');
    }).catch(reason => console.log('Ошибка Promise: ' + reason));
    
    console.log('Запрос на поиск отправлен');
}



// Обновляет пагинацию
function updatePagination() {
    paginationState.paginationGrand.searchPagination = paginationState.paginationGrand.searchPaginationBottom = {
        current: pageState.search.page,
        total: pageState.search.totalPages,
        url: (number) => libChangeUrlParameters(window.location.href, {'page': number}),
        action: (number) => {
            const url = libChangeUrlParameters(window.location.href, {'page': number});
            pageState.search.page = number;
            history.pushState(pageState, '', url);
            getSearchResults();
        },
    };

    searchPagination.setAttribute('updated', Date.now());
    searchPaginationBottom.setAttribute('updated', Date.now());
}



// Обновляет список некоторых применённых фильтров
function updateSearchAppliedOptions() {
    tagListState.tagList.searchAppliedOptions.list = [];

    pageState.search.phrase.forEach(phrase => {
        const removePhrase = () => {
            libRemoveFromArray(pageState.search.phrase, phrase);
            const url = libChangeUrlParameters(window.location.href, {'page': null, 'phrase': pageState.search.phrase.length ? pageState.search.phrase : null});
            history.pushState(pageState, '', url);
            pageState.search.page = 1;
        }

        tagListState.tagList.searchAppliedOptions.list.push({
            type: 'phrase',
            text: phrase,
            bodyAction: {
                action: () => {
                    removePhrase();

                    searchInput.value = phrase;
                    searchInput.focus();

                    console.log('Изменены фильтры: Убрана фраза: ' + phrase);
                    getSearchResults();
                },
                isRemove: true,
            },
            closeAction: () => {
                removePhrase();

                console.log('Изменены фильтры: Убрана фраза: ' + phrase);
                getSearchResults();
            },
        });
    });

    pageState.search.author.forEach(author => {
        tagListState.tagList.searchAppliedOptions.list.push({
            type: 'author',
            text: author,
            closeAction: () => {
                libRemoveFromArray(pageState.search.author, author);
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'author': pageState.search.author.length ? pageState.search.author : null});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;
                
                console.log('Изменены фильтры: Убран  автор: ' + author);
                getSearchResults();
            },
        });
    });

    searchAppliedOptions.setAttribute('updated', Date.now());
}



// Обновляет выборку сортировки и направления
function updateSearchSort() {
    const searchSortMenuModal = document.querySelector('#searchSortMenuBtn + ._menu');
    
    Array.prototype.forEach.call(searchSortMenuModal.getElementsByTagName('input'), element => {
        if (element.name === 'search-sort' && pageState.search.sort === element.value)
            element.checked = true;
        else if (element.name === 'search-direction' && pageState.search.direction === element.value)
            element.checked = true;
    });
}