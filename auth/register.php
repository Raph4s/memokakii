<?php
session_start();
if (isset($_SESSION['user_id'])) { header('Location: ../tasks/index.php'); exit; }
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nama  = trim($_POST['nama']);
  $email = trim($_POST['email']);
  $pass  = $_POST['password'];
  $pass2 = $_POST['password_confirm'];

  if (!$nama || !$email || !$pass) {
    $error = 'Semua field wajib diisi.';
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = 'Format email tidak valid.';
  } elseif (strlen($pass) < 6) {
    $error = 'Password minimal 6 karakter.';
  } elseif ($pass !== $pass2) {
    $error = 'Konfirmasi password tidak cocok.';
  } else {
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
      $error = 'Email sudah terdaftar.';
    } else {
      $hashed = password_hash($pass, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (nama, email, password) VALUES (?, ?, ?)");
      $stmt->execute([$nama, $email, $hashed]);
      $success = 'Pendaftaran berhasil! Silakan login.';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Akun — Memokakii</title>
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
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-md-4 col-sm-10">
      <div class="text-center mb-4">
        <a href="../index.php" class="text-decoration-none h3 fw-bold" style="color: var(--accent-sage);">
          <i class="bi bi-layers-half"></i> Memokakii
        </a>
      </div>
      <div class="card shadow-sm">
        <div class="card-body p-4">
          <h4 class="card-title text-center mb-4 fw-bold">Buat Akun</h4>
          
          <?php if ($error): ?>
            <div class="alert alert-danger py-2 small"><?= $error ?></div>
          <?php endif; ?>
          <?php if ($success): ?>
            <div class="alert alert-success py-2 small"><?= $success ?></div>
          <?php endif; ?>

          <form method="POST" id="formRegister" novalidate>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Nama Lengkap</label>
              <input type="text" name="nama" id="nama" class="form-control" required style="border-radius: 8px;">
              <div class="invalid-feedback">Nama tidak boleh kosong.</div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Email</label>
              <input type="email" name="email" id="email" class="form-control" required style="border-radius: 8px;">
              <div class="invalid-feedback">Format email tidak valid.</div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Password</label>
              <input type="password" name="password" id="password" class="form-control" required style="border-radius: 8px;">
              <div class="invalid-feedback">Password minimal 6 karakter.</div>
            </div>
            <div class="mb-3">
              <label class="form-label small fw-semibold">Konfirmasi Password</label>
              <input type="password" name="password_confirm" id="password_confirm" class="form-control" required style="border-radius: 8px;">
              <div class="invalid-feedback">Password tidak cocok.</div>
            </div>
            <button type="submit" class="btn btn-custom-primary w-100 py-2 mt-2 fw-semibold" style="border-radius: 8px;">Daftar Akun</button>
          </form>
          <hr class="my-4" style="border-color: var(--border-color);">
          <p class="text-center mb-0 small text-muted">
            Sudah punya akun? <a href="login.php" class="text-decoration-none fw-medium" style="color: var(--accent-sage);">Login</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
  document.documentElement.setAttribute('data-bs-theme', savedTheme);

  // Client side validation
  document.getElementById('formRegister').addEventListener('submit', function(e) {
    let valid = true;
    const nama = document.getElementById('nama');
    if (nama.value.trim() === '') { nama.classList.add('is-invalid'); valid = false; } else { nama.classList.remove('is-invalid'); }

    const email = document.getElementById('email');
    const emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRe.test(email.value)) { email.classList.add('is-invalid'); valid = false; } else { email.classList.remove('is-invalid'); }

    const pass = document.getElementById('password');
    if (pass.value.length < 6) { pass.classList.add('is-invalid'); valid = false; } else { pass.classList.remove('is-invalid'); }

    const pass2 = document.getElementById('password_confirm');
    if (pass2.value !== pass.value || pass2.value === '') { pass2.classList.add('is-invalid'); valid = false; } else { pass2.classList.remove('is-invalid'); }

    if (!valid) e.preventDefault();
  });
</script>
</body>
</html>