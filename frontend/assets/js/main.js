// Main application logic
document.addEventListener('DOMContentLoaded', function() {
    loadProducts();
});

function loadProducts() {
    // Simulate loading products from backend
    const products = [
        { id: 1, name: 'Product 1', price: 10.99, description: 'Description 1' },
        { id: 2, name: 'Product 2', price: 15.99, description: 'Description 2' },
        { id: 3, name: 'Product 3', price: 20.99, description: 'Description 3' }
    ];

    const productsList = document.getElementById('products-list');
    if (productsList) {
        products.forEach(product => {
            const productElement = document.createElement('div');
            productElement.className = 'col-md-4 mb-4';
            productElement.innerHTML = `
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">${product.name}</h5>
                        <p class="card-text">${product.description}</p>
                        <p class="card-text">$${product.price}</p>
                        <button class="btn btn-primary" onclick="addToCart(${product.id}, '${product.name}', ${product.price})">Add to Cart</button>
                    </div>
                </div>
            `;
            productsList.appendChild(productElement);
        });
    }
}
