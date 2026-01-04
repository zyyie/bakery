<?php
require_once __DIR__ . '/includes/bootstrap.php';
include("includes/header.php");
?>

<!-- Hero Section Slideshow -->
<div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="2500" data-bs-pause="false">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
  </div>
  <div class="carousel-inner">
    <div class="carousel-item active">
      <div class="carousel-bg carousel-bg-1">
        <div class="text-white carousel-content">
          <h1 class="hero-text">Welcome To<br><span>KARNEEK Bakery</span></h1>
          <a href="contact.php" class="btn-contact">
            <i class="fas fa-phone-alt"></i> Contact Us Now
          </a>
        </div>
      </div>
    </div>
    <div class="carousel-item">
      <div class="carousel-bg carousel-bg-2">
        <div class="text-white carousel-content">
          <h1 class="hero-text">Welcome To<br><span>KARNEEK Bakery</span></h1>
          <a href="contact.php" class="btn-contact">
            <i class="fas fa-phone-alt"></i> Contact Us Now
          </a>
        </div>
      </div>
    </div>
    <div class="carousel-item">
      <div class="carousel-bg carousel-bg-3">
        <div class="text-white carousel-content">
          <h1 class="hero-text">Welcome To<br><span>KARNEEK Bakery</span></h1>
          <a href="contact.php" class="btn-contact">
            <i class="fas fa-phone-alt"></i> Contact Us Now
          </a>
        </div>
      </div>
    </div>
    <div class="carousel-item">
      <div class="carousel-bg carousel-bg-4">
        <div class="text-white carousel-content">
          <h1 class="hero-text">Welcome To<br><span>KARNEEK Bakery</span></h1>
          <a href="contact.php" class="btn-contact">
            <i class="fas fa-phone-alt"></i> Contact Us Now
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
<div class="container-fluid d-flex justify-content-center px-0">
  <div class="welcome-section w-100">
  <div class="row align-items-center">
    <div class="col-md-6 mb-4 mb-md-0">
      <div class="welcome-image-wrapper">
        <img src="https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600" class="img-fluid" alt="Baked Goods">
      </div>
    </div>
    <div class="col-md-6 ps-md-5">
      <h2 class="section-title">Welcome to KARNEEK Bakery</h2>
      <div class="section-divider"></div>
      <div class="welcome-text">
        <p>At Karneek Bakery, we believe premium baking is an art. Every loaf, pastry, and dessert we create is crafted with the finest ingredients, time-honored techniques, and an uncompromising commitment to quality.</p>
        <p>Our bakery is dedicated to delivering premium goods that delight the senses—from rich flavors and delicate textures to beautiful presentation. We source high-quality ingredients and bake with care to ensure freshness, consistency, and excellence in every bite.</p>
        <p>Whether it's a simple indulgence or a special celebration, Karneek Bakery delivers exceptional taste crafted to impress. Thank you for choosing us—we're honored to elevate your everyday and your most memorable moments.</p>
      </div>
      <a href="about.php" class="btn-about">
          <span class="btn-dot"></span>About Us
      </a>
      </div>
    </div>
  </div>
</div>

