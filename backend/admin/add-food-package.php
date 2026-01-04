<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();
include(__DIR__ . "/includes/header.php");

$success = "";
$error = "";

if(isset($_POST['packageName'])){
  $packageName = $_POST['packageName'];
  $foodDescription = $_POST['foodDescription'];
  $itemContains = $_POST['itemContains'];
  $categoryID = intval($_POST['categoryID']);
  $size = $_POST['size'];
  $status = $_POST['status'];
  $suitableFor = intval($_POST['suitableFor']);
  $price = floatval($_POST['price']);
  
  // Handle image upload
  $itemImage = "";
  if(isset($_FILES['itemImage']) && $_FILES['itemImage']['error'] == 0){
    $uploadDir = __DIR__ . "/../../uploads/";
    if(!is_dir($uploadDir)){
      mkdir($uploadDir, 0777, true);
    }
    $fileName = time() . "_" . basename($_FILES['itemImage']['name']);
    $targetFile = $uploadDir . $fileName;
    if(move_uploaded_file($_FILES['itemImage']['tmp_name'], $targetFile)){
      $itemImage = $fileName;
    }
  }
  
  // Input validation
  if(empty($packageName) || empty($price) || $categoryID <= 0){
    $error = "Package name, price, and category are required!";
  } else {
    $query = "INSERT INTO items (packageName, foodDescription, itemContains, categoryID, itemImage, size, status, suitableFor, price) 
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $result = executePreparedUpdate($query, "sssisssid", [
      $packageName, $foodDescription, $itemContains, $categoryID, $itemImage, 
      $size, $status, $suitableFor, $price
    ]);
    
    if($result !== false){
      $success = "Food package added successfully!";
    } else {
      $error = "Failed to add food package!";
    }
  }
}

$catQuery = "SELECT * FROM categories";
$catResult = executeQuery($catQuery);
?>

<h2 class="mb-4">Add Food Package</h2>

<?php if($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<?php if($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<div class="card">
  <div class="card-body">
    <form method="POST" enctype="multipart/form-data">
      <div class="mb-3">
        <label class="form-label">Package Name</label>
        <input type="text" class="form-control" name="packageName" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Food Description</label>
        <textarea class="form-control" name="foodDescription" rows="5"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Item Contains</label>
        <textarea class="form-control" name="itemContains" rows="5"></textarea>
      </div>
      <div class="mb-3">
        <label class="form-label">Category</label>
        <select class="form-select" name="categoryID" required>
          <option value="">Choose Category</option>
          <?php while($cat = mysqli_fetch_assoc($catResult)): ?>
          <option value="<?php echo $cat['categoryID']; ?>"><?php echo $cat['categoryName']; ?></option>
          <?php endwhile; ?>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Item Image</label>
        <input type="file" class="form-control" name="itemImage" accept="image/*">
      </div>
      <div class="mb-3">
        <label class="form-label">Size</label>
        <select class="form-select" name="size">
          <option value="">Select Size</option>
          <option value="Small">Small</option>
          <option value="Medium">Medium</option>
          <option value="Large">Large</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Status</label>
        <select class="form-select" name="status" required>
          <option value="Active">Active</option>
          <option value="Inactive">Inactive</option>
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label">Suitable For no. of people</label>
        <input type="number" class="form-control" name="suitableFor" min="1">
      </div>
      <div class="mb-3">
        <label class="form-label">Price</label>
        <input type="number" class="form-control" name="price" step="0.01" required>
      </div>
      <button type="submit" class="btn btn-warning">ADD</button>
    </form>
  </div>
</div>

<?php include(__DIR__ . "/includes/footer.php"); ?>

