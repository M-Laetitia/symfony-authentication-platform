import GLightbox from 'glightbox';
import Isotope from 'isotope-layout';

import "glightbox/dist/css/glightbox.css";


// -------------------------------------------------------
// Portfolio page (photographer show) — Isotope + GLightbox
// -------------------------------------------------------
if (document.querySelector('.portfolio-page')) {
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

    // see more button
    const moreBtn = document.querySelector('.js-gallery-more');
    if (moreBtn) {
        moreBtn.addEventListener('click', (e) => {
            showAll = true;
            applyFilter();
            e.target.style.display = 'none';
        });
    }
}

// -------------------------------------------------------
// Gallery page (/gallery) — GLightbox + Isotope
// -------------------------------------------------------
if (document.querySelector('.gallery-page')) {
    GLightbox({
        selector: '.js-gallery-lightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: false
    });

    const isoGrid = document.querySelector('.js-gallery-grid');
    const iso = new Isotope(isoGrid, {
        itemSelector: '.js-gallery-item',
        layoutMode: 'masonry',
        percentPosition: true
    });

    iso.once('arrangeComplete', () => {
        isoGrid.classList.add('is-ready');
    });

    let showAll = false;
    let currentFilter = '*';
    const INITIAL_LIMIT = 12;

    const buttons = document.querySelectorAll('.js-filter-button');
    const moreBtn = document.querySelector('.js-gallery-more');
    const allItems = isoGrid.querySelectorAll('.js-gallery-item');
    const descriptionBox = document.querySelector('.js-series-description');

    function resetHiddenState() {
        let visibleCount = 0;
        allItems.forEach(item => {
            const matchesFilter = currentFilter === '*' || item.matches(currentFilter);
            if (matchesFilter) {
                visibleCount++;
                if (visibleCount > INITIAL_LIMIT) {
                    item.classList.add('is-hidden');
                } else {
                    item.classList.remove('is-hidden');
                }
            } else {
                item.classList.remove('is-hidden');
            }
        });

        if (moreBtn) {
            const hiddenAndMatching = [...allItems].filter(item => {
                return item.classList.contains('is-hidden') &&
                    (currentFilter === '*' || item.matches(currentFilter));
            });
            moreBtn.style.display = hiddenAndMatching.length > 0 ? '' : 'none';
        }
    }

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            currentFilter = button.getAttribute('data-filter');
            showAll = false;
            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Handle description display
            if (descriptionBox) {
                const description = button.getAttribute('data-description');
                if (description && description.trim() !== '') {
                    descriptionBox.textContent = description;
                    descriptionBox.classList.add('is-visible');
                } else {
                    descriptionBox.classList.remove('is-visible');
                }
            }
            
            resetHiddenState();
            applyFilter();
        });
    });

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

    resetHiddenState();
    applyFilter();

    if (moreBtn) {
        moreBtn.addEventListener('click', () => {
            showAll = true;
            allItems.forEach(item => item.classList.remove('is-hidden'));
            applyFilter();
            moreBtn.style.display = 'none';
        });
    }
}

// -------------------------------------------------------
// Gallery photographer (/photographer/slug/portfolio) — GLightbox + Isotope
// -------------------------------------------------------

if (document.querySelector('.photographer-portfolio-page')) {
    GLightbox({
        selector: '.js-gallery-lightbox',
        touchNavigation: true,
        loop: true,
        autoplayVideos: false
    });

    const isoGrid = document.querySelector('.js-gallery-grid');
    const iso = new Isotope(isoGrid, {
        itemSelector: '.js-gallery-item',
        layoutMode: 'masonry',
        percentPosition: true
    });

    iso.once('arrangeComplete', () => {
        isoGrid.classList.add('is-ready');
    });

    let showAll = false;
    let currentFilter = '*';
    const INITIAL_LIMIT = 16;

    const buttons = document.querySelectorAll('.js-filter-button');
    const moreBtn = document.querySelector('.js-gallery-more');
    const allItems = isoGrid.querySelectorAll('.js-gallery-item');
    const descriptionBox = document.querySelector('.js-series-description');

    function resetHiddenState() {
        let visibleCount = 0;
        allItems.forEach(item => {
            const matchesFilter = currentFilter === '*' || item.matches(currentFilter);
            if (matchesFilter) {
                visibleCount++;
                if (visibleCount > INITIAL_LIMIT) {
                    item.classList.add('is-hidden');
                } else {
                    item.classList.remove('is-hidden');
                }
            } else {
                item.classList.remove('is-hidden');
            }
        });

        if (moreBtn) {
            const hiddenAndMatching = [...allItems].filter(item => {
                return item.classList.contains('is-hidden') &&
                    (currentFilter === '*' || item.matches(currentFilter));
            });
            moreBtn.style.display = hiddenAndMatching.length > 0 ? '' : 'none';
        }
    }

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            currentFilter = button.getAttribute('data-filter');
            showAll = false;
            buttons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Handle description display
            if (descriptionBox) {
                const description = button.getAttribute('data-description');
                if (description && description.trim() !== '') {
                    descriptionBox.innerHTML = '<p>' + description + '</p>';
                    descriptionBox.classList.add('is-visible');
                } else {
                    descriptionBox.classList.remove('is-visible');
                }
            }
            
            resetHiddenState();
            applyFilter();
        });
    });

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

    resetHiddenState();
    applyFilter();

    if (moreBtn) {
        moreBtn.addEventListener('click', () => {
            showAll = true;
            allItems.forEach(item => item.classList.remove('is-hidden'));
            applyFilter();
            moreBtn.style.display = 'none';
        });
    }
}