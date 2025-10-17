import ImageTool from '@editorjs/image';

export default class CustomImageTool extends ImageTool {
    save(blockContent) {
        // console.log('Avant modification:', blockContent); 
        debugger; 
        if (blockContent.file && blockContent.file.url) {
            const { id, width, height } = blockContent.file;
            blockContent.file = { id, width, height };
        }
        // console.log('Après modification:', blockContent); 
        return super.save(blockContent);
    }
}