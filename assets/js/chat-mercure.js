function initConversationChat() {
    const conversationSection = document.querySelector('[data-page="conversation"]');
    if (!conversationSection) return;

    const form = document.getElementById('message-form');
    // Optional chaining (?.) : évite un TypeError si form est null
    const input = form?.querySelector('textarea, input');
    const conversationId = form?.dataset.conversationId;
    const chatContainer = document.getElementById('chat-messages');

    if (!form || !input || !conversationId || !chatContainer) return;

    setupForm(form, input);
    setupMercure(conversationId, chatContainer);
}

function setupForm(form, input) {
    // async sur le handler pour pouvoir utiliser await dans le callback
    form.addEventListener('submit', async (e) => {
        e.preventDefault();  // bloque la soumission native qui provoquerait un rechargement
        const content = input.value.trim();
        if (!content) return;
        await sendMessage(form, input); // attend la fin de la requête avant de reset
    });
}

async function sendMessage(form, input) {
    // URLSearchParams(new FormData(form)) : sérialise le formulaire au format
    // application/x-www-form-urlencoded (clé=valeur&clé=valeur),
    // ce qui inclut automatiquement le token CSRF Symfony
    const formData = new URLSearchParams(new FormData(form));
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData.toString(),
            redirect: 'manual',
            // redirect: 'manual' empêche fetch de suivre silencieusement les redirections 302
            // sans ça, fetch suivrait le redirect et retournerait un 200 trompeur
            headers: {
                'X-Requested-With': 'XMLHttpRequest', // convention pour identifier les requêtes AJAX côté serveur
                'Content-Type': 'application/x-www-form-urlencoded',
            },
        });
        // response.type === 'opaqueredirect' : cas où redirect:'manual' intercepte un 302
        // le navigateur ne peut pas lire le statut réel, mais la requête a bien abouti
        if (response.ok || response.type === 'opaqueredirect') {
            form.reset();
            input.value = '';
        } else {
            console.error('Error when sending message:', response.status);
        }
    } catch (error) {
        // catch intercepte uniquement les erreurs réseau (pas de connexion, timeout...)
        // les erreurs HTTP (4xx, 5xx) sont gérées par le bloc if/else ci-dessus
        console.error('Network error:', error);
    }
}

function setupMercure(conversationId, chatContainer) {
    // Construction de l'URL avec URLSearchParams pour encoder correctement le topic
    // ex: /conversation/5 → encodé en %2Fconversation%2F5
    const url = new URL('http://localhost:3000/.well-known/mercure');
    url.searchParams.append('topic', '/conversation/' + conversationId);
    // withCredentials: true indispensable pour que le navigateur envoie
    // le cookie mercureAuthorization à Mercure (cross-origin)
    const eventSource = new EventSource(url, { withCredentials: true });
    eventSource.onopen = () => console.log('Connected to Mercure');
    eventSource.onerror = (e) => console.error('Mercure error', e);
    // onmessage : déclenché à chaque événement SSE reçu depuis le hub Mercure
    eventSource.onmessage = (event) => appendMessage(JSON.parse(event.data), chatContainer);
}

function appendMessage(data, chatContainer) {
    const messageEl = document.createElement('div');
    messageEl.className = 'chat-message';

    const strong = document.createElement('strong');
    strong.textContent = data.author; // textContent échappe automatiquement le HTML
    const text = document.createTextNode(': ' + data.content); // idem
    
    messageEl.appendChild(strong);
    messageEl.appendChild(text);
    chatContainer.appendChild(messageEl);

    // scrollTop = scrollHeight : force le scroll vers le dernier message
    chatContainer.scrollTop = chatContainer.scrollHeight;
}


initConversationChat();