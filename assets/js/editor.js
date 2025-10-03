import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import Quote from '@editorjs/quote';
import SimpleImage from '@editorjs/simple-image';

console.log('EDITOR.JS EST CHARGÉ');
// alert('EDITOR.JS EST CHARGÉ');

document.addEventListener('DOMContentLoaded', function() {
    console.log(' DOMContentLoaded déclenché');
    
    // Vérifier que le holder existe
    const holder = document.getElementById('editorjs');
    console.log('Holder editorjs trouvé:', holder);
    
    // Vérifier que le champ caché existe
    const hiddenField = document.getElementById('article_form_content');
    console.log('Champ caché trouvé:', hiddenField);
    
    // Vérifier que le formulaire existe
    const form = document.querySelector('form');
    console.log('Formulaire trouvé:', form);
    
    if (!holder || !hiddenField || !form) {
        console.error('Éléments manquants !');
        return;
    }
    
    const editor = new EditorJS({
        holder: 'editorjs',
        tools: {
            header: Header,
            list: List,
            quote: Quote,
            image: SimpleImage,
        },
        placeholder: 'Commencez à écrire votre article...',
        data: window.articleContent || {},
        onReady: () => {
            console.log('EditorJS prêt');
        }
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        console.log('Submit intercepté');
        
        editor.save()
            .then((outputData) => {
                console.log('Données sauvegardées:', outputData);
                console.log('JSON stringifié:', JSON.stringify(outputData));
                
                hiddenField.value = JSON.stringify(outputData);
                console.log('Champ caché rempli avec:', hiddenField.value);
                
                // Vérifier une dernière fois
                setTimeout(() => {
                    console.log('Valeur finale du champ:', hiddenField.value);
                    console.log('Soumission du formulaire...');
                    form.submit();
                }, 200);
            })
            .catch((error) => {
                console.error('Erreur EditorJS:', error);
                alert('Erreur lors de la sauvegarde du contenu');
            });
    });
});