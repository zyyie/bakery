<?php
require_once __DIR__ . '/includes/bootstrap.php';

$success = "";
$error = "";

if(isset($_POST['name'])){
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $mobileNumber = trim($_POST['mobileNumber']);
  $message = trim($_POST['message']);
  
  // Input validation
  if(empty($name) || empty($email) || empty($message)){
    $error = "Name, email, and message are required!";
  } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    $error = "Invalid email format!";
  } else {
    $query = "INSERT INTO enquiries (name, email, mobileNumber, message) 
              VALUES (?, ?, ?, ?)";
    
    $result = executePreparedUpdate($query, "ssss", [$name, $email, $mobileNumber, $message]);
    
    if($result !== false){
      $success = "Your enquiry has been submitted successfully!";
    } else {
      $error = "Failed to submit enquiry!";
    }
  }
}

$query = "SELECT * FROM pages WHERE pageType = ?";
$result = executePreparedQuery($query, "s", ['contactus']);
$page = mysqli_fetch_assoc($result);

include("includes/header.php");
?>


<div class="container contact-page my-5">
  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow p-4">
        <h3><?php echo e($page['pageTitle']); ?></h3>
        <hr>
        <div class="contact-info">
          <?php echo nl2br(e($page['pageDescription'])); ?>
          <p><i class="fas fa-envelope"></i> <strong>Email:</strong> karneekbakery@gmail.com</p>
          <p><i class="fas fa-phone"></i> <strong>Phone:</strong> 964-9885-950</p>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card shadow p-4">
        <h3>Send Enquiry</h3>
        <?php if($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <?php if($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="" id="contactForm">
          <div class="mb-3">
            <input type="text" name="name" id="name" class="form-control" placeholder="Your Name" required>
            <div class="invalid-feedback"></div>
          </div>
          <div class="mb-3">
            <input type="email" name="email" id="email" class="form-control" placeholder="Your Email" required>
            <div class="invalid-feedback"></div>
          </div>
          <div class="mb-3">
            <input type="tel" name="mobileNumber" id="mobileNumber" class="form-control" placeholder="Your Phone Number" required>
            <div class="invalid-feedback"></div>
          </div>
          <div class="mb-3">
            <textarea name="message" id="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
            <div class="invalid-feedback"></div>
          </div>
          <button type="submit" class="btn btn-send" id="submitBtn">
            <i class="fas fa-paper-plane me-2"></i><span id="submitText">Send Message</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('contactForm');
  const submitBtn = document.getElementById('submitBtn');
  const submitText = document.getElementById('submitText');
  const alertContainer = document.querySelector('.card.shadow.p-4:last-child');
  
  // Pre-fill message if product parameter exists
  const productName = new URLSearchParams(window.location.search).get('product');
  if (productName) {
    document.getElementById('message').value = `Hi! I'm interested in learning more about: ${decodeURIComponent(productName)}. Please contact me with more details.`;
  }

  // Validation rules
  const validators = {
    name: (val) => val.length >= 2 || 'Name must be at least 2 characters',
    email: (val) => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val) || 'Please enter a valid email address',
    mobileNumber: (val) => /^[\d\s\-\+\(\)]+$/.test(val) || 'Please enter a valid phone number',
    message: (val) => val.length >= 10 || 'Message must be at least 10 characters long'
  };

  // Show field error
  function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    field.classList.add('is-invalid');
    const feedback = field.nextElementSibling;
    if (feedback?.classList.contains('invalid-feedback')) feedback.textContent = message;
  }

  // Clear validation
  function clearValidation() {
    form.querySelectorAll('.form-control').forEach(input => {
      input.classList.remove('is-invalid', 'is-valid');
      const feedback = input.nextElementSibling;
      if (feedback?.classList.contains('invalid-feedback')) feedback.textContent = '';
    });
  }

  // Validate form
  function validateForm() {
    clearValidation();
    let isValid = true;
    
    Object.keys(validators).forEach(fieldId => {
      const value = document.getElementById(fieldId).value.trim();
      if (!value) {
        const fieldName = fieldId === 'mobileNumber' ? 'Phone number' : fieldId.charAt(0).toUpperCase() + fieldId.slice(1);
        showError(fieldId, `${fieldName} is required`);
        isValid = false;
      } else {
        const result = validators[fieldId](value);
        if (typeof result === 'string') {
          showError(fieldId, result);
          isValid = false;
        }
      }
    });
    
    return isValid;
  }

  // Show alert
  function showAlert(message, type) {
    alertContainer.querySelectorAll('.alert').forEach(alert => alert.remove());
    
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `${message}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    
    alertContainer.querySelector('h3').insertAdjacentElement('afterend', alertDiv);
    alertDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    
    if (type === 'success') setTimeout(() => alertDiv.remove(), 5000);
  }

  // Handle form submission
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    if (!validateForm()) return;

    submitBtn.disabled = true;
    const originalText = submitText.textContent;
    submitText.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending...';

    try {
      const response = await fetch('contact.php', {
        method: 'POST',
        body: new FormData(form)
      });
      const text = await response.text();
      
      if (text.includes('alert-success') || text.includes('submitted successfully')) {
        showAlert('Your enquiry has been submitted successfully!', 'success');
        form.reset();
        clearValidation();
      } else {
        const errorMatch = text.match(/alert-danger[^>]*>([^<]+)/);
        showAlert(errorMatch ? errorMatch[1].trim() : 'Failed to submit enquiry. Please try again.', 'danger');
      }
    } catch (error) {
      showAlert('Network error. Please check your connection and try again.', 'danger');
    } finally {
      submitBtn.disabled = false;
      submitText.textContent = originalText;
    }
  });

  // Real-time validation on blur
  form.querySelectorAll('.form-control').forEach(field => {
    field.addEventListener('blur', function() {
      const value = this.value.trim();
      if (!value) return;
      
      this.classList.remove('is-invalid', 'is-valid');
      const feedback = this.nextElementSibling;
      if (feedback?.classList.contains('invalid-feedback')) feedback.textContent = '';
      
      const validator = validators[this.id];
      if (validator) {
        const result = validator(value);
        if (typeof result === 'string') {
          showError(this.id, result);
        } else {
          this.classList.add('is-valid');
        }
      }
    });
  });
});
</script>

<?php include("includes/footer.php"); ?>

