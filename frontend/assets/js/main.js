// Main application logic
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
});

function loadProducts() {
    fetch('../backend/products.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(products => {
            const productsList = document.getElementById('products-list');
            if (productsList) {
                products.forEach(product => {
                    const productElement = document.createElement('div');
                    productElement.className = 'col-md-4 mb-4';
                    productElement.innerHTML = `
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">${product.nom_produit}</h5>
                                <p class="card-text">${product.description || 'No description available'}</p>
                                <p class="card-text">$${product.prix}</p>
                                <button class="btn btn-primary" onclick="addToCart(${product.id_produit}, '${product.nom_produit}', ${product.prix})">Add to Cart</button>
                            </div>
                        </div>
                    `;
                    productsList.appendChild(productElement);
                });
            }
        })
        .catch(error => {
            console.error('Error loading products:', error);
            const productsList = document.getElementById('products-list');
            if (productsList) {
                productsList.innerHTML = '<p class="text-center">Erreur lors du chargement des produits.</p>';
            }
        });
}
