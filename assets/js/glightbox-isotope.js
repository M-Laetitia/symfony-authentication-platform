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
    layoutMode: 'fitRows',
    percentPosition: true
});

let showAll = false;
let currentFilter = '*';

// Filters
const buttons = document.querySelectorAll('.js-filter-button');

buttons.forEach(button => {
  button.addEventListener('click', () => {
    currentFilter = button.getAttribute('data-filter');

    buttons.forEach(btn => btn.classList.remove('active'));
    button.classList.add('active');

    applyFilter();
  });
});

// see more button
document.querySelector('.js-gallery-more').addEventListener('click', () => {
  showAll = true;
  applyFilter();
});

// Global function
function applyFilter() {
  iso.arrange({
    filter: function(itemElem) {
      const matchesFilter = currentFilter === '*' || itemElem.matches(currentFilter);
      const isHidden = itemElem.classList.contains('is-hidden');

      if (showAll) return matchesFilter;

      return matchesFilter && !isHidden;
    }
  });
}
applyFilter();
document.querySelector('.js-gallery-more').addEventListener('click', (e) => {
  showAll = true;
  applyFilter();
  e.target.style.display = 'none';
});