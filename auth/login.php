<?php
session_start();
require_once '../config/db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $pass  = $_POST['password'];

  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($user && password_verify($pass, $user['password'])) {
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_nama'] = $user['nama'];
    header('Location: ../tasks/index.php');
    exit;
  } else {
    $error = 'Email atau password salah.';
  }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login — Memokakii</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
  <style>
    [data-bs-theme="light"] {
      --body-bg: #fdfaf4;
      --card-bg: rgba(255, 255, 255, 0.9);
      --text-main: #4a3b32;
      --border-color: #ebdccb;
      --accent-sage: #708238;
      --accent-sage-hover: #5b6b2e;
    }
    [data-bs-theme="dark"] {
      --body-bg: #1c1815;
      --card-bg: rgba(43, 36, 32, 0.85);
      --text-main: #f5ebe6;
      --border-color: rgba(92, 77, 66, 0.4);
      --accent-sage: #8fa453;
      --accent-sage-hover: #a5ba69;
    }
    body {
      background-color: var(--body-bg);
      color: var(--text-main);
      font-family: system-ui, -apple-system, sans-serif;
      transition: background-color 0.4s, color 0.4s;
    }
    .card {
      background-color: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 16px;
    }
    .form-control {
      color: var(--text-main) !important;
      background-color: transparent;
      border-color: var(--border-color) !important;
    }
    .form-control:focus {
      background-color: transparent;
      border-color: var(--accent-sage) !important;
      box-shadow: 0 0 0 0.25rem rgba(112, 130, 56, 0.25) !important;
    }
    .btn-custom-primary {
      background-color: var(--accent-sage);
      color: #fff;
      border: none;
    }
    .btn-custom-primary:hover {
      background-color: var(--accent-sage-hover);
      color: #fff;
    }
  </style>
</head>
<body class="d-flex align-items-center" style="min-height: 100vh;">
<div class="container">
  <div class="row justify-content-center">
    <div class="col-md-4 col-sm-10">
      <div class="text-center mb-4">
        <a href="../index.php" class="text-decoration-none h3 fw-bold" style="color: var(--accent-sage);">
          <i class="bi bi-layers-half"></i> Memokakii
        </a>
      </div>
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="card-title text-center mb-4 fw-bold">Sign In</h4>
          <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?= $error ?></div>
          <?php endif; ?>
          <form method="POST">
            <div class="mb-3">
              <label class="form-label small fw-semibold">Email</label>
              <input type="email" name="email" class="form-control" required style="border-radius: 8px;">
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Password</label>
              <input type="password" name="password" class="form-control" required style="border-radius: 8px;">
            </div>
            <button type="submit" class="btn btn-custom-primary w-100 py-2 mt-2 fw-semibold" style="border-radius: 8px;">Masuk</button>
          </form>
          <hr class="my-4" style="border-color: var(--border-color);">
          <p class="text-center mb-0 small text-muted">
            Belum punya akun? <a href="register.php" class="text-decoration-none fw-medium" style="color: var(--accent-sage);">Daftar di sini</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
  const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  document.documentElement.setAttribute('data-bs-theme', savedTheme);
</script>
</body>
</html>