<!-- Featured Products -->
<div class="container my-5 py-5">
  <h2 class="text-center mb-5" style="color: #333; font-weight: 600;">Featured Products</h2>
  <div class="row">
    <?php
    // Load product images mapping from JSON file
    $imagesMap = [];
    $imagesJsonPath = __DIR__ . '/product-images.json';
    if (file_exists($imagesJsonPath)) {
      $imagesJson = file_get_contents($imagesJsonPath);
      $imagesMap = json_decode($imagesJson, true) ?: [];
    }
    
    // Get one product from each category
    $categoriesQuery = "SELECT categoryID FROM categories ORDER BY categoryID";
    $categoriesResult = executeQuery($categoriesQuery);
    $featuredProducts = [];
    
    while($cat = mysqli_fetch_assoc($categoriesResult)):
      $catId = $cat['categoryID'];
      $productQuery = "SELECT i.*, c.categoryName 
                       FROM items i
                       INNER JOIN categories c ON i.categoryID = c.categoryID
                       WHERE i.status = 'Active' AND i.categoryID = ?
                       LIMIT 1";
      $productResult = executePreparedQuery($productQuery, "i", [$catId]);
      if($productResult && ($product = mysqli_fetch_assoc($productResult))):
        $featuredProducts[] = $product;
      endif;
    endwhile;
    
    // Estimated shipping / delivery dates (vary based on user's location)
    $shipDate = new DateTime(); // Ship out today
    $estimateStart = (clone $shipDate)->modify('+3 days');
    $estimateEnd   = (clone $shipDate)->modify('+7 days');

    if(isset($_SESSION['userID'])) {
      $homeUserID = intval($_SESSION['userID']);
      $homeUserQuery = "SELECT city, zipcode FROM users WHERE userID = ?";
      $homeUserResult = executePreparedQuery($homeUserQuery, "i", [$homeUserID]);
      if($homeUserResult && ($homeUser = mysqli_fetch_assoc($homeUserResult))) {
        $userCity = strtolower(trim($homeUser['city'] ?? ''));
        $userZip  = trim($homeUser['zipcode'] ?? '');

        // Assume store is in Alaminos, Laguna 4001 (near area)
        $isSameCity = ($userCity === 'alaminos' || $userCity === 'laguna') && $userZip === '4001';
        $isSameProvince = (strpos($userCity, 'laguna') !== false || strpos($userCity, 'batangas') !== false);

        if($isSameCity) {
          // Very near: 1–3 days
          $estimateStart = (clone $shipDate)->modify('+1 day');
          $estimateEnd   = (clone $shipDate)->modify('+3 days');
        } elseif($isSameProvince) {
          // Same province / nearby: 2–5 days
          $estimateStart = (clone $shipDate)->modify('+2 days');
          $estimateEnd   = (clone $shipDate)->modify('+5 days');
        } else {
          // Far provinces: 4–9 days
          $estimateStart = (clone $shipDate)->modify('+4 days');
          $estimateEnd   = (clone $shipDate)->modify('+9 days');
        }
      }
    }

    foreach($featuredProducts as $row):
      // Get image from JSON mapping, fallback to database, then placeholder
      $productImage = 'https://via.placeholder.com/300x200';
      $packageName = $row['packageName'];
      
      if (isset($imagesMap[$packageName])) {
        $productImage = $imagesMap[$packageName];
      } elseif (!empty($row['itemImage'])) {
        $productImage = 'bakery bread image/' . $row['itemImage'];
      }
      
      // Resolve the actual image path
      $productImage = resolveImagePath($productImage);
    ?>
    <div class="col-md-3 mb-4">
      <a href="products.php?product=<?php echo (int)$row['itemID']; ?>" style="text-decoration: none; color: inherit; display: block;">
        <div class="card product-card shadow border-0" style="overflow: hidden; cursor: pointer; height: 100%;">
          <img src="<?php echo imageUrl($productImage); ?>" 
               class="card-img-top" alt="<?php echo e($row['packageName']); ?>" 
               style="height: 250px; object-fit: cover; transition: transform 0.3s;"
               onmouseover="this.style.transform='scale(1.05)'"
               onmouseout="this.style.transform='scale(1)'">
          <div class="card-body">
            <h5 class="card-title fw-bold"><?php echo $row['packageName']; ?></h5>
            <p class="card-text text-muted small"><?php echo substr($row['foodDescription'], 0, 60); ?>...</p>
            <p class="h5 text-warning fw-bold mb-1">₱<?php echo $row['price']; ?></p>
            <p class="text-muted small mb-0">
              <i class="fas fa-shipping-fast me-1"></i>
              Ships out <strong><?php echo $shipDate->format('M d'); ?></strong>,
              Est. delivery <strong><?php echo $estimateStart->format('M d'); ?></strong>
              - <strong><?php echo $estimateEnd->format('M d'); ?></strong>
            </p>
          </div>
        </div>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Separator Line -->
<div class="container">
  <hr class="my-5" style="border-top: 2px solid #dee2e6;">
</div>

