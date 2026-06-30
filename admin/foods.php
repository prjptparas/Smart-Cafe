<?php
/**
 * Admin - Food Items Management
 * 
 * Full CRUD for food items with image upload.
 * Supports add, edit, and delete operations.
 */

$adminPage = 'foods';
$adminPageTitle = 'Food Items';

// Process form submissions before header (to handle redirects)
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdminLogin();

$message = '';
$messageType = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = sanitizeInt($_POST['food_id'] ?? 0);
        $categoryId = sanitizeInt($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = sanitizeFloat($_POST['price'] ?? 0);
        $isVeg = isset($_POST['is_veg']) ? 1 : 0;
        $isAvailable = isset($_POST['is_available']) ? 1 : 0;
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        $prepTime = sanitizeInt($_POST['prep_time'] ?? 15);

        // Validation
        if (empty($name) || $categoryId < 1 || $price <= 0) {
            $message = 'Please fill in all required fields.';
            $messageType = 'danger';
        } else {
            // Handle image upload
            $imageName = $_POST['existing_image'] ?? 'default-food.jpg';
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadResult = handleImageUpload($_FILES['image']);
                if ($uploadResult['success']) {
                    $imageName = $uploadResult['filename'];
                } else {
                    $message = $uploadResult['message'];
                    $messageType = 'danger';
                }
            }

            if (empty($message)) {
                if ($action === 'add') {
                    $stmt = $pdo->prepare("INSERT INTO foods (category_id, name, description, price, image, is_veg, is_available, is_featured, prep_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$categoryId, $name, $description, $price, $imageName, $isVeg, $isAvailable, $isFeatured, $prepTime]);
                    $message = 'Food item added successfully!';
                    $messageType = 'success';
                } else {
                    $stmt = $pdo->prepare("UPDATE foods SET category_id=?, name=?, description=?, price=?, image=?, is_veg=?, is_available=?, is_featured=?, prep_time=? WHERE id=?");
                    $stmt->execute([$categoryId, $name, $description, $price, $imageName, $isVeg, $isAvailable, $isFeatured, $prepTime, $id]);
                    $message = 'Food item updated successfully!';
                    $messageType = 'success';
                }
            }
        }
    } elseif ($action === 'delete') {
        $id = sanitizeInt($_POST['food_id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM foods WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Food item deleted successfully!';
            $messageType = 'success';
        }
    }
}

