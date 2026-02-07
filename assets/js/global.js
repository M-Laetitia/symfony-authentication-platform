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

const accentColor = "#5FEEAB";
const grey900 = "#242424";

// hover effect icon-box
document.querySelectorAll('.icon-box__container').forEach(detail => {
    if (!detail) return;

    const link = detail.querySelector('.icon-box__link');
    const svg = detail.querySelector('.icon-box__svg');
    const icons = detail.querySelectorAll('.icon-box');  

    if (!link || !svg) return;

    const paths = svg.querySelectorAll('path');

    link.addEventListener('mouseenter', () => {
        paths.forEach(path => {
            path.style.fill = '#242424';
            path.style.stroke = 'transparent';
        });
        icons.forEach(icon => {
            icon.style.backgroundColor = accentColor;
            icon.style.borderColor = grey900;
        });
    });

    link.addEventListener('mouseleave', () => {
        paths.forEach(path => {
            path.style.fill = '#D2D2CE';
            path.style.stroke = 'transparent';
        });
        icons.forEach(icon => {
            icon.style.backgroundColor = grey900;
            icon.style.borderColor = accentColor;
        });
    });
});