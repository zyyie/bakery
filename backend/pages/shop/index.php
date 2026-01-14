<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
include(__DIR__ . "/../../includes/header.php");
?>

<!-- Hero Section Slideshow -->
<div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="4000" data-bs-pause="false">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <div class="carousel-image-wrapper">
        <img src="../frontend/images/tinapay 1.jpg" class="d-block w-100" alt="Fresh Baked Goods">
        <div class="carousel-overlay"></div>
      </div>
      <div class="carousel-caption d-flex flex-column justify-content-center align-items-center">
        <div class="carousel-content text-center">
          <h1 class="hero-text animate-fade-in">Welcome To<br><span>KARNEEK Bakery</span></h1>
          <p class="hero-subtitle animate-fade-in-delay">Premium Baked Goods Made with Love</p>
          <a href="contact.php" class="btn-contact mt-4 animate-fade-in-delay-2">
            <i class="fas fa-phone-alt"></i> Contact Us Now
          </a>
        </div>
      </div>
    </div>
    <div class="carousel-item">
      <div class="carousel-image-wrapper">
        <img src="../frontend/images/tinapay 2.jpg" class="d-block w-100" alt="Fresh Baked Goods">
        <div class="carousel-overlay"></div>
      </div>
      <div class="carousel-caption d-flex flex-column justify-content-center align-items-center">
        <div class="carousel-content text-center">
          <h1 class="hero-text animate-fade-in">Artisan Quality<br><span>Every Single Day</span></h1>
          <p class="hero-subtitle animate-fade-in-delay">Handcrafted with Traditional Methods</p>
          <a href="products.php" class="btn-contact mt-4 animate-fade-in-delay-2">
            <i class="fas fa-shopping-bag"></i> Shop Now
          </a>
        </div>
      </div>
    </div>
    <div class="carousel-item">
      <div class="carousel-image-wrapper">
        <img src="../frontend/images/tinapay 3.jpg" class="d-block w-100" alt="Fresh Baked Goods">
        <div class="carousel-overlay"></div>
      </div>
      <div class="carousel-caption d-flex flex-column justify-content-center align-items-center">
        <div class="carousel-content text-center">
          <h1 class="hero-text animate-fade-in">Fresh Ingredients<br><span>Premium Taste</span></h1>
          <p class="hero-subtitle animate-fade-in-delay">Sourced Daily for Maximum Freshness</p>
          <a href="products.php" class="btn-contact mt-4 animate-fade-in-delay-2">
            <i class="fas fa-utensils"></i> Explore Products
          </a>
        </div>
      </div>
    </div>
    <div class="carousel-item">
      <div class="carousel-image-wrapper">
        <img src="../frontend/images/tinapay 4.jpg" class="d-block w-100" alt="Fresh Baked Goods">
        <div class="carousel-overlay"></div>
      </div>
      <div class="carousel-caption d-flex flex-column justify-content-center align-items-center">
        <div class="carousel-content text-center">
          <h1 class="hero-text animate-fade-in">Your Celebration<br><span>Our Passion</span></h1>
          <p class="hero-subtitle animate-fade-in-delay">Making Every Moment Special</p>
          <a href="contact.php" class="btn-contact mt-4 animate-fade-in-delay-2">
            <i class="fas fa-calendar-alt"></i> Order Today
          </a>
        </div>
      </div>
    </div>
  </div>
  <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
</div>

