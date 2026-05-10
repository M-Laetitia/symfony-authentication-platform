//& ---------- Scroll to top button -----------
const scrollBtn = document.getElementById('scrollTopBtn');

if (window.scrollY > 300) { 
    scrollBtn.classList.add('visible');
} else {
    scrollBtn.classList.remove('visible');
}

window.addEventListener('scroll', () => {
    if (window.scrollY > 300) { 
        scrollBtn.classList.add('visible');
    } else {
        scrollBtn.classList.remove('visible');
    }
});

scrollBtn.addEventListener('click', () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
});


document.addEventListener('DOMContentLoaded', () => {
    //& ------------- Flash messages --------------
    const flashes = document.querySelectorAll('.flash-message');
    if (flashes.length) {
        flashes.forEach(flash => {
            setTimeout(() => flash.classList.add('show'), 50);

            if (!flash.querySelector('.flash-close')) {
                setTimeout(() => {
                    flash.classList.remove('show'); 
                    setTimeout(() => flash.remove(), 500); 
                }, 4000);
            }
        });

        document.querySelectorAll('.flash-close').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const flash = e.target.closest('.flash-message');
                flash.style.transition = 'opacity 0.5s ease';
                flash.style.opacity = '0';
                setTimeout(() => flash.remove(), 500);
            });
        });
    }

    //& -------------- Menu burger ----------------
    const burger = document.querySelector('.navbar__burger');
    const menu = document.querySelector('.navbar__menu');

    if (burger && menu) {
        burger.addEventListener('click', function() {
            const isOpen = menu.classList.toggle('is-open');
            this.setAttribute('aria-expanded', isOpen);
            menu.setAttribute('aria-hidden', !isOpen);
        });
    }

    //& ------------- Toggle Password -------------
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            input.type = input.type === 'password' ? 'text' : 'password';
        });
    });

    //& ---------- Password requirements ----------
    const passwordInput = document.querySelector('#registration_form_plainPassword_first');
    const requirements = document.querySelector('.password-requirements');

    if (passwordInput && requirements) {
        requirements.classList.remove('show');

        passwordInput.addEventListener('input', function() {
            requirements.classList.toggle('show', this.value.length > 0);
        });

        passwordInput.addEventListener('blur', function() {
            if (this.value.length === 0) {
                requirements.classList.remove('show');
            }
        });

        const lengthReq = document.getElementById('length');
        const uppercaseReq = document.getElementById('uppercase');
        const numberReq = document.getElementById('number');
        const specialReq = document.getElementById('special');

        passwordInput.addEventListener('input', () => {
            const value = passwordInput.value;
            lengthReq?.classList.toggle('valid', value.length >= 12);
            lengthReq?.classList.toggle('invalid', value.length < 12);
            uppercaseReq?.classList.toggle('valid', /[A-Z]/.test(value));
            uppercaseReq?.classList.toggle('invalid', !/[A-Z]/.test(value));
            numberReq?.classList.toggle('valid', /\d/.test(value));
            numberReq?.classList.toggle('invalid', !/\d/.test(value));
            specialReq?.classList.toggle('valid', /[^A-Za-z0-9]/.test(value));
            specialReq?.classList.toggle('invalid', !/[^A-Za-z0-9]/.test(value));
        });
    }
});

//& ------------- Hover icon-box --------------
const accentColor = "#5FEEAB";
const grey900 = "#242424";

document.querySelectorAll('.icon-box__container').forEach(detail => {
    if (!detail) return;

    const link = detail.querySelector('.section-about__feature-text');
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

//& -------- Display/hide aside menu ----------
const toggle = document.querySelector('.side-panel__toggle');
const panel = document.querySelector('.side-panel');
const overlay = document.querySelector('.side-panel__overlay');

function togglePanel() {
    const isOpen = panel.classList.toggle('side-panel--open');

    panel.style.transform = '';

    overlay.classList.toggle('side-panel__overlay--visible', isOpen);
    toggle.classList.toggle('active', isOpen);

    toggle.setAttribute('aria-expanded', isOpen);
    panel.setAttribute('aria-hidden', !isOpen);

    document.body.style.overflow = isOpen ? 'hidden' : '';
}

toggle.addEventListener('click', () => {
    togglePanel();
});
overlay.addEventListener('click', togglePanel);

document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && panel.classList.contains('side-panel--open')) {
        togglePanel();
    }
});


//& ------ Hero parallax --------  
const parallaxImage = document.querySelector('[data-parallax]');

if (parallaxImage) {
    let scrollY = 0;
    let ticking = false;
    
    function updateParallax() {
        parallaxImage.style.transform = `translateY(${scrollY * 0.3}px)`;
        ticking = false;
    }
    
    window.addEventListener('scroll', () => {
        scrollY = window.scrollY;
        
        if (!ticking) {
            window.requestAnimationFrame(updateParallax);
            ticking = true;
        }
    }, { passive: true });
}
