/**
 * Smart Cafe - Live Search
 * 
 * Debounced search with fetch API.
 * Works on the menu page to filter food items in real-time.
 */

(function() {
    'use strict';

    const searchInput = document.getElementById('menuSearch');
    const searchClear = document.getElementById('searchClear');
    const foodGrid = document.getElementById('foodGrid');

    if (!searchInput || !foodGrid) return;

    let searchTimeout = null;

    /**
     * Perform search with debouncing (300ms delay)
     */
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        // Show/hide clear button
        if (searchClear) {
            searchClear.classList.toggle('show', query.length > 0);
        }

        // Clear previous timeout
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Debounce the search
        searchTimeout = setTimeout(function() {
            if (query.length === 0) {
                // If empty, reload all items
                loadMenuItems();
                return;
            }

            if (query.length < 2) return; // Minimum 2 characters

            searchMenuItems(query);
        }, 300);
    });

    /**
     * Clear search input and reload all items
     */
    if (searchClear) {
        searchClear.addEventListener('click', function() {
            searchInput.value = '';
            searchClear.classList.remove('show');
            loadMenuItems();
            searchInput.focus();
        });
    }

    /**
     * Search food items via API
     */
    async function searchMenuItems(query) {
        showSkeletonLoading();

        try {
            const data = await apiRequest('search.php?q=' + encodeURIComponent(query));
            if (data.success && data.data) {
                renderFoodCards(data.data);
            } else {
                showNoResults(query);
            }
        } catch (error) {
            console.error('Search error:', error);
        }
    }

    /**
     * Load all menu items (used when clearing search)
     */
    async function loadMenuItems(categoryId, filters) {
        showSkeletonLoading();

        let endpoint = 'get_menu.php?';
        if (categoryId) endpoint += 'category=' + categoryId + '&';
        if (filters) {
            if (filters.veg_only) endpoint += 'veg_only=1&';
            if (filters.sort) endpoint += 'sort=' + filters.sort + '&';
        }

        try {
            const data = await apiRequest(endpoint);
            if (data.success && data.data && data.data.length > 0) {
                renderFoodCards(data.data);
            } else {
                showNoResults();
            }
        } catch (error) {
            console.error('Load error:', error);
        }
    }

    /**
     * Render food cards into the grid
     */
    function renderFoodCards(foods) {
        foodGrid.innerHTML = '';

        foods.forEach(function(food) {
            const isInCart = SmartCart.isInCart(food.id);
            const vegBadge = food.is_veg == 1
                ? '<span class="badge-veg" title="Vegetarian"><i class="bi bi-circle-fill" style="font-size:0.5rem"></i></span>'
                : '<span class="badge-nonveg" title="Non-Vegetarian"><i class="bi bi-triangle-fill" style="font-size:0.5rem"></i></span>';

            const featuredBadge = food.is_featured == 1
                ? '<span class="badge-featured"><i class="bi bi-star-fill"></i> Popular</span>'
                : '';

            const card = document.createElement('div');
            card.className = 'food-card';
            card.setAttribute('data-food-id', food.id);
            card.setAttribute('data-category', food.category_id);

            card.innerHTML =
                '<div class="food-card-image">' +
                    '<img src="' + food.image_url + '" alt="' + escapeHtml(food.name) + '" loading="lazy" onerror="this.src=\'' + getBaseUrl() + '/assets/images/default-food.jpg\'">' +
                    '<div class="food-card-badges">' + vegBadge + '</div>' +
                    featuredBadge +
                '</div>' +
                '<div class="food-card-body">' +
                    '<div class="food-card-category">' + escapeHtml(food.category_name || '') + '</div>' +
                    '<h3 class="food-card-name">' + escapeHtml(food.name) + '</h3>' +
                    '<p class="food-card-desc">' + escapeHtml(food.description || '') + '</p>' +
                    '<div class="food-card-footer">' +
                        '<div>' +
                            '<span class="food-card-price">' + formatPrice(food.price) + '</span>' +
                            '<div class="food-card-meta"><i class="bi bi-clock"></i> ' + food.prep_time + ' min</div>' +
                        '</div>' +
                        '<button class="btn-add-cart' + (isInCart ? ' added' : '') + '" ' +
                            'onclick="addFoodToCart(event, ' + food.id + ', \'' + escapeAttr(food.name) + '\', ' + food.price + ', \'' + escapeAttr(food.image_url) + '\')">' +
                            '<i class="bi ' + (isInCart ? 'bi-check-lg' : 'bi-plus-lg') + '"></i> ' +
                            (isInCart ? 'Added' : 'Add') +
                        '</button>' +
                    '</div>' +
                '</div>';

            foodGrid.appendChild(card);
        });
    }

    /**
     * Show skeleton loading cards
     */
    function showSkeletonLoading() {
        foodGrid.innerHTML = '';
        for (let i = 0; i < 6; i++) {
            const skeleton = document.createElement('div');
            skeleton.className = 'skeleton-card';
            skeleton.innerHTML =
                '<div class="skeleton skeleton-image"></div>' +
                '<div style="padding: 1rem;">' +
                    '<div class="skeleton skeleton-text short"></div>' +
                    '<div class="skeleton skeleton-text"></div>' +
                    '<div class="skeleton skeleton-text medium"></div>' +
                '</div>';
            foodGrid.appendChild(skeleton);
        }
    }

    /**
     * Show no results message
     */
    function showNoResults(query) {
        foodGrid.innerHTML =
            '<div class="empty-cart" style="grid-column: 1 / -1;">' +
                '<div class="empty-cart-icon"><i class="bi bi-search"></i></div>' +
                '<h3>No items found</h3>' +
                '<p>' + (query ? 'No results for "' + escapeHtml(query) + '". Try a different search.' : 'No items available in this category.') + '</p>' +
            '</div>';
    }

    // Expose for use by other scripts
    window.loadMenuItems = loadMenuItems;
    window.renderFoodCards = renderFoodCards;
    window.showSkeletonLoading = showSkeletonLoading;

})();


/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Escape for HTML attributes
 */
function escapeAttr(text) {
    if (!text) return '';
    return text.replace(/'/g, "\\'").replace(/"/g, '\\"');
}
