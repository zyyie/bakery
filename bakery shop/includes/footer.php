<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <img src="logo.png" alt="Bakery Logo" style="height: 50px;">
        <p class="text-muted">PREMIUM GOODS</p>
        <p class="text-muted">Your Business Address Here</p>
        <p class="text-muted">Phone: 964-9885-950</p>
        <p class="text-muted">Email: karneekbakery@gmail.com</p>
      </div>
      <div class="col-md-4">
        <h5>Useful Links</h5>
        <ul class="list-unstyled">
          <li><a href="about.php" class="text-muted text-decoration-none">About Us</a></li>
          <li><a href="contact.php" class="text-muted text-decoration-none">Contact Us</a></li>
          <li><a href="index.php" class="text-muted text-decoration-none">Home</a></li>
          <li><a href="login.php" class="text-muted text-decoration-none">Login</a></li>
          <li><a href="products.php" class="text-muted text-decoration-none">Food Packages</a></li>
          <li><a href="admin/login.php" class="text-muted text-decoration-none">Admin Login</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h5>Join Our Newsletter Now</h5>
        <p class="text-muted">Get E-mail updates about our latest shop and special offers.</p>
        <form method="POST" action="subscribe.php">
          <div class="input-group mb-3">
            <input type="email" class="form-control" name="email" placeholder="Enter your Email...">
            <button class="btn btn-warning" type="submit">SUBSCRIBE</button>
          </div>
        </form>
        <div class="mt-3">
          <a href="https://www.facebook.com/share/1GSuha14s5/" target="_blank" rel="noopener noreferrer" class="text-dark me-2"><i class="fab fa-facebook fa-2x"></i></a>
          <a href="https://www.instagram.com/karneek_bakery/" target="_blank" rel="noopener noreferrer" class="text-dark me-2"><i class="fab fa-instagram fa-2x"></i></a>
          <a href="https://x.com/karneekbakery_" target="_blank" rel="noopener noreferrer" class="text-dark me-2"><i class="fab fa-twitter fa-2x"></i></a>
        </div>
      </div>
    </div>
    <hr>
    <div class="text-center text-muted">
      <p>Online Cake Ordering @ 2023</p>
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>

<!-- Page Loading Script -->
<script>
(function() {
  const loader = document.getElementById('pageLoader');
  
  // Hide loader when page is fully loaded
  window.addEventListener('load', function() {
    if (loader) {
      loader.classList.remove('active');
    }
  });
  
  // Show loader when clicking on navigation links
  document.addEventListener('DOMContentLoaded', function() {
    // Get all internal links
    const links = document.querySelectorAll('a[href]:not([href^="#"]):not([href^="javascript:"]):not([href^="mailto:"]):not([href^="tel:"])');
    
    links.forEach(function(link) {
      const href = link.getAttribute('href');
      
      // Only handle internal links (same domain)
      if (href && !href.startsWith('http') && !href.startsWith('//')) {
        link.addEventListener('click', function(e) {
          // Don't show loader for dropdowns, modals, or forms
          if (link.closest('.dropdown-menu') || 
              link.hasAttribute('data-bs-toggle') || 
              link.hasAttribute('data-bs-dismiss') ||
              link.closest('form')) {
            return;
          }
          
          // Show loader
          if (loader) {
            loader.classList.add('active');
          }
          
          // Fallback: hide loader after 5 seconds if page doesn't load
          setTimeout(function() {
            if (loader) {
              loader.classList.remove('active');
            }
          }, 5000);
        });
      }
    });
    
    // Handle form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
      // Don't show loader for forms that navigate (like search, subscribe)
      const action = form.getAttribute('action');
      if (action && !action.includes('ajax') && !action.includes('cart.php')) {
        form.addEventListener('submit', function() {
          if (loader) {
            loader.classList.add('active');
          }
          
          setTimeout(function() {
            if (loader) {
              loader.classList.remove('active');
            }
          }, 5000);
        });
      }
    });
  });
  
  // Hide loader if page loads quickly
  if (document.readyState === 'complete') {
    setTimeout(function() {
      if (loader) {
        loader.classList.remove('active');
      }
    }, 300);
  }
})();
</script>

</body>
</html>

