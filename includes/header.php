<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SMS Pro</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?=BASE_URL?>assets/css/style.css" rel="stylesheet">
</head>
<body>

<div class="d-flex">
    <!-- Sidebar -->
    <?php if (isset($_SESSION['username'])): ?>
        <?php include __DIR__ . '/sidebar.php'; ?>
    <?php endif; ?>

    <!-- Main Content -->
    <div class="<?= isset($_SESSION['username']) ? 'main-content' : 'container mt-5' ?>" style="width: 100%;">
        
        <?php if (isset($_SESSION['username'])): ?>
        <!-- Top Navbar -->
        <div class="top-navbar">
            <div class="user-profile">
                <span>Welcome, <strong><?=htmlspecialchars($_SESSION['username'])?></strong> (<?=ucfirst(str_replace('_', ' ', $_SESSION['role']))?>)</span>
                <i class="fas fa-user-circle fa-2x text-secondary"></i>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="container-fluid">
