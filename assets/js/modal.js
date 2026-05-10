//& -------------- proposal modal -------------  
window.openCancelProposalModal = function(proposalId) {
    document.querySelectorAll('.modal.proposal-modal.cancel').forEach(m => m.classList.remove('active'));
    const modal = document.querySelector('#cancelProposalModal');
    if (modal) {
        modal.classList.add('active');
        modal.querySelectorAll('.cancel-modal-btn, .modal-backdrop').forEach(btn => {
            btn.onclick = () => modal.classList.remove('active');
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.proposal-modal.cancel.active').forEach(m => m.classList.remove('active'));
        }
    });
});


//& ------ Confirmation modal proposal --------  
document.addEventListener('DOMContentLoaded', function() {

    const acceptForms = document.querySelectorAll('.proposal-accept-form');
    
    acceptForms.forEach(function(form) {
        console.log('Formulaire trouvé:', form);
        console.log('Action du formulaire:', form.getAttribute('action')); 
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const actionUrl = this.getAttribute('action');
            
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

                const existingModal = document.querySelector('#confirmationModal, #refuseModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                document.body.insertAdjacentHTML('beforeend', html);
                
                const newModal = document.querySelector('#confirmationModal, #refuseModal');
                if (newModal) {
                    newModal.classList.add('active');
                }
            })
            .catch(err => {
                console.error('Erreur fetch:', err);
            });
        });
    });
    

//& -------- Refusal modal proposal -----------  
    const refuseForms = document.querySelectorAll('.proposal-refuse-form');
    
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
                
                const existingModal = document.querySelector('#confirmationModal, #refuseModal');
                if (existingModal) {
                    existingModal.remove();
                }
                
                document.body.insertAdjacentHTML('beforeend', html);
                
                // Ajouter la classe active pour afficher la modale
                const newModal = document.querySelector('#confirmationModal, #refuseModal');
                if (newModal) {
                    newModal.classList.add('active');
                }
            })
            .catch(err => {
                console.error('Erreur fetch refus:', err);
            });
        });
    });
});

document.addEventListener('click', function(e) {

    if (e.target.classList.contains('cancel-modal-btn')) {
        console.log('Fermeture modale (cancel-modal-btn clicked)');
        const modal = e.target.closest('.modal');
        if (modal) {
            modal.style.display = 'none';
        }
    }
});



//& -------- Modal report conversation -------- 
  const reportConversationBtn = document.getElementById('report-conversation-btn');
  const reportModal = document.getElementById('report-modal');
  const modalClose = document.getElementById('modal-close');
  const reportForm = document.getElementById('report-conversation-form');
  
  if (reportConversationBtn) {
    reportConversationBtn.addEventListener('click', () => {
        if (!reportConversationBtn.disabled) {
            reportModal.classList.add('active');
        }
    });
  }
  
  if (modalClose) {
    modalClose.addEventListener('click', () => {
        reportModal.classList.remove('active');
    });
  }
  
  if (reportModal) {
    reportModal.addEventListener('click', (e) => {
        if (e.target === reportModal) {
            reportModal.classList.remove('active');
        }
    });
  }
  
  if (reportForm) {
    reportForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const reason = document.getElementById('report-reason').value;
        const messageReference = document.getElementById('report-message').value;
        const conversationId = document.querySelector('[data-conversation-id]').dataset.conversationId;
        
        try {
            const response = await fetch(`/chat/conversation/${conversationId}/report`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    reason: reason,
                    message_reference: messageReference
                })
            });
            
            if (response.ok) {
                reportForm.reset();
                reportModal.classList.remove('active');
                location.reload();
            } else {
                alert('Error reporting conversation. Please try again.');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error reporting conversation.');
        }
    });
  }
  

//& ---------- Dashboard media edit -----------  
const toggleEditBtn = document.getElementById('toggleEditBtn');
const editFormContainer = document.getElementById('editFormContainer');
const cancelEditBtn = document.getElementById('cancelEditBtn');

if (toggleEditBtn && editFormContainer) {
    toggleEditBtn.addEventListener('click', function() {
        if (editFormContainer.style.display === 'none') {
            editFormContainer.style.display = 'block';
            this.textContent = 'Hide Edit';
            editFormContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        } else {
            editFormContainer.style.display = 'none';
            this.textContent = 'Edit Info';
        }
    });
}

if (cancelEditBtn && editFormContainer) {
    cancelEditBtn.addEventListener('click', function() {
        editFormContainer.style.display = 'none';
        if (toggleEditBtn) {
            toggleEditBtn.textContent = 'Edit Info';
        }
    });
}


//& ---- modal confirmation delete account ----  
if (document.body.classList.contains('page--profile')) {
    const deleteBtn = document.getElementById('delete-account-btn');
    const modal = document.getElementById('delete-account-modal');
    const closeBtn = document.getElementById('modal-close');
    const cancelBtn = document.getElementById('modal-cancel');
    const overlay = document.getElementById('modal-overlay');

    if (deleteBtn && modal) {
        deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            modal.classList.add('active');
            modal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
        });

        function closeModal() {
            modal.classList.remove('active');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        if (closeBtn) closeBtn.addEventListener('click', closeModal);
        if (cancelBtn) cancelBtn.addEventListener('click', closeModal);
        if (overlay) overlay.addEventListener('click', closeModal);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });
    }
}