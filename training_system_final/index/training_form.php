<?php
require_once '../config/config.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $staff_type = $_POST['staff_type'];
    $title = trim($_POST['title']);
    $role = trim($_POST['role']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $hours = $_POST['hours'];
    $type = trim($_POST['type']);
    $institution = trim($_POST['institution']);

    $stmt = $pdo->prepare("INSERT INTO training_entries 
        (staff_name, staff_email, staff_type, title, role, start_date, end_date, hours, type, institution) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$name, $email, $staff_type, $title, $role, $start_date, $end_date, $hours, $type, $institution]);

    $entry_id = $pdo->lastInsertId();
    $unique_code = strtoupper(substr(md5(uniqid(rand(), true)), 0, 8));

    $pdo->prepare("UPDATE training_entries SET unique_code = ? WHERE id = ?")
        ->execute([$unique_code, $entry_id]);

    if (isset($_FILES['doc']) && $_FILES['doc']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['doc']['name'], PATHINFO_EXTENSION);
        $newName = uniqid() . "." . $ext;
        $upload_path = "uploads/" . $newName;
        move_uploaded_file($_FILES['doc']['tmp_name'], $upload_path);
        $pdo->prepare("INSERT INTO supporting_docs (training_entry_id, file_name, file_path) VALUES (?, ?, ?)")
            ->execute([$entry_id, $newName, $upload_path]);
    }

   $message = "Submitted successfully! Your Training Entry Code is: <strong>$unique_code</strong>. Please save it for future reference.";
   $message .= "<br><a href='view_submission.php' class='btn btn-link mt-2'>Click here to view your submission anytime.</a>";

}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Training Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body">
            <h3 class="card-title mb-4">Staff Training Details</h3>
            <?php if ($message) echo "<div class='alert alert-success'>$message</div>"; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label">Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Staff Type</label>
                    <select name="staff_type" class="form-select" required>
                        <option value="COS">COS</option>
                        <option value="Permanent">Permanent</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Training Title</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <input type="text" name="role" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Start Date</label>
                    <input type="date" name="start_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">End Date</label>
                    <input type="date" name="end_date" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Number of Hours</label>
                    <input type="number" name="hours" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Training Type</label>
                    <input type="text" name="type" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Conducted/Sponsored by</label>
                    <input type="text" name="institution" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Upload Document</label>
                    <input type="file" name="doc" class="form-control" accept=".pdf,.doc,.docx">
                </div>
                <button type="submit" class="btn btn-primary w-100">Submit</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
