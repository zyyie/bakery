<?php
session_start();
include("connect.php");

$query = "SELECT * FROM pages WHERE pageType = 'aboutus'";
$result = executeQuery($query);
$page = mysqli_fetch_assoc($result);

include("includes/header.php");
?>


<div class="about-page">
  <div class="container my-5">
    <div class="row justify-content-center">
      <div class="col-lg-10">
        <div class="card">
          <div class="card-body">
            <h2><?php echo $page['pageTitle']; ?></h2>
            <hr>
            <div class="about-content">
              <?php echo nl2br($page['pageDescription']); ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

