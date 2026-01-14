<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$itemID = intval($_GET['id']);
$query = "SELECT items.*, categories.categoryName FROM items 
          LEFT JOIN categories ON items.categoryID = categories.categoryID 
          WHERE items.itemID = ? AND items.status = 'Active'";
$result = executePreparedQuery($query, "i", [$itemID]);
$item = mysqli_fetch_assoc($result);

if(!$item){
  header("Location: products.php");
  exit();
}

include(__DIR__ . "/../../includes/header.php");
?>


<div class="container my-5">
  <div class="row">
    <div class="col-md-6 mb-4">
      <img src="<?php echo product_image_url($item, 1); ?>" 
           class="img-fluid rounded shadow" alt="<?php echo $item['packageName']; ?>">
    </div>
    <div class="col-md-6">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <span class="badge text-brown bg-brown-lighter mb-2"><?php echo $item['categoryName']; ?></span>
          <h2 class="mb-0"><?php echo $item['packageName']; ?></h2>
        </div>
        <button class="btn btn-light rounded-circle" style="width: 50px; height: 50px; padding: 0;" onclick="toggleFavorite(this, <?php echo $item['itemID']; ?>)">
          <i class="far fa-heart fa-2x"></i>
        </button>
      </div>
      <p class="h3 text-brown fw-bold mb-4">₱<?php echo $item['price']; ?></p>
      
      <div class="mb-4">
        <h5>Description</h5>
        <p class="text-muted"><?php echo nl2br($item['foodDescription']); ?></p>
      </div>
      
      <?php if($item['itemContains']): ?>
      <div class="mb-4">
        <h5>Item Contains</h5>
        <p class="text-muted"><?php echo nl2br($item['itemContains']); ?></p>
      </div>
      <?php endif; ?>
      
      <?php if($item['size']): ?>
      <div class="mb-4">
        <h5>Size</h5>
        <p class="text-muted"><?php echo $item['size']; ?></p>
      </div>
      <?php endif; ?>
      
      <?php if($item['suitableFor']): ?>
      <div class="mb-4">
        <h5>Suitable For</h5>
        <p class="text-muted"><?php echo $item['suitableFor']; ?> people</p>
      </div>
      <?php endif; ?>
      
      <form method="POST" action="cart.php" class="mt-4">
        <input type="hidden" name="itemID" value="<?php echo $item['itemID']; ?>">
        <div class="input-group mb-3" style="max-width: 300px;">
          <label class="input-group-text">Quantity</label>
          <input type="number" class="form-control" name="quantity" value="1" min="1">
        </div>
        <button type="submit" class="btn btn-brown btn-lg">ADD TO CART</button>
        <a href="products.php" class="btn btn-outline-brown btn-lg ms-2">Back to Products</a>
      </form>
    </div>
  </div>
</div>

<script>
function toggleFavorite(btn, itemID) {
  const icon = btn.querySelector('i');
  if (icon.classList.contains('far')) {
    icon.classList.remove('far');
    icon.classList.add('fas');
    btn.classList.add('active');
  } else {
    icon.classList.remove('fas');
    icon.classList.add('far');
    btn.classList.remove('active');
  }

  const stored = JSON.parse(localStorage.getItem('favorites') || '[]');
  const idx = stored.indexOf(itemID);
  if (idx === -1) {
    stored.push(itemID);
  } else {
    stored.splice(idx, 1);
  }
  localStorage.setItem('favorites', JSON.stringify(stored));
}
</script>

<?php include(__DIR__ . "/../../includes/footer.php"); ?>
