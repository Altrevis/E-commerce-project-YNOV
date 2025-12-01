// Gestion du passage de commande côté frontend
let currentUser = null;

document.addEventListener('DOMContentLoaded', () => {
    loadCurrentUser();
    renderCheckoutSummary();

    const form = document.getElementById('checkout-form');
    if (form) {
        form.addEventListener('submit', handleCheckoutSubmit);
    }
});

function loadCurrentUser() {
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
            window.location.href = 'login.html';
            return;
        }

        currentUser = data.user;

        const balanceEl = document.getElementById('current-balance');
        if (balanceEl) {
            balanceEl.textContent = `${Number(currentUser.solde || 0).toFixed(2)} €`;
        }
    })
    .catch(() => {
        window.location.href = 'login.html';
    });
}

function renderCheckoutSummary() {
    const summaryEl = document.getElementById('checkout-summary');
    const totalEl = document.getElementById('checkout-total');
    if (!summaryEl || !totalEl) return;

    const cartItems = getCartItems();
    summaryEl.innerHTML = '';

    if (cartItems.length === 0) {
        summaryEl.innerHTML = '<p class="text-muted">Votre panier est vide.</p>';
        totalEl.textContent = '0.00 €';
        return;
    }

    cartItems.forEach(item => {
        const row = document.createElement('div');
        row.className = 'd-flex justify-content-between border-bottom py-2';
        row.innerHTML = `
            <div>
                <strong>${item.name}</strong>
                <div class="small text-muted">${item.quantity} x ${item.price.toFixed(2)} €</div>
            </div>
            <div class="fw-semibold">${(item.price * item.quantity).toFixed(2)} €</div>
        `;
        summaryEl.appendChild(row);
    });

    totalEl.textContent = `${getCartTotal().toFixed(2)} €`;
}

function handleCheckoutSubmit(event) {
    event.preventDefault();

    const cartItems = getCartItems();
    if (cartItems.length === 0) {
        alert('Votre panier est vide.');
        return;
    }

    if (!currentUser) {
        alert('Vous devez être connecté pour passer commande.');
        window.location.href = 'login.html';
        return;
    }

    const payload = {
        cart: cartItems,
        shipping: {
            address: document.getElementById('shipping-address').value,
            city: document.getElementById('shipping-city').value,
            postal: document.getElementById('shipping-postal').value
        }
    };

    toggleCheckoutLoading(true);

    fetch('../backend/orders.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
    })
    .then(response => response.json())
    .then(data => {
        toggleCheckoutLoading(false);

        if (!data.success) {
            alert(data.message || 'Impossible de finaliser la commande.');
            return;
        }

        clearCart();
        alert('Commande confirmée ! Un reçu PDF vous a été envoyé par email.');
        window.location.href = 'account.html';
    })
    .catch(error => {
        console.error('Erreur lors du passage de commande:', error);
        toggleCheckoutLoading(false);
        alert('Erreur réseau. Veuillez réessayer.');
    });
}

function toggleCheckoutLoading(isLoading) {
    const submitBtn = document.querySelector('#checkout-form button[type="submit"]');
    if (!submitBtn) return;

    submitBtn.disabled = isLoading;
    submitBtn.textContent = isLoading ? 'Traitement en cours...' : 'Confirmer la commande';
}

