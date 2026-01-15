<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
require_once dirname(__DIR__) . '/../includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

// Define e() function if not available
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

// Ensure inventory table and required columns exist (self-healing)
function ensure_inventory_schema() {
    // Create table if missing (minimal schema)
    $createSql = "CREATE TABLE IF NOT EXISTS inventory (
        inventoryID INT AUTO_INCREMENT PRIMARY KEY,
        itemID INT NOT NULL UNIQUE,
        stock_qty INT NOT NULL DEFAULT 0
    )";
    executeQuery($createSql);

    // Add missing columns if needed
    $cols = [
        'min_stock_level' => "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS min_stock_level INT NOT NULL DEFAULT 10",
        'reorder_point'   => "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS reorder_point INT NOT NULL DEFAULT 5",
        'last_updated'    => "ALTER TABLE inventory ADD COLUMN IF NOT EXISTS last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP",
    ];

    foreach ($cols as $col => $ddl) {
        // Use non-prepared query for SHOW COLUMNS to avoid driver limitations
        $rs = executeQuery("SHOW COLUMNS FROM inventory LIKE '" . $col . "'");
        if (!$rs || mysqli_num_rows($rs) === 0) {
            executeQuery($ddl);
        }
    }

    // Ensure unique index on itemID
    $idx = executeQuery("SHOW INDEX FROM inventory WHERE Key_name = 'uq_item'");
    if (!$idx || mysqli_num_rows($idx) === 0) {
        // Check if itemID already has a unique index
        $idx2 = executeQuery("SHOW INDEX FROM inventory WHERE Column_name = 'itemID' AND Non_unique = 0");
        if (!$idx2 || mysqli_num_rows($idx2) === 0) {
            executeQuery("ALTER TABLE inventory ADD UNIQUE KEY uq_item (itemID)");
        }
    }
}

ensure_inventory_schema();

// Schema is ensured by ensure_inventory_schema() above; no duplicate CREATE needed

$success = "";
$error = "";

// Handle form submissions
if(isset($_POST['action'])){
    switch($_POST['action']){
        case 'update':
            $itemID = intval($_POST['itemID']);
            $stockQty = intval($_POST['stockQty']);
            $minStock = intval($_POST['minStock']);
            $reorderPoint = 0; // Default value since field is removed from UI
            
            if($itemID > 0 && $stockQty >= 0){
                // Check if item exists in inventory
                $checkQuery = "SELECT inventoryID FROM inventory WHERE itemID = ?";
                $checkResult = executePreparedQuery($checkQuery, "i", [$itemID]);
                
                if($checkResult && mysqli_num_rows($checkResult) > 0){
                    // Update existing inventory
                    $query = "UPDATE inventory SET stock_qty = ?, min_stock_level = ?, reorder_point = ? WHERE itemID = ?";
                    $result = executePreparedUpdate($query, "iiii", [$stockQty, $minStock, $reorderPoint, $itemID]);
                } else {
                    // Insert new inventory entry
                    $query = "INSERT INTO inventory (itemID, stock_qty, min_stock_level, reorder_point) VALUES (?, ?, ?, ?)";
                    $result = executePreparedUpdate($query, "iiii", [$itemID, $stockQty, $minStock, $reorderPoint]);
                }
                
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
            $reorderPoint = 0; // Default value since field is removed from UI
            
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
        case 'sync':
            // Ensure inventory rows exist for all items
            $syncSql = "INSERT IGNORE INTO inventory (itemID, stock_qty, min_stock_level, reorder_point)
                        SELECT i.itemID, 0, 10, 5 FROM items i";
            $ok = executePreparedUpdate($syncSql, "", []);
            if($ok !== false){
                $success = "Inventory synced for all items (missing rows added).";
            } else {
                $error = "Failed to sync inventory!";
            }
            break;
    }
}

// Get selected category for filter (optional)
$selectedCategoryID = isset($_GET['categoryID']) ? (int)$_GET['categoryID'] : 0;

// Get inventory with item details (show ALL items regardless of status)
$query = "SELECT i.*, c.categoryID, c.categoryName, inv.stock_qty, inv.min_stock_level, inv.reorder_point, inv.last_updated 
          FROM items i 
          LEFT JOIN categories c ON i.categoryID = c.categoryID
          LEFT JOIN inventory inv ON i.itemID = inv.itemID";

if ($selectedCategoryID > 0) {
    $query .= " WHERE i.categoryID = ?";
    $result = executePreparedQuery($query . " ORDER BY c.categoryName, i.packageName", "i", [$selectedCategoryID]);
} else {
    $result = executePreparedQuery($query . " ORDER BY c.categoryName, i.packageName", "", []);
}

// Group products by category
$productsByCategory = [];
$usingFallbackSimple = false;
if ($result && mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_assoc($result)) {
        $categoryID = $row['categoryID'] ?? 0;
        $categoryName = $row['categoryName'] ?? 'Uncategorized';
        
        if (!isset($productsByCategory[$categoryID])) {
            $productsByCategory[$categoryID] = [
                'categoryName' => $categoryName,
                'products' => []
            ];
        }
        
        $productsByCategory[$categoryID]['products'][] = $row;
    }
} else {
    // Final fallback: fetch items without joins
    $fallbackRs = executePreparedQuery("SELECT * FROM items ORDER BY packageName", "", []);
    if ($fallbackRs && mysqli_num_rows($fallbackRs) > 0) {
        $usingFallbackSimple = true;
        $productsByCategory[0] = ['categoryName' => 'All Items', 'products' => []];
        while($row = mysqli_fetch_assoc($fallbackRs)) {
            $row['stock_qty'] = $row['stock_qty'] ?? 0;
            $row['min_stock_level'] = $row['min_stock_level'] ?? 10;
            $productsByCategory[0]['products'][] = $row;
        }
    }
}

