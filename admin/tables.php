<?php
/**
 * admin/tables.php
 * ----------------
 * Gestion des tables
 */

$pageTitle = 'Gestion des Tables';

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAdminLogin();

$pdo = getPDO();
$message = '';
$error = '';

// Traitement du changement de statut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_statut') {
    $id = intval($_POST['id'] ?? 0);
    $statut = trim($_POST['statut'] ?? '');
    
    $allowedStatus = ['libre', 'occupee', 'reservee'];
    
    if ($id > 0 && in_array($statut, $allowedStatus)) {
        try {
            $stmt = $pdo->prepare('UPDATE table_restaurant SET statut = :statut WHERE id = :id');
            $stmt->execute(['id' => $id, 'statut' => $statut]);
            $message = 'Statut de la table mis à jour.';
        } catch (PDOException $e) {
            $error = 'Erreur lors de la mise à jour.';
        }
    }
}

// Traitement de l'ajout/modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && ($_POST['action'] === 'add' || $_POST['action'] === 'edit')) {
    $numero = intval($_POST['numero'] ?? 0);
    $capacite = intval($_POST['capacite'] ?? 0);
    $statut = trim($_POST['statut'] ?? 'libre');
    $description = trim($_POST['description'] ?? '');
    $id = $_POST['action'] === 'edit' ? intval($_POST['id'] ?? 0) : 0;
    
    if ($numero <= 0 || $capacite <= 0) {
        $error = 'Le numéro et la capacité doivent être supérieurs à 0.';
    } else {
        try {
            if ($_POST['action'] === 'add') {
                $stmt = $pdo->prepare('INSERT INTO table_restaurant (numero, capacite, statut, description) VALUES (:numero, :capacite, :statut, :description)');
                $stmt->execute(['numero' => $numero, 'capacite' => $capacite, 'statut' => $statut, 'description' => $description]);
                $message = 'Table ajoutée avec succès.';
            } else {
                $stmt = $pdo->prepare('UPDATE table_restaurant SET numero = :numero, capacite = :capacite, statut = :statut, description = :description WHERE id = :id');
                $stmt->execute(['id' => $id, 'numero' => $numero, 'capacite' => $capacite, 'statut' => $statut, 'description' => $description]);
                $message = 'Table modifiée avec succès.';
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Ce numéro de table existe déjà.';
            } else {
                $error = 'Erreur lors de l\'enregistrement.';
            }
        }
    }
}

// Récupération de la table à éditer
$editTable = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare('SELECT * FROM table_restaurant WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $editTable = $stmt->fetch();
}

// Liste des tables
$tables = $pdo->query('SELECT * FROM table_restaurant ORDER BY numero')->fetchAll();

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
        <h2><?= $editTable ? 'Modifier une table' : 'Ajouter une table' ?></h2>
    </div>
    <div class="card-body">
        <form method="POST" class="form">
            <input type="hidden" name="action" value="<?= $editTable ? 'edit' : 'add' ?>">
            <?php if ($editTable): ?>
                <input type="hidden" name="id" value="<?= $editTable['id'] ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Numéro de table *</label>
                    <input type="number" name="numero" value="<?= e($editTable['numero'] ?? '') ?>" min="1" required>
                </div>
                
                <div class="form-group">
                    <label>Capacité (personnes) *</label>
                    <input type="number" name="capacite" value="<?= e($editTable['capacite'] ?? '') ?>" min="1" required>
                </div>
                
                <div class="form-group">
                    <label>Statut *</label>
                    <select name="statut" required>
                        <option value="libre" <?= ($editTable['statut'] ?? 'libre') === 'libre' ? 'selected' : '' ?>>Libre</option>
                        <option value="occupee" <?= ($editTable['statut'] ?? '') === 'occupee' ? 'selected' : '' ?>>Occupée</option>
                        <option value="reservee" <?= ($editTable['statut'] ?? '') === 'reservee' ? 'selected' : '' ?>>Réservée</option>
                    </select>
                </div>

                <div class="form-group" style="flex: 1 1 100%;">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Enter description (optional)"><?= e($editTable['description'] ?? '') ?></textarea>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $editTable ? 'Modifier' : 'Ajouter' ?></button>
                <?php if ($editTable): ?>
                    <a href="tables.php" class="btn btn-outline">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Liste des tables</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Numéro</th>
                        <th>Capacité</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tables)): ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucune table enregistrée</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tables as $table): ?>
                            <tr>
                                <td>#<?= $table['id'] ?></td>
                                <td><strong>Table <?= $table['numero'] ?></strong></td>
                                <td><?= $table['capacite'] ?> personnes</td>
                                <td><?= getStatusBadge($table['statut']) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" class="form-inline">
                                            <input type="hidden" name="action" value="update_statut">
                                            <input type="hidden" name="id" value="<?= $table['id'] ?>">
                                            <select name="statut" class="form-select-sm" onchange="this.form.submit()">
                                                <option value="libre" <?= $table['statut'] === 'libre' ? 'selected' : '' ?>>Libre</option>
                                                <option value="occupee" <?= $table['statut'] === 'occupee' ? 'selected' : '' ?>>Occupée</option>
                                                <option value="reservee" <?= $table['statut'] === 'reservee' ? 'selected' : '' ?>>Réservée</option>
                                            </select>
                                        </form>
                                        <button type="button"
                                                class="btn-icon"
                                                onclick="openDescriptionModal(<?= $table['id'] ?>, 'Table <?= e($table['numero']) ?>', '<?= e(addslashes($table['description'] ?? 'No description')) ?>')"
                                                title="Info">ℹ️</button>
                                        <a href="?edit=<?= $table['id'] ?>" class="btn-icon" title="Modifier">✏️</a>
                                    </div>
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

<!-- Description Modal -->
<div id="descriptionModal" class="modal" style="display:none;">
    <div class="modal-content">
        <span class="modal-close" onclick="closeDescriptionModal()">&times;</span>
        <h3 id="modalTitle"></h3>
        <p id="modalDescription" style="white-space:pre-wrap;"></p>
    </div>
</div>

<style>
.modal {
    position: fixed; top:0; left:0; width:100%; height:100%;
    background: rgba(0,0,0,0.4);
    display:flex; justify-content:center; align-items:center;
    z-index:10000;
}
.modal-content {
    background:#fff; padding:20px; border-radius:8px; width:400px;
}
.modal-close { float:right; cursor:pointer; font-size:22px; }
</style>

<script>
function openDescriptionModal(id, title, description) {
    document.getElementById("modalTitle").innerText = title;
    document.getElementById("modalDescription").innerText = description;
    document.getElementById("descriptionModal").style.display = "flex";
}
function closeDescriptionModal() {
    document.getElementById("descriptionModal").style.display = "none";
}
</script>

