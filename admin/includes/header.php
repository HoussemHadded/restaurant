<?php
/**
 * admin/includes/header.php
 * --------------------------
 * En-tête commun pour toutes les pages admin
 */

requireAdminLogin();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'Admin' ?> - Restaurant Les Jomox</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>Les Jomox</h2>
                <p class="sidebar-subtitle">Administration</p>
            </div>
            
            <nav class="sidebar-nav">
                <a href="index.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
                <a href="menu.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'menu.php' ? 'active' : '' ?>">
                    <span class="nav-icon">🍽️</span>
                    <span>Menu</span>
                </a>
                <a href="commandes.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'commandes.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📦</span>
                    <span>Commandes</span>
                </a>
                <a href="reservations.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'reservations.php' ? 'active' : '' ?>">
                    <span class="nav-icon">📅</span>
                    <span>Réservations</span>
                </a>
                <a href="tables.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'tables.php' ? 'active' : '' ?>">
                    <span class="nav-icon">🪑</span>
                    <span>Tables</span>
                </a>
                <a href="utilisateurs.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) === 'utilisateurs.php' ? 'active' : '' ?>">
                    <span class="nav-icon">👥</span>
                    <span>Utilisateurs</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <p><strong><?= e(($_SESSION['admin_prenom'] ?? $_SESSION['user_prenom'] ?? '') . ' ' . ($_SESSION['admin_nom'] ?? $_SESSION['user_nom'] ?? '')) ?></strong></p>
                    <p class="text-muted"><?= e($_SESSION['admin_email'] ?? $_SESSION['user_email'] ?? '') ?></p>
                </div>
                <a href="logout.php" class="btn btn-outline btn-sm">Déconnexion</a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <h1><?= isset($pageTitle) ? e($pageTitle) : 'Dashboard' ?></h1>
            </div>
            
            <div class="content-body">

