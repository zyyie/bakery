<?php
require_once dirname(__DIR__) . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(dirname(__DIR__) . "/includes/header.php");

$errors = [];
$success = null;

// Handle POST (create/update)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $itemID = (int)($_POST['itemID'] ?? 0);
    $name = trim($_POST['packageName'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $status = in_array($_POST['status'] ?? '', ['Active', 'Inactive']) ? $_POST['status'] : 'Active';
    
    if ($name && $price > 0) {
        if ($itemID > 0) {
            executePreparedUpdate("UPDATE items SET packageName=?, price=?, status=? WHERE itemID=?", "sdsi", [$name, $price, $status, $itemID]);
            $success = 'Item updated.';
        } else {
            executePreparedUpdate("INSERT INTO items (packageName, price, status, creationDate) VALUES (?, ?, ?, NOW())", "sds", [$name, $price, $status]);
            $success = 'Item created.';
        }
    } else {
        $errors[] = 'Name and valid price required.';
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    executePreparedUpdate("DELETE FROM items WHERE itemID=?", "i", [(int)$_GET['delete']]);
    $success = 'Item deleted.';
}

// Load item for edit
$editItem = null;
if (isset($_GET['edit'])) {
    $res = executePreparedQuery("SELECT itemID, packageName, price, status FROM items WHERE itemID=?", "i", [(int)$_GET['edit']]);
    if ($res && $res->num_rows === 1) $editItem = $res->fetch_assoc();
}

// Load items list
$itemsRes = executePreparedQuery("SELECT itemID, packageName, price, status, creationDate FROM items ORDER BY creationDate DESC", "", []);
?>

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">CRUD</h2>
</div>

<div class="row">
  <div class="col-lg-4">
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title mb-3"><?php echo $editItem ? 'Edit Item' : 'Add New Item'; ?></h5>
        
        <?php if ($errors): ?>
          <div class="alert alert-danger"><?php foreach ($errors as $e) echo htmlspecialchars($e) . '<br>'; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="mode" value="<?php echo $editItem ? 'edit' : 'create'; ?>">
          <?php if ($editItem): ?>
            <input type="hidden" name="itemID" value="<?php echo (int)$editItem['itemID']; ?>">
          <?php endif; ?>

          <div class="mb-3">
            <label class="form-label">Package Name</label>
            <input type="text" name="packageName" class="form-control" value="<?php echo htmlspecialchars($editItem['packageName'] ?? ''); ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Price (₱)</label>
            <input type="number" step="0.01" min="0" name="price" class="form-control" value="<?php echo htmlspecialchars($editItem['price'] ?? ''); ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
              <option value="Active" <?php echo ($editItem['status'] ?? 'Active') === 'Active' ? 'selected' : ''; ?>>Active</option>
              <option value="Inactive" <?php echo ($editItem['status'] ?? 'Active') === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary"><?php echo $editItem ? 'Update' : 'Create'; ?></button>
          <?php if ($editItem): ?>
            <a href="crud.php" class="btn btn-secondary ms-2">Cancel</a>
          <?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-3">Items List</h5>
        <div class="table-responsive">
          <table class="table table-striped">
            <thead>
              <tr><th>#</th><th>Name</th><th>Price</th><th>Status</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php if ($itemsRes && $itemsRes->num_rows > 0): ?>
                <?php $i = 1; while ($row = $itemsRes->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($row['packageName']); ?></td>
                    <td>₱<?php echo number_format((float)$row['price'], 2); ?></td>
                    <td><span class="badge bg-<?php echo $row['status'] === 'Active' ? 'success' : 'secondary'; ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['creationDate']); ?></td>
                    <td>
                      <a href="?edit=<?php echo (int)$row['itemID']; ?>" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                      <a href="?delete=<?php echo (int)$row['itemID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?');"><i class="fas fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-center text-muted">No items found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include(dirname(__DIR__) . "/includes/footer.php"); ?>
