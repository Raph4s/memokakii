<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Memokakii</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
  <style>
    /* Theme Color Mapping */
    [data-bs-theme="light"] {
      --body-bg: #fdfaf4; /* Soft Cream */
      --card-bg: rgba(255, 255, 255, 0.9);
      --text-main: #4a3b32; /* Warm Brown */
      --text-muted: #706053;
      --border-color: #ebdccb;
      --accent-sage: #708238; /* Sage Green */
      --accent-sage-hover: #5b6b2e;
      --wave-svg: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 250" preserveAspectRatio="none"><path d="M0,60 Q250,20 500,60 T1000,60 L1000,250 L0,250 Z" fill="rgba(112, 130, 56, 0.12)"/></svg>');
      --player-bg: rgba(255, 255, 255, 0.95);
      --social-bg: #4a3b32;
      --social-color: #fdfaf4;
    }
    
    [data-bs-theme="dark"] {
      --body-bg: #1c1815; /* Charcoal Warm Brown */
      --card-bg: rgba(43, 36, 32, 0.85);
      --text-main: #f5ebe6; /* Cream White text */
      --text-muted: #bdafa6;
      --border-color: rgba(92, 77, 66, 0.4);
      --accent-sage: #8fa453; /* Light Sage Green */
      --accent-sage-hover: #a5ba69;
      --wave-svg: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 250" preserveAspectRatio="none"><path d="M0,60 Q250,20 500,60 T1000,60 L1000,250 L0,250 Z" fill="rgba(143, 164, 83, 0.15)"/></svg>');
      --player-bg: rgba(43, 36, 32, 0.95);
      --social-bg: #f5ebe6;
      --social-color: #1c1815;
    }

    body {
      background-color: var(--body-bg);
      color: var(--text-main);
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      transition: background-color 0.5s ease, color 0.5s ease;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      position: relative;
      overflow-x: hidden;
    }

    .main-content { flex: 1 0 auto; }
    .navbar, .card, .player-container { backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); }
    .navbar { box-shadow: 0 1px 3px rgba(0,0,0,0.05); background-color: var(--card-bg) !important; border-color: var(--border-color) !important; }
    
    .text-custom-primary { color: var(--accent-sage) !important; }
    .btn-custom-primary { background-color: var(--accent-sage); color: #fff; border: none; transition: background-color 0.2s; }
    .btn-custom-primary:hover { background-color: var(--accent-sage-hover); color: #fff; }

    .btn-outline-custom { border: 1px solid var(--border-color); color: var(--text-main); background: transparent; }
    .btn-outline-custom:hover { background-color: var(--border-color); color: var(--text-main); }

    .hero-section { padding: 8rem 0 4rem 0; }
    
    .card {
      background-color: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(74, 59, 50, 0.03);
    }

    /* Background Wave Animation */
    .ocean { height: 280px; width: 100%; position: fixed; bottom: 0; left: 0; z-index: -1; pointer-events: none; }
    .wave { background-image: var(--wave-svg); position: absolute; bottom: 0; left: 0; width: 100%; height: 100%; background-repeat: repeat-x; background-size: 1000px 100%; animation: flawlessLoop 22s linear infinite; }
    @keyframes flawlessLoop { 0% { background-position-x: 0px; } 100% { background-position-x: -1000px; } }

    /* Footer */
    .site-footer { flex-shrink: 0; padding: 2rem 0; margin-top: 4rem; text-align: center; }
    .social-icon-btn {
      display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 50%;
      background-color: var(--social-bg); color: var(--social-color) !important; font-size: 1.25rem; margin: 0 6px; text-decoration: none; transition: transform 0.2s ease;
    }
    .social-icon-btn:hover { transform: scale(1.08); }
    .copyright-text { color: var(--text-muted); font-size: 0.95rem; margin-top: 10px; }
  </style>
</head>
<body>

<div class="ocean"><div class="wave"></div></div>

<div class="main-content">
  <nav class="navbar navbar-expand border-bottom mb-4">
    <div class="container">
      <a class="navbar-brand fw-bold text-custom-primary d-flex align-items-center gap-2" href="index.php">
        <i class="bi bi-layers-half"></i> Memokakii
      </a>
      <ul class="navbar-nav ms-auto align-items-center gap-2">
        <li class="nav-item me-2">
          <button id="themeToggle" class="btn btn-link nav-link text-secondary p-1 fs-5" style="cursor: pointer;">
            <i id="themeIcon" class="bi bi-moon-stars-fill"></i>
          </button>
        </li>
        <?php if (isset($_SESSION['user_id'])): ?>
          <li class="nav-item">
            <a class="btn btn-sm btn-custom-primary px-3 rounded-pill shadow-sm" href="tasks/index.php">Dashboard</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="btn btn-sm btn-outline-custom px-3 rounded-pill me-1" href="auth/login.php">Login</a>
          </li>
          <li class="nav-item">
            <a class="btn btn-sm btn-custom-primary px-3 rounded-pill shadow-sm" href="auth/register.php">Daftar</a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </nav>

  <div class="container hero-section">
    <div class="row align-items-center g-5">
      <div class="col-lg-6 text-center text-lg-start">
        <span class="badge rounded-pill px-3 py-2 mb-3 bg-custom-subtle fw-semibold" style="background-color: rgba(112, 130, 56, 0.15); color: var(--accent-sage);">✨ Site ManageMaxxing</span>
        <h1 class="display-4 fw-bold lh-sm mb-3">Atur Waktu dan Prioritas Tanpa Batas</h1>
        <p class="lead text-muted mb-4">No.1(000) Minimalist Productivity App</p>
        <div class="d-flex flex-wrap justify-content-center justify-content-lg-start gap-3">
          <?php if (isset($_SESSION['user_id'])): ?>
            <a href="tasks/index.php" class="btn btn-lg btn-custom-primary px-4 shadow" style="border-radius: 12px;">Masuk ke Dashboard <i class="bi bi-arrow-right ms-1"></i></a>
          <?php else: ?>
            <a href="auth/register.php" class="btn btn-lg btn-custom-primary px-4 shadow" style="border-radius: 12px;">Mulai Sekarang</a>
            <a href="auth/login.php" class="btn btn-lg btn-outline-custom px-4" style="border-radius: 12px;">Kelola Tugas</a>
          <?php endif; ?>
        </div>
      </div>
      
      <div class="col-lg-6">
        <div class="card p-4 text-center">
          <div class="card-body py-5">
            <div class="text-custom-primary mb-4">
              <i class="bi bi-check2-circle" style="font-size: 4.5rem;"></i>
            </div>
            <h3 class="fw-bold mb-2">No Stress, Max Focus</h3>
            <p class="text-muted mx-auto mb-0" style="max-width: 380px;">Mengurutkan tugas berdasarkan prioritas, atur deadline, dan enjoy progress bar yang bertambah :D.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<footer class="site-footer">
  <div class="container">
    <div class="mb-2">
      <a href="#" class="social-icon-btn"><i class="bi bi-twitter-x"></i></a>
      <a href="#" class="social-icon-btn"><i class="bi bi-youtube"></i></a>
      <a href="https://www.instagram.com/raffa_ramadhika/" class="social-icon-btn"><i class="bi bi-instagram"></i></a>
    </div>
    <p class="copyright-text">© 2026 Memokakii</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Dark Mode button
  const themeToggle = document.getElementById('themeToggle');
  const themeIcon = document.getElementById('themeIcon');
  const htmlTag = document.documentElement;

  const savedTheme = localStorage.getItem('theme');
  if (savedTheme) {
    htmlTag.setAttribute('data-bs-theme', savedTheme);
    updateIcon(savedTheme);
  }

  themeToggle.addEventListener('click', () => {
    const currentTheme = htmlTag.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    htmlTag.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateIcon(newTheme);
  });

  function updateIcon(theme) {
    if (theme === 'dark') {
      themeIcon.className = 'bi bi-sun-fill text-warning';
    } else {
      themeIcon.className = 'bi bi-moon-stars-fill text-secondary';
    }
  }
</script>
</body>
</html>