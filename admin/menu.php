<?php
/**
 * admin/menu.php
 * -------------
 * Gestion du menu (CRUD plats)
 */

$pageTitle = 'Gestion du Menu';

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
    
    if ($action === 'add' || $action === 'edit') {
        $nom = trim($_POST['nom'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $prix = floatval($_POST['prix'] ?? 0);
        $categorie = trim($_POST['categorie'] ?? '');
        $disponible = isset($_POST['disponible']) ? 1 : 0;
        $id = $action === 'edit' ? intval($_POST['id'] ?? 0) : 0;
        
        if (empty($nom) || $prix <= 0 || empty($categorie)) {
            $error = 'Veuillez remplir tous les champs obligatoires.';
        } else {
            try {
                if ($action === 'add') {
                    $stmt = $pdo->prepare('INSERT INTO plat (nom, description, prix, categorie, disponible) VALUES (:nom, :description, :prix, :categorie, :disponible)');
                    $stmt->execute([
                        'nom' => $nom,
                        'description' => $description,
                        'prix' => $prix,
                        'categorie' => $categorie,
                        'disponible' => $disponible,
                    ]);
                    $message = 'Plat ajouté avec succès.';
                } else {
                    $stmt = $pdo->prepare('UPDATE plat SET nom = :nom, description = :description, prix = :prix, categorie = :categorie, disponible = :disponible WHERE id = :id');
                    $stmt->execute([
                        'id' => $id,
                        'nom' => $nom,
                        'description' => $description,
                        'prix' => $prix,
                        'categorie' => $categorie,
                        'disponible' => $disponible,
                    ]);
                    $message = 'Plat modifié avec succès.';
                }
            } catch (PDOException $e) {
                $error = 'Erreur lors de l\'enregistrement.';
            }
        }
    } elseif ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('DELETE FROM plat WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $message = 'Plat supprimé avec succès.';
            } catch (PDOException $e) {
                $error = 'Erreur lors de la suppression.';
            }
        }
    } elseif ($action === 'toggle_disponible') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $stmt = $pdo->prepare('UPDATE plat SET disponible = NOT disponible WHERE id = :id');
                $stmt->execute(['id' => $id]);
                $message = 'Disponibilité mise à jour.';
            } catch (PDOException $e) {
                $error = 'Erreur lors de la mise à jour.';
            }
        }
    }
}

// Récupération du plat à éditer
$editPlat = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare('SELECT * FROM plat WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $editPlat = $stmt->fetch();
}

// Liste des plats
$plats = $pdo->query('SELECT * FROM plat ORDER BY categorie, nom')->fetchAll();

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
        <h2><?= $editPlat ? 'Modifier un plat' : 'Ajouter un plat' ?></h2>
    </div>
    <div class="card-body">
        <form method="POST" class="form">
            <input type="hidden" name="action" value="<?= $editPlat ? 'edit' : 'add' ?>">
            <?php if ($editPlat): ?>
                <input type="hidden" name="id" value="<?= $editPlat['id'] ?>">
            <?php endif; ?>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Nom du plat *</label>
                    <input type="text" name="nom" value="<?= e($editPlat['nom'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Catégorie *</label>
                    <input type="text" name="categorie" value="<?= e($editPlat['categorie'] ?? '') ?>" required placeholder="Ex: Entrée, Plat principal, Dessert">
                </div>
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="3"><?= e($editPlat['description'] ?? '') ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Prix (€) *</label>
                    <input type="number" name="prix" step="0.01" min="0" value="<?= e($editPlat['prix'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="disponible" <?= ($editPlat['disponible'] ?? true) ? 'checked' : '' ?>>
                        <span>Disponible</span>
                    </label>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $editPlat ? 'Modifier' : 'Ajouter' ?></button>
                <?php if ($editPlat): ?>
                    <a href="menu.php" class="btn btn-outline">Annuler</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2>Liste des plats</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Description</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Disponible</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($plats)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">Aucun plat enregistré</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($plats as $plat): ?>
                            <tr>
                                <td>#<?= $plat['id'] ?></td>
                                <td><strong><?= e($plat['nom']) ?></strong></td>
                                <td><?= e(substr($plat['description'] ?? '', 0, 50)) ?><?= strlen($plat['description'] ?? '') > 50 ? '...' : '' ?></td>
                                <td><?= e($plat['categorie']) ?></td>
                                <td><?= formatPrice($plat['prix']) ?></td>
                                <td>
                                    <?php if ($plat['disponible']): ?>
                                        <span class="badge badge-success">Oui</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger">Non</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Changer la disponibilité ?');">
                                            <input type="hidden" name="action" value="toggle_disponible">
                                            <input type="hidden" name="id" value="<?= $plat['id'] ?>">
                                            <button type="submit" class="btn-icon" title="Toggle disponibilité">🔄</button>
                                        </form>
                                        <a href="?edit=<?= $plat['id'] ?>" class="btn-icon" title="Modifier">✏️</a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce plat ?');">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="id" value="<?= $plat['id'] ?>">
                                            <button type="submit" class="btn-icon btn-danger" title="Supprimer">🗑️</button>
                                        </form>
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

