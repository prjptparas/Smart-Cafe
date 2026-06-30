<?php
/**
 * Smart Cafe - Cart Page
 * 
 * Displays cart items from localStorage (rendered via JavaScript).
 * Shows quantity controls, price summary, and checkout button.
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$currentPage = 'cart';
$pageTitle = 'Your Cart';
include __DIR__ . '/../includes/header.php';
?>

<!-- ======== PAGE HEADER ======== -->
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/pages/menu.php">Menu</a></li>
            <li class="breadcrumb-item active">Cart</li>
        </ol>
    </nav>
    <h1 class="section-title"><i class="bi bi-bag"></i> Your Cart</h1>
    <p class="section-subtitle" id="cartSubtitle">Loading your cart...</p>
</div>

<section class="cart-section">
    <div class="container">
        <div class="row g-4">
            <!-- Cart Items -->
            <div class="col-lg-8">
                <div id="cartItems">
                    <!-- Cart items will be rendered here by JavaScript -->
                    <div class="loading-spinner">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary -->
            <div class="col-lg-4">
                <div class="cart-summary" id="cartSummary" style="display:none">
                    <h3 class="cart-summary-title">Order Summary</h3>
                    
                    <div class="cart-summary-row">
                        <span>Subtotal</span>
                        <span id="summarySubtotal">₹ 0.00</span>
                    </div>
                    <div class="cart-summary-row">
                        <span>GST (<?php echo DEFAULT_TAX_RATE; ?>%)</span>
                        <span id="summaryTax">₹ 0.00</span>
                    </div>
                    <div class="cart-summary-row total">
                        <span>Total</span>
                        <span id="summaryTotal">₹ 0.00</span>
                    </div>

                    <a href="<?php echo BASE_URL; ?>/pages/checkout.php" class="btn-primary-smart w-100 justify-content-center mt-3" id="checkoutBtn">
                        <i class="bi bi-shield-check"></i> Proceed to Checkout
                    </a>

                    <a href="<?php echo BASE_URL; ?>/pages/menu.php" class="btn-outline-smart w-100 justify-content-center mt-2" style="font-size:var(--font-size-sm)">
                        <i class="bi bi-plus-lg"></i> Add More Items
                    </a>

                    <button class="btn-outline-smart w-100 justify-content-center mt-2" style="font-size:var(--font-size-sm);color:var(--color-danger);border-color:rgba(231,76,60,0.3)" onclick="clearEntireCart()">
                        <i class="bi bi-trash3"></i> Clear Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
/**
 * Render the cart page using data from SmartCart (localStorage)
 */
document.addEventListener('DOMContentLoaded', function() {
    renderCartPage();
});

function renderCartPage() {
    const items = SmartCart.getItems();
    const container = document.getElementById('cartItems');
    const summary = document.getElementById('cartSummary');
    const subtitle = document.getElementById('cartSubtitle');

    if (items.length === 0) {
        // Empty cart
        container.innerHTML = 
            '<div class="empty-cart">' +
                '<div class="empty-cart-icon"><i class="bi bi-bag-x"></i></div>' +
                '<h3>Your cart is empty</h3>' +
                '<p>Looks like you haven\'t added anything to your cart yet. Start exploring our delicious menu!</p>' +
                '<a href="<?php echo BASE_URL; ?>/pages/menu.php" class="btn-primary-smart">' +
                    '<i class="bi bi-grid-fill"></i> Browse Menu' +
                '</a>' +
            '</div>';
        summary.style.display = 'none';
        subtitle.textContent = 'Your cart is empty';
        return;
    }

    subtitle.textContent = items.length + ' item' + (items.length !== 1 ? 's' : '') + ' in your cart';
    summary.style.display = '';

    // Render items
    let html = '';
    items.forEach(function(item) {
        const itemTotal = item.price * item.quantity;
        html += 
            '<div class="cart-item" data-cart-id="' + item.id + '">' +
                '<div class="cart-item-image">' +
                    '<img src="' + (item.image || '<?php echo BASE_URL; ?>/assets/images/default-food.jpg') + '" ' +
                         'alt="' + escapeHtml(item.name) + '" ' +
                         'onerror="this.src=\'<?php echo BASE_URL; ?>/assets/images/default-food.jpg\'">' +
                '</div>' +
                '<div class="cart-item-info">' +
                    '<h4 class="cart-item-name">' + escapeHtml(item.name) + '</h4>' +
                    '<span class="cart-item-price">' + formatPrice(item.price) + ' each</span>' +
                '</div>' +
                '<div class="qty-control">' +
                    '<button class="qty-btn" onclick="updateCartQty(' + item.id + ', ' + (item.quantity - 1) + ')">' +
                        '<i class="bi bi-dash"></i>' +
                    '</button>' +
                    '<span class="qty-value">' + item.quantity + '</span>' +
                    '<button class="qty-btn" onclick="updateCartQty(' + item.id + ', ' + (item.quantity + 1) + ')">' +
                        '<i class="bi bi-plus"></i>' +
                    '</button>' +
                '</div>' +
                '<div class="cart-item-total">' + formatPrice(itemTotal) + '</div>' +
                '<button class="cart-item-remove" onclick="removeCartItem(' + item.id + ')" title="Remove">' +
                    '<i class="bi bi-trash3"></i>' +
                '</button>' +
            '</div>';
    });
    container.innerHTML = html;

    // Update summary
    const taxRate = <?php echo DEFAULT_TAX_RATE; ?>;
    const subtotal = SmartCart.getSubtotal();
    const tax = SmartCart.getTax(taxRate);
    const total = subtotal + tax;

    document.getElementById('summarySubtotal').textContent = formatPrice(subtotal);
    document.getElementById('summaryTax').textContent = formatPrice(tax);
    document.getElementById('summaryTotal').textContent = formatPrice(total);
}

function updateCartQty(foodId, newQty) {
    SmartCart.updateQuantity(foodId, newQty);
    renderCartPage();
}

function removeCartItem(foodId) {
    SmartCart.removeItem(foodId);
    renderCartPage();
    showToast('Removed', 'Item removed from cart', 'info');
}

function clearEntireCart() {
    if (confirm('Are you sure you want to clear your cart?')) {
        SmartCart.clearCart();
        renderCartPage();
        showToast('Cart Cleared', 'All items removed', 'info');
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
