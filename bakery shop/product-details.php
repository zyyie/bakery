<?php
require_once __DIR__ . '/includes/bootstrap.php';

// Load product images mapping from JSON file
$imagesMap = [];
$imagesJsonPath = __DIR__ . '/product-images.json';
if (file_exists($imagesJsonPath)) {
  $imagesJson = file_get_contents($imagesJsonPath);
  $imagesMap = json_decode($imagesJson, true) ?: [];
}

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

// Get image from JSON mapping, fallback to database, then placeholder
$productImage = 'https://via.placeholder.com/600x400';
$packageName = $item['packageName'];

if (isset($imagesMap[$packageName])) {
  $productImage = $imagesMap[$packageName];
} elseif (!empty($item['itemImage'])) {
  $productImage = 'bakery bread image/' . $item['itemImage'];
}

// Resolve the actual image path
$productImage = resolveImagePath($productImage);

include("includes/header.php");
?>


<div class="container my-5">
  <div class="row">
    <div class="col-md-6 mb-4">
      <img src="<?php echo imageUrl($productImage); ?>" 
           class="img-fluid rounded shadow" alt="<?php echo e($item['packageName']); ?>">
    </div>
    <div class="col-md-6">
      <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
          <span class="badge bg-secondary mb-2"><?php echo $item['categoryName']; ?></span>
          <h2 class="mb-0"><?php echo $item['packageName']; ?></h2>
        </div>
        <button class="btn btn-light rounded-circle" style="width: 50px; height: 50px; padding: 0;" onclick="toggleFavorite(this, <?php echo $item['itemID']; ?>)">
          <i class="far fa-heart fa-2x"></i>
        </button>
      </div>
      <p class="h3 text-warning fw-bold mb-4">₱<?php echo $item['price']; ?></p>
      
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
        <button type="submit" class="btn btn-warning btn-lg">ADD TO CART</button>
        <a href="products.php" class="btn btn-outline-secondary btn-lg ms-2">Back to Products</a>
      </form>
    </div>
  </div>
</div>

<script>
// Check if user is logged in (passed from PHP)
const isUserLoggedIn = <?php echo isset($_SESSION['userID']) ? 'true' : 'false'; ?>;

function toggleFavorite(btn, itemID) {
  // Check if user is logged in
  if (!isUserLoggedIn) {
    // Show login required modal
    const loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
    loginModal.show();
    return;
  }
  
  const icon = btn.querySelector('i');
  if (icon.classList.contains('far')) {
    icon.classList.remove('far');
    icon.classList.add('fas');
    btn.classList.add('favorite-active');
    btn.style.color = '#dc3545';
    // Save to localStorage
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    if (!favorites.includes(itemID)) {
      favorites.push(itemID);
    }
    localStorage.setItem('favorites', JSON.stringify(favorites));
  } else {
    icon.classList.remove('fas');
    icon.classList.add('far');
    btn.classList.remove('favorite-active');
    btn.style.color = '';
    // Remove from localStorage
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    favorites = favorites.filter(id => id !== itemID);
    localStorage.setItem('favorites', JSON.stringify(favorites));
  }
}

// Load favorites on page load
document.addEventListener('DOMContentLoaded', function() {
  let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
  const itemID = <?php echo $item['itemID']; ?>;
  if (favorites.includes(itemID)) {
    const btn = document.querySelector(`button[onclick*="${itemID}"]`);
    if (btn) {
      const icon = btn.querySelector('i');
      icon.classList.remove('far');
      icon.classList.add('fas');
      btn.classList.add('favorite-active');
      btn.style.color = '#dc3545';
    }
  }
});
</script>

<!-- Login Required Modal -->
<div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-2">
        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center py-4">
        <div class="mb-3">
          <i class="fas fa-exclamation-triangle fa-3x text-warning"></i>
        </div>
        <h5 class="modal-title mb-3" id="loginRequiredModalLabel">Login Required</h5>
        <div class="mb-3">
          <i class="fas fa-heart fa-4x text-danger"></i>
        </div>
        <p class="text-muted mb-4">Please log in or sign up first before adding this to your favorites.</p>
        <div class="d-grid gap-2 d-md-flex justify-content-md-center">
          <a href="login.php" class="btn btn-warning btn-lg px-4">LOG IN</a>
          <a href="signup.php" class="btn btn-outline-secondary btn-lg px-4">SIGN UP</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

