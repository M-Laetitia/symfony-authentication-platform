window.openCancelProposalModal = function(proposalId) {
    // Ferme toutes les modales ouvertes
    document.querySelectorAll('.modal.proposal-modal.cancel').forEach(m => m.classList.remove('active'));
    // Ouvre la modale de la proposition demandée
    const modal = document.querySelector('#cancelProposalModal');
    if (modal) {
        modal.classList.add('active');
        // Fermer sur clic backdrop ou bouton annuler
        modal.querySelectorAll('.cancel-modal-btn, .modal-backdrop').forEach(btn => {
            btn.onclick = () => modal.classList.remove('active');
        });
    }
};

document.addEventListener('DOMContentLoaded', () => {
    // Fermer la modale sur ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal.proposal-modal.cancel.active').forEach(m => m.classList.remove('active'));
        }
    });
});
