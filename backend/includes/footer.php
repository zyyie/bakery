<?php
$__scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
$__backendBaseUrl = '/backend';
$__backendPos = strpos($__scriptName, '/backend/');
if ($__backendPos !== false) {
  $__backendBaseUrl = substr($__scriptName, 0, $__backendPos) . '/backend';
}
?>

<!-- Footer -->
<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-md-4">
        <img src="../frontend/images/logo.png" alt="Bakery Logo" style="height: 50px;">
        <p class="text-muted">PREMIUM GOODS</p>
        <p class="text-muted">Your Business Address Here</p>
        <p class="text-muted">Phone: +639493380766</p>
        <p class="text-muted">Email: karneekbakery@gmail.com</p>
      </div>
      <div class="col-md-4">
        <h5>Useful Links</h5>
        <ul class="list-unstyled">
          <li><a href="<?php echo $__backendBaseUrl; ?>/about.php" class="text-muted text-decoration-none">About Us</a></li>
          <li><a href="<?php echo $__backendBaseUrl; ?>/contact.php" class="text-muted text-decoration-none">Contact Us</a></li>
          <li><a href="<?php echo $__backendBaseUrl; ?>/index.php" class="text-muted text-decoration-none">Home</a></li>
          <li><a href="<?php echo $__backendBaseUrl; ?>/login.php" class="text-muted text-decoration-none">Login</a></li>
          <li><a href="<?php echo $__backendBaseUrl; ?>/products.php" class="text-muted text-decoration-none">Food Packages</a></li>
          <li><a href="<?php echo $__backendBaseUrl; ?>/admin/login.php" class="text-muted text-decoration-none">Admin Login</a></li>
        </ul>
      </div>
      <div class="col-md-4">
        <h5>Join Our Newsletter Now</h5>
        <p class="text-muted">Get E-mail updates about our latest shop and special offers.</p>
        <form method="POST" action="api/subscribe.php" id="newsletterForm">
          <div class="input-group mb-3">
            <input type="email" class="form-control" name="email" placeholder="Enter your Email..." required>
            <button class="btn btn-warning" type="submit">SUBSCRIBE</button>
          </div>
          <div id="newsletterMessage" style="margin-top: 10px;"></div>
        </form>
        <script>
        document.getElementById('newsletterForm').addEventListener('submit', function(e) {
          e.preventDefault();
          
          const formData = new FormData(this);
          const messageDiv = document.getElementById('newsletterMessage');
          const submitBtn = this.querySelector('button[type="submit"]');
          const originalText = submitBtn.textContent;
          
          // Show loading state
          submitBtn.disabled = true;
          submitBtn.textContent = 'Subscribing...';
          messageDiv.innerHTML = '';
          
          fetch('api/subscribe-ajax.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              messageDiv.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
              this.querySelector('input[name="email"]').value = '';
            } else {
              messageDiv.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
            }
          })
          .catch(error => {
            messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred. Please try again.</div>';
          })
          .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
          });
        });
        </script>
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

<div id="bakery-ai" class="bakery-ai">
  <button id="bakery-ai-toggle" type="button" class="bakery-ai-toggle" aria-label="Open chat">
    <span class="bakery-ai-badge" id="bakery-ai-badge" style="display:none;">1</span>
    <i class="fas fa-comments" aria-hidden="true"></i>
  </button>

  <div id="bakery-ai-panel" class="bakery-ai-panel" style="display: none;">
    <div class="bakery-ai-header">
      <div class="bakery-ai-header-left">
        <div class="bakery-ai-avatar" aria-hidden="true">
          <i class="fas fa-robot"></i>
        </div>
        <div class="bakery-ai-meta">
          <div class="bakery-ai-title">KARNEEK Bot</div>
          <div class="bakery-ai-time" id="bakery-ai-time"></div>
        </div>
      </div>
      <button id="bakery-ai-close" type="button" class="bakery-ai-close" aria-label="Close chat">
        <i class="fas fa-times"></i>
      </button>
    </div>

    <div id="bakery-ai-welcome" class="bakery-ai-welcome">
      <div class="bakery-ai-welcome-title">Welcome to KARNEEK Bakery</div>
      <div class="bakery-ai-welcome-text">How can I help you today?</div>
      <div class="bakery-ai-actions">
        <button type="button" class="bakery-ai-action" data-quick="Tell me about your products">Products</button>
        <button type="button" class="bakery-ai-action" data-quick="How do I place an order?">Ordering</button>
        <button type="button" class="bakery-ai-action" data-quick="What are your pickup options?">Pickup</button>
        <button type="button" class="bakery-ai-action" data-quick="What are your prices?">Pricing</button>
        <button type="button" class="bakery-ai-action" data-quick="Do you have custom cakes?">Custom Cakes</button>
        <button type="button" class="bakery-ai-action secondary" data-quick="I don't need help now">I don't need help now</button>
      </div>
    </div>

    <div id="bakery-ai-chat" class="bakery-ai-chat" style="display:none;">
      <div id="bakery-ai-messages" class="bakery-ai-messages"></div>
      <form id="bakery-ai-form" class="bakery-ai-form" autocomplete="off">
        <input id="bakery-ai-input" type="text" class="bakery-ai-input" placeholder="Type your message..." />
        <button id="bakery-ai-send" type="submit" class="bakery-ai-send" aria-label="Send">
          <i class="fas fa-paper-plane"></i>
        </button>
      </form>
    </div>
  </div>
