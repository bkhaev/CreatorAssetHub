// Creator Asset Hub(c) 2023. All rights reserved.
//
// The Materials Search module JS-script file

import { testSleep, testSleepAsync } from '../../js/tests.js';
import { sys, tryLogin, sysGetData, sysSaveResourceInPlaylist, sysDeleteResourceFromPlaylist } from '../../js/index.js';
import { libChangeUrlParameters, libRemoveFromArray } from '../../js/bf_library.js';
import { loadHeaderGrand } from '../../components/HeaderNavigator.js';
import { loadFooterGrand } from '../../components/Footer.js';
// import { BannerBuilder } from '../../components/banner/Banner.js';
import { InputTextGrand, state as inputTextGrandState } from '../../components/InputTextGrand.js';
import { TagList, state as tagListState } from '../../components/TagList.js';
import { MaterialItemGrand, state as materialItemState } from '../../components/MaterialItem.js';
import { PaginationGrand, state as paginationState } from '../../components/Pagination.js';
import { ContextMenuGrand, state as contextMenuState } from '../../components/ContextMenu.js';



customElements.define('bf-header-grand', loadHeaderGrand, {extends: 'nav'});
customElements.define('bf-footer-grand', loadFooterGrand, {extends: 'footer'});
customElements.define('bf-input-text-grand', InputTextGrand);
customElements.define('bf-tag-list', TagList, {extends: 'ul'});
customElements.define('bf-material-item', MaterialItemGrand);
customElements.define('bf-pagination-grand', PaginationGrand);



contextMenuState.contextMenuGrand.contextMenuArea = {
    element: contextMenuArea,
    closeOnOutClick: true,
    closeOnResize: true,
    closeOnEsc: true,
    preventDocumentScroll: true,
    list: [],
};

customElements.define('bf-context-dropdown-grand', ContextMenuGrand);



let pageState = {
    search: {
        resourceType: ['materials#image', 'materials#video', 'materials#music', 'materials#sound'],
        query: [],
        ivResolution: {
            minWidth: 0,
            minHeight: 0,
            maxWidth: 0,
            maxHeight: 0,
        },
        imageType: {},
        imageBackground: {},
        videoType: {},
        sort: 'latest',
        page: 1,
        materialsPerPage: 20,
        totalPages: 0,
        results: [],
    },
};



(() => {
    // materialsSearchForm

    materialsSearchForm.addEventListener('submit', e => {
        if (materialsSearchForm.preventSubmit) {
            materialsSearchForm.preventSubmit = false;
            e.preventDefault();
            return;
        }

        if (searchInput.value.trim()) {
            const query = searchInput.value.replace(/^ +| +$|( ) +/g,"$1");
            if (!pageState.search.query.includes(query)) {
                pageState.search.query.push(query);
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'k': pageState.search.query});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;
                
                console.log('Изменены фильтры: Запрос: ' + query);
            }
        }

        searchInput.value = '';
        getSearchResults();

        e.preventDefault();
    });
})();



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
        }
    });
})();



