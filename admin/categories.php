<?php
/**
 * Admin - Categories Management
 * CRUD for food categories.
 */

$adminPage = 'categories';
$adminPageTitle = 'Categories';

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
        $id = sanitizeInt($_POST['category_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'bi-tag');
        $sortOrder = sanitizeInt($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($name)) {
            $message = 'Category name is required.';
            $messageType = 'danger';
        } else {
            if ($action === 'add') {
                $stmt = $pdo->prepare("INSERT INTO categories (name, description, icon, sort_order, is_active) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$name, $description, $icon, $sortOrder, $isActive]);
                $message = 'Category added successfully!';
                $messageType = 'success';
            } else {
                $stmt = $pdo->prepare("UPDATE categories SET name=?, description=?, icon=?, sort_order=?, is_active=? WHERE id=?");
                $stmt->execute([$name, $description, $icon, $sortOrder, $isActive, $id]);
                $message = 'Category updated successfully!';
                $messageType = 'success';
            }
        }
    } elseif ($action === 'delete') {
        $id = sanitizeInt($_POST['category_id'] ?? 0);
        // Check if category has foods
        $foodCount = $pdo->prepare("SELECT COUNT(*) FROM foods WHERE category_id = ?");
        $foodCount->execute([$id]);
        if ($foodCount->fetchColumn() > 0) {
            $message = 'Cannot delete: This category has food items. Remove them first.';
            $messageType = 'danger';
        } else {
            $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Category deleted successfully!';
            $messageType = 'success';
        }
    }
}

// Fetch categories with food count
$categories = $pdo->query("
    SELECT c.*, (SELECT COUNT(*) FROM foods WHERE category_id = c.id) AS food_count
    FROM categories c 
    ORDER BY c.sort_order ASC, c.name ASC
")->fetchAll();

// Available icons
$icons = ['bi-cup-hot', 'bi-cup-straw', 'bi-fire', 'bi-egg-fried', 'bi-cake2', 'bi-lightning', 'bi-basket', 'bi-box', 'bi-star', 'bi-heart', 'bi-droplet', 'bi-snow2', 'bi-tropical-storm', 'bi-tag', 'bi-flower1'];

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> d-flex align-items-center gap-2 alert-dismissible fade show" role="alert" style="border-radius:var(--radius-md)">
    <i class="bi <?php echo $messageType === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill'; ?>"></i>
    <?php echo sanitize($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <p style="color:var(--text-secondary);margin:0"><?php echo count($categories); ?> categories</p>
    <button class="btn-primary-smart" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetCatForm()">
        <i class="bi bi-plus-lg"></i> Add Category
    </button>
</div>

<!-- Categories Table -->
<div class="admin-table-container">
    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Icon</th>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Items</th>
                    <th>Order</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $cat): ?>
                <tr>
                    <td><i class="bi <?php echo sanitize($cat['icon']); ?>" style="font-size:1.3rem;color:var(--color-primary)"></i></td>
                    <td style="font-weight:600"><?php echo sanitize($cat['name']); ?></td>
                    <td style="color:var(--text-secondary);font-size:var(--font-size-sm)"><?php echo sanitize($cat['description']); ?></td>
                    <td><span class="badge bg-secondary" style="border-radius:var(--radius-full)"><?php echo (int)$cat['food_count']; ?></span></td>
                    <td><?php echo (int)$cat['sort_order']; ?></td>
                    <td>
                        <span class="status-badge <?php echo $cat['is_active'] ? 'ready' : 'cancelled'; ?>">
                            <?php echo $cat['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <button class="action-btn" title="Edit" onclick='editCategory(<?php echo json_encode($cat); ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="action-btn delete" title="Delete" onclick="deleteCategory(<?php echo $cat['id']; ?>, '<?php echo addslashes($cat['name']); ?>')">
                                <i class="bi bi-trash3"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div class="modal fade modal-smart" id="categoryModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="catModalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="action" id="catAction" value="add">
                    <input type="hidden" name="category_id" id="catId" value="0">
                    
                    <div class="mb-3">
                        <label class="form-label">Category Name *</label>
                        <input type="text" class="form-control" name="name" id="catName" required placeholder="e.g., Hot Beverages">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" id="catDesc" placeholder="Brief description">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Icon</label>
                        <select class="form-select" name="icon" id="catIcon">
                            <?php foreach ($icons as $icon): ?>
                            <option value="<?php echo $icon; ?>"><?php echo $icon; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="mt-2">
                            Preview: <i class="bi bi-tag" id="iconPreview" style="font-size:1.5rem;color:var(--color-primary)"></i>
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Sort Order</label>
                            <input type="number" class="form-control" name="sort_order" id="catSort" value="0" min="0">
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="catActive" checked>
                                <label class="form-check-label" for="catActive">Active</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-outline-smart btn-sm-smart" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-primary-smart btn-sm-smart">
                        <i class="bi bi-check-lg"></i> <span id="catSubmitText">Add Category</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Icon preview
document.getElementById('catIcon')?.addEventListener('change', function() {
    document.getElementById('iconPreview').className = 'bi ' + this.value;
});

function resetCatForm() {
    document.getElementById('catModalTitle').textContent = 'Add Category';
    document.getElementById('catAction').value = 'add';
    document.getElementById('catId').value = '0';
    document.getElementById('catName').value = '';
    document.getElementById('catDesc').value = '';
    document.getElementById('catIcon').value = 'bi-tag';
    document.getElementById('catSort').value = '0';
    document.getElementById('catActive').checked = true;
    document.getElementById('catSubmitText').textContent = 'Add Category';
    document.getElementById('iconPreview').className = 'bi bi-tag';
}

function editCategory(cat) {
    document.getElementById('catModalTitle').textContent = 'Edit Category';
    document.getElementById('catAction').value = 'edit';
    document.getElementById('catId').value = cat.id;
    document.getElementById('catName').value = cat.name;
    document.getElementById('catDesc').value = cat.description || '';
    document.getElementById('catIcon').value = cat.icon || 'bi-tag';
    document.getElementById('catSort').value = cat.sort_order;
    document.getElementById('catActive').checked = cat.is_active == 1;
    document.getElementById('catSubmitText').textContent = 'Update Category';
    document.getElementById('iconPreview').className = 'bi ' + (cat.icon || 'bi-tag');

    var modal = new bootstrap.Modal(document.getElementById('categoryModal'));
    modal.show();
}

function deleteCategory(id, name) {
    document.getElementById('deleteItemName').textContent = name;
    var form = document.getElementById('deleteForm');
    form.action = '';
    form.innerHTML = '<input type="hidden" name="action" value="delete"><input type="hidden" name="category_id" value="' + id + '">' +
        '<div class="d-flex gap-2 justify-content-center">' +
        '<button type="button" class="btn-outline-smart btn-sm-smart" data-bs-dismiss="modal">Cancel</button>' +
        '<button type="submit" class="btn-primary-smart btn-sm-smart" style="background:linear-gradient(135deg,var(--color-danger),#c0392b)">' +
        '<i class="bi bi-trash3"></i> Delete</button></div>';
    var modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
