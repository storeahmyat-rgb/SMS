<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= SCHOOL_NAME ?> - SMS Pro</title>
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
    <div class="<?= isset($_SESSION['username']) ? 'main-content' : 'container mt-5' ?>" style="width: 100%; min-width: 0;">
        
        <?php if (isset($_SESSION['username'])): ?>
        <!-- Top Navbar -->
        <div class="top-navbar d-flex align-items-center mb-3 mb-md-4 px-3 py-2 shadow-sm bg-white rounded-3">
            <button class="btn btn-light shadow-sm me-3 d-md-none no-print" id="sidebarToggle" type="button" style="width: 45px; height: 45px;">
                <i class="fas fa-bars text-primary"></i>
            </button>
            <div class="user-profile ms-auto">
                <span class="d-none d-sm-inline me-2 small text-muted"><strong><?=htmlspecialchars($_SESSION['username'])?></strong></span>
                <i class="fas fa-user-circle fa-2x text-primary-emphasis"></i>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggle = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            if (toggle && sidebar) {
                toggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('active');
                });
                
                // Close sidebar when clicking outside on mobile
                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 768 && sidebar.classList.contains('active') && !sidebar.contains(e.target) && e.target !== toggle) {
                        sidebar.classList.remove('active');
                    }
                });
            }
        });
        </script>
        <?php endif; ?>
        
        <div class="container-fluid">