(() => {
    // Aside Filters

    Array.prototype.forEach.call(filtersResourceType.getElementsByTagName('input'), element => {
        if (element.name === 'resource-type') {
            element.addEventListener('change', () => {
                let url;
                if (element.value === 'materials') {
                    pageState.search.imageType = {};
                    pageState.search.imageBackground = {};
                    pageState.search.videoType = {};
                    pageState.search.ivResolution.minWidth = pageState.search.ivResolution.minHeight = pageState.search.ivResolution.maxWidth = pageState.search.ivResolution.maxHeight = 0;

                    pageState.search.resourceType = ['materials#image', 'materials#video', 'materials#music', 'materials#sound'];
                    url = libChangeUrlParameters(window.location.href, {'page': null, 'kind': null, 'imageType': null, 'imageBackground': null, 'videoType': null, 'frameMinWidth': null, 'frameMinHeight': null});
                } else {
                    const changes = {
                        'page': null,
                        'kind': element.value,
                    };
                    switch (element.value) {
                        case 'materials#image':
                            pageState.search.videoType = {};

                            changes.videoType = null;
                            break;
                        case 'materials#video':
                            pageState.search.imageType = {};
                            pageState.search.imageBackground = {};

                            changes.imageType = null;
                            changes.imageBackground = null;
                            break;
                        case 'materials#music':
                            pageState.search.imageType = {};
                            pageState.search.imageBackground = {};
                            pageState.search.videoType = {};
                            pageState.search.ivResolution.minWidth = pageState.search.ivResolution.minHeight = pageState.search.ivResolution.maxWidth = pageState.search.ivResolution.maxHeight = 0;
                            break;
                        case 'materials#sound':
                            pageState.search.imageType = {};
                            pageState.search.imageBackground = {};
                            pageState.search.videoType = {};
                            pageState.search.ivResolution.minWidth = pageState.search.ivResolution.minHeight = pageState.search.ivResolution.maxWidth = pageState.search.ivResolution.maxHeight = 0;
                            break;
                    }

                    pageState.search.resourceType = [element.value];
                    url = libChangeUrlParameters(window.location.href, changes);
                }
                
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                console.log('Изменены фильтры: Тип ресурса: ' + element.value);
                getSearchResults();
                updateSearchForm();
            });
        }
    });

    Array.prototype.forEach.call(filtersImageType.getElementsByTagName('input'), element => {
        if (element.name === 'image-type') {
            element.addEventListener('change', () => {
                pageState.search.imageType = {};
                pageState.search.imageType[element.value] = element.dataset.display;
                
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'imageType': Object.keys(pageState.search.imageType)});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                console.log('Изменены фильтры: Тип изображения: ' + element.value);
                getSearchResults();
            });
        }
    });

    Array.prototype.forEach.call(filtersImageBackgroundType.getElementsByTagName('input'), element => {
        if (element.name === 'image-background-type') {
            element.addEventListener('change', () => {
                if (element.checked && !Object.keys(pageState.search.imageBackground).includes(element.value))
                    pageState.search.imageBackground[element.value] = element.dataset.display;
                else if (!element.checked)
                    delete pageState.search.imageBackground[element.value];
                
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'imageBackground': Object.keys(pageState.search.imageBackground).length ? Object.keys(pageState.search.imageBackground) : null});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                console.log('Изменены фильтры: Фон изображения: ' + element.value);
                getSearchResults();
            });
        }
    });

    Array.prototype.forEach.call(filtersVideoType.getElementsByTagName('input'), element => {
        if (element.name === 'video-type') {
            element.addEventListener('change', () => {
                pageState.search.videoType = {};
                pageState.search.videoType[element.value] = element.dataset.display;
                
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'videoType': Object.keys(pageState.search.videoType)});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                console.log('Изменены фильтры: Тип видео: ' + element.value);
                getSearchResults();
            });
        }
    });

    const ivResolutionChangeHandler = element => {
        if (element.value === 'custom' || element === ivMinWidthInp || element === ivMinHeightInp) {
            pageState.search.ivResolution.minWidth = Number(ivMinWidthInp.value);
            pageState.search.ivResolution.minHeight = Number(ivMinHeightInp.value);
        } else {
            const parts = element.value.split('x');
            pageState.search.ivResolution.minWidth = parseInt(parts[0]);
            pageState.search.ivResolution.minHeight = parseInt(parts[1]);
        }
        
        const url = libChangeUrlParameters(window.location.href, {
            'page': null,
            'frameMinWidth': pageState.search.ivResolution.minWidth ? pageState.search.ivResolution.minWidth : null,
            'frameMinHeight': pageState.search.ivResolution.minHeight ? pageState.search.ivResolution.minHeight : null,
        });
        history.pushState(pageState, '', url);
        pageState.search.page = 1;

        console.log('Изменены фильтры: Минимальное разрешение кадра: ' + ((element.value === 'custom' || element === ivMinWidthInp || element === ivMinHeightInp) ? Number(ivMinWidthInp.value) + 'x' + Number(ivMinHeightInp.value) : element.value));
    };

    Array.prototype.forEach.call(filtersIvResolution.getElementsByTagName('input'), element => {
        if (element.name === 'iv-resolution') {
            element.addEventListener('change', () => {
                ivResolutionChangeHandler(element);
                getSearchResults();
            });
        }
    });

    const ivResolutionCustomKeydownHandler = e => {
        if (e.keyCode === 13) // Enter
            materialsSearchForm.preventSubmit = true;
        
        if (e.key === 'Tab') // Tab
            filtersIvResolution.preventChange = true;
        else
            filtersIvResolution.preventChange = false;
    };

    const ivResolutionCustomChangeHandler = input => {
        if (filtersIvResolution.preventChange)
            filtersIvResolution.preventChange = false;
        else {
            iv_resolution_custom.checked = true;
            ivResolutionChangeHandler(input);
            getSearchResults();
        }
    };

    ivMinWidthInp.addEventListener('keydown', e => ivResolutionCustomKeydownHandler(e));
    ivMinHeightInp.addEventListener('keydown', e => ivResolutionCustomKeydownHandler(e));

    ivMinWidthInp.addEventListener('change', () => ivResolutionCustomChangeHandler(ivMinWidthInp));
    ivMinHeightInp.addEventListener('change', () => ivResolutionCustomChangeHandler(ivMinHeightInp));

    document.addEventListener('mousedown', e => {
		if (e.target == ivMinWidthInp || e.target == ivMinHeightInp) // !!! Если нажать на name="iv-resolution" то в итое отправка запроса происходит 2 раза
            filtersIvResolution.preventChange = true;
        else
            filtersIvResolution.preventChange = false;
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

    window.onpopstate = function() {
        if (history.state) {
            pageState = history.state;
            getSearchResults();
            updateSearchForm();
            updateSearchSort();

            console.log('Состояние данных было изменено');
        }
    }

    history.replaceState(pageState, '');

    if (window.location.search) {
        const parameters = new URLSearchParams(window.location.search);
        for (const [key, value] of parameters.entries()) {
            switch (key) {
                case 'k':
                    const queries = value.split(',');
                    queries.forEach(query => pageState.search.query.push(query));
                    break;
                case 'page':
                    pageState.search.page = Number(value);
                    break;
                case 'kind':
                    pageState.search.resourceType = [];
                    const resourceTypes = value.split(',');
                    for (const type of resourceTypes) {
                        if (type === 'materials') {
                            pageState.search.resourceType = ['materials#image', 'materials#video', 'materials#music', 'materials#sound'];
                            break;
                        } else
                            pageState.search.resourceType.push(type);
                    }
                    break;
                case 'imageType':
                    pageState.search.imageType = {};
                    const imageTypes = value.split(',');
                    imageTypes.forEach(type => {
                        pageState.search.imageType[type] = type;
                        Array.prototype.forEach.call(filtersImageType.getElementsByTagName('input'), element => {
                            if (element.name === 'image-type' && element.value === type) {
                                pageState.search.imageType[type] = element.dataset.display;
                                element.checked = true;
                            }
                        });
                    });
                    break;
                case 'imageBackground':
                    pageState.search.imageBackground = {};
                    const imageBackgroundTypes = value.split(',');
                    imageBackgroundTypes.forEach(type => {
                        pageState.search.imageBackground[type] = type;
                        Array.prototype.forEach.call(filtersImageBackgroundType.getElementsByTagName('input'), element => {
                            if (element.name === 'image-background-type' && element.value === type) {
                                pageState.search.imageBackground[type] = element.dataset.display;
                                element.checked = true;
                            }
                        });
                    });
                    break;
                case 'videoType':
                    pageState.search.videoType = {};
                    const videoTypes = value.split(',');
                    videoTypes.forEach(type => {
                        pageState.search.videoType[type] = type;
                        Array.prototype.forEach.call(filtersVideoType.getElementsByTagName('input'), element => {
                            if (element.name === 'video-type' && element.value === type) {
                                pageState.search.videoType[type] = element.dataset.display;
                                element.checked = true;
                            }
                        });
                    });
                    break;
                case 'frameMinWidth':
                    pageState.search.ivResolution.minWidth = Number(value);
                    break;
                case 'frameMinHeight':
                    pageState.search.ivResolution.minHeight = Number(value);
                    break;
                case 'sort':
                    pageState.search.sort = value;
                    break;
            }
        }

        console.log('Изначальное состояние данных было переопределено');
        updateSearchForm();
        updateSearchSort();
    }

    getSearchResults();

    window.onload = function() {
        let searchResultsOldWidth = 0;
        let resizeTimeout;

        window.addEventListener('resize', () => {
            if (!resizeTimeout && searchResultsOldWidth > searchResults.clientWidth) {
                resizeTimeout = setTimeout(function() {
                    resizeTimeout = null;
                    searchResultsOldWidth = searchResults.clientWidth;
                    updateSearchResultView(false);
                }, 66);
            } else if (!resizeTimeout && searchResultsOldWidth < searchResults.clientWidth) {
                resizeTimeout = setTimeout(function() {
                    resizeTimeout = null;
                    searchResultsOldWidth = searchResults.clientWidth;
                    updateSearchResultView(true);
                }, 66);
            }
        }, false);
    }
})();





