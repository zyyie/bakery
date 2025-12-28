<?php
require_once __DIR__ . '/includes/bootstrap.php';

$query = "SELECT * FROM pages WHERE pageType = ?";
$result = executePreparedQuery($query, "s", ['aboutus']);
$page = mysqli_fetch_assoc($result);

include("includes/header.php");
?>


<div class="about-page">
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card">
          <div class="card-body">
            <h2><?php echo e($page['pageTitle']); ?></h2>
            <hr>
            <div class="about-content">
              <?php echo nl2br(e($page['pageDescription'])); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

