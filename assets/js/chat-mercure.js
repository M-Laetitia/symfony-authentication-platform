document.addEventListener('DOMContentLoaded', () => {
    console.log("test hello")
    const form = document.getElementById('message-form');
    const input = document.getElementById('message-input');

    if (!form || !input) return;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const content = input.value;
        if (!content.trim()) return;

        await fetch(form.action, {
            method: 'POST',
            body: new FormData(form)
        });

        input.value = '';
    });
});