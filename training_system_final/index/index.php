<!DOCTYPE html>
<html lang="en">
<head>
  <title>Training Impact Assessment Portal</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #f8f9fa;
      font-family: Arial, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .landing-card {
      background: #ffffff;
      border-radius: 8px;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
      padding: 40px 30px;
      max-width: 600px;
      width: 100%;
      margin: auto; /* centers it vertically with flex */
    }

    .header {
      background-color: #003366;
      color: #fff;
      padding: 20px;
      border-radius: 8px 8px 0 0;
      text-align: center;
    }

    .btn-custom {
      background: #003366;
      color: #fff;
      border: none;
      transition: background 0.3s ease;
    }

    .btn-custom:hover {
      background: #0055aa;
    }

    .hr-login-link {
      position: absolute;
      top: 20px;
      right: 30px;
      font-size: 0.9rem;
    }

    .hr-login-link a {
      color: #003366;
      text-decoration: none;
      font-weight: bold;
    }

    .hr-login-link a:hover {
      text-decoration: underline;
    }

    .form-label {
      font-weight: 500;
    }

    hr {
      border-top: 2px solid #003366;
      opacity: 1;
    }

    /* Input focus effect for modern feel */
    input:focus {
      border-color: #0055aa;
      box-shadow: 0 0 0 0.2rem rgba(0,85,170,.25);
    }

    footer {
      text-align: center;
      font-size: 0.85rem;
      color: #555;
      padding: 15px 0;
    }
  </style>
</head>

<body>
  <!-- HR Login link top right -->
  <div class="hr-login-link">
    <a href="hr_login.php">HR Login</a>
  </div>

  <!-- Landing Card -->
  <div class="landing-card">
    <div class="header">
      <h2>Training Impact Assessment Portal</h2>
    </div>

    <!-- Find Submission -->
    <div class="mt-4">
      <h5 class="mb-3">View My Submission</h5>
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

    <!-- Clear CTAs -->
    <div class="d-grid gap-3">
      <a href="training_form.php" class="btn btn-custom btn-lg">Training Input Form</a>
      <a href="impact.php" class="btn btn-custom btn-lg">Submit Impact Assessment</a>
    </div>
  </div>

  <!-- Tiny footer -->
  <footer>
    &copy; <?php echo date('Y'); ?> DOST-X Training System &middot; Version 1.0
  </footer>

</body>
</html>
