<?php
require_once __DIR__ . '/includes/bootstrap.php';
include("includes/header.php");
?>


<div class="container products-page my-5">
  <div class="row">
    <!-- Sidebar Categories -->
    <div class="col-md-3">
      <div class="card sidebar-card mb-4">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-bars"></i> Shop By Category</h5>
        </div>
        <div class="list-group list-group-flush">
          <a href="products.php" class="list-group-item list-group-item-action">All Categories</a>
          <?php
          $catResult = executePreparedQuery("SELECT * FROM categories", "", []);
          while($catResult && ($cat = mysqli_fetch_assoc($catResult))):
          ?>
          <a href="products.php?category=<?php echo $cat['categoryID']; ?>" 
             class="list-group-item list-group-item-action">
            <?php echo e($cat['categoryName']); ?>
          </a>
          <?php endwhile; ?>
        </div>
      </div>
    </div>

    <!-- Products -->
    <div class="col-md-9">
      <div class="row">
        <?php
        $categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
        if ($categoryId > 0) {
          $query = "SELECT items.*, categories.categoryName FROM items 
                    LEFT JOIN categories ON items.categoryID = categories.categoryID 
                    WHERE items.status = 'Active' AND items.categoryID = ?";
          $result = executePreparedQuery($query, "i", [$categoryId]);
        } else {
          $query = "SELECT items.*, categories.categoryName FROM items 
                    LEFT JOIN categories ON items.categoryID = categories.categoryID 
                    WHERE items.status = 'Active'";
          $result = executePreparedQuery($query, "", []);
        }
        
        if($result && mysqli_num_rows($result) > 0):
          while($row = mysqli_fetch_assoc($result)):
        ?>
        <div class="col-md-4 mb-4">
          <div class="card product-card">
            <div class="position-relative">
              <img src="<?php echo $row['itemImage'] ? 'uploads/' . e($row['itemImage']) : 'https://via.placeholder.com/300x200'; ?>" 
                   class="card-img-top" alt="<?php echo e($row['packageName']); ?>">
              <button class="btn btn-favorite" onclick="toggleFavorite(this, <?php echo (int)$row['itemID']; ?>)">
                <i class="far fa-heart"></i>
              </button>
            </div>
            <div class="card-body">
              <span class="category-badge"><?php echo e($row['categoryName']); ?></span>
              <h5 class="card-title mt-2"><?php echo e($row['packageName']); ?></h5>
              <p class="card-text text-muted"><?php echo e(substr($row['foodDescription'], 0, 60)); ?>...</p>
              <p class="price">₱<?php echo e($row['price']); ?></p>
              <div class="d-grid gap-2">
                <a href="product-details.php?id=<?php echo (int)$row['itemID']; ?>" class="btn btn-warning">View Details</a>
                <form method="POST" action="cart.php" class="mt-2">
                  <input type="hidden" name="itemID" value="<?php echo (int)$row['itemID']; ?>">
                  <div class="input-group">
                    <input type="number" class="form-control quantity-input" name="quantity" value="1" min="1">
                    <button type="submit" class="btn btn-outline-warning add-to-cart-btn">ADD TO CART</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
        <?php 
          endwhile;
        else:
        ?>
        <div class="col-12">
          <div class="alert alert-info">No products found in this category.</div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function toggleFavorite(btn, itemID) {
  const icon = btn.querySelector('i');
  if (icon.classList.contains('far')) {
    icon.classList.remove('far');
    icon.classList.add('fas');
    btn.classList.add('favorite-active');
    // Save to localStorage or send to server
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    if (!favorites.includes(itemID)) {
      favorites.push(itemID);
    }
    localStorage.setItem('favorites', JSON.stringify(favorites));
  } else {
    icon.classList.remove('fas');
    icon.classList.add('far');
    btn.classList.remove('favorite-active');
    // Remove from localStorage
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    favorites = favorites.filter(id => id !== itemID);
    localStorage.setItem('favorites', JSON.stringify(favorites));
  }
}

// Load favorites on page load
document.addEventListener('DOMContentLoaded', function() {
  let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
  favorites.forEach(itemID => {
    const btn = document.querySelector(`button[onclick*="${itemID}"]`);
    if (btn) {
      const icon = btn.querySelector('i');
      icon.classList.remove('far');
      icon.classList.add('fas');
      btn.classList.add('favorite-active');
    }
  });
});
</script>

<?php include("includes/footer.php"); ?>