///////////////// FUNCTIONS /////////////////



// Получает результаты поиска
function getSearchResults() {
    preloaderArea.classList.remove('s-hidden');

    const parameters = new URLSearchParams();
    parameters.append('part', 'snippet,contentDetails,statistics');
    parameters.append('kind', pageState.search.resourceType);

    if (pageState.search.page > 1)
        parameters.append('page', pageState.search.page);
    if (pageState.search.materialsPerPage !== 20)
        parameters.append('phrasesPerPage', pageState.search.materialsPerPage);
    if (pageState.search.query.length)
        parameters.append('k', pageState.search.query);
    if (pageState.search.resourceType.includes('materials#image') && Object.keys(pageState.search.imageType).length)
        parameters.append('imageType', Object.keys(pageState.search.imageType));
    if (pageState.search.resourceType.includes('materials#image') && Object.keys(pageState.search.imageBackground).length)
        parameters.append('imageBackground', Object.keys(pageState.search.imageBackground));
    if (pageState.search.resourceType.includes('materials#video') && Object.keys(pageState.search.videoType).length)
        parameters.append('videoType', Object.keys(pageState.search.videoType));
    if ((pageState.search.resourceType.includes('materials#image') || pageState.search.resourceType.includes('materials#video')) && pageState.search.ivResolution.minWidth)
        parameters.append('frameMinWidth', pageState.search.ivResolution.minWidth);
    if ((pageState.search.resourceType.includes('materials#image') || pageState.search.resourceType.includes('materials#video')) && pageState.search.ivResolution.minHeight)
        parameters.append('frameMinHeight', pageState.search.ivResolution.minHeight);
    if ((pageState.search.resourceType.includes('materials#image') || pageState.search.resourceType.includes('materials#video')) && pageState.search.ivResolution.maxWidth)
        parameters.append('frameMaxWidth', pageState.search.ivResolution.maxWidth);
    if ((pageState.search.resourceType.includes('materials#image') || pageState.search.resourceType.includes('materials#video')) && pageState.search.ivResolution.maxHeight)
        parameters.append('frameMaxHeight', pageState.search.ivResolution.maxHeight);
    if (pageState.search.sort !== 'latest')
        parameters.append('sort', pageState.search.sort);
    
    sysGetData('materials/search?' + parameters).then((data) => {
        if (data.kind === 'materials#materialsListResponse') {
            searchTotalCount.querySelector('span').textContent = data.pageInfo.total;

            pageState.search.totalPages = Math.ceil(data.pageInfo.total / data.pageInfo.resultsPerPage);
            
            let unknownKind = 0;
            const previewsOnLoad = [];
            materialItemState.materialItemGrand = []; // ОПАСНО! ЕСЛИ ЕСТЬ ДРУГИЕ БЛОКИ МАТЕРИАЛОВ НА СТРАНИЦЕ, ОНИ ТОЖЕ УДАЛЯТСЯ

            const blank = document.createElement('div');
            blank.className = '_wrapper';
            searchResults.appendChild(blank);

            const row = document.createElement('div');
            row.className = '_row';
            blank.appendChild(row);

            const materialOnContextMenu = (item) => {
                contextMenuState.contextMenuGrand.contextMenuArea.list = [];

                const openMaterialOption = {
                    text: 'Открыть',
                    url: '#',
                };

                contextMenuState.contextMenuGrand.contextMenuArea.list.push(openMaterialOption);

                /////////////////////////////////////////////

                const saveMaterialInFavoritesOption = {
                    text: 'Сохранить в закладках',
                    icon: 's-save',
                };

                if (sys.account) {
                    let playlistTitle = '';
                    console.log(item)
                    if (item.kind === 'materials#image')
                        playlistTitle = 'Понравившиеся изображения';
                    else if (item.kind === 'materials#video')
                        playlistTitle = 'Понравившиеся видео';
                    else if (item.kind === 'materials#music')
                        playlistTitle = 'Понравившаяся музыка';
                    else if (item.kind === 'materials#sound')
                        playlistTitle = 'Понравившиеся звуки';


                    saveMaterialInFavoritesOption.isChecked = item.contentDetails.savedInFavoritesId.id ? true : false;
                    saveMaterialInFavoritesOption.clickAction = async (isChecked) => {
                        if (isChecked) {
                            const saveId = await sysSaveResourceInPlaylist(item.kind, item.id, playlistTitle);
                            if (saveId) {
                                item.contentDetails.savedInFavoritesId.id = saveId;
                                console.log('Материал сохранён в избранных!');
                            }
                        } else {
                            const result = await sysDeleteResourceFromPlaylist(item.contentDetails.savedInFavoritesId.id);
                            if (result) {
                                console.log('Материал удалён из избранных.');
                                delete item.contentDetails.savedInFavoritesId.id;
                            }
                        }
                    };
                } else {
                    saveMaterialInFavoritesOption.url = '#';
                    saveMaterialInFavoritesOption.clickAction = () =>  window.location.href = 'http://localhost/login?redirect=' + encodeURIComponent(document.location.pathname + document.location.search + document.location.hash) + '#login';
                }

                contextMenuState.contextMenuGrand.contextMenuArea.list.push(saveMaterialInFavoritesOption);
                
                /////////////////////////////////////////////

                const downloadMaterialOption = {
                    text: 'Скачать',
                    clickAction: () => {
                        const a = document.createElement("a");
                        a.href = 'http://localhost/files/' + item.snippet.previewFile.fileName;
                        a.download = item.snippet.previewFile.fileName;
                        a.click();
                    },
                };

                contextMenuState.contextMenuGrand.contextMenuArea.list.push(downloadMaterialOption);
            };

            data.items.forEach(item => {
                let material;
                if (item.kind === 'materials#video' || item.kind === 'materials#image') {
                    materialItemState.materialItemGrand.push({
                        kind: item.kind,
                        id: item.id,
                        title: item.snippet.title,
                        fileCount: item.snippet.filesCount,
                        fileName: item.snippet.previewFile.fileName,
                        width: item.snippet.previewFile.width,
                        height: item.snippet.previewFile.height,
                        duration: item.kind === 'materials#video' ? item.snippet.previewFile.duration : null,
                        fps: item.kind === 'materials#video' ? item.snippet.previewFile.fps : null,
                    });
            
                    material = document.createElement('bf-material-item');
                    material.className = 'spectrum';
                    material.setAttribute('material-id', item.id);

                    material.addEventListener('contextmenu', e => {
                        materialOnContextMenu(item);
                        
                        contextMenuArea.setAttribute('updated', Date.now());
                        contextMenuArea.showMenu(e);
    
                        e.preventDefault(); // предотвращает вызов контекстного меню браузера
                    });

                    if (item.kind === 'materials#image') {
                        let preview = material.querySelector('._preview img');
                        if (preview) {
                            previewsOnLoad.push(new Promise((resolve, reject) => {
                                preview.addEventListener('load', resolve);
                                preview.addEventListener('error', resolve);
                            }));
                        }
                    } else {
                        let preview = material.querySelector('._preview video');
                        let previewSrc = material.querySelector('._preview video source');
                        if (preview && previewSrc) {
                            previewsOnLoad.push(new Promise((resolve, reject) => {
                                preview.addEventListener('loadeddata', resolve);
                                previewSrc.addEventListener('error', resolve);
                            }));
                        }
                    }

                    row.appendChild(material);
                } else if (item.kind === 'materials#sound') {

                } else if (item.kind === 'materials#music') {

                } else
                    unknownKind++;
            });

            if (unknownKind)
                console.warn('Получено ' + unknownKind + ' объектов с неверным типом.');
            
            Promise.all(previewsOnLoad).then(() => {
                const oldSet = searchResults.querySelector('._wrapper');  
                if (oldSet) oldSet.remove(); // удаление старых материалов
                updateSearchResultView();                                 
                preloaderArea.classList.add('s-hidden');
            });
            
            window.scrollTo({
                top: 0,
                behavior: "smooth",
            });

            updateSearchAppliedOptions();
            updatePagination();
        } else
            console.warn('Не был передан нужный тип объекта.');
    }).catch(reason => console.log('Ошибка Promise: ' + reason));
    
    console.log('Запрос на поиск отправлен');
    console.log(pageState);
}



