<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $uid       = $_SESSION['user_id'];
  $judul     = trim($_POST['judul']);
  $deskripsi = trim($_POST['deskripsi']);
  $deadline  = $_POST['deadline'] ?: null;
  $status    = $_POST['status'];
  $prioritas = $_POST['prioritas'];
  $kat_id    = $_POST['kategori_id'] ?: null;

  if ($judul) {
    $stmt = $pdo->prepare("INSERT INTO tasks 
      (user_id, kategori_id, judul, deskripsi, deadline, status, prioritas)
      VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$uid, $kat_id, $judul, $deskripsi, $deadline, $status, $prioritas]);
  }
  
  // Langsung kembalikan ke dashboard utama setelah insert data via modal
  header('Location: index.php');
  exit;
}