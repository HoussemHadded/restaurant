<?php
/**
 * register.php
 * ------------
 * Client registration page
 */

require_once __DIR__ . '/db/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Restaurant Les Jomox</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <h1>Inscription Client</h1>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <form action="php/auth.php" method="POST" class="auth-form">
                <input type="hidden" name="action" value="register">
                
                <div class="form-group">
                    <label for="name">Nom complet</label>
                    <input type="text" id="name" name="name" required placeholder="Prénom Nom">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required placeholder="votre@email.com">
                </div>
                
                <div class="form-group">
                    <label for="telephone">Téléphone (optionnel)</label>
                    <input type="tel" id="telephone" name="telephone" placeholder="0123456789">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe (min. 8 caractères)</label>
                    <input type="password" id="password" name="password" required minlength="8" placeholder="••••••••">
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password" required minlength="8" placeholder="••••••••">
                </div>
                
                <button type="submit" class="btn btn-primary">S'inscrire</button>
            </form>
            
            <p class="auth-link">Vous avez déjà un compte ? <a href="login.php">Connectez-vous ici</a></p>
        </div>
    </div>
</body>
</html>
