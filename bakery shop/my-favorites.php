<?php
session_start();
include("connect.php");

if(!isset($_SESSION['userID'])){
  header("Location: login.php");
  exit();
}

include("includes/header.php");
?>


<div class="container my-5">
  <div id="favorites-container" class="row">
    <!-- Favorites will be loaded here via JavaScript -->
  </div>
  <div id="empty-favorites" class="text-center py-5" style="display: none;">
    <i class="far fa-heart fa-5x text-muted mb-3"></i>
    <h3 class="text-muted">No Favorites Yet</h3>
    <p class="text-muted">Start adding products to your favorites!</p>
    <a href="products.php" class="btn btn-warning mt-3">Browse Products</a>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
  const container = document.getElementById('favorites-container');
  const emptyMessage = document.getElementById('empty-favorites');
  
  if(favorites.length === 0) {
    container.style.display = 'none';
    emptyMessage.style.display = 'block';
    return;
  }
  
  // Fetch product details for all favorites
  const itemIDs = favorites.join(',');
  
  fetch(`get-favorites.php?ids=${itemIDs}`)
    .then(response => response.json())
    .then(data => {
      if(data.length === 0) {
        container.style.display = 'none';
        emptyMessage.style.display = 'block';
        return;
      }
      
      container.innerHTML = data.map(item => `
        <div class="col-md-4 mb-4">
          <div class="card product-card shadow border-0" style="transition: all 0.3s; overflow: hidden;">
            <div class="position-relative">
              <img src="${item.itemImage ? 'uploads/' + item.itemImage : 'https://via.placeholder.com/300x200'}" 
                   class="card-img-top" alt="${item.packageName}" style="height: 250px; object-fit: cover; transition: transform 0.3s;">
              <button class="btn btn-light position-absolute top-0 end-0 m-2 rounded-circle favorite-active" 
                      style="width: 40px; height: 40px; padding: 0; color: #dc3545;" 
                      onclick="toggleFavorite(this, ${item.itemID})">
                <i class="fas fa-heart"></i>
              </button>
            </div>
            <div class="card-body">
              <span class="badge bg-secondary">${item.categoryName || ''}</span>
              <h5 class="card-title mt-2">${item.packageName}</h5>
              <p class="card-text text-muted">${item.foodDescription ? item.foodDescription.substring(0, 60) + '...' : ''}</p>
              <p class="h5 text-warning fw-bold mb-3">₱${item.price}</p>
              <div class="d-grid gap-2">
                <a href="product-details.php?id=${item.itemID}" class="btn btn-warning">View Details</a>
                <form method="POST" action="cart.php" class="mt-2">
                  <input type="hidden" name="itemID" value="${item.itemID}">
                  <div class="input-group">
                    <input type="number" class="form-control" name="quantity" value="1" min="1" style="max-width: 80px;">
                    <button type="submit" class="btn btn-outline-warning flex-grow-1">ADD TO CART</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      `).join('');
    })
    .catch(error => {
      console.error('Error loading favorites:', error);
      container.innerHTML = '<div class="col-12"><div class="alert alert-danger">Error loading favorites. Please try again.</div></div>';
    });
});

function toggleFavorite(btn, itemID) {
  const icon = btn.querySelector('i');
  let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
  
  if (icon.classList.contains('fas')) {
    // Remove from favorites
    favorites = favorites.filter(id => id !== itemID);
    localStorage.setItem('favorites', JSON.stringify(favorites));
    
    // Reload page to update list
    location.reload();
  }
}
</script>

<?php include("includes/footer.php"); ?>

