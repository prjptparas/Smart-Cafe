</main>
<!-- Main Content End -->

<!-- ======== FOOTER ======== -->
<footer class="footer-smart">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <h4 style="font-family:var(--font-heading);font-weight:700;margin-bottom:1rem;color:var(--text-primary)">
                    <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" alt="Smart Cafe Logo" style="height:30px; margin-right:8px; vertical-align:middle;"> <?php echo APP_NAME; ?>
                </h4>
                <p class="footer-desc mt-2">
                    Delicious food delivered right to your table. Scan, order, and enjoy a seamless dining experience.
                </p>
                <div class="d-flex gap-2 mt-3">
                    <span class="btn-icon"><i class="bi bi-facebook"></i></span>
                    <span class="btn-icon"><i class="bi bi-instagram"></i></span>
                    <span class="btn-icon"><i class="bi bi-twitter-x"></i></span>
                </div>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5 class="footer-heading">Quick Links</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>/index.php">Home</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pages/menu.php">Menu</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pages/cart.php">Cart</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/pages/track-order.php">Track Order</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="footer-heading">Support</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo BASE_URL; ?>/pages/feedback.php">Feedback</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/qr/generate.php">QR Codes</a></li>
                    <li><a href="<?php echo BASE_URL; ?>/admin/">Admin Panel</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6">
                <h5 class="footer-heading">Contact</h5>
                <ul class="footer-links">
                    <li><i class="bi bi-geo-alt me-2" style="color:var(--color-primary)"></i> 123 Food Street, Tech City</li>
                    <li><i class="bi bi-telephone me-2" style="color:var(--color-primary)"></i> +91 98765 43210</li>
                    <li><i class="bi bi-envelope me-2" style="color:var(--color-primary)"></i> hello@smartcafe.com</li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Crafted with <i class="bi bi-heart-fill" style="color:var(--color-danger)"></i> for great dining.</p>
        </div>
    </div>
</footer>

<!-- Global Food Detail Modal -->
<div class="modal fade" id="foodDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border: none; border-radius: var(--radius-lg); overflow: hidden; background: var(--bg-primary);">
            <div class="modal-header border-0 position-absolute w-100" style="z-index: 10; justify-content: flex-end; padding: 15px;">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="background-color: rgba(0,0,0,0.5); border-radius: 50%; padding: 10px;"></button>
            </div>
            <img src="" id="modalFoodImage" class="food-modal-img" alt="Food Image">
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div id="modalFoodCategory" style="color: var(--color-primary); font-weight: 700; font-size: 0.85rem; text-transform: uppercase;">Category</div>
                        <h3 id="modalFoodName" style="font-family: var(--font-heading); font-weight: 800; margin-bottom: 5px;">Food Name</h3>
                    </div>
                    <div id="modalFoodPrice" style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary);">₹0.00</div>
                </div>
                
                <p id="modalFoodDesc" class="text-muted" style="font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;">Description goes here.</p>
                
                <h6 style="font-family: var(--font-heading); font-weight: 700; margin-bottom: 10px;">Estimated Nutritional Info</h6>
                <div class="d-flex gap-2 mb-4">
                    <div class="macro-box">
                        <div class="macro-val" id="macroCal">0</div>
                        <div class="macro-label">Kcal</div>
                    </div>
                    <div class="macro-box">
                        <div class="macro-val" id="macroPro">0g</div>
                        <div class="macro-label">Protein</div>
                    </div>
                    <div class="macro-box">
                        <div class="macro-val" id="macroCarb">0g</div>
                        <div class="macro-label">Carbs</div>
                    </div>
                    <div class="macro-box">
                        <div class="macro-val" id="macroFat">0g</div>
                        <div class="macro-label">Fat</div>
                    </div>
                </div>
                
                <div id="modalCartControls" class="cart-controls w-100" data-food-id="" data-food-name="" data-food-price="" data-food-image="">
                    <!-- Dynamic cart controls inserted here -->
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Cart JS -->
<script src="<?php echo BASE_URL; ?>/assets/js/cart.js"></script>
<!-- Main App JS -->
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>

</body>
</html>
