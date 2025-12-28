<?php
session_start();
include("connect.php");

$query = "SELECT * FROM pages WHERE pageType = 'aboutus'";
$result = executeQuery($query);
$page = mysqli_fetch_assoc($result);

include("includes/header.php");
?>


<div class="container my-5">
  <div class="row">
    <div class="col-md-12">
      <div class="card shadow p-5">
        <h2><?php echo $page['pageTitle']; ?></h2>
        <hr>
        <p><?php echo nl2br($page['pageDescription']); ?></p>
      </div>
    </div>
  </div>
</div>

<?php include("includes/footer.php"); ?>

