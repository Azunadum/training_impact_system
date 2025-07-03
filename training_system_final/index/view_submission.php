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
    <title>View Your Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h3 class="card-title mb-4">View Your Training Details</h3>
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
                    <button type="submit" class="btn btn-primary w-100">View Submission</button>
                </form>
            <?php else: ?>
                <h5 class="mb-3">Training Details</h5>
                <ul class="list-group mb-3">
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
                    <ul class="list-group">
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
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
