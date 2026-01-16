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

// Check for success message from redirect
if(isset($_GET['success']) && $_GET['success'] == '1'){
    $success = "Inventory updated successfully!";
}

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Handle form submissions
if(isset($_POST['action'])){
    switch($_POST['action']){
        case 'update':
            // Get itemID from hidden input (formItemID)
            $itemID = intval($_POST['itemID'] ?? 0);
            $stockQty = intval($_POST['stockQty'] ?? 0);
            $minStock = intval($_POST['minStock'] ?? 0);
            $reorderPoint = 0; // Default value since field is removed from UI
            
            // Debug logging
            error_log("Inventory Update - itemID: $itemID, stockQty: $stockQty, minStock: $minStock");
            
            if($itemID > 0 && $stockQty >= 0){
                // Use INSERT ... ON DUPLICATE KEY UPDATE to handle both insert and update
                // This prevents duplicate key errors and ensures the record is always updated
                $query = "INSERT INTO inventory (itemID, stock_qty, min_stock_level, reorder_point, last_updated) 
                         VALUES (?, ?, ?, ?, NOW())
                         ON DUPLICATE KEY UPDATE 
                         stock_qty = VALUES(stock_qty), 
                         min_stock_level = VALUES(min_stock_level), 
                         reorder_point = VALUES(reorder_point), 
                         last_updated = NOW()";
                $result = executePreparedUpdate($query, "iiii", [$itemID, $stockQty, $minStock, $reorderPoint]);
                
                // Check for database errors
                $dbError = $GLOBALS['db_last_error'] ?? null;
                if($result === false || $dbError){
                    $error = "Failed to update inventory!" . ($dbError ? " Error: " . $dbError : "");
                    error_log("Update failed - itemID: $itemID, Error: " . ($dbError ?? "Unknown"));
                    
                    if($isAjax){
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => $error]);
                        exit();
                    }
                } else {
                    // Get updated timestamp
                    $timestampQuery = "SELECT last_updated FROM inventory WHERE itemID = ?";
                    $timestampResult = executePreparedQuery($timestampQuery, "i", [$itemID]);
                    $timestampRow = $timestampResult ? mysqli_fetch_assoc($timestampResult) : null;
                    $lastUpdated = $timestampRow['last_updated'] ?? date('Y-m-d H:i:s');
                    
                    // Log successful update for API verification
                    error_log("Inventory updated - itemID: $itemID, stockQty: $stockQty (will reflect in school cafeteria API immediately)");
                    
                    if($isAjax){
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => 'Inventory updated successfully! Changes will reflect in the school cafeteria API immediately.',
                            'data' => [
                                'itemID' => $itemID,
                                'stockQty' => $stockQty,
                                'minStock' => $minStock,
                                'lastUpdated' => $lastUpdated
                            ]
                        ]);
                        exit();
                    } else {
                        // Redirect to prevent form resubmission and show updated data
                        $redirectUrl = $_SERVER['PHP_SELF'] . ($selectedCategoryID > 0 ? '?categoryID=' . $selectedCategoryID : '');
                        header("Location: " . $redirectUrl . "&success=1");
                        exit();
                    }
                }
            } else {
                $error = "Invalid data provided! itemID: $itemID, stockQty: $stockQty";
                error_log("Invalid data - itemID: $itemID, stockQty: $stockQty");
                
                if($isAjax){
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $error]);
                    exit();
                }
            }
            break;
            
        case 'add':
            // Get itemID from select dropdown (itemIDSelect) or hidden input
            $itemID = intval($_POST['itemID'] ?? $_POST['itemIDSelect'] ?? 0);
            $stockQty = intval($_POST['stockQty'] ?? 0);
            $minStock = intval($_POST['minStock'] ?? 10);
            $reorderPoint = 0; // Default value since field is removed from UI
            
            if($itemID > 0 && $stockQty >= 0){
                // Check if item already exists in inventory
                $checkQuery = "SELECT itemID FROM inventory WHERE itemID = ?";
                $checkResult = executePreparedQuery($checkQuery, "i", [$itemID]);
                
                if($checkResult && mysqli_num_rows($checkResult) > 0){
                    $error = "Item already exists in inventory! Use the Edit button to update it.";
                    
                    if($isAjax){
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => $error]);
                        exit();
                    }
                } else {
                    // Use INSERT IGNORE to prevent duplicate key errors, or regular INSERT
                    $query = "INSERT INTO inventory (itemID, stock_qty, min_stock_level, reorder_point, last_updated) VALUES (?, ?, ?, ?, NOW())";
                    $result = executePreparedUpdate($query, "iiii", [$itemID, $stockQty, $minStock, $reorderPoint]);
                    
                    // Check for database errors
                    $dbError = $GLOBALS['db_last_error'] ?? null;
                    if($result === false || $dbError){
                        // Check if it's a duplicate key error
                        if($dbError && (strpos($dbError, 'Duplicate entry') !== false || strpos($dbError, 'PRIMARY') !== false)){
                            $error = "Item already exists in inventory! Use the Edit button to update it.";
                        } else {
                            $error = "Failed to add item to inventory!" . ($dbError ? " Error: " . $dbError : "");
                        }
                        error_log("Add failed - itemID: $itemID, Error: " . ($dbError ?? "Unknown"));
                        
                        if($isAjax){
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'error' => $error]);
                            exit();
                        }
                    } else {
                        // Get updated timestamp
                        $timestampQuery = "SELECT last_updated FROM inventory WHERE itemID = ?";
                        $timestampResult = executePreparedQuery($timestampQuery, "i", [$itemID]);
                        $timestampRow = $timestampResult ? mysqli_fetch_assoc($timestampResult) : null;
                        $lastUpdated = $timestampRow['last_updated'] ?? date('Y-m-d H:i:s');
                        
                        if($isAjax){
                            header('Content-Type: application/json');
                            echo json_encode([
                                'success' => true,
                                'message' => 'Item added to inventory successfully!',
                                'data' => [
                                    'itemID' => $itemID,
                                    'stockQty' => $stockQty,
                                    'minStock' => $minStock,
                                    'lastUpdated' => $lastUpdated
                                ]
                            ]);
                            exit();
                        } else {
                            // Redirect to prevent form resubmission and show updated data
                            $redirectUrl = $_SERVER['PHP_SELF'] . ($selectedCategoryID > 0 ? '?categoryID=' . $selectedCategoryID : '');
                            header("Location: " . $redirectUrl . "&success=1");
                            exit();
                        }
                    }
                }
            } else {
                $error = "Invalid data provided!";
                
                if($isAjax){
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $error]);
                    exit();
                }
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

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">Inventory Management</h2>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
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
                        <tr data-item-id="<?php echo $row['itemID']; ?>">
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
                                <span class="badge stock-badge bg-<?php echo ($row['stock_qty'] ?? 0) <= ($row['min_stock_level'] ?? 10) ? 'danger' : 'success'; ?> fs-6 px-3 py-2" data-stock="<?php echo $row['stock_qty'] ?? 0; ?>">
                                    <?php echo $row['stock_qty'] ?? 0; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge min-stock-badge bg-info text-dark" data-min-stock="<?php echo $row['min_stock_level'] ?? 0; ?>"><?php echo $row['min_stock_level'] ?? 0; ?></span>
                            </td>
                            <td>
                                <small class="text-muted last-updated" data-last-updated="<?php echo $row['last_updated'] ?? ''; ?>">
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
                            <select class="form-select" name="itemIDSelect" id="itemSelect" required>
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
    document.getElementById('itemSelect').setAttribute('name', 'itemIDSelect');
    document.getElementById('formItemID').value = '';
    document.getElementById('formItemID').removeAttribute('name');
    document.querySelector('#addItemModal .modal-title').textContent = 'Add Item to Inventory';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-plus me-2"></i>Add to Inventory';
});

