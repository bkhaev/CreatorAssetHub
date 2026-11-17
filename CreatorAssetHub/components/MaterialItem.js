// Creator Asset Hub(c) 2023. All rights reserved.
//
// The taglist component loader file

import { libSecondsToHms } from "../js/bf_library.js";



export const state = {
    materialItemGrand: [],
};



export class MaterialItemGrand extends HTMLElement {
    render() {
        const data = state.materialItemGrand.find(i => i.id === Number(this.getAttribute('material-id')));
        if (data !== undefined) {
            const wrapper = document.createElement('div');
            wrapper.className = '_wrapper section-panel'; // ВНИМАНИЕ! НЕ СТОИТ ИСПОЛЬЗОВАТЬ ЗДЕСЬ ГЛОБАВЛЬНЫЕ КЛАССЫ

            

            // ----- ПРЕВЬЮ -----

            const preview = document.createElement('div');
            preview.className = '_preview';

            if (data.fileName) {
                if (data.kind === 'materials#image') {
                    const img = document.createElement('img');
                    img.src = 'http://localhost/files/' + data.fileName;
                    img.height = '225';
                    img.alt = 'preview';
    
                    img.addEventListener('error', () => {
                        console.warn('Ошибка загрузки материала');
                        this.remove();
                    });
    
                    preview.appendChild(img);
                } else if (data.kind === 'materials#video') {
                    const video = document.createElement('video');
                    video.height = '225';
                    video.name = 'media';
                    video.volume = 0;
                    // video.setAttribute('controls', '');
                    // video.setAttribute('autoplay', '');
    
                    const src = document.createElement('source');
                    src.src = 'http://localhost/files/' + data.fileName;
                    src.type = 'video/mp4';
    
                    src.addEventListener('error', () => {
                        console.warn('Ошибка загрузки материала');
                        this.remove();
                    });
    
                    this.addEventListener('mouseenter', () => video.play())
                    this.addEventListener('mouseleave', () => video.pause())
    
                    video.appendChild(src);
                    preview.appendChild(video);
                } else {
                    console.error('MaterialItemGrand: Не был передан нужный тип.');
                }
            } else {
                console.error('MaterialItemGrand: Ссылка на материал не обнаружена.');
                this.remove();
                return;
            }
            
            
            const overlay = document.createElement('div');
            overlay.className = '__overlay';

            preview.appendChild(overlay);

            // ----- ПРЕВЬЮ -----

            

            // ----- ВЕРХНЕЕ ПОЛЕ -----

            const topBox = document.createElement('div');
            topBox.className = '_top-wrapper';


            // ---------- первая строка ----------

            const topRow1 = document.createElement('div');
            topRow1.className = 's-hidden';


            // --------------- первая колонка ---------------

            const topRow1Column1 = document.createElement('div');

            // --------------- первая колонка ---------------


            // --------------- вторая колонка ---------------

            const topRow1Column2 = document.createElement('div');
            
            if (data.fileCount && data.fileCount > 1) {
                const fileProperty = document.createElement('div');
                fileProperty.className = '_property';
    
                const fileText = document.createElement('span');
                fileText.textContent = data.fileCount + ' файла';
    
                const fileIcon = document.createElement('img');
                fileIcon.className = '__icon';
                fileIcon.width = '10';
                fileIcon.height = '10';
                fileIcon.alt = 'attachment';
                fileIcon.src = '../images/icons/light/paper_clip_white.svg';
    
                fileProperty.append(fileText, fileIcon);
    
                topRow1Column2.appendChild(fileProperty);
            }

            // --------------- вторая колонка ---------------


            topRow1.append(topRow1Column1, topRow1Column2);

            // ---------- первая строка ----------


            topBox.append(topRow1);

            // ----- ВЕРХНЕЕ ПОЛЕ -----



            // ----- НИЖНЕЕ ПОЛЕ -----

            const bottomBox = document.createElement('div');
            bottomBox.className = '_bottom-wrapper';


            // ---------- первая строка ----------

            const bottomRow1 = document.createElement('div');


            // --------------- первая колонка ---------------
            
            const bottomRow1Column1 = document.createElement('div');

            const featuresList = document.createElement('ul');
            featuresList.className = '_features-list';
            
            if (data.kind === 'materials#image') {
                if (data.height > 2160) {
                    const fps = document.createElement('li');
                    fps.className = 's-resolution';
                    fps.textContent = '2160p';
        
                    featuresList.appendChild(fps);
                } else if (data.height > 1440) {
                    const fps = document.createElement('li');
                    fps.className = 's-resolution';
                    fps.textContent = '1440p';
        
                    featuresList.appendChild(fps);
                }
            } else if (data.kind === 'materials#video') {
                if (data.height > 2160) {
                    const fps = document.createElement('li');
                    fps.className = 's-resolution';
                    fps.textContent = '2160p';
        
                    featuresList.appendChild(fps);
                } else if (data.height > 1440) {
                    const fps = document.createElement('li');
                    fps.className = 's-resolution';
                    fps.textContent = '1440p';
        
                    featuresList.appendChild(fps);
                }
                
                if (data.fps > 60) {
                    const fps = document.createElement('li');
                    fps.className = 's-fps';
                    fps.textContent = '60fps';
        
                    featuresList.appendChild(fps);
                }
            }

            bottomRow1Column1.appendChild(featuresList);

            // --------------- первая колонка ---------------


            bottomRow1.append(bottomRow1Column1);

            // ---------- первая строка ----------


            // ---------- вторая строка ----------

            const bottomRow2 = document.createElement('div');
            bottomRow2.className = 's-hidden';


            // --------------- первая колонка ---------------
            
            const bottomRow2Column1 = document.createElement('div');

            const caption = document.createElement('div');
            caption.className = '_caption';
            
            const materialTitle = document.createElement('h4');
            materialTitle.title = data.title;
            materialTitle.textContent = data.title;
            
            const updatedStatus = document.createElement('div');
            // updatedStatus.className = '_status s-updated';
            // updatedStatus.title = 'Обновлено';

            caption.append(materialTitle, updatedStatus);

            bottomRow2Column1.appendChild(caption);

            // --------------- первая колонка ---------------

            
            // --------------- вторая колонка ---------------

            const bottomRow2Column2 = document.createElement('div');
            
            if (data.kind === 'materials#video') {
                const durationProperty = document.createElement('div');
                durationProperty.className = '_property';
    
                const durationText = document.createElement('span');
                durationText.textContent = libSecondsToHms(data.duration);
    
                const videoIcon = document.createElement('img');
                videoIcon.className = '_class-1';
                videoIcon.width = '15';
                videoIcon.height = '15';
                videoIcon.alt = 'preview';
                videoIcon.src = '../images/icons/light/play_icon_white.png';
                videoIcon.srcset = '../images/icons/light/play_icon_white.svg';
    
                durationProperty.append(durationText, videoIcon);
    
                bottomRow2Column2.appendChild(durationProperty);
            }

            // --------------- вторая колонка ---------------


            bottomRow2.append(bottomRow2Column1, bottomRow2Column2);

            // ---------- вторая строка ----------


            bottomBox.append(bottomRow1, bottomRow2);

            // ----- НИЖНЕЕ ПОЛЕ -----



            wrapper.append(preview, topBox, bottomBox);

            console.log('Rerendering MaterialItemGrand');
            this.replaceChildren(wrapper);
            return;
        }

        console.log('MaterialItemGrand render fail');
    }

	connectedCallback() {
        if (!this.rendered) {
            this.render();
            this.rendered = true;
        }
    }

    disconnectedCallback() {
        // delete state.phraseItemGrand.find(i => i.id === Number(this.getAttribute('phrase-id')));
    }

    static get observedAttributes() {
        return ['material-id'];
    }

    attributeChangedCallback(name, oldValue, newValue) {
        this.render();
        this.rendered = true;
    }
}