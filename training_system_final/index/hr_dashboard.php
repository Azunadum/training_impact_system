<?php
require_once '../config/config.php';

if (!isset($_SESSION['hr_id'])) die("Access denied.");

// Determine filter if set
$filter = '';
$where = '';
$params = [];

if (isset($_GET['filter']) && in_array($_GET['filter'], ['COS', 'Permanent'])) {
    $filter = $_GET['filter'];
    $where = "WHERE te.staff_type = ?";
    $params[] = $filter;
}

// Prepare query with filter if needed
$stmt = $pdo->prepare("
    SELECT te.*, sd.file_name, sd.file_path
    FROM training_entries te
    LEFT JOIN supporting_docs sd ON te.id = sd.training_entry_id
    $where
    ORDER BY te.id DESC
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
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .dashboard-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .header {
            background-color: #003366;
            color: #fff;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
            margin-bottom: 30px;
        }
        .btn-custom {
            background: #003366;
            color: #fff;
            border: none;
        }
        .btn-custom:hover {
            background: #0055aa;
        }
        .btn-download {
            background: #0055aa;
            color: #fff;
            border: none;
        }
        .btn-download:hover {
            background: #0077cc;
        }
        .btn-filter {
            background: #0055aa;
            color: #fff;
            border: none;
        }
        .btn-filter:hover {
            background: #0077cc;
        }
    </style>
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="dashboard-card">
        <div class="header">
            <h2>Training Impact Assessment</h2>
        </div>

        <div class="mb-4 d-flex flex-wrap justify-content-between gap-2">
            <div class="btn-group" role="group">
                <a href="hr_dashboard.php" class="btn btn-filter <?= $filter == '' ? 'active' : '' ?>">All</a>
                <a href="hr_dashboard.php?filter=COS" class="btn btn-filter <?= $filter == 'COS' ? 'active' : '' ?>">COS Only</a>
                <a href="hr_dashboard.php?filter=Permanent" class="btn btn-filter <?= $filter == 'Permanent' ? 'active' : '' ?>">Permanent Only</a>
            </div>
            <div class="d-flex gap-2">
                <a href="export.php" class="btn btn-download">Download CSV</a>
                <a href="hr_logout.php" class="btn btn-custom">Logout</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Name of Trainee</th>
                        <th>Email</th>
                        <th>Staff</th>
                        <th>Training Title</th>
                        <th>Role</th>
                        <th>Dates</th>
                        <th>Hours</th>
                        <th>Training Type</th>
                        <th>Conducted/Sponsored</th>
                        <th>Training Entry Code</th>
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
                                <td><?= htmlspecialchars($e['start_date']) ?> to <?= htmlspecialchars($e['end_date']) ?></td>
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
                        <tr><td colspan="12" class="text-center">No records found for this filter.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