// Handle form submission with AJAX for real-time UI update
document.getElementById('inventoryForm').addEventListener('submit', function(e) {
    e.preventDefault(); // Prevent default form submission
    
    const action = document.getElementById('formAction').value;
    const formItemID = document.getElementById('formItemID');
    const itemSelect = document.getElementById('itemSelect');
    const submitBtn = document.getElementById('submitBtn');
    const originalBtnText = submitBtn.innerHTML;
    
    // Get form data
    const formData = new FormData(this);
    
    // Ensure correct itemID is set
    if(action === 'update') {
        if(!formItemID.value) {
            alert('Error: Item ID is missing!');
            return false;
        }
        formData.set('itemID', formItemID.value);
    } else {
        const selectValue = itemSelect.value;
        if(selectValue) {
            formData.set('itemID', selectValue);
        } else {
            alert('Please select an item!');
            return false;
        }
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
    
    // Send AJAX request
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            // Get updated values from response or form
            const itemID = data.data ? data.data.itemID : parseInt(formData.get('itemID'));
            const stockQty = data.data ? data.data.stockQty : parseInt(formData.get('stockQty'));
            const minStock = data.data ? data.data.minStock : parseInt(formData.get('minStock'));
            const lastUpdated = data.data ? data.data.lastUpdated : null;
            
            // Update UI directly
            updateInventoryRow(itemID, stockQty, minStock, lastUpdated);
            
            // Show success message
            showAlert('success', data.message || (action === 'update' ? 'Inventory updated successfully!' : 'Item added to inventory successfully!'));
            
            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addItemModal'));
            if(modal) {
                modal.hide();
            }
            
            // Reset form
            document.getElementById('inventoryForm').reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('itemSelectContainer').style.display = 'block';
            document.getElementById('itemDisplayContainer').style.display = 'none';
            document.getElementById('itemSelect').required = true;
            document.getElementById('itemSelect').setAttribute('name', 'itemIDSelect');
            document.getElementById('formItemID').value = '';
            document.getElementById('formItemID').removeAttribute('name');
            document.querySelector('#addItemModal .modal-title').textContent = 'Add Item to Inventory';
            submitBtn.innerHTML = '<i class="fas fa-plus me-2"></i>Add to Inventory';
        } else {
            // Show error message
            showAlert('danger', data.error || 'Failed to update inventory!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('danger', 'An error occurred. Please try again.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalBtnText;
    });
});

