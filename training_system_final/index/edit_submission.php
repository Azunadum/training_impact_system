<?php
require_once '../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$email = $_GET['email'] ?? '';
$code = $_GET['code'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM training_entries WHERE staff_email = ? AND unique_code = ?");
$stmt->execute([$email, $code]);
$entry = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$entry) {
    die('Entry not found.');
}

$stmt = $pdo->prepare("SELECT * FROM supporting_docs WHERE training_entry_id = ?");
$stmt->execute([$entry['id']]);
$existing_docs = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token.');
    }

    $name = trim($_POST['name']);
    $title = trim($_POST['title']);
    $role = trim($_POST['role']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $hours = intval($_POST['hours']);
    $type = trim($_POST['type']);
    $institution = trim($_POST['institution']);

    if ($start_date > $end_date) {
        die("Start date cannot be after end date.");
    }
    if ($hours <= 0) {
        die("Number of hours must be greater than zero.");
    }

    $pdo->prepare("UPDATE training_entries SET staff_name = ?, title = ?, role = ?, start_date = ?, end_date = ?, hours = ?, type = ?, institution = ? WHERE id = ?")
        ->execute([$name, $title, $role, $start_date, $end_date, $hours, $type, $institution, $entry['id']]);

    if (!empty($_POST['delete_doc'])) {
        foreach ($_POST['delete_doc'] as $doc_id) {
            $del = $pdo->prepare("SELECT file_path FROM supporting_docs WHERE id = ?");
            $del->execute([$doc_id]);
            $file = $del->fetch(PDO::FETCH_ASSOC);
            if ($file && file_exists($file['file_path'])) {
                unlink($file['file_path']);
            }
            $pdo->prepare("DELETE FROM supporting_docs WHERE id = ?")->execute([$doc_id]);
        }
    }

    if (!empty($_FILES['docs']['name'][0])) {
        foreach ($_FILES['docs']['name'] as $index => $file_name) {
            if ($_FILES['docs']['error'][$index] !== UPLOAD_ERR_OK) continue;

            $safeName = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", basename($file_name));
            $newName = uniqid() . "_" . $safeName;
            $upload_path = "uploads/" . $newName;

            move_uploaded_file($_FILES['docs']['tmp_name'][$index], $upload_path);

            $pdo->prepare("INSERT INTO supporting_docs (training_entry_id, file_name, file_path) VALUES (?, ?, ?)")
                ->execute([$entry['id'], $safeName, $upload_path]);
        }
    }

    header("Location: view_submission.php?email=" . urlencode($email) . "&code=" . urlencode($code));
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Submission</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        .card { background: #ffffff; border-radius: 8px; box-shadow: 0 0 20px rgba(0,0,0,0.1); padding: 30px; }
        .header { background-color: #003366; color: #fff; padding: 20px; border-radius: 8px 8px 0 0; text-align: center; margin-bottom: 30px; }
        .btn-custom { background: #003366; color: #fff; border: none; }
        .btn-custom:hover { background: #0055aa; }
    </style>
</head>
<body class="bg-light">

<div class="container my-3">
    <a href="index.php" class="btn btn-outline-secondary">⬅️ Back to Home</a>
</div>

<div class="container my-3">
    <div class="card mx-auto" style="max-width: 700px;">
        <div class="header">
            <h3>Edit Submission</h3>
        </div>

        <form id="editForm" method="POST" enctype="multipart/form-data" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <div class="mb-3">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($entry['staff_name']) ?>" required>
                <div class="invalid-feedback">Please enter a valid name.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Title of Training/L&D</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($entry['title']) ?>" required>
                <div class="invalid-feedback">Please enter a valid title.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Role (Participant/Facilitator/Resource Speaker)</label>
                <input type="text" name="role" class="form-control" value="<?= htmlspecialchars($entry['role']) ?>" required>
                <div class="invalid-feedback">Please enter a valid role.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($entry['start_date']) ?>" required>
                <div class="invalid-feedback">Please enter a start date.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($entry['end_date']) ?>" required>
                <div class="invalid-feedback">Please enter an end date.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Number of Hours</label>
                <input type="number" name="hours" class="form-control" min="1" value="<?= htmlspecialchars($entry['hours']) ?>" required>
                <div class="invalid-feedback">Please enter a valid number of hours.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Type of Learning and Development (Managerial/Supervisory/Technical/Foundation)</label>
                <input type="text" name="type" class="form-control" value="<?= htmlspecialchars($entry['type']) ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Conducted/Sponsored by</label>
                <input type="text" name="institution" class="form-control" value="<?= htmlspecialchars($entry['institution']) ?>" required>
                <div class="invalid-feedback">Please enter the institution.</div>
            </div>

            <div class="mb-3">
                <label class="form-label">Current Files</label>
                <?php foreach ($existing_docs as $doc): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="delete_doc[]" value="<?= $doc['id'] ?>">
                        <label class="form-check-label"><?= htmlspecialchars($doc['file_name']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mb-3">
                <label class="form-label">Upload New Documents</label>
                <input type="file" id="docs" name="docs[]" class="form-control" accept=".pdf,.doc,.docx" multiple>
                <ul id="fileList" class="mt-2"></ul>
            </div>

            <button type="submit" class="btn btn-custom w-100">Save Changes</button>
        </form>
    </div>
</div>

<script>
(() => {
  'use strict'
  const form = document.getElementById('editForm')
  const fileInput = document.getElementById('docs')
  const fileList = document.getElementById('fileList')

  fileInput.addEventListener('change', () => {
    fileList.innerHTML = ''
    Array.from(fileInput.files).forEach((file, idx) => {
      const li = document.createElement('li')
      li.textContent = file.name + ' '
      const removeBtn = document.createElement('button')
      removeBtn.textContent = 'Remove'
      removeBtn.type = 'button'
      removeBtn.className = 'btn btn-sm btn-danger ms-2'
      removeBtn.onclick = () => {
        const dt = new DataTransfer()
        Array.from(fileInput.files).forEach((f, i) => {
          if (i !== idx) dt.items.add(f)
        })
        fileInput.files = dt.files
        fileInput.dispatchEvent(new Event('change'))
      }
      li.appendChild(removeBtn)
      fileList.appendChild(li)
    })
  })

  form.addEventListener('submit', event => {
    if (!form.checkValidity()) {
      event.preventDefault()
      event.stopPropagation()
    }
    form.classList.add('was-validated')
  })
})()
</script>
</body>
</html>
