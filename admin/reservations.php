<?php
/**
 * admin/reservations.php
 * ----------------------
 * Gestion des réservations
 */

$pageTitle = 'Gestion des Réservations';

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAdminLogin();

$pdo = getPDO();
$message = '';
$error = '';

// Traitement des actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = intval($_POST['id'] ?? 0);
    
    if ($id > 0) {
        if ($action === 'confirmer') {
            try {
                $stmt = $pdo->prepare('UPDATE reservation SET statut = "confirmee" WHERE id = :id');
                $stmt->execute(['id' => $id]);
                
                // Mettre à jour le statut de la table
                $stmt2 = $pdo->prepare('UPDATE table_restaurant SET statut = "reservee" WHERE id = (SELECT id_table FROM reservation WHERE id = :id)');
                $stmt2->execute(['id' => $id]);
                
                $message = 'Réservation confirmée.';
            } catch (PDOException $e) {
                $error = 'Erreur lors de la confirmation.';
            }
        } elseif ($action === 'annuler') {
            try {
                $stmt = $pdo->prepare('UPDATE reservation SET statut = "annulee" WHERE id = :id');
                $stmt->execute(['id' => $id]);
                
                // Libérer la table
                $stmt2 = $pdo->prepare('UPDATE table_restaurant SET statut = "libre" WHERE id = (SELECT id_table FROM reservation WHERE id = :id)');
                $stmt2->execute(['id' => $id]);
                
                $message = 'Réservation annulée.';
            } catch (PDOException $e) {
                $error = 'Erreur lors de l\'annulation.';
            }
        }
    }
}

// Récupération des réservations
$reservations = $pdo->query("
    SELECT r.*, u.nom, u.prenom, u.email, u.telephone, tr.numero, tr.capacite
    FROM reservation r
    JOIN utilisateur u ON r.id_client = u.id
    JOIN table_restaurant tr ON r.id_table = tr.id
    ORDER BY r.date_reservation DESC
")->fetchAll();

// Statistiques des tables
$tablesStats = [
    'libres' => $pdo->query("SELECT COUNT(*) FROM table_restaurant WHERE statut = 'libre'")->fetchColumn(),
    'occupees' => $pdo->query("SELECT COUNT(*) FROM table_restaurant WHERE statut = 'occupee'")->fetchColumn(),
    'reservees' => $pdo->query("SELECT COUNT(*) FROM table_restaurant WHERE statut = 'reservee'")->fetchColumn(),
];

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="stats-grid" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon">✅</div>
        <div class="stat-content">
            <h3><?= $tablesStats['libres'] ?></h3>
            <p>Tables libres</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">🪑</div>
        <div class="stat-content">
            <h3><?= $tablesStats['occupees'] ?></h3>
            <p>Tables occupées</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon">📅</div>
        <div class="stat-content">
            <h3><?= $tablesStats['reservees'] ?></h3>
            <p>Tables réservées</p>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Toutes les réservations</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Table</th>
                        <th>Date & Heure</th>
                        <th>Durée</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reservations)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune réservation</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reservations as $res): ?>
                            <tr>
                                <td><strong>#<?= $res['id'] ?></strong></td>
                                <td>
                                    <div><?= e($res['prenom'] . ' ' . $res['nom']) ?></div>
                                    <small class="text-muted"><?= e($res['email']) ?></small>
                                    <?php if ($res['telephone']): ?>
                                        <br><small class="text-muted">📞 <?= e($res['telephone']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>Table <?= $res['numero'] ?></strong>
                                    <br><small class="text-muted">Capacité: <?= $res['capacite'] ?> pers.</small>
                                </td>
                                <td><?= formatDateTime($res['date_reservation']) ?></td>
                                <td><?= $res['duree'] ?> min</td>
                                <td><?= getStatusBadge($res['statut']) ?></td>
                                <td>
                                    <?php if ($res['statut'] === 'en_attente'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="confirmer">
                                            <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-success">Confirmer</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="annuler">
                                            <input type="hidden" name="id" value="<?= $res['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Annuler cette réservation ?');">Annuler</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

