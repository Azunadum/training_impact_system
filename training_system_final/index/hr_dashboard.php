<?php
require_once '../config/config.php';

if (!isset($_SESSION['hr_id'])) die("Access denied.");

// ✅ Filters
$filter = '';
$where = '';
$params = [];

if (isset($_GET['filter']) && in_array($_GET['filter'], ['COS', 'Permanent', 'Pending', 'Completed'])) {
    $filter = $_GET['filter'];
    if ($filter === 'Pending' || $filter === 'Completed') {
        $where = "WHERE te.status = ?";
    } else {
        $where = "WHERE te.staff_type = ?";
    }
    $params[] = $filter;
}

// ✅ Sorting
$allowedSort = ['staff_name', 'title', 'start_date', 'end_date', 'hours'];
$sort = 'te.id';
$dir = 'DESC';

if (isset($_GET['sort']) && in_array($_GET['sort'], $allowedSort)) {
    $sort = $_GET['sort'];
}
if (isset($_GET['dir']) && in_array(strtoupper($_GET['dir']), ['ASC', 'DESC'])) {
    $dir = strtoupper($_GET['dir']);
}

// ✅ Pagination
$limit = 10; // rows per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// ✅ Total rows for pagination count
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM training_entries te $where");
$countStmt->execute($params);
$totalRows = $countStmt->fetchColumn();
$totalPages = ceil($totalRows / $limit);

// ✅ Main query with LIMIT & OFFSET
$stmt = $pdo->prepare("
    SELECT te.*, sd.file_name, sd.file_path
    FROM training_entries te
    LEFT JOIN supporting_docs sd ON te.id = sd.training_entry_id
    $where
    ORDER BY $sort $dir
    LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$entries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>HR Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        th a { color: #fff; text-decoration: underline; cursor: pointer; }
        th a:hover { opacity: 0.85; }
        .sort-icon { font-size: 0.75em; margin-left: 4px; }
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
                <a href="hr_dashboard.php" class="btn btn-filter <?= $filter == '' ? 'active' : '' ?>">All</a>
                <a href="hr_dashboard.php?filter=COS" class="btn btn-filter <?= $filter == 'COS' ? 'active' : '' ?>">COS Only</a>
                <a href="hr_dashboard.php?filter=Permanent" class="btn btn-filter <?= $filter == 'Permanent' ? 'active' : '' ?>">Permanent Only</a>
                <a href="hr_dashboard.php?filter=Pending" class="btn btn-filter <?= $filter == 'Pending' ? 'active' : '' ?>">Pending Only</a>
                <a href="hr_dashboard.php?filter=Completed" class="btn btn-filter <?= $filter == 'Completed' ? 'active' : '' ?>">Completed Only</a>
            </div>
            <div class="action-buttons">
                <a href="export.php" class="btn btn-download">Download CSV</a>
                <a href="hr_logout.php" class="btn btn-logout">Logout</a>
            </div>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <?php
                        function sortLink($col, $label, $filter, $sort, $dir) {
                            $nextDir = ($sort == $col && $dir == 'ASC') ? 'DESC' : 'ASC';
                            $icon = '';
                            if ($sort == $col) {
                                $icon = $dir == 'ASC' ? '▲' : '▼';
                            }
                            $url = "hr_dashboard.php?sort=$col&dir=$nextDir";
                            if ($filter) $url .= "&filter=$filter";
                            return "<a href='$url'>$label <span class='sort-icon'>$icon</span></a>";
                        }
                        ?>
                        <th><?= sortLink('staff_name', 'Name', $filter, $sort, $dir) ?></th>
                        <th>Email</th>
                        <th>Staff</th>
                        <th><?= sortLink('title', 'Training Title', $filter, $sort, $dir) ?></th>
                        <th>Role</th>
                        <th><?= sortLink('start_date', 'Start Date', $filter, $sort, $dir) ?></th>
                        <th><?= sortLink('end_date', 'End Date', $filter, $sort, $dir) ?></th>
                        <th><?= sortLink('hours', 'Hours', $filter, $sort, $dir) ?></th>
                        <th>Type</th>
                        <th>Conducted/Sponsored</th>
                        <th>Code</th>
                        <th>Status</th>
                        <th>Document</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($entries): ?>
                    <?php foreach ($entries as $e): ?>
                        <tr>
                            <td><?= htmlspecialchars($e['staff_name']) ?></td>
                            <td><?= htmlspecialchars($e['staff_email']) ?></td>
                            <td><?= htmlspecialchars($e['staff_type']) ?></td>
                            <td><?= htmlspecialchars($e['title']) ?></td>
                            <td><?= htmlspecialchars($e['role']) ?></td>
                            <td><?= htmlspecialchars($e['start_date']) ?></td>
                            <td><?= htmlspecialchars($e['end_date']) ?></td>
                            <td><?= htmlspecialchars($e['hours']) ?></td>
                            <td><?= htmlspecialchars($e['type']) ?></td>
                            <td><?= htmlspecialchars($e['institution']) ?></td>
                            <td><?= htmlspecialchars($e['unique_code']) ?></td>
                            <td><?= htmlspecialchars($e['status']) ?></td>
                            <td>
                                <?php if ($e['file_path']): ?>
                                    <a href="<?= htmlspecialchars($e['file_path']) ?>" target="_blank" class="btn btn-custom btn-sm">View</a>
                                <?php else: ?>
                                    No file
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="13" class="text-center">No records found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- ✅ Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav>
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php
                    $pageUrl = "hr_dashboard.php?page=$i";
                    if ($filter) $pageUrl .= "&filter=$filter";
                    if ($sort) $pageUrl .= "&sort=$sort&dir=$dir";
                    ?>
                    <li class="page-item <?= $page == $i ? 'active' : '' ?>">
                        <a class="page-link" href="<?= $pageUrl ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
        <?php endif; ?>

    </div>
</div>
</body>
</html>
