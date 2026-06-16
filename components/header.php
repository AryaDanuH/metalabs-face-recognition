<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isAdmin = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Metalabs Face Recognition</title>
  <link rel="icon" type="image/png" href="assets/icon.png">
  <link rel="stylesheet" href="assets/style.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@400;500;600;700&display=swap">
  <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
</head>
<body>
<div class="app-shell">
  <?php include 'sidebar.php'; ?>
  <div class="main-content">
    <header class="top-nav">
      <div style="display: flex; align-items: center; gap: var(--unit-sm); opacity: 0.6;">
        <span class="material-symbols-outlined" style="font-size: 16px;">lock</span>
        <span class="font-label-caps">INTERNAL SECURE NETWORK</span>
      </div>
      <div>
        <span class="font-mono-data text-on-surface-variant" style="font-size: 12px;"><?= date('Y-m-d H:i:s') ?></span>
      </div>
    </header>
    <main class="page-content">