// Обновляет пагинацию
function updatePagination() {
    paginationState.paginationGrand.searchPagination = {
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
}



// Обновляет список некоторых применённых фильтров
function updateSearchAppliedOptions() {
    tagListState.tagList.searchAppliedOptions.list = [];

    pageState.search.query.forEach(query => {
        const removeQuery = () => {
            libRemoveFromArray(pageState.search.query, query);
            const url = libChangeUrlParameters(window.location.href, {'page': null, 'k': pageState.search.query.length ? pageState.search.query : null});
            history.pushState(pageState, '', url);
            pageState.search.page = 1;
        }

        tagListState.tagList.searchAppliedOptions.list.push({
            type: 'phrase',
            text: query,
            bodyAction: {
                action: () => {
                    removeQuery();

                    searchInput.value = query;
                    searchInput.focus();

                    console.log('Изменены фильтры: Убран запрос: ' + query);
                    getSearchResults();
                },
                isRemove: true,
            },
            closeAction: () => {
                removeQuery();

                console.log('Изменены фильтры: Убран запрос: ' + query);
                getSearchResults();
            },
        });
    });

    Object.keys(pageState.search.imageType).forEach(type => {
        tagListState.tagList.searchAppliedOptions.list.push({
            type: 'phrase',
            text: pageState.search.imageType[type],
            closeAction: () => {
                delete pageState.search.imageType[type];
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'imageType': Object.keys(pageState.search.imageType).length ? Object.keys(pageState.search.imageType) : null});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                Array.prototype.forEach.call(filtersImageType.getElementsByTagName('input'), element => { if (element.name === 'image-type' && element.value === type) element.checked = false; });

                console.log('Изменены фильтры: Убран тип изображения: ' + type);
                getSearchResults();
            },
        });
    });

    Object.keys(pageState.search.imageBackground).forEach(type => {
        tagListState.tagList.searchAppliedOptions.list.push({
            type: 'phrase',
            text: pageState.search.imageBackground[type],
            closeAction: () => {
                delete pageState.search.imageBackground[type];
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'imageBackground': Object.keys(pageState.search.imageBackground).length ? Object.keys(pageState.search.imageBackground) : null});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                Array.prototype.forEach.call(filtersImageBackgroundType.getElementsByTagName('input'), element => { if (element.name === 'image-background-type' && element.value === type) element.checked = false; });

                console.log('Изменены фильтры: Убран фон изображения: ' + type);
                getSearchResults();
            },
        });
    });

    Object.keys(pageState.search.videoType).forEach(type => {
        tagListState.tagList.searchAppliedOptions.list.push({
            type: 'phrase',
            text: pageState.search.videoType[type],
            closeAction: () => {
                delete pageState.search.videoType[type];
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'videoType': Object.keys(pageState.search.videoType).length ? Object.keys(pageState.search.videoType) : null});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                Array.prototype.forEach.call(filtersVideoType.getElementsByTagName('input'), element => { if (element.name === 'video-type' && element.value === type) element.checked = false; });

                console.log('Изменены фильтры: Убран тип видео: ' + type);
                getSearchResults();
            },
        });
    });

    if (pageState.search.ivResolution.minWidth !== 0 && pageState.search.ivResolution.minHeight !== 0) {
        tagListState.tagList.searchAppliedOptions.list.push({
            type: 'phrase',
            text: 'Мин. ' + pageState.search.ivResolution.minWidth + 'x' + pageState.search.ivResolution.minHeight + 'p',
            closeAction: () => {
                pageState.search.ivResolution.minWidth = pageState.search.ivResolution.minHeight = 0;
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'frameMinWidth': null, 'frameMinHeight': null});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                ivMinWidthInp.value = '';
                ivMinHeightInp.value = '';
                iv_resolution_custom.checked = true;

                console.log('Изменены фильтры: Убрано минимальное разрешение кадра.');
                getSearchResults();
            },
        });
    } else if (pageState.search.ivResolution.minWidth !== 0) {
        tagListState.tagList.searchAppliedOptions.list.push({
            type: 'phrase',
            text: 'Мин. ширина ' + pageState.search.ivResolution.minWidth + 'p',
            closeAction: () => {
                pageState.search.ivResolution.minWidth = 0;
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'frameMinWidth': null});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                ivMinWidthInp.value = '';

                console.log('Изменены фильтры: Убрана минимальная ширина кадра.');
                getSearchResults();
            },
        });
    } else if (pageState.search.ivResolution.minHeight !== 0) {
        tagListState.tagList.searchAppliedOptions.list.push({
            type: 'phrase',
            text: 'Мин. высота ' + pageState.search.ivResolution.minHeight + 'p',
            closeAction: () => {
                pageState.search.ivResolution.minHeight = 0;
                const url = libChangeUrlParameters(window.location.href, {'page': null, 'frameMinHeight': null});
                history.pushState(pageState, '', url);
                pageState.search.page = 1;

                ivMinHeightInp.value = '';

                console.log('Изменены фильтры: Убрана минимальная высота кадра.');
                getSearchResults();
            },
        });
    }
    
    searchAppliedOptions.setAttribute('updated', Date.now());
}



