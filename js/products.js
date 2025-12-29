// Products page filtering, sorting, and wishlist functionality
(function () {

    // Wishlist toggle
    document.addEventListener('click', function (e) {
        if (e.target.closest('.wishlist-btn')) {
            e.preventDefault();
            const btn = e.target.closest('.wishlist-btn');
            const productId = btn.dataset.productId;

            toggleWishlist(productId, btn);
        }
    });

    function toggleWishlist(productId, btn) {
        const isInWishlist = btn.classList.contains('in-wishlist');
        const url = isInWishlist ? 'remove_from_wishlist.php' : 'add_to_wishlist.php';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    btn.classList.toggle('in-wishlist');
                    btn.innerHTML = data.in_wishlist ? '❤' : '♡';

                    // Update wishlist count in header
                    const wishlistCount = document.getElementById('wishlistCount');
                    if (wishlistCount) {
                        wishlistCount.textContent = data.wishlist_count;
                    }
                } else {
                    if (data.message === 'not_logged_in') {
                        window.location.href = 'login.php';
                    } else {
                        alert(data.message || 'Failed to update wishlist');
                    }
                }
            })
            .catch(error => {
                console.error('Wishlist error:', error);
                alert('Failed to update wishlist. Please try again.');
            });
    }

    // Filter and sort functionality
    const filterForm = document.getElementById('filterForm');
    const sortSelect = document.getElementById('sortSelect');

    if (filterForm) {
        filterForm.addEventListener('change', function () {
            applyFilters();
        });
    }

    if (sortSelect) {
        sortSelect.addEventListener('change', function () {
            applyFilters();
        });
    }

    function applyFilters() {
        const formData = new FormData(filterForm);
        if (sortSelect) {
            formData.append('sort', sortSelect.value);
        }

        const params = new URLSearchParams(formData);

        window.showLoading();

        fetch('products.php?' + params.toString())
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newGrid = doc.querySelector('.products-grid');
                const currentGrid = document.querySelector('.products-grid');

                if (newGrid && currentGrid) {
                    currentGrid.innerHTML = newGrid.innerHTML;
                }

                window.hideLoading();
            })
            .catch(error => {
                console.error('Filter error:', error);
                window.hideLoading();
            });
    }

    // Quick view modal
    document.addEventListener('click', function (e) {
        if (e.target.closest('.quick-view-btn')) {
            e.preventDefault();
            const productId = e.target.closest('.quick-view-btn').dataset.productId;
            openQuickView(productId);
        }
    });

    function openQuickView(productId) {
        // Create modal if it doesn't exist
        let modal = document.getElementById('quickViewModal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'quickViewModal';
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <span class="modal-close">&times;</span>
                    <div class="modal-body"></div>
                </div>
            `;
            document.body.appendChild(modal);

            modal.querySelector('.modal-close').addEventListener('click', function () {
                modal.style.display = 'none';
            });

            modal.addEventListener('click', function (e) {
                if (e.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Load product details
        window.showLoading();
        fetch('get_product_details.php?id=' + productId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayQuickView(data.product, modal);
                } else {
                    alert('Failed to load product details');
                }
                window.hideLoading();
            })
            .catch(error => {
                console.error('Quick view error:', error);
                window.hideLoading();
            });
    }

    function displayQuickView(product, modal) {
        const modalBody = modal.querySelector('.modal-body');
        const price = parseFloat(product.price).toFixed(2);

        modalBody.innerHTML = `
            <div class="quick-view-content">
                <div class="quick-view-image">
                    <img src="${product.image}" alt="${product.title}">
                </div>
                <div class="quick-view-info">
                    <h2>${product.title}</h2>
                    ${product.author ? `<p class="author">By ${product.author}</p>` : ''}
                    <p class="price">Rs. ${price}</p>
                    <p class="stock ${product.stock > 0 ? 'in-stock' : 'out-of-stock'}">
                        ${product.stock > 0 ? 'In Stock: ' + product.stock : 'Out of Stock'}
                    </p>
                    ${product.description ? `<p class="description">${product.description}</p>` : ''}
                    <div class="quick-view-actions">
                        <a href="product_detail.php?id=${product.id}" class="btn btn-primary">View Full Details</a>
                        ${product.stock > 0 ? `
                            <form method="post" action="add_to_cart.php" style="display: inline;">
                                <input type="hidden" name="id" value="${product.id}">
                                <input type="hidden" name="qty" value="1">
                                <button type="submit" class="btn btn-success">Add to Cart</button>
                            </form>
                        ` : ''}
                    </div>
                </div>
            </div>
        `;

        modal.style.display = 'block';
    }

})();
