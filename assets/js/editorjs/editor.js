import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import List from '@editorjs/list';
import Quote from '@editorjs/quote';
import SimpleImage from '@editorjs/simple-image';
import ImageTool from '@editorjs/image';
import CustomImageTool from './editorjs/CustomImageTool';


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
            // image: SimpleImage,
            // image: {
            //     class: ImageTool,
            //     config: {
            //         endpoints: {
            //             byFile: 'http://localhost:8080/uploadFile',
            //             // byUrl: 'http://localhost:8008/fetchUrl', 
            //         }
            //     }
            // }
            image: {
                class: CustomImageTool, // utlisation plugin personnalisé
                config: {
                    endpoints: {
                        byFile: '/uploadFile',
                    },
                    // Adaptation de la réponse du backend pour utiliser l'ID
                    onUpload: (response) => {
                        return {
                            success: 1,
                            file: {
                                url: response.file.url,
                                id: response.file.id, // ID du média
                                width: response.file.width,
                                height: response.file.height,
                                caption: response.file.caption,
                            }
                        };
                    }
                }
            }
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