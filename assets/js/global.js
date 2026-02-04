// Gestion automatique des messages flash
document.addEventListener('DOMContentLoaded', () => {
    const flashes = document.querySelectorAll('.flash-message');
    if (!flashes.length) return;

    setTimeout(() => {
        flashes.forEach(element => {
            element.style.transition = 'opacity 0.5s';
            element.style.opacity = '0';
            setTimeout(() => element.remove(), 500);
        });
    }, 4000);
    
    
});