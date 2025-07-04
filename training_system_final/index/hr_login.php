<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM hr_users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['hr_id'] = $user['id'];
        header('Location: hr_dashboard.php');
        exit;
    } else {
        $message = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>HR Login</title>
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
        a {
            color: #003366;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body class="bg-light">
<div class="container my-5">
    <div class="card mx-auto" style="max-width: 400px;">
        <div class="header">
            <h3>HR Login</h3>
        </div>
        <?php if (isset($message)) echo "<div class='alert alert-danger'>$message</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-custom w-100">Login</button>
        </form>
        <p class="text-center mt-3">
            <a href="hr_register.php">Register a new HR user</a>
        </p>
    </div>
</div>
</body>
</html>
