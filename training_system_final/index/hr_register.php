<?php
require_once '../config/config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM hr_users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
        $message = "Email already exists.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO hr_users (name, email, password) VALUES (?, ?, ?)");
        $stmt->execute([$name, $email, $hashed]);
        $message = "HR registered!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register HR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <h3>Register HR</h3>
            <?php if ($message) echo "<div class='alert alert-info'>$message</div>"; ?>
            <form method="POST">
                <div class="mb-3"><label>Name</label><input type="text" name="name" class="form-control" required></div>
                <div class="mb-3"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
                <button type="submit" class="btn btn-primary w-100">Register</button>
            </form>
            <p class="text-center mt-3"><a href="hr_login.php">Back to Login</a></p>
        </div>
    </div>
</div>
</body>
</html>
