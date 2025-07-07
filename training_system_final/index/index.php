<!DOCTYPE html>
<html>
<head>
    <title>Training Impact Assessment Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            position: relative;
            min-height: 100vh;
        }
        .landing-card {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 40px;
        }
        .btn-custom {
            background: #003366;
            color: #fff;
            border: none;
        }
        .btn-custom:hover {
            background: #0055aa;
        }
        .header {
            background-color: #003366;
            color: #fff;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }
        .hr-login-link {
            position: absolute;
            top: 20px;
            right: 40px;
            font-size: 0.9rem;
        }
        .hr-login-link a {
            color: #003366;
            text-decoration: none;
        }
        .hr-login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="hr-login-link">
    <a href="hr_login.php">HR Login</a>
</div>

<div class="container py-5">
    <div class="landing-card mx-auto" style="max-width: 600px;">
        <div class="header">
            <h2>Training Impact Assessment Portal</h2>
        </div>

        <div class="mt-4">
            <h5>View My Submission</h5>
            <form method="POST" action="view_submission.php" class="row g-3 mb-4">
                <div class="col-12">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Training Entry Code</label>
                    <input type="text" name="code" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-custom w-100">Find Training Entry</button>
                </div>
            </form>
        </div>

        <hr class="my-4">

        <div class="d-grid gap-3">
            <a href="training_form.php" class="btn btn-custom btn-lg">Training Input Form</a>
            <a href="impact.php" class="btn btn-custom btn-lg">Submit Impact Assessment</a>
        </div>
    </div>
</div>
</body>
</html>
