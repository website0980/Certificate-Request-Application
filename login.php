<?php
require_once __DIR__ . '/auth.php';
if (isAdminLoggedIn()) {
    header('Location: admin.php');
    exit;
}
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (attemptAdminLogin($username, $password)) {
        header('Location: admin.php');
        exit;
    }
    $message = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <main class="page" id="login-page">
    <div class="card">
      <h1>Admin Login</h1>
      <?php if ($message): ?>
        <p class="error-message"><?php echo htmlspecialchars($message); ?></p>
      <?php endif; ?>
      <form method="POST" action="login.php">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required />

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required />

        <button type="submit" class="primary-button">Log In</button>
      </form>
      <a href="index.php" class="secondary-button" style="display:inline-block; margin-top:1rem; width:100%; text-align:center;">Back to Fill up Form</a>
    </div>
  </main>
</body>
</html>
