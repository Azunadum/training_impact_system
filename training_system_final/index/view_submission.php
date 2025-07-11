<?php
require_once '../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$entry = null;
$files = [];
$error = '';

$email = isset($_GET['email']) ? trim($_GET['email']) : '';
$code  = isset($_GET['code']) ? trim($_GET['code']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $code  = trim($_POST['code']);
}

if ($email && $code) {
    // ✅ FIX: Get only one training entry
    $stmt = $pdo->prepare("SELECT * FROM training_entries WHERE staff_email = ? AND unique_code = ?");
    $stmt->execute([$email, $code]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($entry) {
        // ✅ Then get all supporting docs for this entry
        $stmt = $pdo->prepare("SELECT * FROM supporting_docs WHERE training_entry_id = ?");
        $stmt->execute([$entry['id']]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $error = "No entry found with this email and code.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); padding: 30px; }
        .header { background: #003366; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; margin-bottom: 30px; }
        .btn-custom { background: #003366; color: #fff; border: none; }
    </style>
</head>
<body class="bg-light">

<div class="container my-4">
    <a href="index.php" class="btn btn-outline-secondary mb-3">⬅️ Back to Home</a>

    <div class="card mx-auto" style="max-width: 800px;">
        <div class="header">
            <h3>Training Entry Details</h3>
        </div>

        <?php if ($entry): ?>
            <p><strong>Name:</strong> <?= htmlspecialchars($entry['staff_name']) ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($entry['staff_email']) ?></p>
            <p><strong>Staff Type:</strong> <?= htmlspecialchars($entry['staff_type']) ?></p>
            <p><strong>Title of Training/L&D:</strong> <?= htmlspecialchars($entry['title']) ?></p>
            <p><strong>Role:</strong> <?= htmlspecialchars($entry['role']) ?></p>
            <p><strong>Dates:</strong> <?= htmlspecialchars($entry['start_date']) ?> to <?= htmlspecialchars($entry['end_date']) ?></p>
            <p><strong>Hours:</strong> <?= htmlspecialchars($entry['hours']) ?></p>
            <p><strong>Type of Learning and Development:</strong> <?= htmlspecialchars($entry['type']) ?></p>
            <p><strong>Conducted/Sponsored by:</strong> <?= htmlspecialchars($entry['institution']) ?></p>
            <p><strong>Training Entry Code:</strong> <?= htmlspecialchars($entry['unique_code']) ?></p>

            <?php if ($files): ?>
                <?php foreach ($files as $file): ?>
                    <p><a href="<?= htmlspecialchars($file['file_path']) ?>" target="_blank" class="btn btn-custom">View Supporting Document</a></p>
                <?php endforeach; ?>
            <?php endif; ?>

            <a href="edit_submission.php?email=<?= urlencode($email) ?>&code=<?= urlencode($code) ?>" class="btn btn-custom mt-3">Edit My Submission</a>

        <?php elseif (!$email || !$code): ?>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Training Entry Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-custom">View Details</button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger"><?= $error ?: "No entry found." ?></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
