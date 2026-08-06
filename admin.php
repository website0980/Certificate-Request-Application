<?php
require_once __DIR__ . '/auth.php';
requireAdminLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin - Submitted Applications</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <main class="admin-page">
    <div class="card">
      <div class="preview-header">
        <div>
          <h1>Admin Dashboard</h1>
          <p id="submissionCount">Submitted forms: 0</p>
        </div>
        <div class="button-row" style="gap:0.5rem; align-items:center;">
          <a href="index.php" class="secondary-button">Back to Start</a>
          <a href="logout.php" class="secondary-button">Log Out</a>
        </div>
      </div>
      <div id="submissionList" class="submission-list"></div>
    </div>
  </main>
  <script src="script.js"></script>
</body>
</html>
