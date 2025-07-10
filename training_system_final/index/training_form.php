<?php
require_once '../config/config.php';

// Start session for CSRF
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if missing
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$message = '';
$unique_code = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $staff_type = $_POST['staff_type'];
    $title = trim($_POST['title']);
    $role = trim($_POST['role']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $hours = intval($_POST['hours']);
    $type = trim($_POST['type']);
    $institution = trim($_POST['institution']);

    // Validations
    if ($start_date > $end_date) {
        die("Start date cannot be after end date.");
    }
    if ($hours <= 0) {
        die("Number of hours must be greater than zero.");
    }
    if (empty($_FILES['docs']['name'][0])) {
        die("At least one file must be uploaded.");
    }

    // ✅ Validate files first
    $allowed_exts = ['pdf', 'doc', 'docx'];
    foreach ($_FILES['docs']['name'] as $index => $file_name) {
        if ($_FILES['docs']['error'][$index] !== UPLOAD_ERR_OK) {
            die("Upload error for file: $file_name");
        }
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts)) {
            die("Invalid file extension for file: $file_name");
        }
        $tmpPath = $_FILES['docs']['tmp_name'][$index];
        $mime = mime_content_type($tmpPath);
        if (!in_array($mime, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ])) {
            die("Invalid file type for file: $file_name");
        }
    }

    // ✅ Only insert if files valid
    $stmt = $pdo->prepare("INSERT INTO training_entries 
        (staff_name, staff_email, staff_type, title, role, start_date, end_date, hours, type, institution) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $staff_type, $title, $role, $start_date, $end_date, $hours, $type, $institution]);

    $entry_id = $pdo->lastInsertId();
    $unique_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    $pdo->prepare("UPDATE training_entries SET unique_code = ? WHERE id = ?")
        ->execute([$unique_code, $entry_id]);

    // ✅ Upload files now
    foreach ($_FILES['docs']['name'] as $index => $file_name) {
        $tmpPath = $_FILES['docs']['tmp_name'][$index];
        $safeName = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", basename($file_name));
        $newName = uniqid() . "_" . $safeName;
        $upload_path = "uploads/" . $newName;

        move_uploaded_file($tmpPath, $upload_path);

        $pdo->prepare("INSERT INTO supporting_docs (training_entry_id, file_name, file_path) VALUES (?, ?, ?)")
            ->execute([$entry_id, $safeName, $upload_path]);
    }

    $message = "✅ Submitted successfully!<br>Your <strong>Training Entry Code</strong> is: <strong>$unique_code</strong>.<br>
    Please save it for your 6-Month Impact Assessment.<br>
    <a href='view_submission.php?email=" . urlencode($email) . "&code=" . urlencode($unique_code) . "' class='btn btn-custom mt-3'>View My Submission</a>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Training Input Form</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .card { background: #ffffff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); padding: 30px; }
        .header { background-color: #003366; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; margin-bottom: 30px; }
        .btn-custom { background: #003366; color: #fff; border: none; }
        .btn-custom:hover { background: #0055aa; }
        .modal-header { background-color: #003366; color: #fff; }
    </style>
</head>
<body class="bg-light">

<div class="container my-3">
    <a href="index.php" class="btn btn-outline-secondary">⬅️ Back to Home</a>
</div>

<div class="container my-3">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="header">
            <h3>Staff Training Details Form</h3>
        </div>
        <form id="trainingForm" method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" required>
                <div class="invalid-feedback">Please enter a valid name.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
                <div class="invalid-feedback">Please enter a valid email address.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Staff Type</label>
                <select name="staff_type" class="form-select" required>
                    <option value="" selected disabled>Select type</option>
                    <option value="COS">COS</option>
                    <option value="Permanent">Permanent</option>
                </select>
                <div class="invalid-feedback">Please select a staff type.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Title of Training/L&D</label>
                <input type="text" name="title" class="form-control" required>
                <div class="invalid-feedback">Please enter a valid title.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Role (Participant/Facilitator/Resource Speaker)</label>
                <input type="text" name="role" class="form-control" required>
                <div class="invalid-feedback">Please enter a valid role.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" required>
                <div class="invalid-feedback">Please enter a start date.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" required>
                <div class="invalid-feedback">Please enter an end date.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Number of Hours</label>
                <input type="number" name="hours" class="form-control" min="1" required>
                <div class="invalid-feedback">Please enter a valid number of hours.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Type of Learning and Development (Managerial/Supervisory/Technical/Foundation)</label>
                <input type="text" name="type" class="form-control">
            </div>

            <div class="mb-3">
                <label class="form-label">Conducted/Sponsored by</label>
                <input type="text" name="institution" class="form-control" required>
                <div class="invalid-feedback">Please enter the institution.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload Documents</label>
                <input type="file" id="docs" name="docs[]" class="form-control" accept=".pdf,.doc,.docx" multiple required>
                <div class="invalid-feedback">Please upload at least one document.</div>
            </div>

            <button type="submit" class="btn btn-custom w-100">Submit</button>
        </form>
    </div>
</div>

<?php if ($message): ?>
<div class="modal fade show" id="successModal" tabindex="-1" aria-modal="true" style="display:block;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Success</h5>
      </div>
      <div class="modal-body"><?= $message ?></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-custom" onclick="window.location.href='index.php'">Back to Home</button>
      </div>
    </div>
  </div>
</div>
<div class="modal-backdrop fade show"></div>
<?php endif; ?>

<script>
(() => {
  'use strict'
  const form = document.getElementById('trainingForm')
  const fileInput = document.getElementById('docs')

  form.addEventListener('submit', event => {
    if (fileInput.files.length === 0) {
      fileInput.classList.add('is-invalid')
      event.preventDefault()
      event.stopPropagation()
    } else {
      fileInput.classList.remove('is-invalid')
    }

    if (!form.checkValidity()) {
      event.preventDefault()
      event.stopPropagation()
    }
    form.classList.add('was-validated')
  })

  form.querySelectorAll('input, select').forEach(field => {
    field.addEventListener('blur', () => {
      if (!form.classList.contains('was-validated')) {
        if (!field.checkValidity()) {
          field.classList.add('is-invalid')
        } else {
          field.classList.remove('is-invalid')
        }
      }
    })
  })
})()
</script>

</body>
</html>
