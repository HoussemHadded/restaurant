<?php
/**
 * admin/commandes.php
 * --------------
 * Gestion des commandes
 */

$pageTitle = 'Gestion des Commandes';

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAdminLogin();

$pdo = getPDO();
$message = '';
$error = '';

// Traitement du changement d'état
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_etat') {
    $id = intval($_POST['id'] ?? 0);
    $etat = trim($_POST['etat'] ?? '');
    
    $allowedStates = ['en_attente', 'confirmee', 'servie', 'annulee'];
    
    if ($id > 0 && in_array($etat, $allowedStates)) {
        try {
            $stmt = $pdo->prepare('UPDATE commande SET etat = :etat WHERE id = :id');
            $stmt->execute(['id' => $id, 'etat' => $etat]);
            $message = 'État de la commande mis à jour.';
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour.';
        }
    }
}

// Récupération des commandes
$commandes = $pdo->query("
    SELECT c.*, u.nom, u.prenom, u.email, u.telephone
    FROM commande c
    JOIN utilisateur u ON c.id_client = u.id
    ORDER BY c.date_commande DESC
")->fetchAll();

// Pour chaque commande, récupérer les items
foreach ($commandes as &$commande) {
    $stmt = $pdo->prepare("
        SELECT ci.*, p.nom as plat_nom
        FROM commande_item ci
        JOIN plat p ON ci.id_plat = p.id
        WHERE ci.id_commande = :id
    ");
    $stmt->execute(['id' => $commande['id']]);
    $commande['items'] = $stmt->fetchAll();
}

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Toutes les commandes</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Plats</th>
                        <th>Total</th>
                        <th>État</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($commandes)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucune commande</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($commandes as $commande): ?>
                            <tr>
                                <td><strong>#<?= $commande['id'] ?></strong></td>
                                <td>
                                    <div><?= e($commande['prenom'] . ' ' . $commande['nom']) ?></div>
                                    <small class="text-muted"><?= e($commande['email']) ?></small>
                                </td>
                                <td><?= formatDateTime($commande['date_commande']) ?></td>
                                <td>
                                    <details>
                                        <summary><?= count($commande['items']) ?> plat(s)</summary>
                                        <ul class="list-unstyled mt-2">
                                            <?php foreach ($commande['items'] as $item): ?>
                                                <li>
                                                    <?= e($item['plat_nom']) ?> 
                                                    x<?= $item['quantite'] ?> 
                                                    = <?= formatPrice($item['prix_unitaire'] * $item['quantite']) ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </details>
                                </td>
                                <td><strong><?= formatPrice($commande['total']) ?></strong></td>
                                <td><?= getStatusBadge($commande['etat']) ?></td>
                                <td>
                                    <form method="POST" class="form-inline">
                                        <input type="hidden" name="action" value="update_etat">
                                        <input type="hidden" name="id" value="<?= $commande['id'] ?>">
                                        <select name="etat" class="form-select-sm" onchange="this.form.submit()">
                                            <option value="en_attente" <?= $commande['etat'] === 'en_attente' ? 'selected' : '' ?>>En attente</option>
                                            <option value="confirmee" <?= $commande['etat'] === 'confirmee' ? 'selected' : '' ?>>Confirmée</option>
                                            <option value="servie" <?= $commande['etat'] === 'servie' ? 'selected' : '' ?>>Servie</option>
                                            <option value="annulee" <?= $commande['etat'] === 'annulee' ? 'selected' : '' ?>>Annulée</option>
                                        </select>
                                    </form>
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

