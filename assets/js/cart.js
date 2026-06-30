/**
 * Smart Cafe - Cart Management
 * 
 * Handles all cart operations using localStorage.
 * Provides add, remove, update quantity, and total calculations.
 */

const SmartCart = (function() {
    'use strict';

    const CART_KEY = 'smartcafe_cart';
    const TABLE_KEY = 'smartcafe_table';

    // ---- Storage Operations ----

    /**
     * Get all cart items from localStorage
     */
    function getItems() {
        try {
            const data = localStorage.getItem(CART_KEY);
            return data ? JSON.parse(data) : [];
        } catch (e) {
            console.error('Error reading cart:', e);
            return [];
        }
    }

    /**
     * Save cart items to localStorage
     */
    function saveItems(items) {
        localStorage.setItem(CART_KEY, JSON.stringify(items));
        updateCartBadge();
        dispatchCartEvent();
    }

    // ---- Cart Operations ----

    /**
     * Add an item to the cart
     */
    function addItem(food) {
        const items = getItems();
        const existingIndex = items.findIndex(item => item.id === food.id);

        if (existingIndex > -1) {
            // Item already exists, increment quantity
            items[existingIndex].quantity += 1;
        } else {
            // Add new item
            items.push({
                id: food.id,
                name: food.name,
                price: parseFloat(food.price),
                image: food.image || 'default-food.jpg',
                quantity: 1
            });
        }

        saveItems(items);
        showToast('Added to cart!', food.name, 'success');
        return items;
    }

    /**
     * Remove an item from the cart
     */
    function removeItem(foodId) {
        let items = getItems();
        items = items.filter(item => item.id !== foodId);
        saveItems(items);
        return items;
    }

    /**
     * Update item quantity
     */
    function updateQuantity(foodId, newQty) {
        const items = getItems();
        const itemIndex = items.findIndex(item => item.id === foodId);

        if (itemIndex > -1) {
            if (newQty <= 0) {
                // Remove item if quantity is 0 or less
                items.splice(itemIndex, 1);
            } else {
                items[itemIndex].quantity = newQty;
            }
        }

        saveItems(items);
        return items;
    }

    /**
     * Clear the entire cart
     */
    function clearCart() {
        localStorage.removeItem(CART_KEY);
        updateCartBadge();
        dispatchCartEvent();
    }

    // ---- Calculations ----

    /**
     * Get total number of items in cart
     */
    function getItemCount() {
        const items = getItems();
        return items.reduce((total, item) => total + item.quantity, 0);
    }

    /**
     * Calculate subtotal
     */
    function getSubtotal() {
        const items = getItems();
        return items.reduce((total, item) => total + (item.price * item.quantity), 0);
    }

    /**
     * Calculate tax amount
     */
    function getTax(taxRate) {
        const rate = taxRate || 5; // Default 5% GST
        return (getSubtotal() * rate) / 100;
    }

    /**
     * Calculate grand total
     */
    function getTotal(taxRate) {
        return getSubtotal() + getTax(taxRate);
    }

    // ---- Table Management ----

    /**
     * Set the table number (from QR code)
     */
    function setTable(tableNumber) {
        localStorage.setItem(TABLE_KEY, tableNumber);
    }

    /**
     * Get the current table number
     */
    function getTable() {
        return localStorage.getItem(TABLE_KEY) || '';
    }

    // ---- UI Helpers ----

    /**
     * Update the cart badge count in the navbar
     */
    function updateCartBadge() {
        const badges = document.querySelectorAll('.cart-badge');
        const count = getItemCount();
        
        badges.forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.classList.add('show');
            } else {
                badge.classList.remove('show');
            }
        });
    }

    /**
     * Dispatch a custom event when cart changes
     */
    function dispatchCartEvent() {
        window.dispatchEvent(new CustomEvent('cartUpdated', {
            detail: {
                items: getItems(),
                count: getItemCount(),
                subtotal: getSubtotal()
            }
        }));
    }

    /**
     * Check if a food item is in the cart
     */
    function isInCart(foodId) {
        const items = getItems();
        return items.some(item => item.id === foodId);
    }

    /**
     * Get quantity of a specific item in cart
     */
    function getItemQuantity(foodId) {
        const items = getItems();
        const item = items.find(i => i.id === foodId);
        return item ? item.quantity : 0;
    }

    // Initialize badge on load
    document.addEventListener('DOMContentLoaded', updateCartBadge);

    // ---- Public API ----
    return {
        getItems,
        addItem,
        removeItem,
        updateQuantity,
        clearCart,
        getItemCount,
        getSubtotal,
        getTax,
        getTotal,
        setTable,
        getTable,
        updateCartBadge,
        isInCart,
        getItemQuantity
    };
})();

/* =========================================================================
   Global Cart UI Functions (Inline Quantity & Add to Cart)
   ========================================================================= */

/**
 * Syncs the "Add to Cart" buttons on food cards with the current cart state.
 * If an item is in the cart, it replaces the "Add" button with "- 1 +".
 */
function syncCartUI() {
    const controlContainers = document.querySelectorAll('.cart-controls');
    
    controlContainers.forEach(container => {
        const id = parseInt(container.getAttribute('data-food-id')) || 0;
        const name = container.getAttribute('data-food-name') || '';
        const price = parseFloat(container.getAttribute('data-food-price')) || 0;
        const image = container.getAttribute('data-food-image') || '';
        
        if (!id) return; // Skip if no valid ID
        
        const qty = SmartCart.getItemQuantity(id);
        
        if (qty > 0) {
            // Item is in cart -> Show inline quantity controls
            container.innerHTML = `
                <div class="inline-qty-controls">
                    <button class="btn-qty btn-qty-minus" onclick="updateFoodQuantity(event, ${id}, -1)">
                        <i class="bi bi-dash"></i>
                    </button>
                    <span class="qty-display">${qty}</span>
                    <button class="btn-qty btn-qty-plus" onclick="updateFoodQuantity(event, ${id}, 1)">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
            `;
        } else {
            // Item not in cart -> Show "Add" button
            // Escape attributes safely for the onclick handler
            const safeName = name.replace(/'/g, "\\'");
            const safeImage = image.replace(/'/g, "\\'");
            
            container.innerHTML = `
                <button class="btn-add-cart" onclick="addFoodToCart(event, ${id}, '${safeName}', ${price}, '${safeImage}')">
                    <i class="bi bi-plus-lg"></i> Add
                </button>
            `;
        }
    });
}

/**
 * Add food item to cart (initial add)
 */
function addFoodToCart(event, id, name, price, image) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    SmartCart.addItem({
        id: id,
        name: name,
        price: price,
        image: image
    });
    
    if (typeof showToast === 'function') {
        showToast('Added to Cart', name, 'success');
    }
}

/**
 * Update quantity inline
 */
function updateFoodQuantity(event, id, delta) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    
    const currentQty = SmartCart.getItemQuantity(id);
    const newQty = currentQty + delta;
    
    if (newQty <= 0) {
        SmartCart.removeItem(id);
    } else {
        SmartCart.updateQuantity(id, newQty);
    }
}

// Re-sync UI whenever the cart changes
window.addEventListener('cartUpdated', syncCartUI);

// Initial sync on page load
document.addEventListener('DOMContentLoaded', syncCartUI);