// Get all categories for filter dropdown
$categoriesResult = executePreparedQuery("SELECT categoryID, categoryName FROM categories ORDER BY categoryName", "", []);

include(dirname(__DIR__) . "/includes/header.php");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Inventory Management</h2>
        <div class="d-flex align-items-center gap-2">
            <span class="text-muted small">Filter by Category:</span>
            <form method="GET" class="d-flex align-items-center gap-2">
                <select name="categoryID" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                    <option value="0">All Categories</option>
                    <?php if ($categoriesResult && mysqli_num_rows($categoriesResult) > 0): ?>
                        <?php while($cat = mysqli_fetch_assoc($categoriesResult)): ?>
                            <option value="<?php echo (int)$cat['categoryID']; ?>" <?php echo $selectedCategoryID === (int)$cat['categoryID'] ? 'selected' : ''; ?>>
                                <?php echo e($cat['categoryName']); ?>
                            </option>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </select>
            </form>
        </div>
    </div>
    <div class="d-flex gap-2">
        <form method="POST" class="m-0">
            <input type="hidden" name="action" value="sync">
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-sync me-2"></i>Sync Inventory Rows
            </button>
        </form>
        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="fas fa-plus me-2"></i>Add Item to Inventory
        </button>
    </div>
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

<?php if(!empty($usingFallbackSimple)): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <i class="fas fa-exclamation-triangle me-2"></i>
    Showing items via a simplified fallback (no joins). Consider ensuring categories/inventory tables are populated.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    <div class="mt-1 small">Tip: Use "Sync Inventory Rows" to create missing inventory entries.</div>
</div>
<?php endif; ?>

 

