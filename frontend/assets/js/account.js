document.addEventListener('DOMContentLoaded', function() {
    // Charger les informations de l'utilisateur connecté
    loadUserInfo();

    // Gestionnaire pour la déconnexion
    const logoutBtn = document.getElementById('logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            logout();
        });
    }

    // Gestionnaire pour la recharge de solde
    const topupForm = document.getElementById('topup-form');
    if (topupForm) {
        topupForm.addEventListener('submit', handleTopupSubmit);
    }
});

function loadUserInfo() {
    const userInfo = document.getElementById('user-info');
    if (!userInfo) return;

    userInfo.innerHTML = `
        <p><strong>Prénom:</strong> <span id="user-firstname">Chargement...</span></p>
        <p><strong>Nom:</strong> <span id="user-lastname">Chargement...</span></p>
        <p><strong>Email:</strong> <span id="user-email">Chargement...</span></p>
        <p><strong>Téléphone:</strong> <span id="user-phone">Chargement...</span></p>
        <p><strong>Solde:</strong> <span id="user-balance">Chargement...</span></p>
    `;

    // Récupérer les vraies données de l'utilisateur courant по session
    fetch('../backend/user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ action: 'me' })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.success || !data.user) {
            // Si pas connecté, renvoyer vers la page de connexion
            window.location.href = 'login.html';
            return;
        }

        document.getElementById('user-firstname').textContent = data.user.prenom || '';
        document.getElementById('user-lastname').textContent = data.user.nom || '';
        document.getElementById('user-email').textContent = data.user.email || '';
        document.getElementById('user-phone').textContent = data.user.telephone || '';
        document.getElementById('user-balance').textContent = `${Number(data.user.solde || 0).toFixed(2)} €`;
    })
    .catch(error => {
        console.error('Erreur lors du chargement des informations utilisateur:', error);
        window.location.href = 'login.html';
    });
}

function logout() {
    // Utiliser la même déconnexion globale que le navbar
    if (typeof performLogout === 'function') {
        performLogout();
    } else {
        if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
            fetch('../backend/user.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'logout' })
            })
                .finally(() => {
                    window.location.href = 'index.html';
                });
        }
    }
}

function handleTopupSubmit(event) {
    event.preventDefault();

    const amountInput = document.getElementById('topup-amount');
    const messageEl = document.getElementById('topup-message');
    if (!amountInput || !messageEl) return;

    const raw = parseFloat(amountInput.value);
    if (isNaN(raw) || raw <= 0) {
        messageEl.textContent = 'Veuillez saisir un montant valide.';
        messageEl.classList.remove('text-success');
        messageEl.classList.add('text-danger');
        return;
    }

    messageEl.textContent = 'Traitement en cours...';
    messageEl.classList.remove('text-danger', 'text-success');

    fetch('../backend/user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'topup', amount: raw })
    })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                messageEl.textContent = data.message || 'Erreur lors de la recharge.';
                messageEl.classList.remove('text-success');
                messageEl.classList.add('text-danger');
                return;
            }

            const newBalance = Number(data.new_balance || 0);
            const balanceEl = document.getElementById('user-balance');
            if (balanceEl) {
                balanceEl.textContent = `${newBalance.toFixed(2)} €`;
            }

            messageEl.textContent = 'Fonds ajoutés avec succès.';
            messageEl.classList.remove('text-danger');
            messageEl.classList.add('text-success');
        })
        .catch(error => {
            console.error('Erreur lors de la recharge du solde:', error);
            messageEl.textContent = 'Erreur réseau. Veuillez réessayer.';
            messageEl.classList.remove('text-success');
            messageEl.classList.add('text-danger');
        });
}