// Обновляет представление формы поиска
function updateSearchForm() {
    if (pageState.search.resourceType.toString() === 'materials#image,materials#video,materials#music,materials#sound') {
        filtersImageType.classList.add('s-hidden');
        filtersImageBackgroundType.classList.add('s-hidden');
        filtersVideoType.classList.add('s-hidden');
        filtersIvResolution.classList.add('s-hidden');

        resource_type_0.checked = true;
        Array.prototype.forEach.call(filtersImageType.getElementsByTagName('input'), element => { if (element.name === 'image-type') element.checked = false; });
        Array.prototype.forEach.call(filtersImageBackgroundType.getElementsByTagName('input'), element => { if (element.name === 'image-background-type') element.checked = false; });
        Array.prototype.forEach.call(filtersVideoType.getElementsByTagName('input'), element => { if (element.name === 'video-type') element.checked = false; });
        iv_resolution_custom.checked = true;
        ivMinWidthInp.value = ivMinHeightInp.value = '';
    } else {
        if (pageState.search.resourceType.length !== 1)
            Array.prototype.forEach.call(filtersResourceType.getElementsByTagName('input'), element => { if (element.name === 'resource-type') element.checked = false; });

        pageState.search.resourceType = [...new Set(pageState.search.resourceType)]; // убрать дубликаты
        pageState.search.resourceType.forEach(type => {
            switch (type) {
                case 'materials#image':
                    filtersImageType.classList.remove('s-hidden');
                    filtersImageBackgroundType.classList.remove('s-hidden');
                    filtersVideoType.classList.add('s-hidden');
                    filtersIvResolution.classList.remove('s-hidden');

                    resource_type_1.checked = true;

                    if (Object.keys(pageState.search.imageType).length === 1) {
                        const imageType = Object.keys(pageState.search.imageType)[0];
                        Array.prototype.forEach.call(filtersImageType.getElementsByTagName('input'), element => { if (element.name === 'image-type' && element.value === imageType) element.checked = true; });
                    } else
                        Array.prototype.forEach.call(filtersImageType.getElementsByTagName('input'), element => { if (element.name === 'image-type') element.checked = false; });

                    Array.prototype.forEach.call(filtersImageBackgroundType.getElementsByTagName('input'), element => {
                        if (element.name === 'image-background-type') {
                            if (Object.keys(pageState.search.imageBackground).includes(element.value))
                                element.checked = true;
                            else
                                element.checked = false;
                        }
                    });
                    
                    if (!pageState.search.resourceType.includes('materials#video'))
                        Array.prototype.forEach.call(filtersVideoType.getElementsByTagName('input'), element => { if (element.name === 'video-type') element.checked = false; });

                    if (pageState.search.ivResolution.minWidth && pageState.search.ivResolution.minHeight) {
                        let isSuggested = false;
                        Array.prototype.forEach.call(filtersIvResolution.getElementsByTagName('input'), element => {
                            if (element.name === 'iv-resolution' && element.value === pageState.search.ivResolution.minWidth + 'x' + pageState.search.ivResolution.minHeight + 'p')
                                element.checked = isSuggested = true;
                        });
                        if (!isSuggested) {
                            iv_resolution_custom.checked = true;
                            ivMinWidthInp.value = pageState.search.ivResolution.minWidth;
                            ivMinHeightInp.value = pageState.search.ivResolution.minHeight;
                        }
                    } else {
                        iv_resolution_custom.checked = true;
                        ivMinWidthInp.value = pageState.search.ivResolution.minWidth ? pageState.search.ivResolution.minWidth : '';
                        ivMinHeightInp.value = pageState.search.ivResolution.minHeight ? pageState.search.ivResolution.minHeight : '';
                    }

                    break;
                case 'materials#video':
                    filtersImageType.classList.add('s-hidden');
                    filtersImageBackgroundType.classList.add('s-hidden');
                    filtersVideoType.classList.remove('s-hidden');
                    filtersIvResolution.classList.remove('s-hidden');

                    resource_type_2.checked = true;

                    if (!pageState.search.resourceType.includes('materials#image')) {
                        Array.prototype.forEach.call(filtersImageType.getElementsByTagName('input'), element => { if (element.name === 'image-type') element.checked = false; });
                        Array.prototype.forEach.call(filtersImageBackgroundType.getElementsByTagName('input'), element => { if (element.name === 'image-background-type') element.checked = false; });
                    }

                    if (Object.keys(pageState.search.videoType).length === 1) {
                        const videoType = Object.keys(pageState.search.videoType)[0];
                        Array.prototype.forEach.call(filtersVideoType.getElementsByTagName('input'), element => { if (element.name === 'video-type' && element.value === videoType) element.checked = true; });
                    } else
                        Array.prototype.forEach.call(filtersVideoType.getElementsByTagName('input'), element => { if (element.name === 'video-type') element.checked = false; });

                    if (pageState.search.ivResolution.minWidth && pageState.search.ivResolution.minHeight) {
                        let isSuggested = false;
                        Array.prototype.forEach.call(filtersIvResolution.getElementsByTagName('input'), element => {
                            if (element.name === 'iv-resolution' && element.value === pageState.search.ivResolution.minWidth + 'x' + pageState.search.ivResolution.minHeight + 'p')
                                element.checked = isSuggested = true;
                        });
                        if (!isSuggested) {
                            iv_resolution_custom.checked = true;
                            ivMinWidthInp.value = pageState.search.ivResolution.minWidth;
                            ivMinHeightInp.value = pageState.search.ivResolution.minHeight;
                        }
                    } else {
                        iv_resolution_custom.checked = true;
                        ivMinWidthInp.value = pageState.search.ivResolution.minWidth ? pageState.search.ivResolution.minWidth : '';
                        ivMinHeightInp.value = pageState.search.ivResolution.minHeight ? pageState.search.ivResolution.minHeight : '';
                    }
                    
                    break;
                case 'materials#music':
                    filtersImageType.classList.add('s-hidden');
                    filtersImageBackgroundType.classList.add('s-hidden');
                    filtersVideoType.classList.add('s-hidden');
                    filtersIvResolution.classList.add('s-hidden');

                    if (!pageState.search.resourceType.includes('materials#image')) {
                        Array.prototype.forEach.call(filtersImageType.getElementsByTagName('input'), element => { if (element.name === 'image-type') element.checked = false; });
                        Array.prototype.forEach.call(filtersImageBackgroundType.getElementsByTagName('input'), element => { if (element.name === 'image-background-type') element.checked = false; });
                    }

                    if (!pageState.search.resourceType.includes('materials#video'))
                        Array.prototype.forEach.call(filtersVideoType.getElementsByTagName('input'), element => { if (element.name === 'video-type') element.checked = false; });
                    
                    if (!pageState.search.resourceType.includes('materials#image') && !pageState.search.resourceType.includes('materials#video')) {
                        iv_resolution_custom.checked = true;
                        ivMinWidthInp.value = ivMinHeightInp.value = '';
                    }
                    
                    break;
                case 'materials#sound':
                    filtersImageType.classList.add('s-hidden');
                    filtersImageBackgroundType.classList.add('s-hidden');
                    filtersVideoType.classList.add('s-hidden');
                    filtersIvResolution.classList.add('s-hidden');

                    if (!pageState.search.resourceType.includes('materials#image')) {
                        Array.prototype.forEach.call(filtersImageType.getElementsByTagName('input'), element => { if (element.name === 'image-type') element.checked = false; });
                        Array.prototype.forEach.call(filtersImageBackgroundType.getElementsByTagName('input'), element => { if (element.name === 'image-background-type') element.checked = false; });
                    }

                    if (!pageState.search.resourceType.includes('materials#video'))
                        Array.prototype.forEach.call(filtersVideoType.getElementsByTagName('input'), element => { if (element.name === 'video-type') element.checked = false; });
                    
                    if (!pageState.search.resourceType.includes('materials#image') && !pageState.search.resourceType.includes('materials#video')) {
                        iv_resolution_custom.checked = true;
                        ivMinWidthInp.value = ivMinHeightInp.value = '';
                    }
                    
                    break;
                default:
                    break;
            }
        });
    }
}



