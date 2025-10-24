document.addEventListener('DOMContentLoaded', function() {
    // Charger les informations de l'utilisateur
    loadUserInfo();

    // Gestionnaire pour la déconnexion
    document.getElementById('logout').addEventListener('click', function(e) {
        e.preventDefault();
        logout();
    });
});

function loadUserInfo() {
    // Simuler le chargement des informations utilisateur
    // En réalité, cela devrait être récupéré depuis le backend avec une session
    const userInfo = document.getElementById('user-info');
    userInfo.innerHTML = `
        <p><strong>Prénom:</strong> <span id="user-firstname">Chargement...</span></p>
        <p><strong>Nom:</strong> <span id="user-lastname">Chargement...</span></p>
        <p><strong>Email:</strong> <span id="user-email">Chargement...</span></p>
        <p><strong>Téléphone:</strong> <span id="user-phone">Chargement...</span></p>
    `;

    // Ici, vous devriez faire un appel fetch pour récupérer les vraies données utilisateur
    // Pour l'instant, on simule avec des données fictives
    setTimeout(() => {
        document.getElementById('user-firstname').textContent = 'Jean';
        document.getElementById('user-lastname').textContent = 'Dupont';
        document.getElementById('user-email').textContent = 'jean.dupont@example.com';
        document.getElementById('user-phone').textContent = '0123456789';
    }, 1000);
}

function logout() {
    // Simuler la déconnexion
    // En réalité, cela devrait détruire la session côté serveur
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        // Rediriger vers la page d'accueil ou de connexion
        window.location.href = 'index.html';
    }
}
