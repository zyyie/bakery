<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
require_once __DIR__ . '/../../includes/Email.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if(isset($_POST['email'])){
  $email = trim($_POST['email']);
  
  // Input validation
  if(empty($email)) {
    $response['message'] = 'Please enter your email address.';
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Please enter a valid email address.';
  } else {
    // Check if already subscribed
    $checkQuery = "SELECT * FROM subscribers WHERE email = ?";
    $checkResult = executePreparedQuery($checkQuery, "s", [$email]);
    
    if($checkResult && mysqli_num_rows($checkResult) > 0){
      $response['success'] = true;
      $response['message'] = 'You are already subscribed to our newsletter!';
    } else {
      // Add new subscriber
      $query = "INSERT INTO subscribers (email) VALUES (?)";
      $inserted = executePreparedUpdate($query, "s", [$email]);

      if($inserted !== false){
        // Send welcome email
        try {
          $mailer = new Email();
          $emailSent = $mailer->sendNewsletterWelcome($email);
          
          if($emailSent) {
            $response['success'] = true;
            $response['message'] = 'Successfully subscribed! Check your email for confirmation.';
          } else {
            $response['success'] = true;
            $response['message'] = 'Successfully subscribed! Welcome email will be sent shortly.';
          }
        } catch (Exception $e) {
          $response['success'] = true;
          $response['message'] = 'Successfully subscribed! Email service temporarily unavailable.';
        }
      } else {
        $response['message'] = 'Failed to subscribe. Please try again.';
      }
    }
  }
} else {
  $response['message'] = 'No email provided.';
}

echo json_encode($response);
?>
