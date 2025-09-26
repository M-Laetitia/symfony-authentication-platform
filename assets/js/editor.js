import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import Quote from '@editorjs/quote';
import SimpleImage from '@editorjs/simple-image';

const editor = new EditorJS({
    holder: 'editorjs',
    tools: {
        header: Header,
        list: List,
        paragraph: Paragraph,
        quote: Quote,
        image: SimpleImage,
    },
    placeholder: 'Commencez à écrire votre article...',
    data: window.articleContent || {}, // charge le contenu existant depuis Twig
});

// gérer le submit du formulaire
const form = document.getElementById('articleForm');
if (form) {
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const outputData = await editor.save();
        document.getElementById('article_content').value = JSON.stringify(outputData);
        form.submit();
    });
}
