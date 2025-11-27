<?php
/**
 * admin/utilisateurs.php
 * ----------------------
 * Gestion des utilisateurs (clients)
 */

$pageTitle = 'Gestion des Utilisateurs';

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

requireAdminLogin();

$pdo = getPDO();
$message = '';
$error = '';

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    
    // Ne pas permettre la suppression de l'admin connecté
    if ($id === $_SESSION['admin_id']) {
        $error = 'Vous ne pouvez pas supprimer votre propre compte.';
    } elseif ($id > 0) {
        try {
            // Supprimer uniquement les clients (is_admin = FALSE)
            $stmt = $pdo->prepare('DELETE FROM utilisateur WHERE id = :id AND is_admin = FALSE');
            $stmt->execute(['id' => $id]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'Utilisateur supprimé avec succès.';
            } else {
                $error = 'Utilisateur introuvable ou ne peut pas être supprimé.';
            }
        } catch (PDOException $e) {
            $error = 'Erreur lors de la suppression.';
        }
    }
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit') {
    $id = intval($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    
    if (empty($nom) || empty($prenom) || empty($email)) {
        $error = 'Veuillez remplir tous les champs obligatoires.';
    } else {
        try {
            $stmt = $pdo->prepare('UPDATE utilisateur SET nom = :nom, prenom = :prenom, email = :email, telephone = :telephone WHERE id = :id');
            $stmt->execute([
                'id' => $id,
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephone,
            ]);
            $message = 'Utilisateur modifié avec succès.';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'Cet email est déjà utilisé.';
            } else {
                $error = 'Erreur lors de la modification.';
            }
        }
    }
}

// Récupération de l'utilisateur à éditer
$editUser = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $stmt = $pdo->prepare('SELECT * FROM utilisateur WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $editUser = $stmt->fetch();
}

// Liste des utilisateurs (clients uniquement : is_admin = FALSE)
$users = $pdo->query("SELECT * FROM utilisateur WHERE is_admin = FALSE ORDER BY nom, prenom")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-success"><?= e($message) ?></div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= e($error) ?></div>
<?php endif; ?>

<?php if ($editUser): ?>
    <div class="card">
        <div class="card-header">
            <h2>Modifier un utilisateur</h2>
        </div>
        <div class="card-body">
            <form method="POST" class="form">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom *</label>
                        <input type="text" name="nom" value="<?= e($editUser['nom']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" value="<?= e($editUser['prenom']) ?>" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Email *</label>
                        <input type="email" name="email" value="<?= e($editUser['email']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Téléphone</label>
                        <input type="text" name="telephone" value="<?= e($editUser['telephone'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Modifier</button>
                    <a href="utilisateurs.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>Liste des clients</h2>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted">Aucun client enregistré</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?= $user['id'] ?></td>
                                <td><strong><?= e($user['nom']) ?></strong></td>
                                <td><?= e($user['prenom']) ?></td>
                                <td><?= e($user['email']) ?></td>
                                <td><?= e($user['telephone'] ?? '-') ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?edit=<?= $user['id'] ?>" class="btn-icon" title="Modifier">✏️</a>
                                        <?php if ($user['id'] !== $_SESSION['admin_id']): ?>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                                <button type="submit" class="btn-icon btn-danger" title="Supprimer">🗑️</button>
                                            </form>
                                        <?php endif; ?>
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

