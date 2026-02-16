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

// display / hide aside menu
const toggle = document.querySelector('.side-panel__toggle');
const panel = document.querySelector('.side-panel');
const overlay = document.querySelector('.side-panel__overlay');

function togglePanel() {
    const isOpen = panel.classList.toggle('side-panel--open');

    overlay.classList.toggle('side-panel__overlay--visible', isOpen);

    toggle.setAttribute('aria-expanded', isOpen);
    panel.setAttribute('aria-hidden', !isOpen);

    document.body.style.overflow = isOpen ? 'hidden' : '';
}

toggle.addEventListener('click', () => {
    toggle.classList.toggle('active');
    togglePanel();
    const panel = document.getElementById(toggle.getAttribute('aria-controls'));
    const isExpanded = toggle.classList.contains('active');
    toggle.setAttribute('aria-expanded', isExpanded);
    panel.style.transform = isExpanded ? 'translateX(0)' : 'translateX(-100%)';
});
overlay.addEventListener('click', togglePanel);

document.addEventListener('keydown', e => {
    if (e.key === 'Escape' && panel.classList.contains('side-panel--open')) {
        togglePanel();
    }
});


// menu burger

document.addEventListener('DOMContentLoaded', function() {
    const burger = document.querySelector('.navbar__burger');
    const menu = document.querySelector('.navbar__menu');

    if (burger && menu) {
        burger.addEventListener('click', function() {
            const isOpen = menu.classList.toggle('is-open');

            this.setAttribute('aria-expanded', isOpen);
            menu.setAttribute('aria-hidden', !isOpen);
        });
    }
});


// toggle password register form
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('.toggle-password');

    toggleButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
            } else {
                input.type = 'password';
            }
        });
    });
});

// show requierements 
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.querySelector('#registration_form_plainPassword_first');
    const requirements = document.querySelector('.password-requirements');

    if (!passwordInput || !requirements) return;

    requirements.classList.remove('show');

    passwordInput.addEventListener('input', function() {
        if (this.value.length > 0) {
            requirements.classList.add('show'); 
        } else {
            requirements.classList.remove('show'); 
        }
    });

    passwordInput.addEventListener('blur', function() {
        if (this.value.length === 0) {
            requirements.classList.remove('show');
        }
    });
});

// requierement for password
document.addEventListener('DOMContentLoaded', function () {

    const passwordInput = document.querySelector('#registration_form_plainPassword_first');

    const lengthReq = document.getElementById('length');
    const uppercaseReq = document.getElementById('uppercase');
    const numberReq = document.getElementById('number');
    const specialReq = document.getElementById('special');
  
    passwordInput.addEventListener('input', () => {
      const value = passwordInput.value;
  
      // Lenght ≥ 8
      lengthReq.classList.toggle('valid', value.length >= 8);
      lengthReq.classList.toggle('invalid', value.length < 8);
  
      // Maj
      uppercaseReq.classList.toggle('valid', /[A-Z]/.test(value));
      uppercaseReq.classList.toggle('invalid', !/[A-Z]/.test(value));
  
      // Number
      numberReq.classList.toggle('valid', /\d/.test(value));
      numberReq.classList.toggle('invalid', !/\d/.test(value));
  
      // special 
      specialReq.classList.toggle('valid', /[^A-Za-z0-9]/.test(value));
      specialReq.classList.toggle('invalid', !/[^A-Za-z0-9]/.test(value));
    });
  });
