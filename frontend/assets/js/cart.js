// Gestion du panier côté frontend (stocké dans localStorage)
let cart = JSON.parse(localStorage.getItem('cart')) || [];
let isUserAuthenticated = false;
let authStatusChecked = false;

function persistCart() {
    localStorage.setItem('cart', JSON.stringify(cart));
}

function getCartItems() {
    return cart.map(item => ({ ...item }));
}

function getCartTotal() {
    return cart.reduce((total, item) => total + item.price * item.quantity, 0);
}

async function ensureAuthStatus() {
    if (authStatusChecked) {
        return;
    }
    try {
        const response = await fetch('../backend/user.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'me' })
        });
        const data = await response.json();
        isUserAuthenticated = !!(data.success && data.user);
    } catch (error) {
        console.error('Erreur lors de la vérification de session:', error);
        isUserAuthenticated = false;
    } finally {
        authStatusChecked = true;
    }
}

async function addToCart(productId, name, price) {
    if (!authStatusChecked) {
        await ensureAuthStatus();
    }
    if (!isUserAuthenticated) {
        alert('Veuillez vous connecter ou créer un compte avant d\'ajouter des produits au panier.');
        window.location.href = 'login.html';
        return;
    }

    const existingItem = cart.find(item => item.id === productId);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ id: productId, name, price, quantity: 1 });
    }
    persistCart();
    updateCartDisplay();
}

function removeFromCart(productId) {
    cart = cart.filter(item => item.id !== productId);
    persistCart();
    updateCartDisplay();
}

function clearCart() {
    cart = [];
    persistCart();
    updateCartDisplay();
}

function updateCartDisplay() {
    const cartCount = document.getElementById('cart-count');
    if (cartCount) {
        cartCount.textContent = cart.reduce((total, item) => total + item.quantity, 0);
    }

    const totalElement = document.getElementById('cart-total');
    if (totalElement) {
        totalElement.textContent = `${getCartTotal().toFixed(2)} €`;
    }

    if (document.getElementById('cart-items')) {
        displayCart();
    }
}

function displayCart() {
    const cartItems = document.getElementById('cart-items');
    if (!cartItems) return;

    cartItems.innerHTML = '';

    if (cart.length === 0) {
        cartItems.innerHTML = '<p class="text-muted">Votre panier est vide.</p>';
        return;
    }

    cart.forEach(item => {
        const itemElement = document.createElement('div');
        itemElement.className = 'cart-item d-flex justify-content-between align-items-center border-bottom py-2';
        itemElement.innerHTML = `
            <div>
                <strong>${item.name}</strong>
                <div class="small text-muted">${item.price.toFixed(2)} € x ${item.quantity}</div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold">${(item.price * item.quantity).toFixed(2)} €</span>
                <button class="btn btn-sm btn-outline-danger" onclick="removeFromCart(${item.id})">Supprimer</button>
            </div>
        `;
        cartItems.appendChild(itemElement);
    });
}

// Initialisation à chaque chargement de page
document.addEventListener('DOMContentLoaded', () => {
    cart = JSON.parse(localStorage.getItem('cart')) || [];
    ensureAuthStatus();
    updateCartDisplay();
});
