<?php
session_start();
if (!isset($_SESSION['user_id'])) { header('Location: ../auth/login.php'); exit; }
require_once '../config/db.php';

$uid = $_SESSION['user_id'];
$id  = (int)$_GET['id'];

$stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $uid]);

header('Location: index.php');
exit;