// Function to update inventory row in UI
function updateInventoryRow(itemID, stockQty, minStock, lastUpdated = null) {
    const row = document.querySelector(`tr[data-item-id="${itemID}"]`);
    if(!row) {
        console.warn('Row not found for itemID:', itemID);
        return;
    }
    
    // Update stock badge
    const stockBadge = row.querySelector('.stock-badge');
    if(stockBadge) {
        stockBadge.textContent = stockQty;
        stockBadge.setAttribute('data-stock', stockQty);
        // Update badge color based on stock level
        if(stockQty <= minStock) {
            stockBadge.className = 'badge stock-badge bg-danger fs-6 px-3 py-2';
        } else {
            stockBadge.className = 'badge stock-badge bg-success fs-6 px-3 py-2';
        }
    }
    
    // Update min stock badge
    const minStockBadge = row.querySelector('.min-stock-badge');
    if(minStockBadge) {
        minStockBadge.textContent = minStock;
        minStockBadge.setAttribute('data-min-stock', minStock);
    }
    
    // Update last updated timestamp
    const lastUpdatedEl = row.querySelector('.last-updated');
    if(lastUpdatedEl) {
        let formattedDate;
        if(lastUpdated) {
            const date = new Date(lastUpdated);
            formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
            lastUpdatedEl.textContent = formattedDate + ' ' + formattedTime;
            lastUpdatedEl.setAttribute('data-last-updated', lastUpdated);
        } else {
            const now = new Date();
            formattedDate = now.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            const formattedTime = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
            lastUpdatedEl.textContent = formattedDate + ' ' + formattedTime;
            lastUpdatedEl.setAttribute('data-last-updated', now.toISOString());
        }
    }
    
    // Update edit button to reflect new values
    const editBtn = row.querySelector('button.btn-warning');
    if(editBtn) {
        editBtn.setAttribute('onclick', `editInventory(${itemID}, ${stockQty}, ${minStock})`);
    }
}

// Function to show alert messages
function showAlert(type, message) {
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Create new alert
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Insert at the top of the content area
    const headerBar = document.querySelector('.header-bar');
    if(headerBar && headerBar.nextElementSibling) {
        headerBar.nextElementSibling.insertBefore(alertDiv, headerBar.nextElementSibling.firstChild);
    } else {
        document.querySelector('.header-bar').after(alertDiv);
    }
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        if(alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>
