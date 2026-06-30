<?php
/**
 * Smart Cafe - Checkout Page
 * 
 * Customer fills in details and places the order.
 * Cart data comes from localStorage, sent via JS to the API.
 */

require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';

$currentPage = 'cart';
$pageTitle = 'Checkout';
include __DIR__ . '/../includes/header.php';
?>

<!-- ======== PAGE HEADER ======== -->
<div class="page-header">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/pages/cart.php">Cart</a></li>
            <li class="breadcrumb-item active">Checkout</li>
        </ol>
    </nav>
    <h1 class="section-title"><i class="bi bi-shield-check"></i> Checkout</h1>
    <p class="section-subtitle">Complete your order details</p>
</div>

<section class="checkout-section">
    <div class="container">
        <div class="row g-4">
            <!-- Checkout Form -->
            <div class="col-lg-7">
                <div class="glass-card checkout-form">
                    <h4 style="font-family:var(--font-heading);font-weight:700;margin-bottom:1.5rem">
                        <i class="bi bi-person" style="color:var(--color-primary)"></i> Your Details
                    </h4>

                    <form id="checkoutForm" onsubmit="return placeOrder(event)">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label" for="customerName">Your Name</label>
                                <input type="text" class="form-control" id="customerName" placeholder="Enter your name" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="customerPhone">Phone Number</label>
                                <input type="tel" class="form-control" id="customerPhone" placeholder="10-digit mobile number" pattern="[6-9][0-9]{9}" title="Enter a valid 10-digit phone number" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label" for="tableNumber">Table Number</label>
                                <select class="form-select" id="tableNumber" required>
                                    <option value="">Select Table</option>
                                    <?php for ($i = 1; $i <= TOTAL_TABLES; $i++): ?>
                                    <option value="<?php echo $i; ?>">Table <?php echo $i; ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method</label>
                                <div>
                                    <div class="payment-option selected" onclick="selectPayment(this, 'cash')">
                                        <div class="payment-icon"><i class="bi bi-cash-coin"></i></div>
                                        <div>
                                            <div style="font-weight:600;font-size:0.9rem">Cash</div>
                                            <div style="font-size:0.75rem;color:var(--text-tertiary)">Pay at counter</div>
                                        </div>
                                        <input type="radio" name="payment" value="cash" checked style="display:none">
                                    </div>
                                    <div class="payment-option" onclick="selectPayment(this, 'upi')">
                                        <div class="payment-icon"><i class="bi bi-phone"></i></div>
                                        <div>
                                            <div style="font-weight:600;font-size:0.9rem">UPI</div>
                                            <div style="font-size:0.75rem;color:var(--text-tertiary)">Google Pay, PhonePe</div>
                                        </div>
                                        <input type="radio" name="payment" value="upi" style="display:none">
                                    </div>
                                    <div class="payment-option" onclick="selectPayment(this, 'card')">
                                        <div class="payment-icon"><i class="bi bi-credit-card"></i></div>
                                        <div>
                                            <div style="font-weight:600;font-size:0.9rem">Card</div>
                                            <div style="font-size:0.75rem;color:var(--text-tertiary)">Debit/Credit card</div>
                                        </div>
                                        <input type="radio" name="payment" value="card" style="display:none">
                                    </div>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label" for="specialInstructions">Special Instructions (Optional)</label>
                                <textarea class="form-control" id="specialInstructions" rows="3" placeholder="Any allergies, spice preferences, or special requests..."></textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary-smart w-100 justify-content-center mt-4 btn-lg-smart" id="placeOrderBtn">
                            <i class="bi bi-check-circle"></i> Place Order
                        </button>
                    </form>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-5">
                <div class="cart-summary" id="checkoutSummary">
                    <h3 class="cart-summary-title">Order Summary</h3>
                    <div id="checkoutItems">
                        <!-- Items rendered by JS -->
                    </div>
                    <hr style="border-color:var(--border-color)">
                    <div class="cart-summary-row">
                        <span>Subtotal</span>
                        <span id="checkSubtotal">₹ 0.00</span>
                    </div>
                    <div class="cart-summary-row">
                        <span>GST (<?php echo DEFAULT_TAX_RATE; ?>%)</span>
                        <span id="checkTax">₹ 0.00</span>
                    </div>
                    <div class="cart-summary-row total">
                        <span>Total</span>
                        <span id="checkTotal">₹ 0.00</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