<!-- Welcome Section -->
<section class="welcome-section py-5 my-5">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <div class="welcome-image-wrapper position-relative">
          <img src="../frontend/images/tinapay 1.jpg" class="img-fluid rounded-4 shadow-lg" alt="KARNEEK Bakery">
          <div class="image-badge">
            <i class="fas fa-award"></i>
            <span>Premium Quality</span>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="welcome-content ps-lg-5">
          <span class="section-label">About Us</span>
          <h2 class="section-title mb-4">Welcome to KARNEEK Bakery</h2>
          <div class="section-divider mb-4"></div>
          <div class="welcome-text mb-5">
            <p class="lead mb-4">At KARNEEK Bakery, we believe premium baking is an art. Every loaf, pastry, and dessert we create is crafted with the finest ingredients, time-honored techniques, and an uncompromising commitment to quality.</p>
            <p class="mb-3">Our bakery is dedicated to delivering premium goods that delight the senses—from rich flavors and delicate textures to beautiful presentation. We source high-quality ingredients and bake with care to ensure freshness, consistency, and excellence in every bite.</p>
            <p class="mb-0">Whether it's a simple indulgence or a special celebration, KARNEEK Bakery delivers exceptional taste crafted to impress. Thank you for choosing us—we're honored to elevate your everyday and your most memorable moments.</p>
          </div>
          <div class="welcome-features mb-5">
            <div class="row g-3">
              <div class="col-6">
                <div class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <span>Fresh Daily</span>
                </div>
              </div>
              <div class="col-6">
                <div class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <span>Premium Ingredients</span>
                </div>
              </div>
              <div class="col-6">
                <div class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <span>Artisan Crafted</span>
                </div>
              </div>
              <div class="col-6">
                <div class="feature-item">
                  <i class="fas fa-check-circle"></i>
                  <span>Quality Guaranteed</span>
                </div>
              </div>
            </div>
          </div>
          <a href="contact.php" class="btn-about">
            <i class="fas fa-info-circle me-2"></i>Learn More About Us
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Featured Products -->
<section class="featured-products py-5 my-5">
  <div class="container">
    <div class="text-center mb-5">
      <span class="section-label">Our Products</span>
      <h2 class="section-title mb-3">Featured Products</h2>
      <p class="section-subtitle">Discover our handpicked selection of premium baked goods</p>
      <div class="section-divider mx-auto mb-5"></div>
    </div>
    <div class="row g-4 mb-5">
      <?php
      // Show one active product per category
      $query = "
        SELECT i.*
        FROM items i
        INNER JOIN (
          SELECT categoryID, MIN(itemID) AS itemID
          FROM items
          WHERE status = 'Active'
          GROUP BY categoryID
        ) pick ON pick.itemID = i.itemID
        WHERE i.status = 'Active'
        ORDER BY i.categoryID
      ";
      $result = executeQuery($query);
      while($row = mysqli_fetch_assoc($result)):
      ?>
      <div class="col-lg-3 col-md-6">
        <div class="card product-card h-100 shadow-sm border-0">
          <div class="product-image-wrapper js-quickview" data-itemid="<?php echo (int)$row['itemID']; ?>" style="cursor: pointer;">
            <img src="<?php echo product_image_url($row, 1); ?>" 
                 class="card-img-top" alt="<?php echo htmlspecialchars($row['packageName']); ?>">
          </div>
          <div class="card-body d-flex flex-column p-4">
            <h5 class="card-title fw-bold mb-3"><?php echo htmlspecialchars($row['packageName']); ?></h5>
            <p class="card-text text-muted small flex-grow-1 mb-3"><?php echo htmlspecialchars(substr($row['foodDescription'], 0, 80)); ?><?php echo strlen($row['foodDescription']) > 80 ? '...' : ''; ?></p>
            <div class="d-flex justify-content-between align-items-center mt-auto pt-3 border-top">
              <p class="h5 text-brown fw-bold mb-0">₱<?php echo number_format($row['price'], 2); ?></p>
            </div>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <div class="text-center mt-5">
      <a href="products.php" class="btn btn-brown btn-lg">
        <i class="fas fa-th me-2"></i>View All Products
      </a>
    </div>
  </div>
</section>

<!-- Categories Section -->
<section class="categories-section py-5 my-5">
  <div class="container">
    <style>
      /* Scoped size tweaks for category icons on homepage */
      .categories-section .category-icon { width: 64px; height: 64px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; margin: 0 auto 12px; }
      .categories-section .category-icon i { font-size: 26px; }
    </style>
    <div class="text-center mb-5">
      <span class="section-label">Our Categories</span>
      <h2 class="section-title mb-3">What We Offer</h2>
      <p class="section-subtitle">Explore our wide range of delicious baked goods</p>
      <div class="section-divider mx-auto mb-5"></div>
    </div>
    <div class="row g-4">
      <?php
      // Show all categories and map a specific icon for each by name
      $catResult = executePreparedQuery("SELECT * FROM categories", "", []);
      $iconMap = [
        // breads (distinct icons per category)
        'classic & basic bread' => 'fa-bread-slice',
        'sweet bread' => 'fa-cake-candles',
        'filled / stuffed bread' => 'fa-stroopwafel',
        'buns & rolls' => 'fa-hamburger',
        'bread–cake combo' => 'fa-cake-candles',
        'bread-cake combo' => 'fa-cake-candles', // fallback for hyphen variant
        'special (budget-friendly)' => 'fa-star',
        // sweets
        'cookies' => 'fa-cookie-bite',
        'crinkles' => 'fa-cookie',
        'brownies' => 'fa-square'
      ];
      while($catResult && ($cat = mysqli_fetch_assoc($catResult))):
        $key = strtolower(trim($cat['categoryName']));
        $icon = isset($iconMap[$key]) ? $iconMap[$key] : 'fa-bread-slice';
      ?>
      <div class="col-lg-4 col-md-6">
        <div class="category-card text-center h-100">
          <div class="category-icon">
            <i class="fas <?php echo $icon; ?>"></i>
          </div>
          <h4 class="category-title"><?php echo e($cat['categoryName']); ?></h4>
          <p class="category-description">Discover our delicious <?php echo strtolower(e($cat['categoryName'])); ?> made with premium ingredients and traditional methods.</p>
          <a href="products.php?category=<?php echo $cat['categoryID']; ?>" class="btn btn-outline-brown">
            Explore <?php echo e($cat['categoryName']); ?> <i class="fas fa-arrow-right ms-2"></i>
          </a>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <div class="text-center mt-5">
      <a href="products.php" class="btn btn-brown btn-lg">
        <i class="fas fa-th me-2"></i>View All Categories
      </a>
    </div>
  </div>
