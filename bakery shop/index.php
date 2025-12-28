<?php
session_start();
include("connect.php");
include("includes/header.php");
?>

<style>
  body {
    background-color: #E3D9CA;
  }
  .welcome-image-wrapper {
    position: relative;
  }
  .welcome-image-wrapper img {
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
  }
</style>

<!-- Hero Section Slideshow -->
<div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="2500" data-bs-pause="false" style="height: 600px; position: relative;">
  <div class="carousel-indicators">
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2"></button>
    <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="3"></button>
  </div>
  <div class="carousel-inner" style="height: 100%;">
    <div class="carousel-item active" style="height: 100%;">
      <div style="height: 100%; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('tinapay 1.jpg') center/cover; display: flex; align-items: center; justify-content: flex-end; padding-right: 100px;">
        <div class="text-white" style="max-width: 500px;">
          <h1 class="display-3 fw-bold mb-3">Welcome To KARNEEK Bakery</h1>
          <a href="contact.php" class="btn btn-warning btn-lg">
            <i class="fas fa-phone me-2"></i> Contact Us
          </a>
        </div>
      </div>
    </div>
    <div class="carousel-item" style="height: 100%;">
      <div style="height: 100%; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('tinapay 2.jpg') center/cover; display: flex; align-items: center; justify-content: flex-end; padding-right: 100px;">
        <div class="text-white" style="max-width: 500px;">
          <h1 class="display-3 fw-bold mb-3">Welcome To KARNEEK Bakery</h1>
          <a href="contact.php" class="btn btn-warning btn-lg">
            <i class="fas fa-phone me-2"></i> Contact Us
          </a>
        </div>
      </div>
    </div>
    <div class="carousel-item" style="height: 100%;">
      <div style="height: 100%; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('tinapay 3.jpg') center/cover; display: flex; align-items: center; justify-content: flex-end; padding-right: 100px;">
        <div class="text-white" style="max-width: 500px;">
          <h1 class="display-3 fw-bold mb-3">Welcome To KARNEEK Bakery</h1>
          <a href="contact.php" class="btn btn-warning btn-lg">
            <i class="fas fa-phone me-2"></i> Contact Us
          </a>
        </div>
      </div>
    </div>
    <div class="carousel-item" style="height: 100%;">
      <div style="height: 100%; background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('tinapay 4.jpg') center/cover; display: flex; align-items: center; justify-content: flex-end; padding-right: 100px;">
        <div class="text-white" style="max-width: 500px;">
          <h1 class="display-3 fw-bold mb-3">Welcome To KARNEEK Bakery</h1>
          <a href="contact.php" class="btn btn-warning btn-lg">
            <i class="fas fa-phone me-2"></i> Contact Us
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
<div class="container my-5 py-5" style="background-color: #CDBA96; padding: 60px 30px !important; border-radius: 25px;">
  <div class="row align-items-center">
    <div class="col-md-6 mb-4 mb-md-0">
      <div class="welcome-image-wrapper">
        <img src="https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=600" class="img-fluid" alt="Baked Goods" style="height: 500px; object-fit: cover; width: 100%; border-radius: 20px;">
      </div>
    </div>
    <div class="col-md-6 ps-md-5">
      <h2 class="mb-3" style="color: #333; font-weight: 600;">Welcome to KARNEEK Bakery</h2>
      <div class="mb-4" style="width: 80px; height: 3px; background-color: #d4a574;"></div>
      <p class="text-muted mb-4" style="line-height: 1.8;">
        Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure. To take a trivial example, which of us ever undertakes laborious physical exercise.
      </p>
      <a href="about.php" class="btn text-white" style="background-color: #8B4513; padding: 12px 30px; border-radius: 5px;">
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

