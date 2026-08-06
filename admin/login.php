<?php
require_once __DIR__ . '/../includes/common.php';
require_once __DIR__ . '/../includes/auth.php';

if (isAdminLoggedIn()) {
    redirect('/admin/dashboard.php');
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $username = getPostValue('username');
    $password = getPostValue('password');
    if (attemptAdminLogin($username, $password)) {
        redirect('/admin/dashboard.php');
    }
    $message = 'Invalid username or password.';
}
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/app.css" />
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-7 col-lg-5">
        <div class="card shadow-sm border-0">
          <div class="card-body p-4">
            <h1 class="h3 mb-3 text-center">Admin Login</h1>
            <?php if ($message): ?>
              <div class="alert alert-danger" role="alert"><?php echo e($message); ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
              <input type="hidden" name="csrf_token" value="<?php echo e($csrfToken); ?>">
              <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <input class="form-control" id="username" type="text" name="username" required>
              </div>
              <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <input class="form-control" id="password" type="password" name="password" required>
              </div>
              <button class="btn btn-primary w-100" type="submit">Sign In</button>
            </form>
            <div class="text-center mt-3">
              <a href="../index.php" class="link-secondary">Back to User Application</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
