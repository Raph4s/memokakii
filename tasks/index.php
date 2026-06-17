<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/db.php';

$uid    = $_SESSION['user_id'];
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$page   = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit  = 5;
$offset = ($page - 1) * $limit;

// Total rows untuk pagination
$stmtCount = $pdo->prepare("
  SELECT COUNT(*) FROM tasks t
  LEFT JOIN kategori k ON t.kategori_id = k.id
  WHERE t.user_id = ? AND (t.judul LIKE ? OR k.nama LIKE ?)
");
$stmtCount->execute([$uid, "%$search%", "%$search%"]);
$total = $stmtCount->fetchColumn();
$totalPage = ceil($total / $limit);

// Query utama
$stmt = $pdo->prepare("
  SELECT t.*, k.nama AS kategori, k.warna,
         label_prioritas(t.prioritas) AS prioritas_label
  FROM tasks t
  LEFT JOIN kategori k ON t.kategori_id = k.id
  WHERE t.user_id = ? AND (t.judul LIKE ? OR k.nama LIKE ?)
  ORDER BY 
    FIELD(t.prioritas,'high','medium','low'),
    t.deadline ASC
  LIMIT $limit OFFSET $offset
");
$stmt->execute([$uid, "%$search%", "%$search%"]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil semua daftar kategori milik user untuk pilihan di Modal
$cats = $pdo->query("SELECT * FROM kategori")->fetchAll(PDO::FETCH_ASSOC);

// Persentase selesai
$stmtPct = $pdo->prepare("SELECT persen_selesai(?) AS pct");
$stmtPct->execute([$uid]);
$pct = round($stmtPct->fetchColumn());

// Overdue dari view
$overdue = $pdo->prepare("SELECT COUNT(*) FROM v_task_overdue WHERE user_id = ?");
$overdue->execute([$uid]);
$overdueCount = $overdue->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard — TodoApp</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
  
  <style>
    /* Palette: Soft Cream (#fdfaf4), Sage Green (#708238), Warm Brown (#4a3b32) */
    [data-bs-theme="light"] {
      --body-bg: #fdfaf4; 
      --card-bg: rgba(255, 255, 255, 0.9);
      --text-main: #4a3b32; 
      --text-muted: #706053;
      --table-header-bg: #e2d7c5; 
      --table-header-text: #4a3b32;
      --border-color: #ebdccb;
      --accent-sage: #708238; 
      --accent-sage-hover: #5b6b2e;
      --wave-svg: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 250" preserveAspectRatio="none"><path d="M0,60 Q250,20 500,60 T1000,60 L1000,250 L0,250 Z" fill="rgba(112, 130, 56, 0.12)"/></svg>');
      --player-bg: rgba(255, 255, 255, 0.95);
      --social-bg: #4a3b32;
      --social-color: #fdfaf4;
      --modal-bg: #fdfaf4;
      --option-text: #4a3b32;
      --option-bg: #ffffff;
      --table-bg: #ecdcc9; 
      --badge-high-bg: #f8d7da;
      --badge-high-text: #842029;
      --badge-medium-bg: #fff3cd;
      --badge-medium-text: #664d03;
      --badge-low-bg: #d1e7dd;
      --badge-low-text: #0f5132;
    }
    
    [data-bs-theme="dark"] {
      --body-bg: #1c1815; 
      --card-bg: rgba(43, 36, 32, 0.85);
      --text-main: #f5ebe6; 
      --text-muted: #bdafa6;
      --table-header-bg: rgba(28, 24, 21, 0.9);
      --table-header-text: #bdafa6;
      --border-color: rgba(92, 77, 66, 0.4);
      --accent-sage: #8fa453; 
      --accent-sage-hover: #a5ba69;
      --wave-svg: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 250" preserveAspectRatio="none"><path d="M0,60 Q250,20 500,60 T1000,60 L1000,250 L0,250 Z" fill="rgba(143, 164, 83, 0.15)"/></svg>');
      --player-bg: rgba(43, 36, 32, 0.95);
      --social-bg: #f5ebe6;
      --social-color: #1c1815;
      --modal-bg: #2b2420;
      --option-text: #f5ebe6;
      --option-bg: #2b2420;
      --table-bg: transparent;
      --badge-high-bg: rgba(217, 83, 79, 0.2);
      --badge-high-text: #ea8685;
      --badge-medium-bg: rgba(240, 173, 78, 0.2);
      --badge-medium-text: #f3c47a;
      --badge-low-bg: rgba(112, 130, 56, 0.2);
      --badge-low-text: #a5ba69;
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
    .btn-custom-primary { background-color: var(--accent-sage); color: #fff; border: none; }
    .btn-custom-primary:hover { background-color: var(--accent-sage-hover); color: #fff; }
    
    .table-container-card {
      background-color: var(--table-bg) !important;
      border: 1px solid var(--border-color); 
      border-radius: 12px; 
      box-shadow: 0 8px 32px 0 rgba(74, 59, 50, 0.04);
      overflow: hidden;
    }

    .card { 
      background-color: var(--card-bg);
      border: 1px solid var(--border-color); 
      border-radius: 12px; 
      box-shadow: 0 8px 32px 0 rgba(74, 59, 50, 0.04);
    }

    .modal-content { background-color: var(--modal-bg); border: 1px solid var(--border-color); border-radius: 14px; color: var(--text-main); }
    .form-control, .form-select { color: var(--text-main) !important; background-color: transparent; border-color: var(--border-color) !important; }
    .form-control:focus, .form-select:focus { border-color: var(--accent-sage) !important; box-shadow: 0 0 0 0.25rem rgba(112, 130, 56, 0.25) !important; background-color: transparent; }
    
    .form-select option {
      color: var(--option-text) !important;
      background-color: var(--option-bg) !important;
    }

    .table { background-color: transparent !important; }
    .table thead th { 
      background-color: var(--table-header-bg) !important; 
      color: var(--table-header-text) !important; 
      font-weight: 600; 
      font-size: 0.75rem; 
      letter-spacing: 0.5px; 
      border-bottom: 1px solid var(--border-color) !important; 
    }
    .table td { vertical-align: middle; font-size: 0.9rem; border-bottom: 1px solid var(--border-color) !important; }
    .table-hover tbody tr:hover { background-color: rgba(74, 59, 50, 0.03) !important; }
    
    .badge-priority-high { background-color: var(--badge-high-bg) !important; color: var(--badge-high-text) !important; font-weight: 600 !important; }
    .badge-priority-medium { background-color: var(--badge-medium-bg) !important; color: var(--badge-medium-text) !important; font-weight: 600 !important; }
    .badge-priority-low { background-color: var(--badge-low-bg) !important; color: var(--badge-low-text) !important; font-weight: 600 !important; }

    .progress-bar-custom { background-color: var(--accent-sage); }
    .bg-custom-subtle { background-color: #f4eee1; color: var(--accent-sage); }
    [data-bs-theme="dark"] .bg-custom-subtle { background-color: #3d332e; color: var(--accent-sage); }

    .ocean { height: 280px; width: 100%; position: fixed; bottom: 0; left: 0; z-index: -1; pointer-events: none; }
    .wave { background-image: var(--wave-svg); position: absolute; bottom: 0; left: 0; width: 100%; height: 100%; background-repeat: repeat-x; background-size: 1000px 100%; animation: flawlessLoop 22s linear infinite; }
    @keyframes flawlessLoop { 0% { background-position-x: 0px; } 100% { background-position-x: -1000px; } }

    /* Floating Audio Player */
    .player-container {
      position: fixed; bottom: 24px; left: 24px; z-index: 1050;
      background-color: var(--player-bg); border: 1px solid var(--border-color);
      border-radius: 30px; padding: 8px 16px; box-shadow: 0 10px 25px rgba(0,0,0,0.08);
      display: flex; align-items: center; gap: 12px; max-width: 290px;
    }
    .player-btn {
      width: 36px; height: 36px; border-radius: 50%;
      background-color: var(--text-main); color: var(--body-bg);
      border: none; display: flex; align-items: center; justify-content: center; cursor: pointer;
    }
    .player-info { font-size: 0.8rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 110px; }
    .mute-btn { background: none; border: none; padding: 0; cursor: pointer; display: inline-flex; align-items: center; }
    .volume-slider { width: 50px; height: 4px; cursor: pointer; }

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

<div class="player-container">
  <button id="playBtn" class="player-btn"><i id="playIcon" class="bi bi-play-fill"></i></button>
  <div class="player-info">
    <span class="fw-semibold d-block text-truncate" id="trackName">Lo-Fi</span>
    <small class="text-muted" id="trackStatus">Paused</small>
  </div>
  <div class="d-flex align-items-center gap-1">
    <button id="muteBtn" class="mute-btn" title="Mute/Unmute">
      <i id="muteIcon" class="bi bi-volume-up text-muted fs-5"></i>
    </button>
    <input type="range" id="volumeControl" class="form-range volume-slider" min="0" max="1" step="0.1" value="0.5">
  </div>
  <audio id="bgMusic" src="../assets/Music.mp3" loop></audio>
</div>

<div class="main-content">
  <nav class="navbar navbar-expand-md border-bottom mb-4">
    <div class="container">
      <a class="navbar-brand fw-bold text-custom-primary d-flex align-items-center gap-2" href="../index.php">
        <i class="bi bi-layers-half"></i> Memokakii
      </a>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center gap-3">
          <li class="nav-item">
            <button id="themeToggle" class="btn btn-link nav-link text-secondary p-1 fs-5" style="cursor: pointer;">
              <i id="themeIcon" class="bi bi-moon-stars-fill"></i>
            </button>
          </li>
          <li class="nav-item">
            <span class="nav-link fw-medium d-flex align-items-center">
              <i class="bi bi-person-circle me-1 text-secondary"></i> <?= htmlspecialchars($_SESSION['user_nama'] ?? 'User') ?>
            </span>
          </li>
          <li class="nav-item">
            <a class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" href="../auth/logout.php">
              <i class="bi bi-box-arrow-right"></i> Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container py-2">
    <div class="row g-3 mb-4">
      <div class="col-md-6">
        <div class="card p-4">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted small fw-medium mb-1">PROGRESS BAR</p>
              <h3 class="fw-bold mb-0"><?= $pct ?>%</h3>
            </div>
            <div class="bg-custom-subtle rounded-3 p-2 d-inline-flex">
              <i class="bi bi-bar-chart-line-fill fs-4"></i>
            </div>
          </div>
          <div class="progress mt-3" style="height: 8px;">
            <div class="progress-bar progress-bar-custom rounded-pill" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
      </div>
      <div class="col-md-6">
        <div class="card p-4 <?= $overdueCount > 0 ? 'border-start border-danger border-4' : '' ?>">
          <div class="d-flex justify-content-between align-items-start">
            <div>
              <p class="text-muted small fw-medium mb-1">LATE!!!</p>
              <h3 class="fw-bold mb-0 <?= $overdueCount > 0 ? 'text-danger' : '' ?>"><?= $overdueCount ?></h3>
            </div>
            <div class="<?= $overdueCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-custom-subtle' ?> rounded-3 p-2 d-inline-flex">
              <i class="bi bi-exclamation-triangle-fill fs-4"></i>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-3 align-items-center justify-content-between mb-4">
      <div class="col-12 col-sm-auto"><h4 class="fw-bold mb-0">Daftar Tugas</h4></div>
      <div class="col-12 col-sm-auto">
        <div class="d-flex flex-wrap gap-2 justify-content-sm-end">
          <form class="d-flex position-relative" method="GET">
            <input type="search" name="q" class="form-control form-control-sm pe-5 bg-body" style="border-radius: 8px; min-width: 220px;" placeholder="Cari tugas..." value="<?= htmlspecialchars($search) ?>">
            <button class="btn btn-link position-absolute end-0 top-50 translate-middle-y text-muted p-2" type="submit"><i class="bi bi-search"></i></button>
          </form>
          <button type="button" class="btn btn-sm btn-custom-primary d-flex align-items-center gap-1 shadow-sm" style="border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#tambahTugasModal">
            <i class="bi bi-plus-lg"></i> Tambah Tugas
          </button>
        </div>
      </div>
    </div>

    <div class="table-container-card shadow-sm border-0">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 px-2">
          <thead>
            <tr>
              <th class="ps-4">#</th>
              <th>Judul</th>
              <th>Kategori</th>
              <th>Deadline</th>
              <th>Prioritas</th>
              <th>Status</th>
              <th class="pe-4 text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($tasks)): ?>
              <tr><td colspan="7" class="text-center text-muted py-5"><i class="bi bi-clipboard-x fs-2 d-block text-secondary mb-2"></i>Tidak ada tugas ditemukan.</td></tr>
            <?php else: ?>
              <?php foreach ($tasks as $i => $t): ?>
              <tr>
                <td class="ps-4 text-muted font-monospace fw-medium"><?= $offset + $i + 1 ?></td>
                <td class="fw-semibold"><?= htmlspecialchars($t['judul']) ?></td>
                <td>
                  <?php if ($t['kategori']): ?>
                    <span class="badge shadow-sm" style="background-color:<?= $t['warna'] ?>; color: #fff; font-weight: 500;"><?= htmlspecialchars($t['kategori']) ?></span>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td class="<?= ($t['deadline'] && $t['deadline'] < date('Y-m-d') && $t['status'] != 'done') ? 'text-danger fw-bold' : 'text-secondary' ?>">
                  <i class="bi bi-calendar3 me-1"></i><?= $t['deadline'] ? date('d M Y', strtotime($t['deadline'])) : '—' ?>
                </td>
                <td>
                  <?php 
                    $p_key = strtolower($t['prioritas']); 
                    $p_labels = ['high' => 'Tinggi', 'medium' => 'Sedang', 'low' => 'Rendah'];
                    $display_label = $p_labels[$p_key] ?? 'Rendah';
                  ?>
                  <span class="badge badge-priority-<?= $p_key ?> px-2.5 py-1"><?= $display_label ?></span>
                </td>
                <td>
                  <?php
                    $badge = ['pending' => 'secondary', 'in_progress' => 'warning text-dark', 'done' => 'success'];
                    $label = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'done' => 'Selesai'];
                  ?>
                  <span class="badge bg-<?= $badge[$t['status']] ?> rounded-1 px-2.5 py-1"><?= $label[$t['status']] ?></span>
                </td>
                <td class="pe-4 text-end">
                  <div class="d-inline-flex gap-1">
                    <button type="button" class="btn btn-sm btn-link text-custom-primary border-0 p-1 fs-5" title="Edit" 
                            onclick="openEditModal(<?= htmlspecialchars(json_encode($t)) ?>)">
                      <i class="bi bi-pencil-square"></i>
                    </button>
                    <a href="delete.php?id=<?= $t['id'] ?>" class="btn btn-sm btn-link text-danger border-0 p-1 fs-5" onclick="return confirm('Hapus tugas ini?')" title="Hapus"><i class="bi bi-trash3"></i></a>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
    
    <?php if ($totalPage > 1): ?>
    <nav class="mt-4">
      <ul class="pagination pagination-md justify-content-center border-0">
        <?php for ($i = 1; $i <= $totalPage; $i++): ?>
          <li class="page-item <?= $i == $page ? 'active' : '' ?> mx-1">
            <a class="page-link rounded shadow-sm border-0" href="?page=<?= $i ?>&q=<?= urlencode($search) ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>
    <?php endif; ?>
  </div>
</div>

<div class="modal fade" id="tambahTugasModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">Tambah Tugas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="create.php" method="POST" id="formCreateModal">
        <div class="modal-body py-3">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Judul <span class="text-danger">*</span></label>
            <input type="text" name="judul" id="modal_create_judul" class="form-control" required style="border-radius: 8px;">
            <div class="invalid-feedback">Judul minimal 3 karakter.</div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Deskripsi</label>
            <textarea name="deskripsi" class="form-control" rows="3" style="border-radius: 8px;"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Deadline</label>
              <input type="date" name="deadline" class="form-control" style="border-radius: 8px;">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Kategori</label>
              <select name="kategori_id" class="form-select" style="border-radius: 8px;">
                <option value="">— Pilih —</option>
                <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"><?= $c['nama'] ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Prioritas</label>
              <select name="prioritas" class="form-select" style="border-radius: 8px;">
                <option value="low">🟢 Rendah</option>
                <option value="medium" selected>🟡 Sedang</option>
                <option value="high">🔴 Tinggi</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Status</label>
              <select name="status" class="form-select" style="border-radius: 8px;">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Selesai</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-sm btn-light border" style="border-radius: 8px;" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-custom-primary px-3" style="border-radius: 8px;">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="editTugasModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content shadow-lg">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">Edit Tugas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="edit.php" method="POST" id="formEditModal">
        <input type="hidden" name="confirm_edit" value="1">
        <input type="hidden" name="id" id="modal_edit_id">
        
        <div class="modal-body py-3">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Judul <span class="text-danger">*</span></label>
            <input type="text" name="judul" id="modal_edit_judul" class="form-control" required style="border-radius: 8px;">
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Deskripsi</label>
            <textarea name="deskripsi" id="modal_edit_deskripsi" class="form-control" rows="3" style="border-radius: 8px;"></textarea>
          </div>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Deadline</label>
              <input type="date" name="deadline" id="modal_edit_deadline" class="form-control" style="border-radius: 8px;">
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Kategori</label>
              <select name="kategori_id" id="modal_edit_kategori" class="form-select" style="border-radius: 8px;">
                <option value="">— Pilih —</option>
                <?php foreach ($cats as $c): ?><option value="<?= $c['id'] ?>"><?= $c['nama'] ?></option><?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Prioritas</label>
              <select name="prioritas" id="modal_edit_prioritas" class="form-select" style="border-radius: 8px;">
                <option value="low">🟢 Rendah</option>
                <option value="medium">🟡 Sedang</option>
                <option value="high">🔴 Tinggi</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-semibold">Status</label>
              <select name="status" id="modal_edit_status" class="form-select" style="border-radius: 8px;">
                <option value="pending">Pending</option>
                <option value="in_progress">In Progress</option>
                <option value="done">Selesai</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer border-top-0 pt-0">
          <button type="button" class="btn btn-sm btn-light border" style="border-radius: 8px;" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-custom-primary px-3" style="border-radius: 8px;">Simpan Perubahan</button>
        </div>
      </form>
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
  // --- Dark Mode Controller ---
  const themeToggle = document.getElementById('themeToggle'), themeIcon = document.getElementById('themeIcon'), htmlTag = document.documentElement;
  const savedTheme = localStorage.getItem('theme');
  if (savedTheme) { htmlTag.setAttribute('data-bs-theme', savedTheme); updateIcon(savedTheme); }
  themeToggle.addEventListener('click', () => {
    const currentTheme = htmlTag.getAttribute('data-bs-theme');
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    htmlTag.setAttribute('data-bs-theme', newTheme);
    localStorage.setItem('theme', newTheme);
    updateIcon(newTheme);
  });
  function updateIcon(theme) { themeIcon.className = theme === 'dark' ? 'bi bi-sun-fill text-warning' : 'bi bi-moon-stars-fill text-secondary'; }

  // --- Audio Player + Mute  ---
  const bgMusic = document.getElementById('bgMusic'), playBtn = document.getElementById('playBtn'), playIcon = document.getElementById('playIcon'), trackStatus = document.getElementById('trackStatus'), volumeControl = document.getElementById('volumeControl'), muteBtn = document.getElementById('muteBtn'), muteIcon = document.getElementById('muteIcon');
  bgMusic.volume = volumeControl.value;
  playBtn.addEventListener('click', () => {
    if (bgMusic.paused) { bgMusic.play().then(() => { playIcon.className = 'bi bi-pause-fill'; trackStatus.innerText = 'Now Playing'; }).catch(() => alert("Pemberitahuan: Mohon periksa apakah file MP3 sudah di-upload dengan benar di server.")); }
    else { bgMusic.pause(); playIcon.className = 'bi bi-play-fill'; trackStatus.innerText = 'Paused'; }
  });
  muteBtn.addEventListener('click', () => {
    bgMusic.muted = !bgMusic.muted;
    muteIcon.className = bgMusic.muted ? 'bi bi-volume-mute text-danger fs-5' : (volumeControl.value > 0.5 ? 'bi bi-volume-up text-muted fs-5' : 'bi bi-volume-down text-muted fs-5');
  });
  volumeControl.addEventListener('input', (e) => {
    bgMusic.volume = e.target.value;
    bgMusic.muted = (e.target.value == 0);
    muteIcon.className = bgMusic.muted ? 'bi bi-volume-mute text-danger fs-5' : (e.target.value > 0.5 ? 'bi bi-volume-up text-muted fs-5' : 'bi bi-volume-down text-muted fs-5');
  });

  // --- Open & Populate Edit Modal Window ---
  const bsEditModal = new bootstrap.Modal(document.getElementById('editTugasModal'));
  function openEditModal(taskData) {
    document.getElementById('modal_edit_id').value = taskData.id;
    document.getElementById('modal_edit_judul').value = taskData.judul;
    document.getElementById('modal_edit_deskripsi').value = taskData.deskripsi || '';
    document.getElementById('modal_edit_deadline').value = taskData.deadline || '';
    document.getElementById('modal_edit_kategori').value = taskData.kategori_id || '';
    document.getElementById('modal_edit_prioritas').value = taskData.prioritas;
    document.getElementById('modal_edit_status').value = taskData.status;
    bsEditModal.show();
  }

  // Frontend Validasi Tambah Tugas
  document.getElementById('formCreateModal').addEventListener('submit', function(e) {
    const judul = document.getElementById('modal_create_judul');
    if (judul.value.trim().length < 3) { e.preventDefault(); judul.classList.add('is-invalid'); }
  });

  // Dialog Konfirmasi Edit Tugas
  document.getElementById('formEditModal').addEventListener('submit', function(e) {
    if (!confirm('Yakin ingin menyimpan perubahan pada tugas ini?')) { e.preventDefault(); }
  });
</script>
</body>
</html>