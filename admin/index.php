<?php
/**
 * admin/index.php
 * ---------------
 * Dashboard admin - Vue d'ensemble
 */

$pageTitle = 'Dashboard';

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAdminLogin();

$pdo = getPDO();

// Statistiques
$stats = [
    'plats' => $pdo->query('SELECT COUNT(*) FROM plat')->fetchColumn(),
    'commandes_aujourdhui' => $pdo->query("SELECT COUNT(*) FROM commande WHERE DATE(date_commande) = CURDATE()")->fetchColumn(),
    'reservations_aujourdhui' => $pdo->query("SELECT COUNT(*) FROM reservation WHERE DATE(date_reservation) = CURDATE()")->fetchColumn(),
    'tables_occupees' => $pdo->query("SELECT COUNT(*) FROM table_restaurant WHERE statut = 'occupee'")->fetchColumn(),
    'commandes_en_attente' => $pdo->query("SELECT COUNT(*) FROM commande WHERE etat = 'en_attente'")->fetchColumn(),
    'chiffre_affaires_jour' => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM commande WHERE DATE(date_commande) = CURDATE()")->fetchColumn(),
];

// Dernières commandes
$recentOrders = $pdo->query("
    SELECT c.*, u.nom, u.prenom 
    FROM commande c 
    JOIN utilisateur u ON c.id_client = u.id 
    ORDER BY c.date_commande DESC 
    LIMIT 5
")->fetchAll();

// Dernières réservations
$recentReservations = $pdo->query("
    SELECT r.*, u.nom, u.prenom, tr.numero 
    FROM reservation r 
    JOIN utilisateur u ON r.id_client = u.id 
    JOIN table_restaurant tr ON r.id_table = tr.id 
    ORDER BY r.date_reservation DESC 
    LIMIT 5
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon">🍽️</div>
        <div class="stat-content">
            <h3><?= $stats['plats'] ?></h3>
            <p>Plats au menu</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📦</div>
        <div class="stat-content">
            <h3><?= $stats['commandes_aujourdhui'] ?></h3>
            <p>Commandes aujourd'hui</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
            <h3><?= $stats['reservations_aujourdhui'] ?></h3>
            <p>Réservations aujourd'hui</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🪑</div>
        <div class="stat-content">
            <h3><?= $stats['tables_occupees'] ?></h3>
            <p>Tables occupées</p>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">⏳</div>
        <div class="stat-content">
            <h3><?= $stats['commandes_en_attente'] ?></h3>
            <p>Commandes en attente</p>
        </div>
    </div>
    
    <div class="stat-card stat-card-primary">
        <div class="stat-icon">💰</div>
        <div class="stat-content">
            <h3><?= formatPrice($stats['chiffre_affaires_jour']) ?></h3>
            <p>Chiffre d'affaires du jour</p>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <div class="dashboard-card">
        <div class="card-header">
            <h2>Dernières commandes</h2>
            <a href="commandes.php" class="btn-link">Voir tout</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>État</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentOrders)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucune commande récente</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentOrders as $order): ?>
                            <tr>
                                <td>#<?= $order['id'] ?></td>
                                <td><?= e($order['prenom'] . ' ' . $order['nom']) ?></td>
                                <td><?= formatDateTime($order['date_commande']) ?></td>
                                <td><?= formatPrice($order['total']) ?></td>
                                <td><?= getStatusBadge($order['etat']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="dashboard-card">
        <div class="card-header">
            <h2>Dernières réservations</h2>
            <a href="reservations.php" class="btn-link">Voir tout</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Table</th>
                        <th>Date</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentReservations)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucune réservation récente</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentReservations as $res): ?>
                            <tr>
                                <td>#<?= $res['id'] ?></td>
                                <td><?= e($res['prenom'] . ' ' . $res['nom']) ?></td>
                                <td>Table <?= $res['numero'] ?></td>
                                <td><?= formatDateTime($res['date_reservation']) ?></td>
                                <td><?= getStatusBadge($res['statut']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

