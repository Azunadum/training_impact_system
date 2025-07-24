<?php
require_once '../config/config.php';

if (!isset($_SESSION['hr_id'])) die("Access denied.");
?>

<!DOCTYPE html>
<html>
<head>
  <title>HR Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
    .dashboard-card { background: #fff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); padding: 30px; }
    .header { background-color: #003366; color: #fff; padding: 25px; border-radius: 8px 8px 0 0; text-align: center; margin-bottom: 25px; }
    .btn-custom, .btn-filter, .btn-download, .btn-logout { background: #003366; color: #fff; border: none; }
    .btn-custom:hover, .btn-filter:hover, .btn-download:hover, .btn-logout:hover { background: #0055aa; }
    .btn-filter.active { background: #0055aa; }
    .actions-bar { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-bottom: 25px; gap: 10px; }
    .filter-group { display: flex; flex-wrap: wrap; gap: 8px; }
    .action-buttons { display: flex; flex-wrap: wrap; gap: 8px; }
    .search-bar { margin-bottom: 20px; }
    th a { color: #fff; text-decoration: underline; cursor: pointer; }
    th a:hover { opacity: 0.85; }
    .sort-icon { font-size: 0.75em; margin-left: 4px; }
    thead th {
    position: sticky;
    top: 0;
    background-color: #003366;
    color: #fff; /* ensure text is readable on dark bg */
    z-index: 1;
    }
    .table-responsive { max-height: 500px; overflow-y: auto; }
    .table th, .table td { vertical-align: middle; }
    .pagination { justify-content: center; margin-top: 20px; }
    .pagination a { color: #003366; }
    .pagination a:hover { text-decoration: none; }
    .pagination .active a { background-color: #0055aa; color: #fff; border-color: #0055aa; }
    .pagination .disabled a { color: #ccc; }
    .pagination .disabled a:hover { background-color: #f8f9fa; color: #ccc; }
    .no-results { text-align: center; color: #888; font-style: italic; margin-top: 20px; }
    .table th { cursor: pointer; }
    .table th a { color: inherit; text-decoration: none; }
    .table th a:hover { text-decoration: underline; }
    .table th a .sort-icon { font-size: 0.75em; margin-left: 4px; }
    .table th a.active { color: #fff; }
    .table th a.active .sort-icon { color: #fff; }
    .table th a.inactive { color: #ccc; }
    .table th a.inactive .sort-icon { color: #ccc; }
    .table th a.inactive:hover { color: #fff; }
    .table th a.inactive:hover .sort-icon { color: #fff; }              
  </style>
</head>
<body class="bg-light">
<div class="container my-5">
  <div class="dashboard-card">
    <div class="header">
      <h2>Training Impact Assessment Dashboard</h2>
    </div>

    <!-- Filters & actions -->
    <div class="actions-bar">
      <div class="filter-group">
        <button class="btn btn-filter active" data-filter="">All</button>
        <button class="btn btn-filter" data-filter="COS">COS Only</button>
        <button class="btn btn-filter" data-filter="Permanent">Permanent Only</button>
        <button class="btn btn-filter" data-filter="Pending">Pending Only</button>
        <button class="btn btn-filter" data-filter="Completed">Completed Only</button>
      </div>
      <div class="action-buttons">
        <a href="export.php" class="btn btn-download">Download CSV</a>
        <a href="hr_logout.php" class="btn btn-logout">Logout</a>
      </div>
    </div>

    <!-- Search bar -->
    <div class="search-bar input-group">
        <input type="text" id="searchInput" class="form-control" placeholder="Search name, title or code...">
        <button id="searchBtn" class="btn btn-custom">Search</button>
        <button id="clearSearchBtn" class="btn btn-outline-secondary">Clear</button>
    </div>

    <!-- AJAX result container -->
    <div id="dataContainer">
      <!-- Table rows + pagination will be loaded here -->
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  let filter = '';
  let search = '';
  let sort = '';
  let dir = '';
  let page = 1;

  function loadData() {
    const params = new URLSearchParams();
    if (filter) params.append('filter', filter);
    if (search) params.append('search', search);
    if (sort) { params.append('sort', sort); params.append('dir', dir); }
    params.append('page', page);

    fetch('hr_dashboard_data.php?' + params.toString())
      .then(res => res.text())
      .then(html => {
        document.getElementById('dataContainer').innerHTML = html;
        document.querySelectorAll('.page-link').forEach(link => {
          link.addEventListener('click', e => {
            e.preventDefault();
            page = link.dataset.page;
            loadData();
          });
        });
        document.querySelectorAll('th a').forEach(a => {
          a.addEventListener('click', e => {
            e.preventDefault();
            sort = a.dataset.sort;
            dir = a.dataset.dir;
            loadData();
          });
        });
      });
  } 

  function showPreview(url, type) {
  const preview = document.getElementById('previewContent');
  preview.innerHTML = '';

  if (type === 'pdf') {
    preview.innerHTML = `<iframe src="${url}" width="100%" height="600px" style="border: none;"></iframe>`;
  } else if (type === 'image') {
    preview.innerHTML = `<img src="${url}" class="img-fluid" alt="Preview">`;
  } else {
    preview.innerHTML = `<p>Preview not supported.</p>`;
  }

  const modal = new bootstrap.Modal(document.getElementById('filePreviewModal'));
  modal.show();
  } 

  document.querySelectorAll('.btn-filter').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.btn-filter').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      filter = btn.dataset.filter;
      page = 1;
      loadData();
    });
  });

  document.getElementById('searchBtn').addEventListener('click', () => {
    search = document.getElementById('searchInput').value.trim();
    page = 1;
    loadData();
  });

  document.getElementById('clearSearchBtn').addEventListener('click', () => {
  document.getElementById('searchInput').value = '';
  search = '';
  page = 1;
  loadData();
  });

  document.getElementById('searchInput').addEventListener('keyup', e => {
  if (e.key === 'Enter') {
    document.getElementById('searchBtn').click();
  }
});

  loadData();
</script>
  
<!-- File Preview Modal -->
<div class="modal fade" id="filePreviewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">File Preview</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <div id="previewContent" style="max-height: 600px; overflow-y: auto;"></div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
