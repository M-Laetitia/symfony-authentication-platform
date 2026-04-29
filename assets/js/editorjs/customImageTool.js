import ImageTool from '@editorjs/image';

export default class CustomImageTool extends ImageTool {

    constructor(props) {
        super(props);
        
        // Preserve custom data fields that ImageTool doesn't know about
        console.log('CustomImageTool constructor, props.data:', props.data);
        if (props.data) {
            this.data.alt = props.data.alt || '';
            this.data.caption = props.data.caption || '';
            console.log('Initialized custom data. Alt:', this.data.alt, 'Caption:', this.data.caption);
        }
    }

    render() {
        const wrapper = super.render();
        
        // Create/update caption field
        let captionWrapper = wrapper.querySelector('.image-tool__caption');
        let captionInput;
        
        if (captionWrapper) {
            // If wrapper exists, find or create the input inside it
            captionInput = captionWrapper.querySelector('input');
            if (!captionInput) {
                captionInput = document.createElement('input');
                captionInput.placeholder = 'Image caption (optional)';
                captionInput.classList.add('cdx-input');
                captionInput.setAttribute('name', 'caption');
                captionWrapper.appendChild(captionInput);
            }
        } else {
            // Create new wrapper and input
            captionWrapper = document.createElement('div');
            captionWrapper.classList.add('cdx-input', 'image-tool__caption');
            
            captionInput = document.createElement('input');
            captionInput.placeholder = 'Image caption (optional)';
            captionInput.classList.add('cdx-input');
            captionInput.setAttribute('name', 'caption');
            
            captionWrapper.appendChild(captionInput);
            wrapper.appendChild(captionWrapper);
        }
        
        // Set caption value
        captionInput.value = this.data.caption || '';
        captionInput.addEventListener('input', (e) => {
            this.data.caption = e.target.value;
        });
        
        // Create/update alt text field
        let altTextWrapper = wrapper.querySelector('.image-tool__alt-text');
        let altTextInput;
        
        if (altTextWrapper) {
            altTextInput = altTextWrapper.querySelector('input');
            if (!altTextInput) {
                altTextInput = document.createElement('input');
                altTextInput.placeholder = 'Image short description (alt text)';
                altTextInput.classList.add('cdx-input');
                altTextInput.setAttribute('name', 'alt');
                altTextWrapper.appendChild(altTextInput);
            }
        } else {
            altTextWrapper = document.createElement('div');
            altTextWrapper.classList.add('cdx-input', 'image-tool__alt-text');
            
            altTextInput = document.createElement('input');
            altTextInput.placeholder = 'Image short description (alt text)';
            altTextInput.classList.add('cdx-input');
            altTextInput.setAttribute('name', 'alt');
            
            altTextWrapper.appendChild(altTextInput);
            wrapper.appendChild(altTextWrapper);
        }
        
        // Set alt text value
        altTextInput.value = this.data.alt || '';
        altTextInput.addEventListener('input', (e) => {
            this.data.alt = e.target.value;
        });
        
        return wrapper;
    }

    save(blockContent) {
        // Manually retrieve values from inputs to avoid saving DOM elements
        const caption = this.data.caption || '';
        const alt = this.data.alt || '';
        
        // Let parent handle file data
        const savedData = super.save(blockContent);
        
        // Merge with our custom fields
        return {
            ...savedData,
            caption: caption,
            alt: alt
        };
    }
}