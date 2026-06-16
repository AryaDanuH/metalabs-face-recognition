<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === 'Metalabs' && $password === 'metarawr') {
        $_SESSION['admin_logged_in'] = true;
        header("Location: index.php");
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Login - Metalabs</title>
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap">
</head>
<body style="align-items: center; justify-content: center; background-image: radial-gradient(circle at top, rgba(225, 29, 72, 0.1), transparent 40%);">

  <div class="glass-card" style="width: 100%; max-width: 400px; padding: 40px; text-align: center;">
    <h1 class="font-display-lg" style="margin-bottom: 8px;">META<span class="text-primary">LABS</span></h1>
    <p class="text-on-surface-variant font-body-sm" style="margin-bottom: 32px;">Internal Secure Network Login</p>
    
    <?php if ($error): ?>
      <div style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 12px; border-radius: 4px; margin-bottom: 16px; font-size: 14px;">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" style="display: flex; flex-direction: column; gap: 16px; text-align: left;">
      <div style="display: flex; flex-direction: column; gap: 8px;">
        <label class="font-label-caps">Username</label>
        <input type="text" name="username" class="input-field" required autofocus>
      </div>
      <div style="display: flex; flex-direction: column; gap: 8px;">
        <label class="font-label-caps">Password</label>
        <input type="password" name="password" class="input-field" required>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top: 16px;">Access Secure Terminal</button>
    </form>
    
    <div style="margin-top: 32px;">
      <a href="index.php" class="text-on-surface-variant font-body-sm" style="opacity: 0.8; text-decoration: underline;">&larr; Return to Public View</a>
    </div>
  </div>

</body>
</html>
