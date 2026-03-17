// FLash messages
document.addEventListener('DOMContentLoaded', () => {
    const flashes = document.querySelectorAll('.flash-message');
    if (!flashes.length) return;

    flashes.forEach(flash => {
        setTimeout(() => flash.classList.add('show'), 50);

        if (!flash.querySelector('.flash-close')) {
            setTimeout(() => {
                flash.classList.remove('show'); 
                setTimeout(() => flash.remove(), 500); 
            }, 4000);
        }
    });

    // Close btn
    document.querySelectorAll('.flash-close').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const flash = e.target.closest('.flash-message');
            flash.style.transition = 'opacity 0.5s ease';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        });
    });
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

    if (!passwordInput) {
        return
    };
  
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

  // modal report message
  document.querySelectorAll('.btn-report').forEach(button => {
    button.addEventListener('click', () => {
        const msgId = button.dataset.messageId;
        document.getElementById('report-message-id').value = msgId;
        document.getElementById('report-modal').style.display = 'flex';
    });
});

// Modale de confirmation d'acceptation de proposition
console.log('JavaScript chargé !');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM chargé, recherche des formulaires...');
    
    // Intercepter les formulaires d'acceptation
    const acceptForms = document.querySelectorAll('.proposal-accept-form');
    console.log('Formulaires acceptation trouvés:', acceptForms.length);
    
    acceptForms.forEach(function(form) {
        console.log('Formulaire trouvé:', form);
        console.log('Action du formulaire:', form.getAttribute('action')); // Utilise getAttribute au lieu de .action
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Submit intercepté !');
            
            // Forcer la récupération avec getAttribute
            const actionUrl = this.getAttribute('action');
            console.log('URL récupérée:', actionUrl);
            
            if (!actionUrl || actionUrl.includes('[object')) {
                console.error('URL invalide:', actionUrl);
                return;
            }
            
            fetch(actionUrl, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Status:', response.status);
                if (!response.ok) {
                    return response.json().then(data => {
                        alert(data.error);
                        throw new Error(data.error);
                    });
                }
                return response.text();
            })
            .then(html => {
                console.log('Modale reçue, taille:', html.length);
                
                // Supprimer toute modale existante
                const existingModal = document.querySelector('#confirmationModal, #refuseModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                // Injecter la modale
                document.body.insertAdjacentHTML('beforeend', html);
            })
            .catch(err => {
                console.error('Erreur fetch:', err);
            });
        });
    });
    
    // Même chose pour les formulaires de refus
    const refuseForms = document.querySelectorAll('.proposal-refuse-form');
    console.log('Formulaires refus trouvés:', refuseForms.length);
    
    refuseForms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Submit refus intercepté !');
            
            const actionUrl = this.getAttribute('action');
            console.log('URL refus récupérée:', actionUrl);
            
            fetch(actionUrl, {
                method: 'POST',
                body: new FormData(this),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(data => {
                        alert(data.error);
                        throw new Error(data.error);
                    });
                }
                return response.text();
            })
            .then(html => {
                console.log('Modale refus reçue');
                
                const existingModal = document.querySelector('#confirmationModal, #refuseModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                document.body.insertAdjacentHTML('beforeend', html);
            })
            .catch(err => {
                console.error('Erreur fetch refus:', err);
            });
        });
    });
});

// Fermer les modales
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('cancel-modal-btn') || e.target.textContent === 'Annuler') {
        console.log('Fermeture modale');
        const modal = e.target.closest('.modal');
        if (modal) {
            modal.remove();
        }
    }
});