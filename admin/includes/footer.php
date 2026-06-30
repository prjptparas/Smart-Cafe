    </div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<!-- Delete Confirmation Modal (reusable) -->
<div class="modal fade modal-smart" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-body">
                <div class="delete-confirm">
                    <div class="delete-icon">
                        <i class="bi bi-trash3"></i>
                    </div>
                    <h5 style="font-family:var(--font-heading);font-weight:700">Delete Item?</h5>
                    <p style="color:var(--text-secondary);font-size:var(--font-size-sm)">
                        Are you sure you want to delete <strong id="deleteItemName"></strong>? This action cannot be undone.
                    </p>
                    <form id="deleteForm" method="POST">
                        <input type="hidden" name="action" value="delete">
                        <div class="d-flex gap-2 justify-content-center">
                            <button type="button" class="btn-outline-smart btn-sm-smart" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn-primary-smart btn-sm-smart" style="background:linear-gradient(135deg,var(--color-danger),#c0392b)">
                                <i class="bi bi-trash3"></i> Delete
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Toast Container -->
<div class="toast-container"></div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/app.js"></script>
<script src="<?php echo BASE_URL; ?>/assets/js/admin.js"></script>

</body>
</html>