</div>

<script>
(function() {
  const toggleBtn = document.getElementById('bakery-ai-toggle');
  const closeBtn = document.getElementById('bakery-ai-close');
  const panel = document.getElementById('bakery-ai-panel');
  const timeEl = document.getElementById('bakery-ai-time');
  const badgeEl = document.getElementById('bakery-ai-badge');
  const welcomeEl = document.getElementById('bakery-ai-welcome');
  const chatEl = document.getElementById('bakery-ai-chat');
  const messagesEl = document.getElementById('bakery-ai-messages');
  const form = document.getElementById('bakery-ai-form');
  const input = document.getElementById('bakery-ai-input');

  if (!toggleBtn || !closeBtn || !panel || !messagesEl || !form || !input || !welcomeEl || !chatEl) return;

  const history = [];

  function formatTime(d) {
    const h = String(d.getHours()).padStart(2, '0');
    const m = String(d.getMinutes()).padStart(2, '0');
    return `${h}:${m}`;
  }

  function showWelcome() {
    welcomeEl.style.display = 'block';
    chatEl.style.display = 'none';
  }

  function showChat() {
    welcomeEl.style.display = 'none';
    chatEl.style.display = 'flex';
    input.focus();
    if (messagesEl.childElementCount === 0) {
      addMsg('assistant', "Hi! I'm the KARNEEK Bakery assistant. Ask me anything about our products, ordering, and delivery.");
    }
  }

  function setOpen(open) {
    panel.style.display = open ? 'flex' : 'none';
    panel.classList.toggle('is-open', open);
    if (open) {
      if (timeEl) timeEl.textContent = formatTime(new Date());
      if (badgeEl) badgeEl.style.display = 'none';
      if (messagesEl.childElementCount === 0) {
        showWelcome();
      } else {
        showChat();
      }
    }
  }

  function addMsg(role, text) {
    const row = document.createElement('div');
    row.className = 'bakery-ai-msg bakery-ai-msg-' + role;
    row.textContent = text;
    messagesEl.appendChild(row);
    messagesEl.scrollTop = messagesEl.scrollHeight;
  }

  function pushHistory(role, content) {
    history.push({ role, content });
    if (history.length > 10) history.splice(0, history.length - 10);
  }

  toggleBtn.addEventListener('click', function() {
    setOpen(panel.style.display === 'none');
  });
  closeBtn.addEventListener('click', function() { setOpen(false); });

  document.querySelectorAll('#bakery-ai .bakery-ai-action').forEach(function(btn) {
    btn.addEventListener('click', function() {
      const quick = (btn.getAttribute('data-quick') || '').trim();
      showChat();
      if (quick) {
        input.value = quick;
        form.dispatchEvent(new Event('submit', { cancelable: true }));
      }
    });
  });

  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const text = (input.value || '').trim();
    if (!text) return;

    input.value = '';
    addMsg('user', text);
    pushHistory('user', text);

    const typing = document.createElement('div');
    typing.className = 'bakery-ai-typing';
    typing.textContent = 'Typing...';
    messagesEl.appendChild(typing);
    messagesEl.scrollTop = messagesEl.scrollHeight;

    try {
      const res = await fetch('api/ollama-chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text, history })
      });

      const data = await res.json().catch(() => ({}));
      typing.remove();

      if (!res.ok) {
        addMsg('assistant', (data && data.error) ? data.error : 'AI is unavailable right now.');
        return;
      }

      const reply = (data && data.reply) ? String(data.reply) : '';
      const safe = reply.trim() || 'Sorry, I could not generate a response right now.';
      addMsg('assistant', safe);
      pushHistory('assistant', safe);
    } catch (err) {
      typing.remove();
      addMsg('assistant', 'AI is unavailable right now. Make sure Ollama is running.');
    }
  });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>
</body>
</html>