</section>

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
// Toggle favorite function for homepage
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

  if (qvQtyPresets) {
    qvQtyPresets.innerHTML = presets.map(n => `<button type="button" class="btn btn-outline-brown btn-sm qv-preset" data-n="${n}">${n} pcs</button>`).join('');
    qvQtyPresets.addEventListener('click', (e) => {
      const t = e.target;
      if (!(t instanceof HTMLElement)) return;
      const btn = t.closest('.qv-preset');
      if (!btn) return;
      const n = parseInt(btn.getAttribute('data-n') || '1', 10);
      if (qvQtyInput) qvQtyInput.value = String(Math.max(1, n));
    });
  }

  if (qvMinus) qvMinus.addEventListener('click', () => {
    const v = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
    if (qvQtyInput) qvQtyInput.value = String(Math.max(1, v - 1));
  });
  if (qvPlus) qvPlus.addEventListener('click', () => {
    const v = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
    if (qvQtyInput) qvQtyInput.value = String(Math.max(1, v + 1));
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

  // Minimal image lightbox (initialized once)
  if (!window._bakeryLightboxInit) {
    window._bakeryLightboxInit = true;
    const style = document.createElement('style');
    style.textContent = `
      .lb-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);display:none;align-items:center;justify-content:center;z-index:2000}
      .lb-overlay.is-open{display:flex}
      .lb-content{position:relative;max-width:90vw;max-height:90vh}
      .lb-img{max-width:90vw;max-height:90vh;object-fit:contain;border-radius:6px;box-shadow:0 10px 30px rgba(0,0,0,.5)}
      .lb-close,.lb-prev,.lb-next{position:absolute;top:50%;transform:translateY(-50%);background:rgba(255,255,255,.2);color:#fff;border:none;border-radius:50%;width:48px;height:48px;display:flex;align-items:center;justify-content:center;cursor:pointer}
      .lb-close{top:12px;right:12px;transform:none}
      .lb-prev{left:12px}
      .lb-next{right:12px}
      @media (max-width: 768px){.lb-prev{left:8px}.lb-next{right:8px}}
    `;
    document.head.appendChild(style);

    const overlay = document.createElement('div');
    overlay.className = 'lb-overlay';
    overlay.innerHTML = `
      <div class="lb-content">
        <button type="button" class="lb-close" aria-label="Close">✕</button>
        <button type="button" class="lb-prev" aria-label="Previous">❮</button>
        <img class="lb-img" alt="" />
        <button type="button" class="lb-next" aria-label="Next">❯</button>
      </div>`;
    document.body.appendChild(overlay);

    let imgs = [];
    let idx = 0;
    function setSrc(i){
      idx = (i+imgs.length)%imgs.length;
      const img = overlay.querySelector('.lb-img');
      if (img) img.src = imgs[idx] || '';
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
    overlay.querySelector('.lb-prev')?.addEventListener('click', () => setSrc(idx-1));
    overlay.querySelector('.lb-next')?.addEventListener('click', () => setSrc(idx+1));
    overlay.addEventListener('click', (e)=>{ if (e.target === overlay) close(); });

    // expose
    window.openBakeryLightbox = openLightbox;
  }

  document.querySelectorAll('.js-quickview').forEach(btn => {
    btn.addEventListener('click', async () => {
      const itemId = parseInt(btn.getAttribute('data-itemid') || '0', 10);
      if (!itemId || !qvModal) return;
      // Capture the image src that was clicked (if the trigger contains an <img>)
      const clickedImgEl = btn.querySelector('img');
      const desiredSrc = clickedImgEl ? clickedImgEl.src : '';

      currentItemId = itemId;
      if (qvQtyInput) qvQtyInput.value = '1';
      setPackageType('box');

      // Show loading state
      const qvBody = document.querySelector('#quickViewModal .modal-body');
      const originalContent = qvBody ? qvBody.innerHTML : '';
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
        // Restore modal body HTML from the original template
        if (qvBody) {
          qvBody.innerHTML = `
            <div class="row g-3">
              <div class="col-md-6">
                <div class="mb-2" style="border-radius: 12px; overflow: hidden;">
                  <img id="qvMainImg" src="" alt="" style="width:100%; height: 300px; object-fit: cover;">
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
        if (priceEl) priceEl.textContent = `₱${item.price || ''}`;
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
        // If the opener had an image, try to start with that image
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
          const v = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
          if (qvQtyInput) qvQtyInput.value = String(Math.max(1, v - 1));
        });
        if (qvPlusNew) qvPlusNew.addEventListener('click', () => {
          const v = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
          if (qvQtyInput) qvQtyInput.value = String(Math.max(1, v + 1));
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
          const qty = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
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
          const qty = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
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
          qvBody.innerHTML = `<div class="alert alert-danger">Failed to load product details. Please try again.</div>`;
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
    const qty = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
    try {
      await apiAddToCart(currentItemId, Math.max(1, qty));
      window.location.href = 'cart.php';
    } catch (e) {
      alert(String(e.message || e));
    }
  });

  if (qvBuy) qvBuy.addEventListener('click', async () => {
    const qty = qvQtyInput ? parseInt(qvQtyInput.value || '1', 10) : 1;
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