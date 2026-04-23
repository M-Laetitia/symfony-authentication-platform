import ImageTool from '@editorjs/image';

export default class CustomImageTool extends ImageTool {

    render() {
        const wrapper = super.render();
        
        // Créer le champ pour l'alt text
        const altTextWrapper = document.createElement('div');
        altTextWrapper.classList.add('cdx-input', 'image-tool__alt-text');
        
        const altTextInput = document.createElement('input');
        altTextInput.placeholder = 'Image short description (alt text)';
        altTextInput.value = this.data.alt || '';
        altTextInput.classList.add('cdx-input');
        altTextInput.setAttribute('name', 'alt');
        
        // Écouter les changements
        altTextInput.addEventListener('input', (e) => {
            this.data.alt = e.target.value;
        });
        
        altTextWrapper.appendChild(altTextInput);
        wrapper.appendChild(altTextWrapper);
        
        return wrapper;
    }
    save(blockContent) {
        // console.log('Avant modification:', blockContent); 
        if (blockContent.file && blockContent.file.url) {
            const { url, id, width, height } = blockContent.file;
            blockContent.file = { url, id, width, height };
        }
        // console.log('Après modification:', blockContent); 
        return super.save(blockContent);
    }
}