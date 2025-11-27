<?php
/**
 * login.php
 * ---------
 * Page de connexion unique pour l'application Restaurant Les Jomox
 * 
 * Fonctionnalités :
 * - Formulaire de connexion (email + mot de passe)
 * - Vérification des identifiants dans la table utilisateur
 * - Redirection selon le rôle (admin → admin/, client → interface client)
 * - Protection contre l'injection SQL (PDO + requêtes préparées)
 * - Hashage sécurisé des mots de passe (password_verify)
 */

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si déjà connecté, rediriger selon le rôle (is_admin)
if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin'])) {
    if ($_SESSION['is_admin']) {
        // Admin → Interface admin
        header('Location: admin/index.php');
        exit();
    } else {
        // Client → Interface client
        header('Location: dashboard.php');
        exit();
    }
}

// Configuration de la base de données
require_once __DIR__ . '/db/config.php';

// Variable pour stocker les messages d'erreur
// Note: getPDO() est disponible depuis db/config.php
$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération et nettoyage des données
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    // Validation des champs
    if (empty($email) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        try {
            // Connexion à la base de données
            $pdo = getPDO();
            
            // Requête préparée pour récupérer l'utilisateur par email
            // is_admin: TRUE = admin, FALSE = client
            $stmt = $pdo->prepare("
                SELECT id, nom, prenom, email, mot_de_passe, is_admin 
                FROM utilisateur 
                WHERE email = :email 
                LIMIT 1
            ");
            
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();
            
            // Vérification de l'utilisateur et du mot de passe
            if ($user && password_verify($password, $user['mot_de_passe'])) {
                // Connexion réussie - Création de la session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['is_admin'] = (bool)$user['is_admin']; // Convertir en booléen PHP
                
                // Clear any old registration/success messages since user is now logged in
                unset($_SESSION['success']);
                
                // Redirection selon le rôle (is_admin)
                if ($user['is_admin']) {
                    // Admin → Interface admin
                    header('Location: admin/index.php');
                    exit();
                } else {
                    // Client → Interface client
                    header('Location: dashboard.php');
                    exit();
                }
            } else {
                // Identifiants incorrects
                $error = 'Email ou mot de passe incorrect.';
            }
            
        } catch (PDOException $e) {
            // Erreur de base de données (ne pas exposer les détails)
            error_log('Login error: ' . $e->getMessage());
            $error = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Restaurant Les Jomox</title>
    <link rel="stylesheet" href="login.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-card">
            <!-- En-tête avec logo/titre -->
            <div class="login-header">
                <div class="icon-circle">
                    <span>🍽️</span>
                </div>
                <h1>Restaurant Les Jomox</h1>
                <p class="subtitle">Connectez-vous à votre compte</p>
            </div>
            
            <!-- Message d'erreur -->
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <span class="alert-icon">⚠️</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Message de succès (ex: après inscription) -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px;">
                    <span class="alert-icon">✅</span>
                    <span><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></span>
                </div>
            <?php endif; ?>
            
            <!-- Formulaire de connexion -->
            <form method="POST" class="login-form" novalidate>
                <div class="form-group">
                    <label for="email">
                        <span class="label-icon">📧</span>
                        Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="votre@email.com"
                        required 
                        autofocus
                        autocomplete="email"
                    >
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <span class="label-icon">🔒</span>
                        Mot de passe
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                    >
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <span>Se connecter</span>
                    <span class="btn-icon">→</span>
                </button>
            </form>
            
            <!-- Liens supplémentaires -->
            <div class="login-footer">
                <p class="text-muted">
                    Vous n'avez pas de compte ? 
                    <a href="register.php" class="link">Créer un compte</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
