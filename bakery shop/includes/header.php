<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bakery - Premium Goods</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .navbar-brand {
      font-family: 'Brush Script MT', cursive;
      font-size: 2rem;
    }
    .top-bar {
      background-color: #f8f9fa;
      padding: 8px 0;
      font-size: 0.9rem;
    }
    .hero-section {
      position: relative;
    }
    #heroCarousel .carousel-fade .carousel-item {
      opacity: 0;
      transition-property: opacity;
      transition-duration: 0.8s;
    }
    #heroCarousel .carousel-fade .carousel-item.active,
    #heroCarousel .carousel-fade .carousel-item-next.carousel-item-start,
    #heroCarousel .carousel-fade .carousel-item-prev.carousel-item-end {
      opacity: 1;
    }
    #heroCarousel .carousel-fade .active.carousel-item-start,
    #heroCarousel .carousel-fade .active.carousel-item-end {
      opacity: 0;
    }
    #heroCarousel .carousel-control-prev,
    #heroCarousel .carousel-control-next {
      width: 5%;
    }
    #heroCarousel .carousel-indicators {
      bottom: 20px;
    }
    #heroCarousel .carousel-indicators button {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      margin: 0 5px;
    }
    .product-card:hover img {
      transform: scale(1.1);
    }
    .footer {
      background-color: #f8f9fa;
      padding: 40px 0 20px;
      margin-top: 50px;
    }
    .product-card {
      transition: all 0.3s;
    }
    .product-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 10px 30px rgba(0,0,0,0.2) !important;
      border-color: #ffc107 !important;
    }
    .product-card:hover .card-img-top {
      transform: scale(1.1);
    }
    .product-card:hover .card-body {
      background-color: #fff8e1;
    }
    .favorite-active {
      color: #dc3545 !important;
    }
    .favorite-active i {
      color: #dc3545 !important;
    }
    .nav-link {
      font-weight: bold !important;
    }
  </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light shadow-sm" style="background-color: #E3D9CA;">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="logo.png" alt="Bakery Logo" style="height: 100px;">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="index.php">HOME</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="products.php">PRODUCTS</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="about.php">ABOUT</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="contact.php">CONTACT</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="admin/login.php">ADMIN LOGIN</a>
        </li>
        <?php if(isset($_SESSION['userID'])): ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
            MY ACCOUNT
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="my-orders.php">My Order</a></li>
            <li><a class="dropdown-item" href="cart.php">My Cart</a></li>
            <li><a class="dropdown-item" href="my-favorites.php">My Favorites</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="logout.php">Logout</a></li>
          </ul>
        </li>
        <?php else: ?>
        <li class="nav-item">
          <a class="nav-link" href="login.php">LOGIN</a>
        </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