const TAX_RATE = <?php echo DEFAULT_TAX_RATE; ?>;
const BASE_URL = '<?php echo BASE_URL; ?>';

document.addEventListener('DOMContentLoaded', function() {
    renderCheckoutSummary();
    autoFillTable();
});

/**
 * Render order summary in the sidebar
 */
function renderCheckoutSummary() {
    const items = SmartCart.getItems();
    
    if (items.length === 0) {
        // Redirect back to cart if empty
        window.location.href = BASE_URL + '/pages/cart.php';
        return;
    }

    const container = document.getElementById('checkoutItems');
    let html = '';

    items.forEach(function(item) {
        html += 
            '<div class="cart-summary-row" style="margin-bottom:0.75rem">' +
                '<div>' +
                    '<div style="font-weight:600;font-size:0.9rem">' + escapeHtml(item.name) + '</div>' +
                    '<div style="font-size:0.8rem;color:var(--text-tertiary)">' + formatPrice(item.price) + ' × ' + item.quantity + '</div>' +
                '</div>' +
                '<span style="font-weight:600">' + formatPrice(item.price * item.quantity) + '</span>' +
            '</div>';
    });

    container.innerHTML = html;

    // Totals
    const subtotal = SmartCart.getSubtotal();
    const tax = SmartCart.getTax(TAX_RATE);
    document.getElementById('checkSubtotal').textContent = formatPrice(subtotal);
    document.getElementById('checkTax').textContent = formatPrice(tax);
    document.getElementById('checkTotal').textContent = formatPrice(subtotal + tax);
}

/**
 * Auto-fill table number from localStorage or URL
 */
function autoFillTable() {
    const table = SmartCart.getTable();
    if (table) {
        document.getElementById('tableNumber').value = table;
    }
}

/**
 * Select payment method
 */
function selectPayment(element, method) {
    document.querySelectorAll('.payment-option').forEach(function(opt) {
        opt.classList.remove('selected');
        opt.querySelector('input').checked = false;
    });
    element.classList.add('selected');
    element.querySelector('input').checked = true;
}

/**
 * Place the order
 */
async function placeOrder(event) {
    event.preventDefault();

    const btn = document.getElementById('placeOrderBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Placing Order...';

    const items = SmartCart.getItems();
    const subtotal = SmartCart.getSubtotal();
    const tax = SmartCart.getTax(TAX_RATE);

    const orderData = {
        customer_name: document.getElementById('customerName').value.trim(),
        customer_phone: document.getElementById('customerPhone').value.trim(),
        table_number: parseInt(document.getElementById('tableNumber').value),
        payment_method: document.querySelector('input[name="payment"]:checked').value,
        special_instructions: document.getElementById('specialInstructions').value.trim(),
        subtotal: subtotal,
        tax: tax,
        total: subtotal + tax,
        items: items.map(function(item) {
            return {
                food_id: item.id,
                food_name: item.name,
                food_price: item.price,
                quantity: item.quantity,
                subtotal: item.price * item.quantity
            };
        })
    };

    try {
        const response = await fetch(BASE_URL + '/api/place_order.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(orderData)
        });

        const data = await response.json();

        if (data.success) {
            // Clear cart and redirect to success page
            SmartCart.clearCart();
            window.location.href = BASE_URL + '/pages/order-success.php?order=' + encodeURIComponent(data.order_number);
        } else {
            showToast('Order Failed', data.message || 'Please try again.', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Place Order';
        }
    } catch (error) {
        showToast('Network Error', 'Could not connect to server. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Place Order';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
