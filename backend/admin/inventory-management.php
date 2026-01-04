<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

// Define e() function if not available
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$success = "";
$error = "";

// Handle form submissions
if(isset($_POST['action'])){
    switch($_POST['action']){
        case 'update':
            $itemID = intval($_POST['itemID']);
            $stockQty = intval($_POST['stockQty']);
            $minStock = intval($_POST['minStock']);
            $reorderPoint = intval($_POST['reorderPoint']);
            
            if($itemID > 0 && $stockQty >= 0){
                $query = "UPDATE inventory SET stock_qty = ?, min_stock_level = ?, reorder_point = ? WHERE itemID = ?";
                $result = executePreparedUpdate($query, "iiii", [$stockQty, $minStock, $reorderPoint, $itemID]);
                
                if($result !== false){
                    $success = "Inventory updated successfully!";
                } else {
                    $error = "Failed to update inventory!";
                }
            } else {
                $error = "Invalid data provided!";
            }
            break;
            
        case 'add':
            $itemID = intval($_POST['itemID']);
            $stockQty = intval($_POST['stockQty']);
            $minStock = intval($_POST['minStock'] ?? 10);
            $reorderPoint = intval($_POST['reorderPoint'] ?? 5);
            
            if($itemID > 0 && $stockQty >= 0){
                // Check if item already exists in inventory
                $checkQuery = "SELECT inventoryID FROM inventory WHERE itemID = ?";
                $checkResult = executePreparedQuery($checkQuery, "i", [$itemID]);
                
                if($checkResult && mysqli_num_rows($checkResult) > 0){
                    $error = "Item already exists in inventory!";
                } else {
                    $query = "INSERT INTO inventory (itemID, stock_qty, min_stock_level, reorder_point) VALUES (?, ?, ?, ?)";
                    $result = executePreparedUpdate($query, "iiii", [$itemID, $stockQty, $minStock, $reorderPoint]);
                    
                    if($result !== false){
                        $success = "Item added to inventory successfully!";
                    } else {
                        $error = "Failed to add item to inventory!";
                    }
                }
            } else {
                $error = "Invalid data provided!";
            }
            break;
    }
}

// Get inventory with item details
$query = "SELECT i.*, inv.stock_qty, inv.min_stock_level, inv.reorder_point, inv.last_updated 
          FROM items i 
          LEFT JOIN inventory inv ON i.itemID = inv.itemID 
          WHERE i.status = 'Active'
          ORDER BY i.packageName";
$result = executePreparedQuery($query, "", []);

include(__DIR__ . "/includes/header.php");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Inventory Management</h2>
    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
        <i class="fas fa-plus me-2"></i>Add Item to Inventory
    </button>
</div>

<?php if($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Item Image</th>
                        <th>Item Name</th>
                        <th>Category</th>
                        <th>Current Stock</th>
                        <th>Min Stock Level</th>
                        <th>Reorder Point</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($result)): ?>
                        <tr>
                            <td>
                                <img src="<?php echo product_image_url($row, 1); ?>" 
                                     alt="<?php echo e($row['packageName']); ?>" 
                                     style="width: 50px; height: 50px; object-fit: cover;" class="rounded">
                            </td>
                            <td>
                                <strong><?php echo e($row['packageName']); ?></strong>
                                <br><small class="text-muted">ID: <?php echo $row['itemID']; ?></small>
                            </td>
                            <td><?php echo e($row['categoryName']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($row['stock_qty'] ?? 0) <= ($row['min_stock_level'] ?? 10) ? 'danger' : 'success'; ?>">
                                    <?php echo $row['stock_qty'] ?? 0; ?>
                                </span>
                            </td>
                            <td><?php echo $row['min_stock_level'] ?? 0; ?></td>
                            <td><?php echo $row['reorder_point'] ?? 0; ?></td>
                            <td>
                                <?php echo $row['last_updated'] ? date('M d, Y H:i', strtotime($row['last_updated'])) : 'Never'; ?>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editInventory(<?php echo $row['itemID']; ?>, <?php echo $row['stock_qty'] ?? 0; ?>, <?php echo $row['min_stock_level'] ?? 0; ?>, <?php echo $row['reorder_point'] ?? 0; ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="py-4">
                                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No items found in inventory</p>
                                    <p class="text-muted">Add items to manage stock levels</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Update Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item to Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Select Item</label>
                            <select class="form-select" name="itemID" required>
                                <option value="">Choose an item...</option>
                                <?php
                                $itemsQuery = "SELECT itemID, packageName FROM items WHERE status = 'Active' AND itemID NOT IN (SELECT itemID FROM inventory) ORDER BY packageName";
                                $itemsResult = executePreparedQuery($itemsQuery, "", []);
                                while($item = mysqli_fetch_assoc($itemsResult)):
                                ?>
                                <option value="<?php echo $item['itemID']; ?>"><?php echo e($item['packageName']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Stock Quantity</label>
                            <input type="number" class="form-control" name="stockQty" min="0" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Min Stock Level</label>
                            <input type="number" class="form-control" name="minStock" min="0" value="10">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Reorder Point</label>
                            <input type="number" class="form-control" name="reorderPoint" min="0" value="5">
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Add to Inventory
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editInventory(itemID, currentStock, minStock, reorderPoint) {
    // Populate modal with current values
    document.querySelector('select[name="itemID"]').value = itemID;
    document.querySelector('input[name="stockQty"]').value = currentStock;
    document.querySelector('input[name="minStock"]').value = minStock;
    document.querySelector('input[name="reorderPoint"]').value = reorderPoint;
    
    // Change form action to update
    document.querySelector('input[name="action"]').value = 'update';
    
    // Change modal title
    document.querySelector('#addItemModal .modal-title').textContent = 'Update Inventory';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
    modal.show();
}
</script>

<?php include(__DIR__ . "/includes/footer.php"); ?>
