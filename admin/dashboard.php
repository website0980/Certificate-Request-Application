<?php
require_once __DIR__ . '/../includes/common.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireAdminLogin();
$stats = Database::getStats();
$recent = Database::getRecentApplications(5);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/app.css" />
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php">Admin Dashboard</a>
      <div>
        <a href="applications.php" class="btn btn-outline-light me-2">Applications</a>
        <a href="logout.php" class="btn btn-light">Log Out</a>
      </div>
    </div>
  </nav>
  <main class="container py-5">
    <div class="row g-4">
      <div class="col-md-3">
        <div class="card shadow-sm border-0 p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <p class="text-uppercase text-muted small mb-1">Total Applications</p>
              <h3 class="mb-0"><?php echo e($stats['total']); ?></h3>
            </div>
            <span class="badge bg-info">Live</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0 p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <p class="text-uppercase text-muted small mb-1">Today's Applications</p>
              <h3 class="mb-0"><?php echo e($stats['today']); ?></h3>
            </div>
            <span class="badge bg-success">Today</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0 p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <p class="text-uppercase text-muted small mb-1">Pending Certificates</p>
              <h3 class="mb-0"><?php echo e($stats['pending']); ?></h3>
            </div>
            <span class="badge bg-warning">Pending</span>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card shadow-sm border-0 p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
              <p class="text-uppercase text-muted small mb-1">Printed Certificates</p>
              <h3 class="mb-0"><?php echo e($stats['printed']); ?></h3>
            </div>
            <span class="badge bg-secondary">Printed</span>
          </div>
        </div>
      </div>
    </div>

    <div class="row mt-4">
      <div class="col-lg-8">
        <div class="card shadow-sm border-0 p-4">
          <h4 class="mb-3">Recently Submitted</h4>
          <div class="list-group">
            <?php if (count($recent) === 0): ?>
              <div class="list-group-item">No recent submissions.</div>
            <?php else: ?>
              <?php foreach ($recent as $application): ?>
                <div class="list-group-item">
                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <strong><?php echo e($application['reference_no']); ?></strong>
                      <div class="small text-muted"><?php echo e($application['full_name']); ?> &bull; <?php echo e($application['designation']); ?></div>
                    </div>
                    <span class="badge <?php echo $application['status'] === 'Printed' ? 'bg-secondary' : 'bg-warning'; ?>"><?php echo e($application['status']); ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="card shadow-sm border-0 p-4">
          <h4 class="mb-3">Filters</h4>
          <ul class="list-group list-group-flush">
            <li class="list-group-item"><a href="applications.php?filter=pending" class="stretched-link text-decoration-none">Pending</a></li>
            <li class="list-group-item"><a href="applications.php?filter=printed" class="stretched-link text-decoration-none">Printed</a></li>
            <li class="list-group-item"><a href="applications.php?filter=today" class="stretched-link text-decoration-none">Today's Applications</a></li>
            <li class="list-group-item"><a href="applications.php?filter=this_week" class="stretched-link text-decoration-none">This Week</a></li>
            <li class="list-group-item"><a href="applications.php?filter=this_month" class="stretched-link text-decoration-none">This Month</a></li>
          </ul>
        </div>
      </div>
    </div>

    <div class="mt-4 text-end">
      <a href="applications.php" class="btn btn-primary">Manage Applications</a>
    </div>
  </main>
</body>
</html>
