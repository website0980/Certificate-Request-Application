<?php
require_once __DIR__ . '/../includes/common.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
requireAdminLogin();
$applications = Database::listApplications();
$filter = $_GET['filter'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Application Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/app.css" />
</head>
<body class="bg-light">
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
      <a class="navbar-brand" href="dashboard.php">Admin Dashboard</a>
      <div>
        <a href="dashboard.php" class="btn btn-outline-light me-2">Dashboard</a>
        <a href="logout.php" class="btn btn-light">Log Out</a>
      </div>
    </div>
  </nav>
  <main class="container py-5">
    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
      <div>
        <h1 class="h4 mb-1">Application Management</h1>
        <p class="text-muted mb-0">Search, preview, print, and manage submitted applications.</p>
      </div>
      <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <div class="card shadow-sm border-0 p-4 mb-4">
      <div class="row g-3">
        <div class="col-md-4">
          <input type="search" id="searchInput" class="form-control" placeholder="Search reference, name, office, designation, status" />
        </div>
        <div class="col-md-3">
          <select id="filterSelect" class="form-select">
            <option value="">All Statuses</option>
            <option value="Pending">Pending</option>
            <option value="Printed">Printed</option>
          </select>
        </div>
        <div class="col-md-3">
          <select id="dateFilterSelect" class="form-select">
            <option value="">All dates</option>
            <option value="today">Today</option>
            <option value="this_week">This Week</option>
            <option value="this_month">This Month</option>
          </select>
        </div>
        <div class="col-md-2 text-md-end">
          <button id="refreshButton" class="btn btn-primary w-100">Refresh</button>
        </div>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle" id="applicationsTable">
        <thead class="table-light">
          <tr>
            <th scope="col">Reference No.</th>
            <th scope="col">Name</th>
            <th scope="col">Designation</th>
            <th scope="col">Office</th>
            <th scope="col">Date</th>
            <th scope="col">Status</th>
            <th scope="col">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if (count($applications) === 0): ?>
            <tr><td colspan="7" class="text-center py-4">No applications found.</td></tr>
          <?php else: ?>
            <?php foreach ($applications as $application): ?>
              <tr data-application='<?php echo json_encode($application, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>
                <td><?php echo e($application['reference_no']); ?></td>
                <td><?php echo e($application['full_name']); ?></td>
                <td><?php echo e($application['designation']); ?></td>
                <td><?php echo e($application['office']); ?></td>
                <td><?php echo e($application['request_date']); ?></td>
                <td><?php echo e($application['status']); ?></td>
                <td>
                  <div class="btn-group" role="group" aria-label="Actions">
                    <a href="/preview.php?id=<?php echo e($application['id']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">Preview</a>
                    <a href="/print.php?id=<?php echo e($application['id']); ?>" target="_blank" class="btn btn-sm btn-outline-success">Print</a>
                    <?php if ($application['status'] !== 'Printed'): ?>
                      <button type="button" class="btn btn-sm btn-outline-secondary mark-printed-button" data-id="<?php echo e($application['id']); ?>">Mark Printed</button>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-button" data-id="<?php echo e($application['id']); ?>">Delete</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    const applications = Array.from(document.querySelectorAll('#applicationsTable tbody tr')).map(row => ({
      row,
      data: JSON.parse(row.dataset.application),
    }));

    const searchInput = document.getElementById('searchInput');
    const filterSelect = document.getElementById('filterSelect');
    const dateFilterSelect = document.getElementById('dateFilterSelect');
    const refreshButton = document.getElementById('refreshButton');

    const renderRows = () => {
      const query = searchInput.value.toLowerCase();
      const filterStatus = filterSelect.value;
      const dateFilter = dateFilterSelect.value;
      const rows = applications.filter(({data}) => {
        const matchesSearch = query === '' || [data.reference_no, data.full_name, data.designation, data.office, data.purpose, data.status, data.request_date].some(value => value.toLowerCase().includes(query));
        const matchesStatus = filterStatus === '' || data.status === filterStatus;
        const today = new Date().toISOString().slice(0,10);
        const requestDate = data.request_date;
        let matchesDate = true;
        if (dateFilter === 'today') {
          matchesDate = requestDate === today;
        } else if (dateFilter === 'this_week') {
          const now = new Date();
          const monday = new Date(now.setDate(now.getDate() - ((now.getDay() + 6) % 7)));
          const d = new Date(requestDate);
          matchesDate = d >= monday;
        } else if (dateFilter === 'this_month') {
          const [year, month] = requestDate.split('-');
          const now = new Date();
          matchesDate = Number(year) === now.getFullYear() && Number(month) === now.getMonth() + 1;
        }
        return matchesSearch && matchesStatus && matchesDate;
      });
      document.querySelector('#applicationsTable tbody').innerHTML = rows.map(({row}) => row.outerHTML).join('');
      attachRowActions();
    };

    const attachRowActions = () => {
      document.querySelectorAll('.delete-button').forEach(button => {
        button.addEventListener('click', async () => {
          const id = button.dataset.id;
          if (!confirm('Delete this application?')) return;
          await fetch(`../api.php?action=delete&id=${encodeURIComponent(id)}`, { method: 'POST' });
          location.reload();
        });
      });
      document.querySelectorAll('.mark-printed-button').forEach(button => {
        button.addEventListener('click', async () => {
          const id = button.dataset.id;
          await fetch(`../api.php?action=mark_printed&id=${encodeURIComponent(id)}`, { method: 'POST' });
          location.reload();
        });
      });
    };

    searchInput.addEventListener('input', renderRows);
    filterSelect.addEventListener('change', renderRows);
    dateFilterSelect.addEventListener('change', renderRows);
    refreshButton.addEventListener('click', () => location.reload());
    attachRowActions();
  </script>
</body>
</html>
