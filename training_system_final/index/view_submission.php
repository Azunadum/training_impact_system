<?php
require_once '../config/config.php';
$message = '';
$entry = null;
$docs = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $code = trim($_POST['code']);

    $stmt = $pdo->prepare("SELECT * FROM training_entries WHERE staff_email = ? AND unique_code = ?");
    $stmt->execute([$email, $code]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        $message = "No record found for that email and code.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM supporting_docs WHERE training_entry_id = ?");
        $stmt->execute([$entry['id']]);
        $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submitted Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .card {
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
    </style>
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="header">
            <h3>View Submitted Training Details</h3>
        </div>

        <?php if ($message) echo "<div class='alert alert-danger'>$message</div>"; ?>

        <?php if (!$entry): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Training Entry Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-custom w-100">View Submission</button>
            </form>
        <?php else: ?>
            <h5 class="mb-3">Training Details</h5>
            <ul class="list-group mb-4">
                <li class="list-group-item"><strong>Name:</strong> <?= htmlspecialchars($entry['staff_name']) ?></li>
                <li class="list-group-item"><strong>Email:</strong> <?= htmlspecialchars($entry['staff_email']) ?></li>
                <li class="list-group-item"><strong>Staff (COS/Permanent):</strong> <?= htmlspecialchars($entry['staff_type']) ?></li>
                <li class="list-group-item"><strong>Title of the Training:</strong> <?= htmlspecialchars($entry['title']) ?></li>
                <li class="list-group-item"><strong>Role:</strong> <?= htmlspecialchars($entry['role']) ?></li>
                <li class="list-group-item"><strong>Inclusive Dates:</strong> <?= htmlspecialchars($entry['start_date']) ?> to <?= htmlspecialchars($entry['end_date']) ?></li>
                <li class="list-group-item"><strong>Number of Hours:</strong> <?= htmlspecialchars($entry['hours']) ?></li>
                <li class="list-group-item"><strong>Type of Learning:</strong> <?= htmlspecialchars($entry['type']) ?></li>
                <li class="list-group-item"><strong>Conducted/Sponsored by:</strong> <?= htmlspecialchars($entry['institution']) ?></li>
                <li class="list-group-item"><strong>Training Entry Code:</strong> <?= htmlspecialchars($entry['unique_code']) ?></li>
            </ul>

            <h5 class="mb-3">Uploaded Documents</h5>
            <?php if ($docs): ?>
                <ul class="list-group mb-3">
                    <?php foreach ($docs as $doc): ?>
                        <li class="list-group-item">
                            <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">
                                <?= htmlspecialchars($doc['file_name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No supporting documents found.</p>
            <?php endif; ?>

            <a href="index.php" class="btn btn-custom w-100">Back to Portal</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
