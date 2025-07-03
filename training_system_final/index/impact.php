<?php
require_once '../config/config.php';

$message = '';
$entry = null;

if (isset($_POST['lookup'])) {
    $email = trim($_POST['email']);
    $code = trim($_POST['unique_code']);

    $stmt = $pdo->prepare("SELECT * FROM training_entries WHERE staff_email = ? AND unique_code = ?");
    $stmt->execute([$email, $code]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$entry) {
        $message = "No matching training entry found!";
    }
}

if (isset($_POST['feedback_submit'])) {
    $training_id = (int)$_POST['training_id'];
    $rating = (int)$_POST['rating'];
    $comments = trim($_POST['comments']);

    $stmt = $pdo->prepare("INSERT INTO impact_assessments (training_entry_id, rating, comments) VALUES (?, ?, ?)");
    $stmt->execute([$training_id, $rating, $comments]);

    $pdo->prepare("UPDATE training_entries SET status = 'Completed' WHERE id = ?")->execute([$training_id]);

    $message = "Impact feedback submitted! Thank you.";
    $entry = null;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Impact Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h3 class="card-title mb-4">Impact Feedback</h3>
            <?php if ($message) echo "<div class='alert alert-info'>$message</div>"; ?>

            <?php if (!$entry): ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Training Entry Code</label>
                    <input type="text" name="unique_code" class="form-control" required>
                </div>
                <button type="submit" name="lookup" class="btn btn-primary w-100">Find My Training Entry</button>
            </form>
            <?php else: ?>
            <form method="POST">
                <input type="hidden" name="training_id" value="<?= htmlspecialchars($entry['id']) ?>">
                <div class="mb-3">
                    <label class="form-label">Training Title</label>
                    <input type="text" value="<?= htmlspecialchars($entry['title']) ?>" class="form-control" disabled>
                </div>
                <div class="mb-3">
                    <label class="form-label">Rating (1-5)</label>
                    <input type="number" name="rating" min="1" max="5" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Comments</label>
                    <textarea name="comments" class="form-control"></textarea>
                </div>
                <button type="submit" name="feedback_submit" class="btn btn-success w-100">Submit Feedback</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
