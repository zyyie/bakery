<?php
require_once __DIR__ . '/includes/bootstrap.php';
requireAdminLogin();
adminRegenerateSession();

$error = '';
$success = '';

$adminID = (int)($_SESSION['adminID'] ?? 0);

$currentUsername = '';
$res = executePreparedQuery('SELECT username, password FROM admin WHERE adminID = ? LIMIT 1', 'i', [$adminID]);
$adminRow = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;

if ($adminRow) {
    $currentUsername = (string)$adminRow['username'];
} else {
    $error = 'Admin account not found.';
}

function verify_admin_password($plain, $storedHash) {
    $storedHash = (string)$storedHash;
    $plain = (string)$plain;

    if ($storedHash === '') {
        return false;
    }

    if (strlen($storedHash) === 32 && ctype_xdigit($storedHash)) {
        return hash_equals($storedHash, md5($plain));
    }

    if (str_starts_with($storedHash, '$2y$') || str_starts_with($storedHash, '$2a$') || str_starts_with($storedHash, '$2b$')) {
        return password_verify($plain, $storedHash);
    }

    return hash_equals($storedHash, $plain);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $adminRow) {
    $newUsername = isset($_POST['username']) ? trim((string)$_POST['username']) : '';
    $oldPassword = isset($_POST['old_password']) ? (string)$_POST['old_password'] : '';
    $newPassword = isset($_POST['new_password']) ? (string)$_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? (string)$_POST['confirm_password'] : '';

    if ($newUsername === '') {
        $error = 'Username is required.';
    } elseif (!verify_admin_password($oldPassword, $adminRow['password'])) {
        $error = 'Old password is incorrect.';
    } else {
        $doUpdatePassword = false;
        if ($newPassword !== '' || $confirmPassword !== '') {
            if (strlen($newPassword) < 8) {
                $error = 'New password must be at least 8 characters.';
            } elseif ($newPassword !== $confirmPassword) {
                $error = 'New password and confirmation do not match.';
            } else {
                $doUpdatePassword = true;
            }
        }

        if ($error === '') {
            $existing = executePreparedQuery('SELECT adminID FROM admin WHERE username = ? AND adminID <> ? LIMIT 1', 'si', [$newUsername, $adminID]);
            if ($existing && $existing->num_rows > 0) {
                $error = 'That username is already taken.';
            } else {
                if ($doUpdatePassword) {
                    $newHash = password_hash($newPassword, PASSWORD_BCRYPT);
                    executePreparedUpdate('UPDATE admin SET username = ?, password = ? WHERE adminID = ?', 'ssi', [$newUsername, $newHash, $adminID]);
                } else {
                    executePreparedUpdate('UPDATE admin SET username = ? WHERE adminID = ?', 'si', [$newUsername, $adminID]);
                }

                $_SESSION['adminUsername'] = $newUsername;
                $success = 'Account updated successfully.';

                $res2 = executePreparedQuery('SELECT username, password FROM admin WHERE adminID = ? LIMIT 1', 'i', [$adminID]);
                $adminRow = ($res2 && $res2->num_rows > 0) ? $res2->fetch_assoc() : $adminRow;
                $currentUsername = (string)($adminRow['username'] ?? $currentUsername);
            }
        }
    }
}

include __DIR__ . '/includes/header.php';
?>

<div class="header-bar">
  <h2 class="mb-0" style="color: #333; font-weight: 600;">Admin Account</h2>
</div>

<div class="row">
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <?php if ($error !== ''): ?>
          <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
          <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" action="">
          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($currentUsername); ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Old Password</label>
            <input type="password" name="old_password" class="form-control" required>
          </div>

          <hr>

          <div class="mb-3">
            <label class="form-label">New Password (optional)</label>
            <input type="password" name="new_password" class="form-control" minlength="8">
          </div>

          <div class="mb-3">
            <label class="form-label">Confirm New Password</label>
            <input type="password" name="confirm_password" class="form-control" minlength="8">
          </div>

          <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php';
