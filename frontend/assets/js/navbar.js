// Gestion globale de la barre de navigation en fonction de la session utilisateur

document.addEventListener('DOMContentLoaded', () => {
    updateNavbarAuthState();
    attachLogoutHandlers();
});

function updateNavbarAuthState() {
    fetch('../backend/user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'me' })
    })
        .then(response => response.json())
        .then(data => {
            const isLoggedIn = !!(data.success && data.user);

            const navAccount = document.querySelectorAll('.nav-link-account');
            const navLogin = document.querySelectorAll('.nav-link-login');
            const navLogout = document.querySelectorAll('.nav-link-logout');

            navAccount.forEach(el => {
                el.style.display = isLoggedIn ? '' : 'none';
            });

            navLogout.forEach(el => {
                el.style.display = isLoggedIn ? '' : 'none';
            });

            navLogin.forEach(el => {
                el.style.display = isLoggedIn ? 'none' : '';
            });
        })
        .catch(err => {
            console.error('Erreur lors de la mise à jour du navbar:', err);
        });
}

function attachLogoutHandlers() {
    const logoutLinks = document.querySelectorAll('.nav-link-logout');
    logoutLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            performLogout();
        });
    });
}

function performLogout() {
    if (!confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        return;
    }

    fetch('../backend/user.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'logout' })
    })
        .then(response => response.json())
        .then(data => {
            // On ignore le résultat exact, on force l’UI en mode non connecté
            if (typeof localStorage !== 'undefined') {
                // Optionnel : vider le panier côté client
                // localStorage.removeItem('cart');
            }
            window.location.href = 'index.html';
        })
        .catch(err => {
            console.error('Erreur lors de la déconnexion:', err);
            window.location.href = 'index.html';
        });
}

