// Search functionality with real-time AJAX
(function () {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimeout;

    if (!searchInput || !searchResults) return;

    // Debounced search function
    searchInput.addEventListener('input', function () {
        const query = this.value.trim();

        clearTimeout(searchTimeout);

        if (query.length < 2) {
            searchResults.style.display = 'none';
            searchResults.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 300);
    });

    // Perform AJAX search
    function performSearch(query) {
        fetch('search.php?q=' + encodeURIComponent(query))
            .then(response => response.json())
            .then(data => {
                displayResults(data);
            })
            .catch(error => {
                console.error('Search error:', error);
                searchResults.innerHTML = '<div class="search-error">Search failed. Please try again.</div>';
                searchResults.style.display = 'block';
            });
    }

    // Display search results
    function displayResults(results) {
        if (results.length === 0) {
            searchResults.innerHTML = '<div class="no-results">No products found</div>';
            searchResults.style.display = 'block';
            return;
        }

        let html = '<div class="search-results-list">';
        results.forEach(product => {
            const price = parseFloat(product.price).toFixed(2);
            html += `
                <a href="product_detail.php?id=${product.id}" class="search-result-item">
                    <img src="${product.image}" alt="${product.title}" class="search-result-image">
                    <div class="search-result-info">
                        <div class="search-result-title">${product.title}</div>
                        ${product.author ? `<div class="search-result-author">${product.author}</div>` : ''}
                        <div class="search-result-price">Rs. ${price}</div>
                    </div>
                </a>
            `;
        });
        html += '</div>';

        searchResults.innerHTML = html;
        searchResults.style.display = 'block';
    }

    // Close search results when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-container')) {
            searchResults.style.display = 'none';
        }
    });

    // Keyboard navigation
    searchInput.addEventListener('keydown', function (e) {
        const items = searchResults.querySelectorAll('.search-result-item');
        if (items.length === 0) return;

        let currentIndex = -1;
        items.forEach((item, index) => {
            if (item.classList.contains('active')) {
                currentIndex = index;
            }
        });

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (currentIndex < items.length - 1) {
                if (currentIndex >= 0) items[currentIndex].classList.remove('active');
                items[currentIndex + 1].classList.add('active');
                items[currentIndex + 1].scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            if (currentIndex > 0) {
                items[currentIndex].classList.remove('active');
                items[currentIndex - 1].classList.add('active');
                items[currentIndex - 1].scrollIntoView({ block: 'nearest' });
            }
        } else if (e.key === 'Enter') {
            if (currentIndex >= 0) {
                e.preventDefault();
                items[currentIndex].click();
            }
        }
    });
})();
