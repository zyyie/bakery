<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
include(__DIR__ . "/../../includes/header.php");
?>


<div class="container products-page my-5">
  <div class="mb-3">
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-2"></i>Back to Home
    </a>
  </div>
  
  <div class="row">
    <!-- Sidebar Categories -->
    <div class="col-md-3">
      <div class="card sidebar-card mb-4">
        <div class="card-header">
          <h5 class="mb-0"><i class="fas fa-bars"></i> Shop By Category</h5>
        </div>
        <div class="list-group list-group-flush">
          <a href="products.php" class="list-group-item list-group-item-action <?php echo !isset($_GET['category']) ? 'active' : ''; ?>">All Categories</a>
          <?php
          // Get unique categories, using MIN(categoryID) to keep the first occurrence if duplicates exist
          $catResult = executePreparedQuery("SELECT MIN(categoryID) as categoryID, categoryName, MIN(creationDate) as creationDate FROM categories GROUP BY categoryName ORDER BY MIN(categoryID) ASC", "", []);
          while($catResult && ($cat = mysqli_fetch_assoc($catResult))):
          ?>
          <a href="products.php?category=<?php echo $cat['categoryID']; ?>" 
             class="list-group-item list-group-item-action <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['categoryID']) ? 'active' : ''; ?>">
            <?php echo e($cat['categoryName']); ?>
          </a>
          <?php endwhile; ?>
        </div>
      </div>
    </div>

    <!-- Products -->
    <div class="col-md-9">
      <?php
      $categoryId = isset($_GET['category']) ? intval($_GET['category']) : 0;
      $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
      
      // Check if inventory table exists
      $hasInventory = false;
      try {
          $invCheck = executePreparedQuery("SHOW TABLES LIKE 'inventory'", "", []);
          $hasInventory = ($invCheck && mysqli_num_rows($invCheck) > 0);
      } catch (Exception $e) {
          $hasInventory = false;
      }
      
      // Build query with search and category filtering
      $whereConditions = ["items.status = 'Active'"];
      $params = [];
      $paramTypes = "";
      
      if ($categoryId > 0) {
        $whereConditions[] = "items.categoryID = ?";
        $params[] = $categoryId;
        $paramTypes .= "i";
      }
      
      if (!empty($searchTerm)) {
        $whereConditions[] = "(items.packageName LIKE ? OR items.foodDescription LIKE ? OR categories.categoryName LIKE ?)";
        $searchPattern = '%' . $searchTerm . '%';
        $params[] = $searchPattern;
        $params[] = $searchPattern;
        $params[] = $searchPattern;
        $paramTypes .= "sss";
      }
      
      $whereClause = implode(' AND ', $whereConditions);
      
      if ($hasInventory) {
        $query = "SELECT items.*, categories.categoryName, inv.stock_qty 
                  FROM items 
                  LEFT JOIN categories ON items.categoryID = categories.categoryID 
                  LEFT JOIN inventory inv ON inv.itemID = items.itemID
                  WHERE $whereClause
                  ORDER BY items.packageName ASC";
      } else {
        $query = "SELECT items.*, categories.categoryName, 0 as stock_qty 
                  FROM items 
                  LEFT JOIN categories ON items.categoryID = categories.categoryID 
                  WHERE $whereClause
                  ORDER BY items.packageName ASC";
      }
      
      if (!empty($params)) {
        $result = executePreparedQuery($query, $paramTypes, $params);
      } else {
        $result = executePreparedQuery($query, "", []);
      }
      ?>
      
      <!-- Search Bar -->
      <div class="mb-3 d-flex justify-content-end">
        <form method="GET" action="products.php" class="d-flex gap-2 align-items-center w-100">
          <input type="hidden" name="category" value="<?php echo isset($_GET['category']) ? htmlspecialchars($_GET['category']) : ''; ?>">
          <div class="input-group flex-grow-1" style="border-radius: 50px; overflow: hidden; border: 1px solid #dee2e6;">
            <span class="input-group-text bg-light border-0" style="padding: 0.5rem 0.75rem;">
              <i class="fas fa-search text-muted" style="font-size: 0.875rem;"></i>
            </span>
            <input type="text" 
                   class="form-control border-0" 
                   name="search" 
                   id="searchInput"
                   placeholder="Search products..." 
                   value="<?php echo htmlspecialchars($searchTerm); ?>"
                   autocomplete="off"
                   style="font-size: 0.875rem; padding: 0.5rem 0.75rem; height: 40px;">
          </div>
          <button type="submit" class="btn btn-brown rounded-pill" style="font-size: 0.875rem; padding: 0.5rem 1.25rem; height: 40px; white-space: nowrap;">
            <i class="fas fa-search me-1"></i>Search
          </button>
          <?php if (!empty($searchTerm)): ?>
            <a href="products.php<?php echo $categoryId > 0 ? '?category=' . $categoryId : ''; ?>" 
               class="btn btn-outline-secondary rounded-pill" style="font-size: 0.875rem; padding: 0.5rem 1.25rem; height: 40px; white-space: nowrap;">
              <i class="fas fa-times me-1"></i>Clear
            </a>
          <?php endif; ?>
        </form>
      </div>
      
      <div class="row">
        <?php
        // Debug: Check for query errors
        if ($result === false) {
          $dbError = isset($GLOBALS['db_last_error']) ? $GLOBALS['db_last_error'] : 'Unknown database error';
          echo '<div class="col-12"><div class="alert alert-danger">Database Error: ' . htmlspecialchars($dbError) . '</div></div>';
        }
        
        if($result && mysqli_num_rows($result) > 0):
          while($row = mysqli_fetch_assoc($result)):
        ?>
        <div class="col-md-4 mb-4">
          <div class="card product-card h-100 shadow-sm border-0">
            <?php $stockQty = isset($row['stock_qty']) ? intval($row['stock_qty']) : 0; ?>
            <div class="product-image-wrapper js-quickview" data-itemid="<?php echo (int)$row['itemID']; ?>" style="cursor: pointer;">
              <img src="<?php echo product_image_url($row, 1); ?>" 
                   class="card-img-top" alt="<?php echo e($row['packageName']); ?>">
            </div>
            <div class="card-body d-flex flex-column p-4">
              <span class="category-badge"><?php echo e($row['categoryName']); ?></span>
              <h5 class="card-title fw-bold mt-2 mb-2"><?php echo e($row['packageName']); ?></h5>
              <p class="card-text text-muted small flex-grow-1 mb-3"><?php echo e(substr($row['foodDescription'], 0, 80)); ?><?php echo strlen($row['foodDescription']) > 80 ? '...' : ''; ?></p>
              <div class="small text-muted mb-2">
                Stock: <span class="fw-semibold <?php echo $stockQty <= 5 ? 'text-danger' : 'text-success'; ?>"><?php echo $stockQty; ?></span>
              </div>
              <div class="small text-muted mb-3">
                Delivery: <span class="fw-semibold">1–2 days</span> <span class="ms-1">(9:00 AM – 6:00 PM)</span>
              </div>
              <div class="d-flex align-items-center mt-auto pt-3 border-top">
                <p class="h5 text-brown fw-bold mb-0">₱<?php echo number_format((float)$row['price'], 2); ?></p>
              </div>
            </div>
          </div>
        </div>
        <?php 
          endwhile;
        else:
        ?>
        <div class="col-12">
          <div class="alert alert-info text-center py-5">
            <i class="fas fa-search fa-3x mb-3 text-muted"></i>
            <h5>No products found</h5>
            <?php if (!empty($searchTerm)): ?>
              <p>No products match your search "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>".</p>
              <p class="mb-0">
                <a href="products.php<?php echo $categoryId > 0 ? '?category=' . $categoryId : ''; ?>" class="btn btn-brown">
                  <i class="fas fa-arrow-left me-2"></i>View All Products
                </a>
              </p>
            <?php else: ?>
              <p>No products found in this category.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content" style="border-radius: 14px; overflow: hidden;">
      <div class="modal-header border-0" style="background: #fff;">
        <h5 class="modal-title" id="qvTitle">Product Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" style="max-height: 75vh; overflow: auto;">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="mb-2" style="border-radius: 12px; overflow: hidden;">
              <img id="qvMainImg" src="" alt="" style="width:100%; height: 260px; object-fit: cover;">
            </div>
            <div class="d-flex gap-2 align-items-center">
              <button type="button" class="btn btn-outline-brown btn-sm" id="qvPrev" aria-label="Prev"><i class="fas fa-chevron-left"></i></button>
              <div class="d-flex gap-2" id="qvThumbs" style="overflow:auto;"></div>
              <button type="button" class="btn btn-outline-brown btn-sm" id="qvNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mb-2">
              <span class="badge bg-brown-lighter text-brown" id="qvCategory"></span>
            </div>
            <div class="d-flex justify-content-between align-items-start">
              <div>
                <h5 class="mb-1" id="qvName"></h5>
                <div class="text-brown fw-bold" id="qvPrice"></div>
              </div>
              <button type="button" class="btn btn-light rounded-circle" style="width:40px;height:40px;" aria-label="Favorite"><i class="far fa-heart"></i></button>
            </div>
            <div class="mt-3">
              <div class="fw-bold mb-1">Description</div>
              <div class="text-muted" id="qvDesc"></div>
            </div>
            <div class="mt-3">
              <div class="fw-bold mb-1">Item Contains</div>
              <div class="text-muted" id="qvContains"></div>
            </div>

            <div class="mt-3">
              <div class="fw-bold mb-2">Quantity:</div>
              <div class="d-flex flex-wrap gap-2 mb-2" id="qvQtyPresets"></div>
              <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-brown" id="qvMinus">-</button>
                <input type="number" class="form-control" id="qvQty" value="1" min="1" style="max-width: 90px; text-align:center;">
                <button type="button" class="btn btn-outline-brown" id="qvPlus">+</button>
              </div>
            </div>

            <div class="mt-3">
              <div class="fw-bold mb-2">Package Type</div>
              <div class="btn-group w-100" role="group" aria-label="Package type">
                <button type="button" class="btn btn-brown" id="qvTypeBox">Per Box</button>
                <button type="button" class="btn btn-outline-brown" id="qvTypePack">Per Pack</button>
              </div>
            </div>

            <div class="mt-4">
              <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-brown flex-fill" id="qvAdd">ADD TO CART</button>
                <button type="button" class="btn btn-brown flex-fill" id="qvBuy">BUY NOW</button>
              </div>
            </div>

            <div class="mt-4">
              <div class="fw-bold mb-2"><i class="fas fa-star me-2 text-warning"></i>Customer Reviews</div>
              <div class="alert alert-warning small mb-2">You can only review products you have received. Please wait until your order is delivered.</div>
              <div class="text-muted small">No reviews yet. Be the first to review!</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function toggleFavorite(btn, itemID) {
  const icon = btn.querySelector('i');
  const itemName = btn.closest('.product-card').querySelector('.card-title').textContent;
  
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
    showToast('Added to Favorites', `${itemName} added to favorites`, 'success');
  } else {
    icon.classList.remove('fas');
    icon.classList.add('far');
    btn.classList.remove('favorite-active');
    // Remove from localStorage
    let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
    favorites = favorites.filter(id => id !== itemID);
    localStorage.setItem('favorites', JSON.stringify(favorites));
    showToast('Removed from Favorites', `${itemName} removed from favorites`, 'info');
  }
}

