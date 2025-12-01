// Main application logic pour la page d'accueil (index.html)
document.addEventListener('DOMContentLoaded', function() {
    loadHomeProductsWithCounters();
});

function loadHomeProductsWithCounters() {
    fetch('../backend/products.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(products => {
            const productsList = document.getElementById('products-list');
            if (!productsList) return;

            const currentCart = typeof getCartItems === 'function' ? getCartItems() : [];

            productsList.innerHTML = '';
            products.forEach(product => {
                const quantityInCart =
                    currentCart.find(item => item.id === product.id_produit)?.quantity || 0;

                const productElement = document.createElement('div');
                productElement.className = 'col-md-4 mb-4';
                productElement.innerHTML = `
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">${product.nom_produit}</h5>
                            <p class="card-text flex-grow-1">
                                ${product.description || 'No description available'}
                            </p>
                            <p class="card-text fw-semibold mb-3">${Number(product.prix).toFixed(2)} €</p>
                            <div class="d-flex align-items-center justify-content-between">
                                <button
                                    class="btn btn-primary btn-add-to-cart me-2"
                                    data-id="${product.id_produit}"
                                    data-name="${product.nom_produit.replace(/"/g, '&quot;')}"
                                    data-price="${product.prix}"
                                >
                                    Add to Cart
                                </button>
                                <span
                                    class="badge bg-secondary"
                                    data-product-qty-id="${product.id_produit}"
                                >
                                    Dans le panier: ${quantityInCart}
                                </span>
                            </div>
                        </div>
                    </div>
                `;
                productsList.appendChild(productElement);
            });

            attachHomeAddToCartHandlers();
        })
        .catch(error => {
            console.error('Error loading products:', error);
            const productsList = document.getElementById('products-list');
            if (productsList) {
                productsList.innerHTML = '<p class="text-center">Erreur lors du chargement des produits.</p>';
            }
        });
}

function attachHomeAddToCartHandlers() {
    const buttons = document.querySelectorAll('.btn-add-to-cart');
    buttons.forEach(button => {
        button.addEventListener('click', async () => {
            const id = parseInt(button.dataset.id, 10);
            const name = button.dataset.name;
            const price = parseFloat(button.dataset.price);

            try {
                await addToCart(id, name, price);

                if (typeof getCartItems === 'function') {
                    const updatedCart = getCartItems();
                    const item = updatedCart.find(i => i.id === id);
                    const qtySpan = document.querySelector(
                        `[data-product-qty-id="${id}"]`
                    );
                    if (qtySpan && item) {
                        qtySpan.textContent = `Dans le panier: ${item.quantity}`;
                    }
                }
            } catch (e) {
                console.error('Erreur lors de l’ajout au panier:', e);
            }
        });
    });
}