// Fetch all food items with category names
$foods = $pdo->query("
    SELECT f.*, c.name AS category_name 
    FROM foods f 
    JOIN categories c ON f.category_id = c.id 
    ORDER BY f.created_at DESC
")->fetchAll();

// Fetch categories for the form dropdown
$categories = $pdo->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> d-flex align-items-center gap-2 alert-dismissible fade show" role="alert" style="border-radius:var(--radius-md)">
    <i class="bi <?php echo $messageType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'; ?>"></i>
    <?php echo sanitize($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Action Bar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p style="color:var(--text-secondary);margin:0"><?php echo count($foods); ?> food items</p>
    <button class="btn-primary-smart" data-bs-toggle="modal" data-bs-target="#foodModal" onclick="resetFoodForm()">
        <i class="bi bi-plus-lg"></i> Add Food Item
    </button>
</div>

<!-- Food Items Table -->
<div class="admin-table-container">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($foods)): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-egg-fried"></i></div>
                            <h4>No food items yet</h4>
                            <p>Add your first food item to get started.</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($foods as $food): ?>
                <tr>
                    <td>
                        <img src="<?php echo getFoodImageUrl($food['image']); ?>" 
                             alt="<?php echo sanitize($food['name']); ?>" 
                             class="food-thumb"
                             onerror="this.src='<?php echo BASE_URL; ?>/assets/images/default-food.jpg'">
                    </td>
                    <td>
                        <div style="font-weight:600"><?php echo sanitize($food['name']); ?></div>
                        <small style="color:var(--text-tertiary)"><?php echo (int)$food['prep_time']; ?> min prep</small>
                    </td>
                    <td><?php echo sanitize($food['category_name']); ?></td>
                    <td style="font-weight:600;color:var(--color-primary)"><?php echo formatPrice($food['price']); ?></td>
                    <td>
                        <?php if ($food['is_veg']): ?>
                            <span class="badge-veg" style="display:inline-flex;width:20px;height:20px;border-radius:4px" title="Veg">
                                <i class="bi bi-circle-fill" style="font-size:0.4rem"></i>
                            </span>
                        <?php else: ?>
                            <span class="badge-nonveg" style="display:inline-flex;width:20px;height:20px;border-radius:4px" title="Non-Veg">
                                <i class="bi bi-triangle-fill" style="font-size:0.4rem"></i>
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $food['is_available'] ? 'ready' : 'cancelled'; ?>">
                            <?php echo $food['is_available'] ? 'Available' : 'Unavailable'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($food['is_featured']): ?>
                            <i class="bi bi-star-fill" style="color:#f39c12"></i>
                        <?php else: ?>
                            <i class="bi bi-star" style="color:var(--text-tertiary)"></i>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="action-btn" title="Edit" onclick='editFood(<?php echo json_encode($food); ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn delete" title="Delete" onclick="deleteFoodItem(<?php echo $food['id']; ?>, '<?php echo addslashes($food['name']); ?>')">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Food Modal -->
<div class="modal fade modal-smart" id="foodModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="foodModalTitle">Add Food Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" id="foodAction" value="add">
                    <input type="hidden" name="food_id" id="foodId" value="0">
                    <input type="hidden" name="existing_image" id="existingImage" value="default-food.jpg">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Food Name *</label>
                            <input type="text" class="form-control" name="name" id="foodName" required placeholder="Enter food name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Category *</label>
                            <select class="form-select" name="category_id" id="foodCategory" required>
                                <option value="">Select</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo sanitize($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" id="foodDesc" rows="2" placeholder="Brief description of the dish"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Price (<?php echo CURRENCY_SYMBOL; ?>) *</label>
                            <input type="number" class="form-control" name="price" id="foodPrice" step="0.01" min="1" required placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Prep Time (min)</label>
                            <input type="number" class="form-control" name="prep_time" id="foodPrepTime" min="1" value="15">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control image-upload-input" name="image" accept="image/jpeg,image/png,image/webp" data-preview="imagePreview">
                        </div>
                        <div class="col-12">
                            <div class="image-preview" id="imagePreview">
                                <div class="preview-placeholder">
                                    <i class="bi bi-image" style="font-size:1.5rem"></i><br>
                                    Preview
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_veg" id="foodVeg" checked>
                                <label class="form-check-label" for="foodVeg">Vegetarian</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_available" id="foodAvailable" checked>
                                <label class="form-check-label" for="foodAvailable">Available</label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_featured" id="foodFeatured">
                                <label class="form-check-label" for="foodFeatured">Featured</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-smart btn-sm-smart" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-smart btn-sm-smart">
                        <i class="bi bi-check-lg"></i> <span id="foodSubmitText">Add Item</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetFoodForm() {
    document.getElementById('foodModalTitle').textContent = 'Add Food Item';
    document.getElementById('foodAction').value = 'add';
    document.getElementById('foodId').value = '0';
    document.getElementById('foodName').value = '';
    document.getElementById('foodCategory').value = '';
    document.getElementById('foodDesc').value = '';
    document.getElementById('foodPrice').value = '';
    document.getElementById('foodPrepTime').value = '15';
    document.getElementById('foodVeg').checked = true;
    document.getElementById('foodAvailable').checked = true;
    document.getElementById('foodFeatured').checked = false;
    document.getElementById('existingImage').value = 'default-food.jpg';
    document.getElementById('imagePreview').innerHTML = '<div class="preview-placeholder"><i class="bi bi-image" style="font-size:1.5rem"></i><br>Preview</div>';
    document.getElementById('foodSubmitText').textContent = 'Add Item';
}

function editFood(food) {
    document.getElementById('foodModalTitle').textContent = 'Edit Food Item';
    document.getElementById('foodAction').value = 'edit';
    document.getElementById('foodId').value = food.id;
    document.getElementById('foodName').value = food.name;
    document.getElementById('foodCategory').value = food.category_id;
    document.getElementById('foodDesc').value = food.description || '';
    document.getElementById('foodPrice').value = food.price;
    document.getElementById('foodPrepTime').value = food.prep_time;
    document.getElementById('foodVeg').checked = food.is_veg == 1;
    document.getElementById('foodAvailable').checked = food.is_available == 1;
    document.getElementById('foodFeatured').checked = food.is_featured == 1;
    document.getElementById('existingImage').value = food.image || 'default-food.jpg';
    document.getElementById('foodSubmitText').textContent = 'Update Item';

    // Show current image
    var imgUrl = '<?php echo BASE_URL; ?>/admin/uploads/' + food.image;
    document.getElementById('imagePreview').innerHTML = '<img src="' + imgUrl + '" alt="Preview" onerror="this.parentElement.innerHTML=\'<div class=preview-placeholder><i class=bi bi-image style=font-size:1.5rem></i><br>No Image</div>\'">';

    var modal = new bootstrap.Modal(document.getElementById('foodModal'));
    modal.show();
}

function deleteFoodItem(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    var form = document.getElementById('deleteForm');
    form.action = '';
    // Add hidden fields
    form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="food_id" value="' + id + '">' +
        '<div class="d-flex gap-2 justify-content-center">' +
        '<button type="button" class="btn-outline-smart btn-sm-smart" data-bs-dismiss="modal">Cancel</button>' +
        '<button type="submit" class="btn-primary-smart btn-sm-smart" style="background:linear-gradient(135deg,var(--color-danger),#c0392b)">' +
        '<i class="bi bi-trash3"></i> Delete</button></div>';
    var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
