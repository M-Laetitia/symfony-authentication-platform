import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';
import './js/global.js';

import GLightbox from 'glightbox';
import Isotope from 'isotope-layout';

import "glightbox/dist/css/glightbox.css";
// GLightbox init
const lightbox = GLightbox({
    selector: '.js-gallery-lightbox'
});
// Isotope init
const iso = new Isotope('.js-gallery-grid', {
    itemSelector: '.js-gallery-item',
    layoutMode: 'fitRows'
});
// Filter buttons
document.querySelectorAll('.js-filter-button').forEach(button => {
    button.addEventListener('click', () => {
        const filter = button.getAttribute('data-filter');
        iso.arrange({ filter: filter });
    });
});



if (document.querySelector('#conversation-page')) {
    import('./js/chat-mercure.js');
}

// console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');
