document.addEventListener('DOMContentLoaded', () => {
    console.log("test hello")
    const form = document.getElementById('message-form');
    const input = document.getElementById('message-input');

    if (!form || !input) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const content = input.value;
        if (!content.trim()) return;

        // On envoie le form au serveur avec AJAX
        // form.action URL définie dans le form Twig ( >route SYmfony qui gère MessageFormType)
        // await on attend la réponse avant de continuer
        await fetch(form.action, {
            method: 'POST',
            // récupère les données du form (ici le content) pour l'envoyer en POST
            body: new FormData(form)
        });

        input.value = '';
    });
});