<!-- Categories -->
<div class="container my-5">
  <div class="row">
    <?php
    // Define exact category order as specified
    $categoryOrder = [
      'Bread–Cake Combo',
      'Brownies',
      'Buns & Rolls',
      'Classic & Basic Bread',
      'Cookies',
      'Crinkles',
      'Filled / Stuffed Bread',
      'Special (Budget-Friendly)',
      'Sweet Bread'
    ];
    
    // Get all categories from database
    $categoriesQuery = "SELECT * FROM categories";
    $categoriesResult = executeQuery($categoriesQuery);
    
    // Map category names to Font Awesome icons - specific icons for each category
    $categoryIcons = [
      'Classic & Basic Bread' => 'fa-bread-slice',
      'Sweet Bread' => 'fa-cookie',
      'Filled / Stuffed Bread' => 'fa-layer-group',
      'Filled - Stuffed Bread' => 'fa-layer-group',
      'Buns & Rolls' => 'fa-circle',
      'Bread–Cake Combo' => 'fa-birthday-cake',
      'Special (Budget-Friendly)' => 'fa-tag',
      'Cookies' => 'fa-cookie-bite',
      'Crinkles' => 'fa-cookie-bite',
      'Brownies' => 'fa-square'
    ];
    
    // Map category names to descriptions
    $categoryDescriptions = [
      'Bread–Cake Combo' => 'Perfect combination of bread and cake in one delightful treat. Mini slices and combos for every occasion.',
      'Brownies' => 'Rich, fudgy chocolate brownies with various flavors. Dense, moist, and irresistibly delicious.',
      'Buns & Rolls' => 'Soft, fluffy buns and dinner rolls perfect for any meal. Freshly baked and always satisfying.',
      'Classic & Basic Bread' => 'Traditional Filipino bread favorites like pandesal. Simple, timeless, and always fresh from the oven.',
      'Cookies' => 'Crispy, chewy cookies in assorted flavors. Perfect for snacking or sharing with loved ones.',
      'Crinkles' => 'Soft, crinkled cookies with powdered sugar coating. Available in various flavors like chocolate, ube, and matcha.',
      'Filled / Stuffed Bread' => 'Bread filled with savory or sweet fillings. From ham and cheese to tuna, every bite is a surprise.',
      'Special (Budget-Friendly)' => 'Affordable special treats that don\'t compromise on quality. Great value for your money.',
      'Sweet Bread' => 'Sweetened bread varieties with delightful fillings and toppings. Perfect for dessert or merienda time.'
    ];
    
    // Build categories map
    $categoriesMap = [];
    if($categoriesResult && mysqli_num_rows($categoriesResult) > 0):
      while($cat = mysqli_fetch_assoc($categoriesResult)):
        $categoriesMap[$cat['categoryName']] = $cat;
      endwhile;
    endif;
    
    // Display categories in the specified order
    foreach ($categoryOrder as $categoryName):
      if (isset($categoriesMap[$categoryName])):
        $cat = $categoriesMap[$categoryName];
        // Get icon for category, default to bread icon if not found
        $iconClass = isset($categoryIcons[$categoryName]) ? $categoryIcons[$categoryName] : 'fa-bread-slice';
        // Get description for category, default to generic description if not found
        $categoryDescription = isset($categoryDescriptions[$categoryName]) ? $categoryDescriptions[$categoryName] : 'Discover our premium selection of freshly baked goods.';
    ?>
    <div class="col-md-3 mb-3">
      <div class="card shadow-sm category-card h-100">
        <div class="card-body text-center p-3 d-flex flex-column">
          <div class="mb-2">
            <i class="fas <?php echo e($iconClass); ?> fa-lg text-warning mb-2"></i>
          </div>
          <h6 class="mb-2 fw-bold" style="min-height: 2.5rem; display: flex; align-items: center; justify-content: center;"><?php echo e($categoryName); ?></h6>
          <p class="text-muted small mb-3 flex-grow-1" style="font-size: 0.75rem; line-height: 1.4;"><?php echo e($categoryDescription); ?></p>
          <div class="mt-auto">
            <a href="products.php?category=<?php echo (int)$cat['categoryID']; ?>" class="btn btn-warning btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.75rem;">READ MORE</a>
          </div>
        </div>
      </div>
    </div>
    <?php 
      endif;
    endforeach;
    ?>
  </div>
</div>

<?php include("includes/footer.php"); ?>


