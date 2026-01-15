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
  <div class="mb-3">
    <a href="products.php<?php echo isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'products.php') !== false ? '' : ''; ?>" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left me-2"></i>Back to Products
    </a>
  </div>
  
  <div class="row">
    <div class="col-md-6 mb-4">
      <div class="position-relative" style="border-radius: 12px; overflow: hidden; background: #f8f9fa;">
        <img id="productMainImg" src="<?php echo product_image_url($item, 1); ?>" 
             class="img-fluid rounded shadow" alt="<?php echo $item['packageName']; ?>" 
             style="width: 100%; max-height: 600px; object-fit: contain; cursor: zoom-in; display: block;">
      </div>
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

// Product image lightbox
document.addEventListener('DOMContentLoaded', function() {
  const itemID = <?php echo $item['itemID']; ?>;
  let productImages = [];
  let currentProductImageIdx = 0;
  
  // Initialize lightbox if not already initialized
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
    window.openBakeryLightbox = openLightbox;
  }
  
  // Load product images
  fetch(`/bakery/backend/api/restful/item-details.php?id=${itemID}`)
    .then(res => res.json())
    .then(data => {
      productImages = data.images || [data.imageUrl || '<?php echo product_image_url($item, 1); ?>'];
      currentProductImageIdx = 0;
    })
    .catch(err => {
      console.error('Error loading product images:', err);
      productImages = ['<?php echo product_image_url($item, 1); ?>'];
    });
  
  // Click image to open in lightbox
  const mainImg = document.getElementById('productMainImg');
  if (mainImg) {
    mainImg.addEventListener('click', () => {
      if (window.openBakeryLightbox) {
        window.openBakeryLightbox(productImages, currentProductImageIdx);
      }
    });
  }
});
</script>


<?php include(__DIR__ . "/../../includes/footer.php"); ?>
