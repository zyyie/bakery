<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

$query = "SELECT * FROM pages WHERE pageType = ?";
$result = executePreparedQuery($query, "s", ['aboutus']);
$page = mysqli_fetch_assoc($result);

include(__DIR__ . "/../../includes/header.php");
?>


<div class="about-page">
  <div class="container my-5">
    <div class="mb-3">
      <a href="index.php" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-arrow-left me-2"></i>Back to Home
      </a>
    </div>
    
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card shadow about-card">
          <div class="card-body p-5">
            <div class="text-center mb-5">
              <h1 class="display-4 fw-bold text-brown">About KARNEEK Bakery</h1>
              <div class="divider bg-brown mx-auto"></div>
              <p class="lead text-muted mt-3">3rd Year IT Project | Polytechnic University of the Philippines - Sto. Tomas Campus</p>
            </div>
            <div class="about-content">
              <p>KARNEEK Bakery is a 3rd year IT project from the Polytechnic University of the Philippines - Sto. Tomas Campus. This e-commerce platform showcases Filipino innovation in web development, combining traditional bakery business with modern technology. Our project demonstrates practical skills in PHP programming, database management, and user interface design while serving as a fully functional online bakery system.</p>
              <?php 
              // Display database content but filter out the Seattle text
              $dbContent = e($page['pageDescription']);
              $seattleText = "We are known as the best catering company in Seattle for good reason. Our dedication and commitment to quality and sustainability has earned us a loyal following among our clientele, one that continues to grow based on enthusiastic referrals. For nearly two decades, we have bridged the gap between the land, the sea, and your table. We leverage the best ingredients Washington has to offer, preparing them mindfully and always from scratch.";
              
              $cleanContent = str_replace($seattleText, "", $dbContent);
              if (!empty(trim($cleanContent))) {
                  echo nl2br($cleanContent);
              }
              ?>
            </div>
            <div class="row mt-5">
              <div class="col-md-4 text-center mb-4">
                <div class="feature-box">
                  <div class="feature-icon mb-3">
                    <i class="fas fa-leaf fa-3x text-brown"></i>
                  </div>
                  <h4>Locally Sourced</h4>
                  <p class="text-muted">Supporting Filipino farmers by using premium local ingredients.</p>
                </div>
              </div>
              <div class="col-md-4 text-center mb-4">
                <div class="feature-box">
                  <div class="feature-icon mb-3">
                    <i class="fas fa-graduation-cap fa-3x text-brown"></i>
                  </div>
                  <h4>Student Innovation</h4>
                  <p class="text-muted">A 3rd year IT project showcasing Filipino talent in web development.</p>
                </div>
              </div>
              <div class="col-md-4 text-center mb-4">
                <div class="feature-box">
                  <div class="feature-icon mb-3">
                    <i class="fas fa-heart fa-3x text-brown"></i>
                  </div>
                  <h4>Baked with Passion</h4>
                  <p class="text-muted">Combining traditional Filipino baking with modern innovation.</p>
                </div>
              </div>
            </div>
            <div class="row mt-5">
              <div class="col-md-6">
                <div class="story-section">
                  <h3 class="text-brown mb-3">Our Story</h3>
                  <p>Born from a 3rd year IT project at the Polytechnic University of the Philippines - Sto. Tomas Campus, KARNEEK Bakery represents the fusion of Filipino entrepreneurship and technological innovation. This project demonstrates our team's ability to create a fully functional e-commerce platform with inventory management, payment processing, and customer service features.</p>
                  <div class="mt-3">
                    <span class="badge bg-brown me-2">PHP</span>
                    <span class="badge bg-brown me-2">MySQL</span>
                    <span class="badge bg-brown me-2">Bootstrap</span>
                    <span class="badge bg-brown">PayPal</span>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="values-section">
                  <h3 class="text-brown mb-3">Project Values</h3>
                  <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-check text-brown me-2"></i> Filipino innovation</li>
                    <li class="mb-2"><i class="fas fa-check text-brown me-2"></i> Practical e-commerce</li>
                    <li class="mb-2"><i class="fas fa-check text-brown me-2"></i> User-centered design</li>
                    <li class="mb-2"><i class="fas fa-check text-brown me-2"></i> Secure payments</li>
                    <li class="mb-2"><i class="fas fa-check text-brown me-2"></i> Mobile-responsive</li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include(__DIR__ . "/../../includes/footer.php"); ?>

