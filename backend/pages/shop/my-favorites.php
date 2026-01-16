
<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
include(__DIR__ . "/../../includes/header.php");
?>

<div class="checkout-container">
  <div class="mb-3">
    <a href="products.php" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-2"></i>Back to Products
    </a>
  </div>
  
  <div class="checkout-header">
    <h1>My Favorites</h1>
    <p>Your saved items</p>
  </div>

  <div class="checkout-form-card">
    <div class="checkout-form-header">
      <h4><i class="fas fa-heart me-2"></i>Favorites</h4>
    </div>
    <div class="checkout-form-body">
      <div id="favoritesEmpty" class="alert alert-info text-center" style="display:none;">
        <h4 class="mb-2">No favorites yet</h4>
        <a href="products.php" class="btn btn-brown">Browse Products</a>
      </div>
      <div class="row g-4" id="favoritesGrid"></div>
    </div>
  </div>
</div>

<script>
(function() {
  const grid = document.getElementById('favoritesGrid');
  const emptyEl = document.getElementById('favoritesEmpty');
  if (!grid || !emptyEl) return;

  const favorites = JSON.parse(localStorage.getItem('favorites') || '[]')
    .map(v => parseInt(v, 10))
    .filter(v => Number.isFinite(v) && v > 0);

  if (!favorites.length) {
    emptyEl.style.display = 'block';
    return;
  }

  function cardHtml(item) {
    const img = item.imageUrl || '';
    const name = item.packageName || '';
    const desc = item.foodDescription || '';
    const shortDesc = desc.length > 80 ? desc.slice(0, 80) + '...' : desc;
    const price = item.price ? Number(item.price).toFixed(2) : '0.00';
    const stock = (typeof item.stockQty === 'number') ? item.stockQty : parseInt(item.stockQty || '0', 10);
    const stockClass = stock <= 5 ? 'text-danger' : 'text-success';

    return `
      <div class="col-lg-3 col-md-6">
        <div class="card product-card h-100 shadow-sm border-0">
          <div class="product-image-wrapper">
            <img src="${img}" class="card-img-top" alt="${name}">
            <div class="product-overlay">
              <a href="products.php" class="btn btn-brown btn-sm">
                <i class="fas fa-eye"></i> View Details
              </a>
              <button type="button" class="btn btn-favorite btn-sm favorite-active" data-itemid="${item.itemID}">
                <i class="fas fa-heart"></i>
              </button>
            </div>
          </div>
          <div class="card-body d-flex flex-column p-4">
            <h5 class="card-title fw-bold mb-3">${name}</h5>
            <p class="card-text text-muted small flex-grow-1 mb-3">${shortDesc}</p>
            <div class="small text-muted mb-2">Stock: <span class="fw-semibold ${stockClass}">${isNaN(stock) ? 0 : stock}</span></div>
            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
              <p class="h5 text-brown fw-bold mb-0">₱${price}</p>
              <form method="POST" action="cart.php" class="m-0">
                <input type="hidden" name="itemID" value="${item.itemID}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="btn btn-outline-brown btn-sm" ${stock <= 0 ? 'disabled' : ''}>
                  <i class="fas fa-shopping-cart"></i>
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    `;
  }

  Promise.all(
    favorites.map(id => fetch(`api/item-details.php?id=${encodeURIComponent(id)}`).then(r => r.json()))
  )
  .then(items => {
    const valid = items.filter(it => it && it.itemID);
    if (!valid.length) {
      emptyEl.style.display = 'block';
      return;
    }
    grid.innerHTML = valid.map(cardHtml).join('');
  })
  .catch(() => {
    emptyEl.style.display = 'block';
  });
})();
</script>

<?php include(__DIR__ . "/../../includes/footer.php"); ?>

