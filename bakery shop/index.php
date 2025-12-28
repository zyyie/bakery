<?php
session_start();
include("connect.php");
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
<div class="container welcome-section">
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
        About Us
      </a>
    </div>
  </div>
</div>

<!-- Featured Products -->
<div class="container my-5 py-5">
  <h2 class="text-center mb-5" style="color: #333; font-weight: 600;">Featured Products</h2>
  <div class="row">
    <?php
    $query = "SELECT * FROM items WHERE status = 'Active' LIMIT 4";
    $result = executeQuery($query);
    while($row = mysqli_fetch_assoc($result)):
    ?>
    <div class="col-md-3 mb-4">
      <div class="card product-card shadow border-0" style="overflow: hidden;">
        <img src="<?php echo $row['itemImage'] ? 'uploads/'.$row['itemImage'] : 'https://via.placeholder.com/300x200'; ?>" 
             class="card-img-top" alt="<?php echo $row['packageName']; ?>" style="height: 250px; object-fit: cover; transition: transform 0.3s;">
        <div class="card-body">
          <h5 class="card-title fw-bold"><?php echo $row['packageName']; ?></h5>
          <p class="card-text text-muted small"><?php echo substr($row['foodDescription'], 0, 60); ?>...</p>
          <p class="h5 text-warning fw-bold mb-3">₱<?php echo $row['price']; ?></p>
          <a href="products.php" class="btn btn-warning w-100">View Details</a>
        </div>
      </div>
    </div>
    <?php endwhile; ?>
  </div>
</div>

<!-- Categories -->
<div class="container my-5">
  <div class="row">
    <div class="col-md-4 mb-4">
      <div class="card shadow">
        <div class="card-body text-center">
          <i class="fas fa-birthday-cake fa-3x text-warning mb-3"></i>
          <h4>Cakes</h4>
          <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque rhoncus diam et dui cursus facilisis.</p>
          <a href="products.php" class="btn btn-warning">READ MORE</a>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card shadow">
        <div class="card-body text-center">
          <i class="fas fa-cookie fa-3x text-warning mb-3"></i>
          <h4>Cupcakes</h4>
          <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque rhoncus diam et dui cursus facilisis.</p>
          <a href="products.php" class="btn btn-warning">READ MORE</a>
        </div>
      </div>
    </div>
    <div class="col-md-4 mb-4">
      <div class="card shadow">
        <div class="card-body text-center">
          <i class="fas fa-cookie-bite fa-3x text-warning mb-3"></i>
          <h4>Pies</h4>
          <p class="text-muted">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Pellentesque rhoncus diam et dui cursus facilisis.</p>
          <a href="products.php" class="btn btn-warning">READ MORE</a>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

