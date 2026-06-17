<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Verifikasi token konfirmasi edit modal
  if (!isset($_POST['confirm_edit'])) { 
    header('Location: index.php'); 
    exit; 
  }

  $uid       = $_SESSION['user_id'];
  $id        = (int)$_POST['id']; // ID didapatkan dari field hidden modal
  $judul     = trim($_POST['judul']);
  $deskripsi = trim($_POST['deskripsi']);
  $deadline  = $_POST['deadline'] ?: null;
  $status    = $_POST['status'];
  $prioritas = $_POST['prioritas'];
  $kat_id    = $_POST['kategori_id'] ?: null;

  if ($id && $judul) {
    $stmt = $pdo->prepare("
      UPDATE tasks SET 
        judul = ?, 
        deskripsi = ?, 
        deadline = ?, 
        status = ?, 
        prioritas = ?, 
        kategori_id = ?
      WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$judul, $deskripsi, $deadline, $status, $prioritas, $kat_id, $id, $uid]);
  }
  
  // Berhasil diupdate, langsung kembalikan ke dashboard utama
  header('Location: index.php');
  exit;
} else {
  // Jika diakses direct via URL GET, kembalikan ke dashboard
  header('Location: index.php');
  exit;
}