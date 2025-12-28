<?php
session_start();
include("connect.php");
include("includes/header.php");
?>


<div class="container my-5">
  <div class="row">
    <!-- Sidebar Categories -->
    <div class="col-md-3">
      <div class="card shadow mb-4">
        <div class="card-header bg-warning">
          <h5 class="mb-0"><i class="fas fa-bars"></i> Shop By Category</h5>
        </div>
        <div class="list-group list-group-flush">
          <a href="products.php" class="list-group-item list-group-item-action">All Categories</a>
          <?php
          $catQuery = "SELECT * FROM categories";
          $catResult = executeQuery($catQuery);
          while($cat = mysqli_fetch_assoc($catResult)):
          ?>
          <a href="products.php?category=<?php echo $cat['categoryID']; ?>" 
             class="list-group-item list-group-item-action">
            <?php echo $cat['categoryName']; ?>
          </a>
          <?php endwhile; ?>
        </div>
      </div>
    </div>

    <!-- Products -->
    <div class="col-md-9">
      <div class="row">
        <?php
        $categoryFilter = isset($_GET['category']) ? "AND items.categoryID = ".intval($_GET['category']) : "";
        $query = "SELECT items.*, categories.categoryName FROM items 
                  LEFT JOIN categories ON items.categoryID = categories.categoryID 
                  WHERE items.status = 'Active' $categoryFilter";
        $result = executeQuery($query);
        
        if(mysqli_num_rows($result) > 0):
          while($row = mysqli_fetch_assoc($result)):
        ?>
        <div class="col-md-4 mb-4">
          <div class="card product-card shadow border-0" style="transition: all 0.3s; overflow: hidden;">
            <div class="position-relative">
              <img src="<?php echo $row['itemImage'] ? 'uploads/'.$row['itemImage'] : 'https://via.placeholder.com/300x200'; ?>" 
                   class="card-img-top" alt="<?php echo $row['packageName']; ?>" style="height: 250px; object-fit: cover; transition: transform 0.3s;">
              <button class="btn btn-light position-absolute top-0 end-0 m-2 rounded-circle" style="width: 40px; height: 40px; padding: 0;" onclick="toggleFavorite(this, <?php echo $row['itemID']; ?>)">
                <i class="far fa-heart"></i>
              </button>
            </div>
            <div class="card-body">
              <span class="badge bg-secondary"><?php echo $row['categoryName']; ?></span>
              <h5 class="card-title mt-2"><?php echo $row['packageName']; ?></h5>
              <p class="card-text text-muted"><?php echo substr($row['foodDescription'], 0, 60); ?>...</p>
              <p class="h5 text-warning fw-bold mb-3">₱<?php echo $row['price']; ?></p>
              <div class="d-grid gap-2">
                <a href="product-details.php?id=<?php echo $row['itemID']; ?>" class="btn btn-warning">View Details</a>
                <form method="POST" action="cart.php" class="mt-2">
                  <input type="hidden" name="itemID" value="<?php echo $row['itemID']; ?>">
                  <div class="input-group">
                    <input type="number" class="form-control" name="quantity" value="1" min="1" style="max-width: 80px;">
                    <button type="submit" class="btn btn-outline-warning flex-grow-1">ADD TO CART</button>
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

