<?php
/**
 * dashboard.php
 * -------------
 * Main dashboard - redirects based on user role (admin or client)
 */

require_once __DIR__ . '/db/config.php';
requireLogin();

require_once __DIR__ . '/php/menu.php';
require_once __DIR__ . '/php/orders.php';
require_once __DIR__ . '/php/reservations.php';

$conn = getDBConnection();

// Get statistics
$total_dishes = 0;
$total_orders = 0;
$pending_orders = 0;
$total_reservations = 0;

if (isAdmin()) {
    // Admin dashboard stats
    $result = $conn->query("SELECT COUNT(*) as count FROM plat");
    if ($result) {
        $total_dishes = $result->fetch_assoc()['count'];
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM commande");
    if ($result) {
        $total_orders = $result->fetch_assoc()['count'];
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM commande WHERE etat = 'en_attente'");
    if ($result) {
        $pending_orders = $result->fetch_assoc()['count'];
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM reservation");
    if ($result) {
        $total_reservations = $result->fetch_assoc()['count'];
    }
} else {
    // Client dashboard stats
    $user_id = $_SESSION['user_id'] ?? 0;
    $result = $conn->query("SELECT COUNT(*) as count FROM commande WHERE id_client = $user_id");
    if ($result) {
        $total_orders = $result->fetch_assoc()['count'];
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM commande WHERE id_client = $user_id AND etat = 'en_attente'");
    if ($result) {
        $pending_orders = $result->fetch_assoc()['count'];
    }
    
    $result = $conn->query("SELECT COUNT(*) as count FROM reservation WHERE id_client = $user_id");
    if ($result) {
        $total_reservations = $result->fetch_assoc()['count'];
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Restaurant Les Jomox</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <h2>Restaurant Les Jomox</h2>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <?php if (isAdmin()): ?>
                        <li><a href="admin/menu.php">Gérer le Menu</a></li>
                        <li><a href="admin/commandes.php">Commandes</a></li>
                        <li><a href="admin/reservations.php">Réservations</a></li>
                    <?php else: ?>
                        <li><a href="menu.php">Menu</a></li>
                        <li><a href="reservations.php">Réservations</a></li>
                    <?php endif; ?>
                    <li><a href="php/auth.php?action=logout">Déconnexion</a></li>
                </ul>
                <div class="user-info">
                    <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_nom'] ?? $_SESSION['user_prenom'] ?? 'Utilisateur'); ?> (<?php echo (isset($_SESSION['is_admin']) && $_SESSION['is_admin']) ? 'Admin' : 'Client'; ?>)</span>
                </div>
            </nav>
        </header>

        <div class="dashboard">
            <h1>Dashboard</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="stats">
                <?php if (isAdmin()): ?>
                    <div class="stat-card">
                        <h3><?php echo $total_dishes; ?></h3>
                        <p>Plats au menu</p>
                    </div>
                <?php endif; ?>
                <div class="stat-card">
                    <h3><?php echo $total_orders; ?></h3>
                    <p>Total des commandes</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $pending_orders; ?></h3>
                    <p>Commandes en attente</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $total_reservations; ?></h3>
                    <p>Réservations</p>
                </div>
            </div>

            <div class="text-center mt-30">
                <?php if (isAdmin()): ?>
                    <h2>Actions rapides</h2>
                    <div style="margin-top: 20px;">
                        <a href="admin/menu.php" class="btn btn-primary">Gérer le Menu</a>
                        <a href="admin/commandes.php" class="btn btn-primary">Voir les Commandes</a>
                        <a href="admin/reservations.php" class="btn btn-primary">Gérer les Réservations</a>
                    </div>
                <?php else: ?>
                    <h2>Actions rapides</h2>
                    <div style="margin-top: 20px;">
                        <a href="menu.php" class="btn btn-primary">Voir le Menu & Commander</a>
                        <a href="reservations.php" class="btn btn-primary">Faire une Réservation</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