// Обновляет выборку сортировки
function updateSearchSort() {
    const searchSortMenuModal = document.querySelector('#searchSortMenuBtn + ._menu');
    
    Array.prototype.forEach.call(searchSortMenuModal.getElementsByTagName('input'), element => {
        if (element.name === 'search-sort' && pageState.search.sort === element.value)
            element.checked = true;
    });
}



// Выравнивает поля с материвалами
// Для правильной работы актуальные материалы должны находиться в _wrapper:last-of-type!
function updateSearchResultView(islarger = false) {
    try {
        const containerWidth = searchResults.getBoundingClientRect().width; // clientWidth округляет число до целых
        const rem = parseInt(getComputedStyle(document.documentElement).fontSize);
        const gap = rem * getComputedStyle(searchResults, null).getPropertyValue('--spaceBetween').match(/[-]{0,1}[\d]*[.]{0,1}[\d]+/g);
        // console.log('containerWidth: ' + containerWidth + ', gap: ' + gap)

        const set = searchResults.querySelector('._wrapper:last-of-type');
        let rows = set.getElementsByClassName('_row');

        const createRow = () => {
            const row = document.createElement('div');
            row.className = '_row';
            set.appendChild(row);
            return row;
        }

        for (let i = 0; i < rows.length; i++) {
            let materials = rows[i].getElementsByTagName('bf-material-item');
            const widthSum = [];
            let rowHeight = rows[i].style.height.match(/[-]{0,1}[\d]*[.]{0,1}[\d]+/g);
            if (!rowHeight)
                rowHeight = getComputedStyle(rows[i], null).getPropertyValue('height').match(/[-]{0,1}[\d]*[.]{0,1}[\d]+/g);

            Array.prototype.forEach.call(materials, material => widthSum.push(material.getBoundingClientRect().width + gap)); // widthSum всего
            
            if (!islarger && rowHeight < 225 && widthSum.length > 1) {
                while ((widthSum.reduce((acc, val) => acc + val, 0) - gap) > containerWidth) {
                    if (rows[i + 1] === undefined)
                        createRow();
                    rows[i + 1].prepend(materials[materials.length - 1]);
                    widthSum.pop();
                }
            }

            const height = (rowHeight * containerWidth) / (widthSum.reduce((acc, val) => acc + val, 0) - (widthSum.length * gap)); // новая высота без учёта отступов
            widthSum.forEach((width, index) => widthSum[index] = ((width - gap) * height) / rowHeight); // обновлённый widthSum
            const heughtGap = (height * containerWidth) / (widthSum.reduce((acc, val) => acc + val, 0) + (gap * (widthSum.length - 1))); // высота с учётом отступов

            rows[i].style.height = heughtGap + 'px';
        }

        console.log('Представление материалов обновлено');
    } catch (error) {
        console.warn('Ошибка представления материалов, ' + error);
    }
}