const form = document.getElementById('message-form');
const input = form?.querySelector('textarea, input');
const conversationId = form?.dataset.conversationId;
const chatContainer = document.getElementById('chat-messages');

if (form && input && conversationId && chatContainer) {

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        console.log('Submit intercepted');
        const content = input.value.trim();
        if (!content) return;

        const formData = new URLSearchParams(new FormData(form));
        const response = await fetch(form.action, {
            method: 'POST',
            body: formData.toString(),
            redirect: 'manual',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded',
            },
        });

        console.log('Réponse:', response.status);
        form.reset();
        input.value = '';
    });

    const url = new URL('http://localhost:3000/.well-known/mercure');
    url.searchParams.append('topic', '/conversation/' + conversationId);

    const eventSource = new EventSource(url, {
        withCredentials: true
    });
    eventSource.onopen = () => console.log('Connected to Mercure');
    eventSource.onerror = (e) => console.error('Mercure error', e);

    eventSource.onmessage = (event) => {
        const data = JSON.parse(event.data);
        const messageEl = document.createElement('div');
        messageEl.className = 'chat-message';
        messageEl.innerHTML = `<strong>${data.author}</strong>: ${data.content}`;
        chatContainer.appendChild(messageEl);
        chatContainer.scrollTop = chatContainer.scrollHeight;
    };
}