<?php
/**
 * menu.php
 * --------
 * Client menu page - Browse and order dishes
 */

require_once __DIR__ . '/db/config.php';
requireClient();

require_once __DIR__ . '/php/menu.php';

$category = isset($_GET['category']) ? $_GET['category'] : 'all';
$dishes = getAllDishes($category);
$categories = getCategories();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Restaurant Les Jomox</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <h2>Restaurant Les Jomox</h2>
                <ul class="nav-links">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="menu.php">Menu</a></li>
                    <li><a href="reservations.php">Réservations</a></li>
                    <li><a href="php/auth.php?action=logout">Déconnexion</a></li>
                </ul>
                <div class="user-info">
                    <span>Bienvenue, <?php echo htmlspecialchars($_SESSION['user_nom'] ?? $_SESSION['user_prenom'] ?? 'Utilisateur'); ?></span>
                </div>
            </nav>
        </header>

        <div class="dashboard">
            <h1>Notre Menu</h1>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="filter-section">
                <label for="category">Filtrer par catégorie :</label>
                <select id="category" onchange="window.location.href='menu.php?category=' + this.value">
                    <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>Toutes les catégories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (empty($dishes)): ?>
                <div class="text-center">
                    <p>Aucun plat trouvé dans cette catégorie.</p>
                </div>
            <?php else: ?>
                <div class="menu-grid">
                    <?php foreach ($dishes as $dish): ?>
                        <div class="dish-card">
                            <div class="dish-image">
                                <div class="dish-icon">🍽️</div>
                            </div>
                            <div class="dish-info">
                                <h3><?php echo htmlspecialchars($dish['nom']); ?></h3>
                                <?php if (!empty($dish['description'])): ?>
                                    <p class="dish-description"><?php echo htmlspecialchars($dish['description']); ?></p>
                                <?php endif; ?>
                                <div class="category"><?php echo htmlspecialchars($dish['categorie']); ?></div>
                                <div class="price"><?php echo number_format($dish['prix'], 2, ',', ' '); ?> €</div>
                                <form action="php/orders.php" method="POST" class="order-form">
                                    <input type="hidden" name="action" value="place_order">
                                    <input type="hidden" name="dish_id" value="<?php echo $dish['id']; ?>">
                                    <label>Quantité :</label>
                                    <input type="number" name="quantity" value="1" min="1" required>
                                    <button type="submit" class="btn btn-primary btn-sm">Commander</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