<?php if (!empty($productsByCategory)): ?>
    <?php foreach($productsByCategory as $categoryID => $categoryData): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-folder me-2"></i><?php echo e($categoryData['categoryName']); ?>
                <span class="badge bg-light text-dark ms-2"><?php echo count($categoryData['products']); ?> item(s)</span>
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Item Name</th>
                            <th>Current Stock</th>
                            <th>Min Stock Level</th>
                            <th>Last Updated</th>
                            <th style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categoryData['products'] as $row): ?>
                        <tr>
                            <td>
                                <?php 
                                // Use depth 3 for this page (admin/catalog is 3 levels deep from project root)
                                $imageUrl = function_exists('product_image_url') ? product_image_url($row, 3) : '../../../frontend/images/placeholder.jpg';
                                ?>
                                <img src="<?php echo $imageUrl; ?>" 
                                     alt="<?php echo e($row['packageName']); ?>" 
                                     style="width: 60px; height: 60px; object-fit: cover;" 
                                     class="rounded border"
                                     onerror="this.src='<?php echo htmlspecialchars($appBasePath ?? ''); ?>/frontend/images/placeholder.jpg'">
                            </td>
                            <td>
                                <strong><?php echo e($row['packageName']); ?></strong>
                                <br><small class="text-muted">ID: <?php echo $row['itemID']; ?></small>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo ($row['stock_qty'] ?? 0) <= ($row['min_stock_level'] ?? 10) ? 'danger' : 'success'; ?> fs-6 px-3 py-2">
                                    <?php echo $row['stock_qty'] ?? 0; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-info text-dark"><?php echo $row['min_stock_level'] ?? 0; ?></span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo $row['last_updated'] ? date('M d, Y H:i', strtotime($row['last_updated'])) : 'Never'; ?>
                                </small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-warning" onclick="editInventory(<?php echo $row['itemID']; ?>, <?php echo $row['stock_qty'] ?? 0; ?>, <?php echo $row['min_stock_level'] ?? 0; ?>)">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <div class="text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">No items found in inventory</h5>
                <p class="text-muted">Add items to manage stock levels</p>
                <button class="btn btn-success mt-3" data-bs-toggle="modal" data-bs-target="#addItemModal">
                    <i class="fas fa-plus me-2"></i>Add Item to Inventory
                </button>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Add/Update Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Item to Inventory</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form method="POST" id="inventoryForm">
                    <input type="hidden" name="action" value="add" id="formAction">
                    <input type="hidden" name="itemID" id="formItemID" value="">
                    <div class="row">
                        <div class="col-md-6 mb-3" id="itemSelectContainer">
                            <label class="form-label">Select Item</label>
                            <select class="form-select" name="itemID" id="itemSelect" required>
                                <option value="">Choose an item...</option>
                                <?php
                                $itemsQuery = "SELECT itemID, packageName FROM items WHERE itemID NOT IN (SELECT COALESCE(itemID, 0) FROM inventory WHERE itemID IS NOT NULL) ORDER BY packageName";
                                $itemsResult = executePreparedQuery($itemsQuery, "", []);
                                while($item = mysqli_fetch_assoc($itemsResult)):
                                ?>
                                <option value="<?php echo $item['itemID']; ?>"><?php echo e($item['packageName']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3" id="itemDisplayContainer" style="display: none;">
                            <label class="form-label">Item</label>
                            <input type="text" class="form-control" id="itemDisplay" readonly>
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
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-plus me-2"></i>Add to Inventory
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function editInventory(itemID, currentStock, minStock) {
    // Get item name for display
    const row = event.target.closest('tr');
    const itemName = row.querySelector('td:nth-child(2) strong').textContent;
    
    // Hide select, show display
    document.getElementById('itemSelectContainer').style.display = 'none';
    document.getElementById('itemDisplayContainer').style.display = 'block';
    document.getElementById('itemDisplay').value = itemName;
    document.getElementById('formItemID').value = itemID;
    
    // Populate form with current values
    document.getElementById('itemSelect').required = false;
    document.querySelector('input[name="stockQty"]').value = currentStock;
    document.querySelector('input[name="minStock"]').value = minStock;
    
    // Change form action to update
    document.getElementById('formAction').value = 'update';
    
    // Change modal title and button
    document.querySelector('#addItemModal .modal-title').textContent = 'Update Inventory';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save me-2"></i>Update Inventory';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('addItemModal'));
    modal.show();
}

// Reset modal when closed
document.getElementById('addItemModal').addEventListener('hidden.bs.modal', function() {
    // Reset form
    document.getElementById('inventoryForm').reset();
    document.getElementById('formAction').value = 'add';
    document.getElementById('itemSelectContainer').style.display = 'block';
    document.getElementById('itemDisplayContainer').style.display = 'none';
    document.getElementById('itemSelect').required = true;
    document.getElementById('formItemID').value = '';
    document.querySelector('#addItemModal .modal-title').textContent = 'Add Item to Inventory';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus me-2"></i>Add to Inventory';
});

// Handle form submission
document.getElementById('inventoryForm').addEventListener('submit', function(e) {
    const action = document.getElementById('formAction').value;
    if(action === 'update') {
        // For update, disable select so it doesn't submit, use hidden itemID
        document.getElementById('itemSelect').disabled = true;
        document.getElementById('formItemID').disabled = false;
    } else {
        // For add, use select value and clear/disable hidden input
        const selectValue = document.getElementById('itemSelect').value;
        if(selectValue) {
            document.getElementById('formItemID').value = selectValue;
        }
        document.getElementById('itemSelect').disabled = false;
        document.getElementById('formItemID').disabled = false;
    }
});
</script>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>
