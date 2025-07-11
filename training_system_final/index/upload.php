<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entry_id = intval($_POST['entry_id']);
    if (!$entry_id) die('Invalid entry ID');

    if (empty($_FILES['docs']['name'][0])) die("No files uploaded.");

    $allowed_exts = ['pdf', 'doc', 'docx'];
    foreach ($_FILES['docs']['name'] as $index => $file_name) {
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_exts)) die("Invalid file: $file_name");
        $tmpPath = $_FILES['docs']['tmp_name'][$index];
        $safeName = preg_replace("/[^a-zA-Z0-9_\.-]/", "_", basename($file_name));
        $newName = uniqid() . "_" . $safeName;
        $upload_path = "uploads/" . $newName;

        move_uploaded_file($tmpPath, $upload_path);

        $pdo->prepare("INSERT INTO supporting_docs (training_entry_id, file_name, file_path) VALUES (?, ?, ?)")
            ->execute([$entry_id, $safeName, $upload_path]);
    }

    header("Location: edit_submission.php?id=$entry_id&success=1");
}
else {
    die('Invalid request method.');
}   