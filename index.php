<?php
require_once __DIR__ . '/includes/common.php';
$csrfToken = generateCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Certificate Request Application</title>
  <meta name="csrf-token" content="<?php echo e($csrfToken); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/app.css" />
</head>
<body class="bg-light">
  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-xl-9 col-lg-10">
        <div class="card shadow-sm border-0 overflow-hidden">
          <div class="card-body p-5">
            <section id="welcomeSection" class="app-section">
              <div class="text-center mb-4">
                <span class="badge bg-primary mb-3">Certificate Request</span>
                <h1 class="display-6">Certificate Request Application</h1>
                <p class="text-muted">Please complete the application form below. Your submitted information will be used to generate your official certificate.</p>
              </div>
              <div class="d-grid gap-3">
                <button id="startButton" class="btn btn-primary btn-lg">Start Application</button>
                <a href="admin/login.php" class="btn btn-outline-secondary btn-lg">Admin Login</a>
              </div>
            </section>

            <section id="formSection" class="app-section d-none">
              <div class="d-flex align-items-center justify-content-between mb-4">
                <h2>Application Form</h2>
                <button id="backToWelcome" class="btn btn-link">Back to Start</button>
              </div>
              <div id="formAlert"></div>
              <form id="applicationForm" novalidate>
                <input type="hidden" id="csrfToken" name="csrf_token" value="<?php echo e($csrfToken); ?>" />
                <div class="mb-3">
                  <label for="fullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input type="text" id="fullName" name="full_name" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label for="designation" class="form-label">Designation <span class="text-danger">*</span></label>
                  <input type="text" id="designation" name="designation" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label for="office" class="form-label">Office / Agency <span class="text-danger">*</span></label>
                  <input type="text" id="office" name="office" class="form-control" required />
                </div>
                <div class="mb-3">
                  <label for="purpose" class="form-label">Purpose <span class="text-danger">*</span></label>
                  <textarea id="purpose" name="purpose" class="form-control" rows="4" required></textarea>
                </div>
                <div class="mb-4">
                  <label for="requestDate" class="form-label">Date <span class="text-danger">*</span></label>
                  <input type="date" id="requestDate" name="request_date" class="form-control" required />
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100" id="submitApplicationButton">Submit Application</button>
              </form>
            </section>

            <section id="successSection" class="app-section d-none text-center">
              <div class="mb-4">
                <div class="text-success display-4">✓</div>
                <h2 class="mt-3">Application Submitted Successfully!</h2>
                <p class="text-muted">Your application has been received successfully.</p>
              </div>
              <div class="mb-3">
                <p class="fw-bold">Reference Number:</p>
                <p id="referenceNumber" class="h4 text-primary"></p>
              </div>
              <button id="newApplicationButton" class="btn btn-primary btn-lg">Submit Another Application</button>
              <div class="mt-3">
                <a href="admin/login.php" class="btn btn-link">Admin Login</a>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>
