// Creator Asset Hub(c) 2023. All rights reserved.
//
// The taglist component loader file

import { sysGetData, sysPutData } from "../../../js/index.js";



export class UserMinePhrasesPanel extends HTMLElement {
    render() {
        this.wrapper = document.createElement('details');
        
        const summary = document.createElement('summary');

        this.summaryBlock = document.createElement('img');
        this.summaryBlock.className = 's-content';
        this.summaryBlock.src = '../images/icons/loading_gray.svg';
        this.summaryBlock.alt = 'Загрузка...';
        this.summaryBlock.width = '15';
        this.summaryBlock.height = '15';

        const hidegroupHeaderDisplayText = document.createElement('span');
        hidegroupHeaderDisplayText.className = '_load-btn-text';
        hidegroupHeaderDisplayText.textContent = 'Загрузить мои фразы';

        summary.append(this.summaryBlock, hidegroupHeaderDisplayText);

        this.wrapper.addEventListener('toggle', () => {
            if (this.wrapper.hasAttribute('open')) {
                hidegroupHeaderDisplayText.textContent = 'Скрыть мои фразы';
                
                if (!this.wrapper.firstOpen) {
                    // после первого открытия загрузка
                    this.wrapper.firstOpen = true;
                    this.getMinePhrases();
                }
            } else
                hidegroupHeaderDisplayText.textContent = 'Загрузить мои фразы';
        });


        const rowWrapper1 = document.createElement('div');
        rowWrapper1.className = '_columned-row';

        const accessModeControlList = document.createElement('ul');
        accessModeControlList.className = 'variative-list-grand';


        const openAccessModeOption = document.createElement('li');

        this.openAccessModeInput = document.createElement('input');
        this.openAccessModeInput.type = 'checkbox';
        this.openAccessModeInput.name = 'access-mode';
        this.openAccessModeInput.id = 'access_mode_1';
        this.openAccessModeInput.value = 'open';
        this.openAccessModeInput.autocomplete = 'off';
        this.openAccessModeInput.checked = true;
        this.openAccessModeInput.disabled = true;

        this.openAccessModeInput.addEventListener('change', () => {
            if (!this.openAccessModeInput.checked && !this.sharedAccessModeInput.checked && !this.privateAccessModeInput.checked)
                this.openAccessModeInput.checked = true;
            else
                this.getMinePhrases();
        });

        const openAccessModeLabel = document.createElement('label');
        openAccessModeLabel.className = 'li_content';
        openAccessModeLabel.htmlFor = 'access_mode_1';
        openAccessModeLabel.textContent = 'Открытые';

        openAccessModeOption.append(this.openAccessModeInput, openAccessModeLabel);
        accessModeControlList.appendChild(openAccessModeOption);


        const sharedAccessModeOption = document.createElement('li');

        this.sharedAccessModeInput = document.createElement('input');
        this.sharedAccessModeInput.type = 'checkbox';
        this.sharedAccessModeInput.name = 'access-mode';
        this.sharedAccessModeInput.id = 'access_mode_2';
        this.sharedAccessModeInput.value = 'shared';
        this.sharedAccessModeInput.autocomplete = 'off';
        this.sharedAccessModeInput.checked = true;
        this.sharedAccessModeInput.disabled = true;

        this.sharedAccessModeInput.addEventListener('change', () => {
            if (!this.openAccessModeInput.checked && !this.sharedAccessModeInput.checked && !this.privateAccessModeInput.checked)
                this.sharedAccessModeInput.checked = true;
            else
                this.getMinePhrases();
        });

        const sharedAccessModeLabel = document.createElement('label');
        sharedAccessModeLabel.className = 'li_content';
        sharedAccessModeLabel.htmlFor = 'access_mode_2';
        sharedAccessModeLabel.textContent = 'По ссылке';

        sharedAccessModeOption.append(this.sharedAccessModeInput, sharedAccessModeLabel);
        accessModeControlList.appendChild(sharedAccessModeOption);


        const privateAccessModeOption = document.createElement('li');

        this.privateAccessModeInput = document.createElement('input');
        this.privateAccessModeInput.type = 'checkbox';
        this.privateAccessModeInput.name = 'access-mode';
        this.privateAccessModeInput.id = 'access_mode_3';
        this.privateAccessModeInput.value = 'private';
        this.privateAccessModeInput.autocomplete = 'off';
        this.privateAccessModeInput.checked = true;
        this.privateAccessModeInput.disabled = true;

        this.privateAccessModeInput.addEventListener('change', () => {
            if (!this.openAccessModeInput.checked && !this.sharedAccessModeInput.checked && !this.privateAccessModeInput.checked)
                this.privateAccessModeInput.checked = true;
            else
                this.getMinePhrases();
        });

        const privateAccessModeLabel = document.createElement('label');
        privateAccessModeLabel.className = 'li_content';
        privateAccessModeLabel.htmlFor = 'access_mode_3';
        privateAccessModeLabel.textContent = 'Закрытые';

        privateAccessModeOption.append(this.privateAccessModeInput, privateAccessModeLabel);
        accessModeControlList.appendChild(privateAccessModeOption);


        const phrasesControlList = document.createElement('div');
        phrasesControlList.className = '_control-list';


        const phrasesDeleteBtn = document.createElement('button');
        phrasesDeleteBtn.type = 'button';
        phrasesDeleteBtn.textContent = 'Удалить';

        phrasesControlList.appendChild(phrasesDeleteBtn);

        rowWrapper1.append(accessModeControlList, phrasesControlList);

        this.phrasesTable = this.createPhrasesTable();

        this.wrapper.append(summary, rowWrapper1, this.phrasesTable);
        

        this.setSkeletons();
        this.appendChild(this.wrapper);

        console.log('Rendering UserMinePhrasesPanel');
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



    createPhrasesTable() {
        const table = document.createElement('div');
        table.className = 'row-table-grand';
        table.role = 'table';

        const headerRow = document.createElement('div');
        headerRow.className = '_row s-header';
        headerRow.role = 'row';

        table.header = headerRow;


        const phrasesTableSourceColumn = document.createElement('div');
        phrasesTableSourceColumn.className = '_cell s-column-source _cell-text _header-text';
        phrasesTableSourceColumn.role = 'cell';

        const phrasesTableSourceColumnText = document.createElement('h3');
        phrasesTableSourceColumnText.textContent = 'Источник';

        phrasesTableSourceColumn.appendChild(phrasesTableSourceColumnText);
        headerRow.appendChild(phrasesTableSourceColumn);


        const phrasesTablePhraseColumn = document.createElement('div');
        phrasesTablePhraseColumn.className = '_cell s-column-phrase _cell-text _header-text';
        phrasesTablePhraseColumn.role = 'cell';

        const phrasesTablePhraseColumnText = document.createElement('h3');
        phrasesTablePhraseColumnText.textContent = 'Фраза';

        phrasesTablePhraseColumn.appendChild(phrasesTablePhraseColumnText);
        headerRow.appendChild(phrasesTablePhraseColumn);


        const phrasesTableAuthorColumn = document.createElement('div');
        phrasesTableAuthorColumn.className = '_cell s-column-author _cell-text _header-text';
        phrasesTableAuthorColumn.role = 'cell';

        const phrasesTableAuthorColumnText = document.createElement('h3');
        phrasesTableAuthorColumnText.textContent = 'Автор';

        phrasesTableAuthorColumn.appendChild(phrasesTableAuthorColumnText);
        headerRow.appendChild(phrasesTableAuthorColumn);


        const phrasesTableAccessColumn = document.createElement('div');
        phrasesTableAccessColumn.className = '_cell s-column-access _cell-text _header-text';
        phrasesTableAccessColumn.role = 'cell';

        const phrasesTableAccessColumnText = document.createElement('h3');
        phrasesTableAccessColumnText.textContent = 'Доступ';

        phrasesTableAccessColumn.appendChild(phrasesTableAccessColumnText);
        headerRow.appendChild(phrasesTableAccessColumn);


        const phrasesTableViewsColumn = document.createElement('div');
        phrasesTableViewsColumn.className = '_cell s-column-views _cell-text _header-text';
        phrasesTableViewsColumn.role = 'cell';

        const phrasesTableViewsColumnText = document.createElement('h3');
        phrasesTableViewsColumnText.textContent = 'Просмотры';

        phrasesTableViewsColumn.appendChild(phrasesTableViewsColumnText);
        headerRow.appendChild(phrasesTableViewsColumn);


        const phrasesTableSelectColumn = document.createElement('div');
        phrasesTableSelectColumn.className = '_cell s-column-last';
        phrasesTableSelectColumn.role = 'cell';

        const phrasesTableSelectSwitcher = document.createElement('div');
        phrasesTableSelectSwitcher.className = 'switch-input-minor';

        const phrasesTableSelectInput = document.createElement('input');
        phrasesTableSelectInput.type = 'checkbox';
        phrasesTableSelectInput.name = 'mine_phrases_select';
        phrasesTableSelectInput.id = 'mine_phrases_all_select';
        phrasesTableSelectInput.autocomplete = 'off';

        // phrasesTableSelectInput.addEventListener('change', () => console.log(1));

        const phrasesTableSelectLabel = document.createElement('label');
        phrasesTableSelectLabel.htmlFor = 'mine_phrases_all_select';
        phrasesTableSelectLabel.className = 's-text-right';

        const phrasesTableSelectLabelText = document.createElement('span');
        phrasesTableSelectLabelText.textContent = 'Все';

        phrasesTableSelectLabel.append(phrasesTableSelectLabelText);
        phrasesTableSelectSwitcher.append(phrasesTableSelectInput, phrasesTableSelectLabel);

        phrasesTableSelectColumn.appendChild(phrasesTableSelectSwitcher);
        headerRow.appendChild(phrasesTableSelectColumn);
        table.appendChild(headerRow);

        return table;
    }



    setSkeletons(count = 6) {
        if (!this.phrasesTable)
            return;
        
        const skeletons = [];
        while (count) {
            const phrasesTableRow = document.createElement('div');
            phrasesTableRow.className = '_row';
            phrasesTableRow.role = 'row';

            let skeletonElement;

            const getNewSkeleton = () => {
                skeletonElement = document.createElement('div');
                skeletonElement.className = 'skeleton s-rounded s-text';
            };

            const phrasesTableSourceColumn = document.createElement('div');
            phrasesTableSourceColumn.className = '_cell s-column-source';
            phrasesTableSourceColumn.role = 'cell';

            getNewSkeleton();
            phrasesTableSourceColumn.appendChild(skeletonElement);
            phrasesTableRow.appendChild(phrasesTableSourceColumn);


            const phrasesTablePhraseColumn = document.createElement('div');
            phrasesTablePhraseColumn.className = '_cell s-column-phrase';
            phrasesTablePhraseColumn.role = 'cell';

            getNewSkeleton();
            phrasesTablePhraseColumn.appendChild(skeletonElement);
            phrasesTableRow.appendChild(phrasesTablePhraseColumn);


            const phrasesTableAuthorColumn = document.createElement('div');
            phrasesTableAuthorColumn.className = '_cell s-column-author';
            phrasesTableAuthorColumn.role = 'cell';

            getNewSkeleton();
            phrasesTableAuthorColumn.appendChild(skeletonElement);
            phrasesTableRow.appendChild(phrasesTableAuthorColumn);


            const phrasesTableAccessColumn = document.createElement('div');
            phrasesTableAccessColumn.className = '_cell s-column-access';
            phrasesTableAccessColumn.role = 'cell';

            getNewSkeleton();
            phrasesTableAccessColumn.appendChild(skeletonElement);
            phrasesTableRow.appendChild(phrasesTableAccessColumn);


            const phrasesTableViewsColumn = document.createElement('div');
            phrasesTableViewsColumn.className = '_cell s-column-views';
            phrasesTableViewsColumn.role = 'cell';

            getNewSkeleton();
            phrasesTableViewsColumn.appendChild(skeletonElement);
            phrasesTableRow.appendChild(phrasesTableViewsColumn);


            const phrasesTableSelectColumn = document.createElement('div');
            phrasesTableSelectColumn.className = '_cell s-column-last';
            phrasesTableSelectColumn.role = 'cell';

            getNewSkeleton();
            phrasesTableSelectColumn.appendChild(skeletonElement);
            phrasesTableRow.appendChild(phrasesTableSelectColumn);


            skeletons.push(phrasesTableRow);
            count--;
        }

        if (!this.phrasesTable)
            this.phrasesTable = this.createPhrasesTable();

        this.phrasesTable.append(...skeletons);
    }



    async getMinePhrases() {
        let accessMode = [];
        if (this.openAccessModeInput.checked)
            accessMode.push('open');
        if (this.sharedAccessModeInput.checked)
            accessMode.push('shared');
        if (this.privateAccessModeInput.checked)
            accessMode.push('private');

        const totalPhrasesCountData = await sysGetData('phrases/search?part=statistics&mine=true').catch(reason => {
            console.log('Ошибка Promise: ' + reason);
            return;
        });

        const data = await sysGetData('phrases/search?part=snippet,contentDetails,statistics&mine=true&accessMode=' + accessMode.join(',')).catch(reason => {
            console.log('Ошибка Promise: ' + reason);
            return;
        });

        if (data.kind === 'phrases#phrasesListResponse') {
            this.openAccessModeInput.disabled = false;
            this.sharedAccessModeInput.disabled = false;
            this.privateAccessModeInput.disabled = false;

            const summaryBlock = document.createElement('ul');
            summaryBlock.className = 'local-second-text horizontal-dot-list s-content';

            const totalPhrases = document.createElement('li');
            totalPhrases.textContent = 'Всего фраз: ' + (totalPhrasesCountData.kind === 'phrases#phrasesListResponse' ? totalPhrasesCountData.pageInfo.total : 0);

            summaryBlock.appendChild(totalPhrases);

            let accessFilteredTotalPhrasesText;
            if (this.openAccessModeInput.checked && this.sharedAccessModeInput.checked && !this.privateAccessModeInput.checked)
                accessFilteredTotalPhrasesText = 'Открытых и по ссылке: ';
            else if (this.openAccessModeInput.checked && !this.sharedAccessModeInput.checked && this.privateAccessModeInput.checked)
                accessFilteredTotalPhrasesText = 'Открытых и ограниченных: ';
            else if (!this.openAccessModeInput.checked && this.sharedAccessModeInput.checked && this.privateAccessModeInput.checked)
                accessFilteredTotalPhrasesText = 'По ссылке и ограниченных: ';
            else if (this.openAccessModeInput.checked && !this.sharedAccessModeInput.checked && !this.privateAccessModeInput.checked)
                accessFilteredTotalPhrasesText = 'Открытых: ';
            else if (!this.openAccessModeInput.checked && this.sharedAccessModeInput.checked && !this.privateAccessModeInput.checked)
                accessFilteredTotalPhrasesText = 'По ссылке: ';
            else if (!this.openAccessModeInput.checked && !this.sharedAccessModeInput.checked && this.privateAccessModeInput.checked)
                accessFilteredTotalPhrasesText = 'Ограниченных: ';

            if (accessFilteredTotalPhrasesText) {
                const accessFilteredTotalPhrases = document.createElement('li');
                accessFilteredTotalPhrases.textContent = accessFilteredTotalPhrasesText + data.pageInfo.total;
                summaryBlock.appendChild(accessFilteredTotalPhrases);
            }
    
            if (data.pageInfo.total > 0) {
                if (this.listResultNote)
                    this.listResultNote.remove();
                
                if (!this.phrasesTable) {
                    this.phrasesTable = this.createPhrasesTable();
                    this.wrapper.appendChild(this.phrasesTable)
                }

                this.setPhrases(data.items);
            } else {
                if (!this.listResultNote)
                    this.listResultNote = document.createElement('p');
                
                this.listResultNote.textContent = 'Не добавлено ни одной фразы';

                if (this.phrasesTable) {
                    this.phrasesTable.remove();
                    this.phrasesTable = null;
                }

                this.wrapper.appendChild(this.listResultNote);
            }

            this.summaryBlock.replaceWith(summaryBlock);
            this.summaryBlock = summaryBlock;
        }
        else
            console.warn('Не был передан нужный тип объекта.');
    }

    setPhrases(phrasesList) {
        let invalidKind = 0;
        const rows = [];
        phrasesList.forEach(item => {
            if (item.kind === 'phrases#phrase') {
                let authors = [];
                item.snippet.authors.forEach(author => {
                    if (author.kind === 'phrases#author') {
                        authors.push(author.authorName);
                    } else
                        console.warn('Не был передан нужный тип объекта.');
                });

                const row = document.createElement('div');
                row.className = '_row';
                row.role = 'row';



                // ----- ИСТОЧНИК -----

                const colSource = document.createElement('div');
                colSource.className = '_cell s-column-source';
                colSource.role = 'cell';

                const sourceSummaryList = document.createElement('div');

                if (String(item.snippet.previewSource.url).substring(0, 30) === 'https://www.youtube.com/embed/') {
                    const sourceVideo = document.createElement('div');
                    sourceVideo.className = 'tooltip-minor';
                    
                    const sourceVideoImg = document.createElement('img');
                    sourceVideoImg.width = 15;
                    sourceVideoImg.height = 15;
                    sourceVideoImg.alt = 'link';
                    sourceVideoImg.src = '../images/icons/dark/play_icon.png';
                    sourceVideoImg.srcset = '../images/icons/dark/play_icon.svg';

                    const sourceVideoToolTip = document.createElement('div');
                    sourceVideoToolTip.className = '_tooltip bottom-rect';
                    sourceVideoToolTip.role = 'tooltip';
                    sourceVideoToolTip.textContent = 'Есть видео-превью';

                    sourceVideo.append(sourceVideoImg, sourceVideoToolTip);
                    sourceSummaryList.appendChild(sourceVideo);
                }

                if (item.snippet.previewSource.url) {
                    const sourceLink = document.createElement('div');
                    sourceLink.className = 'tooltip-minor';
                    
                    const sourceLinkImg = document.createElement('img');
                    sourceLinkImg.width = 15;
                    sourceLinkImg.height = 15;
                    sourceLinkImg.alt = 'link';
                    sourceLinkImg.src = '../images/icons/dark/40pt/link.svg';

                    const sourceLinkToolTip = document.createElement('div');
                    sourceLinkToolTip.className = '_tooltip bottom-rect';
                    sourceLinkToolTip.role = 'tooltip';
                    sourceLinkToolTip.textContent = 'Есть ссылка на источник';

                    sourceLink.append(sourceLinkImg, sourceLinkToolTip);
                    sourceSummaryList.appendChild(sourceLink);
                }

                if (item.snippet.filesCount) {
                    const sourceLink = document.createElement('div');
                    sourceLink.className = 'tooltip-minor';
                    
                    const sourceLinkImg = document.createElement('img');
                    sourceLinkImg.width = 15;
                    sourceLinkImg.height = 15;
                    sourceLinkImg.alt = 'link';
                    sourceLinkImg.src = '../images/icons/dark/paper_clip.svg';

                    const sourceLinkToolTip = document.createElement('div');
                    sourceLinkToolTip.className = '_tooltip bottom-rect';
                    sourceLinkToolTip.role = 'tooltip';
                    sourceLinkToolTip.textContent = 'Есть файлы';

                    sourceLink.append(sourceLinkImg, sourceLinkToolTip);
                    sourceSummaryList.appendChild(sourceLink);
                }

                colSource.appendChild(sourceSummaryList);
                row.appendChild(colSource);

                // ----- ИСТОЧНИК -----



                // ----- ФРАЗА -----

                const colPhrase = document.createElement('div');
                colPhrase.className = '_cell s-column-phrase _cell-text';
                colPhrase.role = 'cell';

                const phraseText = document.createElement('span');
                phraseText.className = '_cell-text';
                phraseText.textContent = phraseText.title = item.snippet.phrase;

                colPhrase.appendChild(phraseText);
                row.appendChild(colPhrase);

                // ----- ФРАЗА -----



                // ----- АВТОР -----

                const colAuthor = document.createElement('div');
                colAuthor.className = '_cell s-column-author _cell-text';
                colAuthor.role = 'cell';

                const authorText = document.createElement('span');
                authorText.className = '_cell-text';
                authorText.textContent = authorText.title = authors.join(', ');

                colAuthor.appendChild(authorText);
                row.appendChild(colAuthor);

                // ----- АВТОР -----



                // ----- УРОВЕНЬ ДОСТУПА -----

                const colAccess = document.createElement('div');
                colAccess.className = '_cell s-column-access';
                colAccess.role = 'cell';

                const accessMenuBox = document.createElement('div');
                accessMenuBox.className = 'menu-btn-minor';

                const accessText = document.createElement('label');
                accessText.className = '_cell-text';
                accessText.htmlFor = 'user_mine_phrase_' + item.id + '_emotions';
                accessText.textContent = item.contentDetails.accessMode;

                const accessMenuBtn = document.createElement('button');
                accessMenuBtn.type = 'button';
                accessMenuBtn.className = '_btn';
                accessMenuBtn.id = 'user_mine_phrase_' + item.id + '_emotions';

                const accessMenuBtnSemanticText = document.createElement('span');
                accessMenuBtnSemanticText.className = 'semantic-hidden';
                accessMenuBtnSemanticText.textContent = 'Access Mode Menu';

                const accessMenuBtnImg = document.createElement('img');
                accessMenuBtnImg.width = 10;
                accessMenuBtnImg.height = 10;
                accessMenuBtnImg.alt = 'open';
                accessMenuBtnImg.src = '../images/icons/dark/40pt/arrow.png';
                accessMenuBtnImg.srcset = '../images/icons/dark/40pt/arrow.svg';

                accessMenuBtn.append(accessMenuBtnSemanticText, accessMenuBtnImg);

                const accessMenuModal = document.createElement('div');
                accessMenuModal.className = '_menu';

                const accessMenuOptionsList = document.createElement('ul');


                const accessChangeHandler = (accessMode) => {
                    const data = {
                        id: item.id,
                        contentDetails: {
                            accessMode: accessMode,
                        },
                    };
                };


                // ---------- открытый доступ ----------

                const accessMenuOpenOption = document.createElement('li');

                const accessMenuOpenOptionInput = document.createElement('input');
                accessMenuOpenOptionInput.type = 'radio';
                accessMenuOpenOptionInput.id = 'user_mine_phrase_' + item.id + '_emotion_open';
                accessMenuOpenOptionInput.name = 'user_mine_phrase_' + item.id + '_emotion';
                accessMenuOpenOptionInput.autocomplete = 'off';
                
                if (item.contentDetails.accessMode === 'открытый')
                    accessMenuOpenOptionInput.checked = true;

                accessMenuOpenOptionInput.addEventListener('change', () => {
                    accessText.textContent = 'Открытый';
                    accessMenuBox.classList.remove('s-expanded');
                    if (accessMenuOpenOptionInput.checked)
                        accessChangeHandler('открытый');
                });

                const accessMenuOpenOptionLabel = document.createElement('label');
                accessMenuOpenOptionLabel.className = 'li_content';
                accessMenuOpenOptionLabel.htmlFor = 'user_mine_phrase_' + item.id + '_emotion_open';

                const accessMenuOpenOptionText = document.createElement('span');
                accessMenuOpenOptionText.textContent = 'Открытый';

                accessMenuOpenOptionLabel.appendChild(accessMenuOpenOptionText);

                accessMenuOpenOption.append(accessMenuOpenOptionInput, accessMenuOpenOptionLabel);
                accessMenuOptionsList.appendChild(accessMenuOpenOption);

                // ---------- открытый доступ ----------


                // ---------- доступ по ссылке ----------

                const accessMenuSharedOption = document.createElement('li');

                const accessMenuSharedOptionInput = document.createElement('input');
                accessMenuSharedOptionInput.type = 'radio';
                accessMenuSharedOptionInput.id = 'user_mine_phrase_' + item.id + '_emotion_shared';
                accessMenuSharedOptionInput.name = 'user_mine_phrase_' + item.id + '_emotion';
                accessMenuSharedOptionInput.autocomplete = 'off';
                
                if (item.contentDetails.accessMode === 'по ссылке')
                    accessMenuSharedOptionInput.checked = true;

                accessMenuSharedOptionInput.addEventListener('change', () => {
                    accessText.textContent = 'По ссылке';
                    accessMenuBox.classList.remove('s-expanded');
                    if (accessMenuSharedOptionInput.checked)
                        accessChangeHandler('по ссылке');
                });

                const accessMenuSharedOptionLabel = document.createElement('label');
                accessMenuSharedOptionLabel.className = 'li_content';
                accessMenuSharedOptionLabel.htmlFor = 'user_mine_phrase_' + item.id + '_emotion_shared';

                const accessMenuSharedOptionText = document.createElement('span');
                accessMenuSharedOptionText.textContent = 'По ссылке';

                accessMenuSharedOptionLabel.appendChild(accessMenuSharedOptionText);

                accessMenuSharedOption.append(accessMenuSharedOptionInput, accessMenuSharedOptionLabel);
                accessMenuOptionsList.appendChild(accessMenuSharedOption);

                // ---------- доступ по ссылке ----------


                // ---------- ограниченный доступ ----------

                const accessMenuPrivateOption = document.createElement('li');

                const accessMenuPrivateOptionInput = document.createElement('input');
                accessMenuPrivateOptionInput.type = 'radio';
                accessMenuPrivateOptionInput.id = 'user_mine_phrase_' + item.id + '_emotion_private';
                accessMenuPrivateOptionInput.name = 'user_mine_phrase_' + item.id + '_emotion';
                accessMenuPrivateOptionInput.autocomplete = 'off';
                
                if (item.contentDetails.accessMode === 'ограниченный')
                    accessMenuPrivateOptionInput.checked = true;

                accessMenuPrivateOptionInput.addEventListener('change', () => {
                    accessText.textContent = 'Ограниченный';
                    accessMenuBox.classList.remove('s-expanded');
                    if (accessMenuPrivateOptionInput.checked)
                        accessChangeHandler('ограниченный');
                });

                const accessMenuPrivateOptionLabel = document.createElement('label');
                accessMenuPrivateOptionLabel.className = 'li_content';
                accessMenuPrivateOptionLabel.htmlFor = 'user_mine_phrase_' + item.id + '_emotion_private';

                const accessMenuPrivateOptionText = document.createElement('span');
                accessMenuPrivateOptionText.textContent = 'Ограниченный';

                accessMenuPrivateOptionLabel.appendChild(accessMenuPrivateOptionText);

                accessMenuPrivateOption.append(accessMenuPrivateOptionInput, accessMenuPrivateOptionLabel);
                accessMenuOptionsList.appendChild(accessMenuPrivateOption);

                // ---------- ограниченный доступ ----------


                accessMenuBtn.addEventListener('click', () => {
                    accessMenuBox.classList.add('s-expanded');
                    accessMenuBox.preventHideEvent = true;
                });
            
                document.addEventListener('click', e => {
                    const isPhraseAccessMenuModal = e.target == accessMenuModal || accessMenuModal.contains(e.target);
                    
                    if (!isPhraseAccessMenuModal && accessMenuBox.preventHideEvent)
                        accessMenuBox.preventHideEvent = false;
                    else if (!isPhraseAccessMenuModal)
                        accessMenuBox.classList.remove('s-expanded');
                });


                accessMenuModal.appendChild(accessMenuOptionsList);
                accessMenuBox.append(accessText, accessMenuBtn, accessMenuModal);

                colAccess.appendChild(accessMenuBox);
                row.appendChild(colAccess);

                // ----- УРОВЕНЬ ДОСТУПА -----



                // ----- ПРОСМОТРЫ -----

                const colViews = document.createElement('div');
                colViews.className = '_cell s-column-views';
                colViews.role = 'cell';

                const viewsSummaryWrapper = document.createElement('div');
                viewsSummaryWrapper.className = '_cell-text';

                const viewsSummaryIcon = document.createElement('img');
                viewsSummaryIcon.width = 15;
                viewsSummaryIcon.height = 15;
                viewsSummaryIcon.alt = 'eye';
                viewsSummaryIcon.src = '../images/icons/dark/eye.svg';

                const viewsText = document.createElement('span');
                viewsText.className = '_cell-text';
                viewsText.textContent = item.statistics.viewCount;

                viewsSummaryWrapper.append(viewsSummaryIcon, viewsText);
                colViews.appendChild(viewsSummaryWrapper);
                row.appendChild(colViews);

                // ----- ПРОСМОТРЫ -----



                // ----- ПОСЛЕДНЯЯ КОЛОНКА -----

                const colLast = document.createElement('div');
                colLast.className = '_cell s-column-last';
                colLast.role = 'cell';

                const colLastWrapper = document.createElement('div');


                // ---------- выборка ----------

                const phraseSelectBox = document.createElement('div');
                phraseSelectBox.className = 'switch-input-minor';

                const phraseSelectBoxInput = document.createElement('input');
                phraseSelectBoxInput.type = 'checkbox';
                phraseSelectBoxInput.name = 'mine_phrases_select';
                phraseSelectBoxInput.id = 'mine_phrase_' + item.id + '_select';
                phraseSelectBoxInput.value = item.id;
                phraseSelectBoxInput.autocomplete = 'off';

                const phraseSelectLabel = document.createElement('label');
                phraseSelectLabel.className = 's-text-right';
                phraseSelectLabel.htmlFor = 'mine_phrase_' + item.id + '_select';

                phraseSelectBox.append(phraseSelectBoxInput, phraseSelectLabel);
                colLastWrapper.appendChild(phraseSelectBox);

                // ---------- выборка ----------


                // ---------- статус анонимности ----------

                if (item.snippet.isUserAnonym) {
                    const phraseAnonymStatus = document.createElement('div');
                    phraseAnonymStatus.className = 'tooltip-minor';

                    const phraseAnonymIcon = document.createElement('img');
                    phraseAnonymIcon.width = 15;
                    phraseAnonymIcon.height = 15;
                    phraseAnonymIcon.alt = 'user slash';
                    phraseAnonymIcon.src = '../images/icons/dark/user_slash.svg';

                    const phraseAnonymToolTip = document.createElement('div');
                    phraseAnonymToolTip.className = '_tooltip bottom-rect';
                    phraseAnonymToolTip.role = 'tooltip';
                    phraseAnonymToolTip.textContent = 'Анонимно';

                    phraseAnonymStatus.append(phraseAnonymIcon, phraseAnonymToolTip);
                    colLastWrapper.appendChild(phraseAnonymStatus);
                }

                // ---------- статус анонимности ----------


                // ---------- статус проверки ----------

                if (item.contentDetails.contentStatus === 'проверено') {
                    const phraseApprovedStatus = document.createElement('div');
                    phraseApprovedStatus.className = 'tooltip-minor';

                    const phraseApprovedIcon = document.createElement('img');
                    phraseApprovedIcon.width = 15;
                    phraseApprovedIcon.height = 15;
                    phraseApprovedIcon.alt = 'user slash';
                    phraseApprovedIcon.src = '../images/icons/check_green.svg';

                    const phraseApprovedToolTip = document.createElement('div');
                    phraseApprovedToolTip.className = '_tooltip bottom-rect';
                    phraseApprovedToolTip.role = 'tooltip';
                    phraseApprovedToolTip.textContent = 'Проверено';

                    phraseApprovedStatus.append(phraseApprovedIcon, phraseApprovedToolTip);
                    colLastWrapper.appendChild(phraseApprovedStatus);
                }

                // ---------- статус проверки ----------

                
                colLast.appendChild(colLastWrapper);
                row.appendChild(colLast);


                rows.push(row);
            } else
                invalidKind++;
        });

        if (invalidKind)
            console.warn('Получено ' + invalidKind + ' объектов с неверным типом.');

        this.phrasesTable.replaceChildren(this.phrasesTable.header, ...rows);
    }
}