// Load favorites on page load
// Toast notification system
function showToast(title, message, type = 'success') {
  const container = document.querySelector('.bakery-toast-container') || (() => {
    const div = document.createElement('div');
    div.className = 'bakery-toast-container';
    document.body.appendChild(div);
    return div;
  })();

  const toast = document.createElement('div');
  toast.className = `bakery-toast ${type}`;
  
  const iconMap = {
    success: 'fas fa-check-circle',
    error: 'fas fa-exclamation-circle',
    info: 'fas fa-info-circle'
  };

  toast.innerHTML = `
    <div class="bakery-toast-icon">
      <i class="${iconMap[type]}"></i>
    </div>
    <div class="bakery-toast-content">
      <div class="bakery-toast-title">${title}</div>
      <div class="bakery-toast-message">${message}</div>
    </div>
    <button class="bakery-toast-close">
      <i class="fas fa-times"></i>
    </button>
  `;

  container.appendChild(toast);

  // Show toast
  setTimeout(() => toast.classList.add('show'), 10);

  // Auto hide after 3 seconds
  const hideToast = () => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 300);
  };
  const timeoutId = setTimeout(hideToast, 3000);

  // Close button
  toast.querySelector('.bakery-toast-close').addEventListener('click', () => {
    clearTimeout(timeoutId);
    hideToast();
  });
}

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

  const qvModalEl = document.getElementById('quickViewModal');
  const qvModal = qvModalEl ? new bootstrap.Modal(qvModalEl) : null;

  const presets = [1, 4, 6, 8, 12, 16, 20, 24, 32];
  const qvQtyPresets = document.getElementById('qvQtyPresets');
  const qvQtyInput = document.getElementById('qvQty');
  const qvMinus = document.getElementById('qvMinus');
  const qvPlus = document.getElementById('qvPlus');
  const qvTypeBox = document.getElementById('qvTypeBox');
  const qvTypePack = document.getElementById('qvTypePack');

  let currentItemId = 0;
  let currentImages = [];
  let currentImageIdx = 0;
  let currentUnitPrice = 0; // base price per item/pack

  function setPackageType(type) {
    const qvTypeBox = document.getElementById('qvTypeBox');
    const qvTypePack = document.getElementById('qvTypePack');
    // If modal is using the new package toggle (package-btn), drive the active state
    if (qvTypeBox && qvTypePack && qvTypeBox.classList.contains('package-btn') && qvTypePack.classList.contains('package-btn')) {
      qvTypeBox.classList.toggle('active', type === 'box');
      qvTypePack.classList.toggle('active', type === 'pack');
      return;
    }
    if (type === 'box') {
      if (qvTypeBox) {
        qvTypeBox.classList.add('btn-brown');
        qvTypeBox.classList.remove('btn-outline-brown');
      }
      if (qvTypePack) {
        qvTypePack.classList.add('btn-outline-brown');
        qvTypePack.classList.remove('btn-brown');
      }
    } else {
      if (qvTypePack) {
        qvTypePack.classList.add('btn-brown');
        qvTypePack.classList.remove('btn-outline-brown');
      }
      if (qvTypeBox) {
        qvTypeBox.classList.add('btn-outline-brown');
        qvTypeBox.classList.remove('btn-brown');
      }
    }
  }

  function setMainImage(idx) {
    if (!currentImages.length) return;
    currentImageIdx = Math.max(0, Math.min(idx, currentImages.length - 1));
    const img = document.getElementById('qvMainImg');
    if (img) img.src = currentImages[currentImageIdx];
  }

  function updateDisplayedPrice() {
    const priceEl = document.getElementById('qvPrice');
    const qtyEl = document.getElementById('qvQty');
    if (!priceEl || !qtyEl || !currentUnitPrice) return;
    let qty = parseInt(qtyEl.value || '1', 10);
    if (isNaN(qty) || qty < 1) qty = 1;
    const total = currentUnitPrice * qty;
    priceEl.textContent = `₱${total.toFixed(2)}`;
  }

  if (qvQtyPresets) {
    qvQtyPresets.innerHTML = presets.map(n => `<button type="button" class="btn btn-outline-brown btn-sm qv-preset" data-n="${n}">${n} pcs</button>`).join('');
    qvQtyPresets.addEventListener('click', (e) => {
      const t = e.target;
      if (!(t instanceof HTMLElement)) return;
      const btn = t.closest('.qv-preset');
      if (!btn) return;
      const n = parseInt(btn.getAttribute('data-n') || '1', 10);
      if (qvQtyInput) qvQtyInput.value = String(Math.max(1, n));
      updateDisplayedPrice();
    });
  }

  if (qvMinus) qvMinus.addEventListener('click', () => {
    const v = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
    if (qvQtyInput) qvQtyInput.value = String(Math.max(1, v - 1));
    updateDisplayedPrice();
  });
  if (qvPlus) qvPlus.addEventListener('click', () => {
    const v = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
    if (qvQtyInput) qvQtyInput.value = String(Math.max(1, v + 1));
    updateDisplayedPrice();
  });
  if (qvTypeBox) qvTypeBox.addEventListener('click', () => setPackageType('box'));
  if (qvTypePack) qvTypePack.addEventListener('click', () => setPackageType('pack'));

  const prevBtn = document.getElementById('qvPrev');
  const nextBtn = document.getElementById('qvNext');
  if (prevBtn) prevBtn.addEventListener('click', () => setMainImage(currentImageIdx - 1));
  if (nextBtn) nextBtn.addEventListener('click', () => setMainImage(currentImageIdx + 1));

  const thumbsWrap = document.getElementById('qvThumbs');
  if (thumbsWrap) {
    thumbsWrap.addEventListener('click', (e) => {
      const t = e.target;
      if (!(t instanceof HTMLElement)) return;
      const img = t.closest('.qv-thumb');
      if (!img) return;
      const all = Array.from(thumbsWrap.querySelectorAll('.qv-thumb'));
      const idx = all.indexOf(img);
      if (idx >= 0) setMainImage(idx);
    });
  }

  async function loadItem(itemId) {
    const res = await fetch(`api/item-details.php?id=${encodeURIComponent(itemId)}`);
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data && data.error ? data.error : 'Failed to load item');
    return data;
  }

  // Minimal image lightbox (initialized once globally)
  if (!window._bakeryLightboxInit) {
    window._bakeryLightboxInit = true;
    const style = document.createElement('style');
    style.textContent = `
      .lb-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);display:none;align-items:center;justify-content:center;z-index:2000}
      .lb-overlay.is-open{display:flex}
      .lb-content{position:relative;max-width:90vw;max-height:90vh}
      .lb-img{max-width:90vw;max-height:90vh;object-fit:contain;border-radius:6px;box-shadow:0 10px 30px rgba(0,0,0,.5)}
      .lb-close{position:absolute;top:20px;right:20px;background:rgba(255,255,255,.9);color:#333;border:none;border-radius:50%;width:50px;height:50px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:24px;z-index:10;transition:all 0.3s ease}
      .lb-prev,.lb-next{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.95);color:var(--brown-primary);border:3px solid var(--brown-primary);border-radius:50%;width:60px;height:60px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:24px;z-index:10;transition:all 0.3s ease;box-shadow:0 4px 12px rgba(0,0,0,0.3)}
      .lb-prev:hover,.lb-next:hover{background:var(--brown-primary);color:#fff;transform:translateY(-50%) scale(1.1);box-shadow:0 6px 16px rgba(0,0,0,0.4)}
      .lb-close:hover{background:rgba(255,255,255,1);transform:scale(1.1)}
      .lb-prev{left:20px}
      .lb-next{right:20px}
      @media (max-width: 768px){.lb-prev{left:10px}.lb-next{right:10px}.lb-prev,.lb-next{width:50px;height:50px;font-size:20px}}
    `;
    document.head.appendChild(style);

    const overlay = document.createElement('div');
    overlay.className = 'lb-overlay';
    overlay.innerHTML = `
      <div class="lb-content">
        <button type="button" class="lb-close" aria-label="Close"><i class="fas fa-times"></i></button>
        <button type="button" class="lb-prev" aria-label="Previous"><i class="fas fa-chevron-left"></i></button>
        <img class="lb-img" alt="" />
        <button type="button" class="lb-next" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
      </div>`;
    document.body.appendChild(overlay);

    let imgs = [];
    let idx = 0;
    function setSrc(i){
      if (imgs.length === 0) return;
      idx = ((i % imgs.length) + imgs.length) % imgs.length;
      const img = overlay.querySelector('.lb-img');
      if (img) img.src = imgs[idx] || '';
      // Update arrow visibility
      const prevBtn = overlay.querySelector('.lb-prev');
      const nextBtn = overlay.querySelector('.lb-next');
      if (prevBtn) {
        prevBtn.style.opacity = idx === 0 ? '0.5' : '1';
        prevBtn.style.cursor = idx === 0 ? 'not-allowed' : 'pointer';
      }
      if (nextBtn) {
        nextBtn.style.opacity = idx === imgs.length - 1 ? '0.5' : '1';
        nextBtn.style.cursor = idx === imgs.length - 1 ? 'not-allowed' : 'pointer';
      }
    }
    function openLightbox(images, startIdx){
      imgs = Array.isArray(images)? images.slice(): [];
      if (!imgs.length) return;
      setSrc(startIdx||0);
      overlay.classList.add('is-open');
      document.addEventListener('keydown', keyNav);
    }
    function close(){
      overlay.classList.remove('is-open');
      document.removeEventListener('keydown', keyNav);
    }
    function keyNav(e){
      if (e.key === 'Escape') return close();
      if (e.key === 'ArrowLeft') return setSrc(idx-1);
      if (e.key === 'ArrowRight') return setSrc(idx+1);
    }
    overlay.querySelector('.lb-close')?.addEventListener('click', close);
    overlay.querySelector('.lb-prev')?.addEventListener('click', (e) => {
      e.stopPropagation();
      if (idx > 0) setSrc(idx-1);
    });
    overlay.querySelector('.lb-next')?.addEventListener('click', (e) => {
      e.stopPropagation();
      if (idx < imgs.length - 1) setSrc(idx+1);
    });
    overlay.addEventListener('click', (e)=>{ if (e.target === overlay) close(); });

    // expose
    window.openBakeryLightbox = openLightbox;
  }

  document.querySelectorAll('.js-quickview').forEach(btn => {
    btn.addEventListener('click', async () => {
      const itemId = parseInt(btn.getAttribute('data-itemid') || '0', 10);
      if (!itemId || !qvModal) return;
      // Capture clicked image src (if present) so we can start the modal on that image
      const clickedImgEl = btn.querySelector('img');
      const desiredSrc = clickedImgEl ? clickedImgEl.src : '';

      currentItemId = itemId;
      if (qvQtyInput) qvQtyInput.value = '1';
      setPackageType('box');

      // Show loading state in the Quick View modal then open it
      const qvBody = document.querySelector('#quickViewModal .modal-body');
      if (qvBody) {
        qvBody.innerHTML = `
          <div class="bakery-loading-inline">
            <div class="bakery-loading">
              <div class="bakery-loading-dot"></div>
              <div class="bakery-loading-dot"></div>
              <div class="bakery-loading-dot"></div>
            </div>
            <div>Loading product details...</div>
          </div>
        `;
      }
      qvModal.show();

      try {
        const item = await loadItem(itemId);
        if (qvBody) {
          qvBody.innerHTML = `
            <div class="row g-3">
              <div class="col-md-6">
                <div class="product-gallery position-relative">
                  <img id="qvMainImg" src="" alt="" style="width:100%; height: 400px; object-fit: contain; background: #f8f9fa; cursor: zoom-in;">
                </div>
                <div class="gallery-controls">
                  <button type="button" class="gallery-nav-btn" id="qvPrev" aria-label="Prev"><i class="fas fa-chevron-left"></i></button>
                  <div class="gallery-thumbs" id="qvThumbs"></div>
                  <button type="button" class="gallery-nav-btn" id="qvNext" aria-label="Next"><i class="fas fa-chevron-right"></i></button>
                </div>
              </div>
              <div class="col-md-6">
                <div class="product-info">
                  <div class="product-category" id="qvCategory"></div>
                  <div class="product-header">
                    <div>
                      <h5 class="product-title" id="qvName"></h5>
                      <div class="product-price" id="qvPrice"></div>
                    </div>
                    <button type="button" class="favorite-btn" aria-label="Favorite"><i class="far fa-heart"></i></button>
                  </div>
                  <div class="product-section">
                    <div class="product-section-title">Description</div>
                    <div class="product-section-content" id="qvDesc"></div>
                  </div>
                  <div class="product-section">
                    <div class="product-section-title">Item Contains</div>
                    <div class="product-section-content" id="qvContains"></div>
                  </div>

                  <div class="product-section">
                    <div class="product-section-title">Availability</div>
                    <div class="product-section-content">
                      <div class="d-flex flex-column gap-1">
                        <div>Stock: <span class="fw-semibold" id="qvStock"></span></div>
                        <div>Delivery: <span class="fw-semibold" id="qvDeliveryLead"></span> <span class="text-muted" id="qvDeliveryWindow"></span></div>
                      </div>
                    </div>
                  </div>

                  <div class="quantity-section">
                    <div class="product-section-title">Quantity:</div>
                    <div class="quantity-presets" id="qvQtyPresets"></div>
                    <div class="quantity-controls">
                      <button type="button" class="quantity-btn" id="qvMinus">-</button>
                      <input type="number" class="quantity-input" id="qvQty" value="1" min="1">
                      <button type="button" class="quantity-btn" id="qvPlus">+</button>
                    </div>
                  </div>

                  <div class="product-section">
                    <div class="product-section-title">Package Type</div>
                    <div class="package-toggle">
                      <button type="button" class="package-btn" id="qvTypeBox">Per Box</button>
                      <button type="button" class="package-btn" id="qvTypePack">Per Pack</button>
                    </div>
                  </div>

                  <div class="action-buttons">
                    <button type="button" class="action-btn action-btn-cart" id="qvAdd">ADD TO CART</button>
                    <button type="button" class="action-btn action-btn-buy" id="qvBuy">BUY NOW</button>
                  </div>

                  <div class="reviews-section">
                    <div class="reviews-title"><i class="fas fa-star me-2 text-warning"></i>Customer Reviews</div>
                    <div class="reviews-alert">You can only review products you have received. Please wait until your order is delivered.</div>
                    <div class="reviews-list" id="reviewsList">
                      <!-- Reviews will be loaded here -->
                    </div>
                    <div class="review-form">
                      <div class="review-form-title">Write a Review</div>
                      <div class="review-rating-input" id="reviewRatingInput">
                        <i class="fas fa-star star" data-rating="1"></i>
                        <i class="fas fa-star star" data-rating="2"></i>
                        <i class="fas fa-star star" data-rating="3"></i>
                        <i class="fas fa-star star" data-rating="4"></i>
                        <i class="fas fa-star star" data-rating="5"></i>
                      </div>
                      <textarea class="review-textarea" id="reviewTextarea" placeholder="Share your experience with this product..."></textarea>
                      <button type="button" class="review-submit-btn" id="reviewSubmitBtn">Submit Review</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `;
        }

        // Re-get references after restoring HTML
        const nameEl = document.getElementById('qvName');
        const catEl = document.getElementById('qvCategory');
        const priceEl = document.getElementById('qvPrice');
        const descEl = document.getElementById('qvDesc');
        const contEl = document.getElementById('qvContains');
        const titleEl = document.getElementById('qvTitle');
        const stockEl = document.getElementById('qvStock');
        const delLeadEl = document.getElementById('qvDeliveryLead');
        const delWinEl = document.getElementById('qvDeliveryWindow');

        if (nameEl) nameEl.textContent = item.packageName || '';
        if (catEl) catEl.textContent = item.categoryName || '';
        // Store base unit price and show initial total based on quantity
        currentUnitPrice = parseFloat(item.price || '0') || 0;
        if (priceEl) {
          updateDisplayedPrice();
        }
        if (descEl) descEl.textContent = item.foodDescription || '';
        if (contEl) contEl.textContent = item.itemContains || '';
        if (titleEl) titleEl.textContent = item.packageName || 'Product Details';

        if (stockEl) {
          const s = (typeof item.stockQty === 'number') ? item.stockQty : parseInt(item.stockQty || '0', 10);
          stockEl.textContent = String(isNaN(s) ? 0 : s);
          stockEl.classList.toggle('text-danger', (s <= 5));
          stockEl.classList.toggle('text-success', (s > 5));
        }
        if (delLeadEl) delLeadEl.textContent = item.deliveryLeadTime || '1–2 days';
        if (delWinEl) delWinEl.textContent = item.deliveryWindow ? `(${item.deliveryWindow})` : '(9:00 AM – 6:00 PM)';

        currentImages = item.images || [item.imageUrl];
        currentImageIdx = 0;
        const thumbsWrap = document.getElementById('qvThumbs');
        if (thumbsWrap) {
          thumbsWrap.innerHTML = '';
          currentImages.forEach((src, i) => {
            const thumb = document.createElement('img');
            thumb.className = 'gallery-thumb';
            thumb.src = src;
            thumb.alt = '';
            thumbsWrap.appendChild(thumb);
          });
        }
        // If we know which image was clicked, try to open the modal at that image
        let initIdx = 0;
        if (desiredSrc) {
          const foundIdx = currentImages.findIndex(src => src === desiredSrc);
          if (foundIdx >= 0) initIdx = foundIdx;
        }
        setMainImage(initIdx);

        // Click main image to open lightbox with arrows (no need to click thumbnails one by one)
        const mainImgEl = document.getElementById('qvMainImg');
        if (mainImgEl) {
          mainImgEl.style.cursor = 'zoom-in';
          mainImgEl.addEventListener('click', () => {
            if (window.openBakeryLightbox) window.openBakeryLightbox(currentImages, currentImageIdx);
          });
        }

        // Re-attach quantity presets
        const qvQtyPresetsEl = document.getElementById('qvQtyPresets');
        if (qvQtyPresetsEl) {
          qvQtyPresetsEl.innerHTML = presets.map(n => `<button type="button" class="quantity-preset" data-n="${n}">${n} pcs</button>`).join('');
          qvQtyPresetsEl.addEventListener('click', (e) => {
            const t = e.target;
            if (!(t instanceof HTMLElement)) return;
            const btn = t.closest('.quantity-preset');
            if (!btn) return;
            const n = parseInt(btn.getAttribute('data-n') || '1', 10);
            const qvQtyInputNew = document.getElementById('qvQty');
            if (qvQtyInputNew) qvQtyInputNew.value = String(Math.max(1, n));
            updateDisplayedPrice();
            // Update active state
            qvQtyPresetsEl.querySelectorAll('.quantity-preset').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
          });
        }

        // Re-attach quantity controls
        const qvMinusNew = document.getElementById('qvMinus');
        const qvPlusNew = document.getElementById('qvPlus');
        const qvTypeBoxNew = document.getElementById('qvTypeBox');
        const qvTypePackNew = document.getElementById('qvTypePack');

        if (qvMinusNew) qvMinusNew.addEventListener('click', () => {
          // Get fresh reference to quantity input
          const qtyInputEl = document.getElementById('qvQty');
          const v = qtyInputEl ? parseInt(qtyInputEl.value || '1', 10) : 1;
          if (qtyInputEl) qtyInputEl.value = String(Math.max(1, v - 1));
          updateDisplayedPrice();
        });
        if (qvPlusNew) qvPlusNew.addEventListener('click', () => {
          // Get fresh reference to quantity input
          const qtyInputEl = document.getElementById('qvQty');
          const v = qtyInputEl ? parseInt(qtyInputEl.value || '1', 10) : 1;
          if (qtyInputEl) qtyInputEl.value = String(Math.max(1, v + 1));
          updateDisplayedPrice();
        });
        if (qvTypeBoxNew) qvTypeBoxNew.addEventListener('click', () => setPackageType('box'));
        if (qvTypePackNew) qvTypePackNew.addEventListener('click', () => setPackageType('pack'));

        // Re-attach image navigation
        const prevBtnNew = document.getElementById('qvPrev');
        const nextBtnNew = document.getElementById('qvNext');
        if (prevBtnNew) prevBtnNew.addEventListener('click', () => setMainImage(currentImageIdx - 1));
        if (nextBtnNew) nextBtnNew.addEventListener('click', () => setMainImage(currentImageIdx + 1));

        const thumbsWrapNew = document.getElementById('qvThumbs');
        if (thumbsWrapNew) {
          thumbsWrapNew.addEventListener('click', (e) => {
            const t = e.target;
            if (!(t instanceof HTMLElement)) return;
            const img = t.closest('.gallery-thumb');
            if (!img) return;
            const all = Array.from(thumbsWrapNew.querySelectorAll('.gallery-thumb'));
            const idx = all.indexOf(img);
            if (idx >= 0) {
              all.forEach(th => th.classList.remove('active'));
              img.classList.add('active');
              setMainImage(idx);
            }
          });
        }

        // Re-attach action buttons
        const qvAddNew = document.getElementById('qvAdd');
        const qvBuyNew = document.getElementById('qvBuy');

        if (qvAddNew) qvAddNew.addEventListener('click', async () => {
          // Get fresh reference to quantity input from the dynamically loaded modal
          const qtyInputEl = document.getElementById('qvQty');
          const qty = qtyInputEl ? parseInt(qtyInputEl.value || '1', 10) : 1;
          const btn = qvAddNew;
          const originalText = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Adding...';
          btn.disabled = true;
          try {
            const result = await apiAddToCart(currentItemId, Math.max(1, qty));
            showToast('Added to Cart', `${result.cartCount} item${result.cartCount > 1 ? 's' : ''} in your cart`, 'success');
            btn.innerHTML = '<i class="fas fa-check me-2"></i>Added!';
            setTimeout(() => {
              btn.innerHTML = originalText;
              btn.disabled = false;
            }, 1500);
          } catch (e) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            showToast('Error', 'Failed to add to cart. Please try again.', 'error');
          }
        });

        if (qvBuyNew) qvBuyNew.addEventListener('click', async () => {
          // Get fresh reference to quantity input from the dynamically loaded modal
          const qtyInputEl = document.getElementById('qvQty');
          const qty = qtyInputEl ? parseInt(qtyInputEl.value || '1', 10) : 1;
          const btn = qvBuyNew;
          const originalText = btn.innerHTML;
          btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing...';
          btn.disabled = true;
          try {
            await apiAddToCart(currentItemId, Math.max(1, qty));
            showToast('Processing', 'Redirecting to checkout...', 'info');
            setTimeout(() => {
              window.location.href = 'checkout.php';
            }, 1000);
          } catch (e) {
            btn.innerHTML = originalText;
            btn.disabled = false;
            showToast('Error', 'Failed to process order. Please try again.', 'error');
          }
        });

        // Reviews functionality
        const reviewsList = document.getElementById('reviewsList');
        const reviewRatingInput = document.getElementById('reviewRatingInput');
        const reviewTextarea = document.getElementById('reviewTextarea');
        const reviewSubmitBtn = document.getElementById('reviewSubmitBtn');
        let selectedRating = 0;

        // Load reviews for this product
        loadReviews(currentItemId);

        // Rating star selection
        if (reviewRatingInput) {
          reviewRatingInput.addEventListener('click', (e) => {
            const star = e.target.closest('.star');
            if (!star) return;
            const rating = parseInt(star.getAttribute('data-rating'));
            selectedRating = rating;
            updateRatingDisplay(rating);
          });
        }

        // Submit review
        if (reviewSubmitBtn) {
          reviewSubmitBtn.addEventListener('click', () => {
            const reviewText = reviewTextarea ? reviewTextarea.value.trim() : '';
            if (selectedRating === 0) {
              showToast('Error', 'Please select a rating', 'error');
              return;
            }
            if (!reviewText) {
              showToast('Error', 'Please write a review', 'error');
              return;
            }
            
            // Here you would normally submit to a reviews API
            // For now, we'll just add it to the display
            addReviewToDisplay({
              author: 'You',
              rating: selectedRating,
              date: new Date().toLocaleDateString(),
              content: reviewText
            });
            
            // Reset form
            selectedRating = 0;
            updateRatingDisplay(0);
            if (reviewTextarea) reviewTextarea.value = '';
            showToast('Success', 'Your review has been submitted', 'success');
          });
        }

        function updateRatingDisplay(rating) {
          if (reviewRatingInput) {
            const stars = reviewRatingInput.querySelectorAll('.star');
            stars.forEach((star, index) => {
              if (index < rating) {
                star.classList.add('active');
              } else {
                star.classList.remove('active');
              }
            });
          }
        }

        function loadReviews(productId) {
          // Here you would normally fetch reviews from an API
          // For demo, we'll add some sample reviews
          const sampleReviews = [
            {
              author: 'John D.',
              rating: 5,
              date: '2024-01-15',
              content: 'Excellent product! Fresh and delicious. Will definitely order again.'
            },
            {
              author: 'Maria S.',
              rating: 4,
              date: '2024-01-10',
              content: 'Good quality bread, delivered on time. Packaging could be better.'
            }
          ];
          
          if (reviewsList) {
            reviewsList.innerHTML = '';
            sampleReviews.forEach(review => addReviewToDisplay(review));
          }
        }

        function addReviewToDisplay(review) {
          if (!reviewsList) return;
          
          const reviewEl = document.createElement('div');
          reviewEl.className = 'review-item';
          reviewEl.innerHTML = `
            <div class="review-header">
              <span class="review-author">${review.author}</span>
              <span class="review-date">${review.date}</span>
            </div>
            <div class="review-rating">
              ${Array.from({length: 5}, (_, i) => 
                `<i class="fas fa-star star ${i < review.rating ? '' : 'empty'}"></i>`
              ).join('')}
            </div>
            <div class="review-content">${review.content}</div>
          `;
          reviewsList.appendChild(reviewEl);
        }

        // Favorite button functionality
        const favBtn = document.querySelector('#quickViewModal .favorite-btn');
        if (favBtn) {
          favBtn.addEventListener('click', () => {
            const icon = favBtn.querySelector('i');
            const isFavorited = icon.classList.contains('fas');
            const itemName = (document.getElementById('qvName') || {}).textContent || 'this item';
            
            if (isFavorited) {
              icon.classList.remove('fas');
              icon.classList.add('far');
              favBtn.classList.remove('active');
              let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
              favorites = favorites.filter(id => id !== currentItemId);
              localStorage.setItem('favorites', JSON.stringify(favorites));
              showToast('Removed from Favorites', `${itemName} removed from favorites`, 'info');
            } else {
              icon.classList.remove('far');
              icon.classList.add('fas');
              favBtn.classList.add('active');
              let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
              if (!favorites.includes(currentItemId)) {
                favorites.push(currentItemId);
              }
              localStorage.setItem('favorites', JSON.stringify(favorites));
              showToast('Added to Favorites', `${itemName} added to favorites`, 'success');
            }
          });
        }
      } catch (e) {
        if (qvBody) {
          const errorMsg = e.message || 'Failed to load product details. Please try again.';
          qvBody.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${errorMsg}</div>`;
          console.error('Error loading product details:', e);
        }
      }
    });
  });

  async function apiAddToCart(itemID, quantity) {
    const res = await fetch('api/cart-add.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ itemID, quantity })
    });
    const data = await res.json().catch(() => ({}));
    if (!res.ok) throw new Error(data && data.error ? data.error : 'Failed to add to cart');
    return data;
  }

  const qvAdd = document.getElementById('qvAdd');
  const qvBuy = document.getElementById('qvBuy');

  if (qvAdd) qvAdd.addEventListener('click', async () => {
    // Get fresh reference to quantity input
    const qtyInputEl = document.getElementById('qvQty');
    const qty = qtyInputEl ? parseInt(qtyInputEl.value || '1', 10) : 1;
    try {
      await apiAddToCart(currentItemId, Math.max(1, qty));
      window.location.href = 'cart.php';
    } catch (e) {
      alert(String(e.message || e));
    }
  });

  if (qvBuy) qvBuy.addEventListener('click', async () => {
    // Get fresh reference to quantity input
    const qtyInputEl = document.getElementById('qvQty');
    const qty = qtyInputEl ? parseInt(qtyInputEl.value || '1', 10) : 1;
    try {
      await apiAddToCart(currentItemId, Math.max(1, qty));
      window.location.href = 'checkout.php';
    } catch (e) {
      alert(String(e.message || e));
    }
  });

});
</script>


<?php include(__DIR__ . "/../../includes/footer.php"); ?>

