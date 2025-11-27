<?php
/**
 * admin/includes/auth.php
 * -----------------------
 * Fonctions d'authentification et de sécurité pour l'admin
 * Note: La session est déjà démarrée dans db/config.php
 */

/**
 * Vérifie si l'utilisateur est connecté en tant qu'admin
 * Utilise is_admin (booléen) : TRUE = admin, FALSE = client
 * Compatible avec la session créée par login.php principal
 */
function isAdminLoggedIn() {
    // Vérifier la session créée par login.php principal
    if (isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
        // Synchroniser avec les variables admin pour compatibilité
        if (!isset($_SESSION['admin_id'])) {
            $_SESSION['admin_id'] = $_SESSION['user_id'];
            $_SESSION['admin_nom'] = $_SESSION['user_nom'] ?? '';
            $_SESSION['admin_prenom'] = $_SESSION['user_prenom'] ?? '';
            $_SESSION['admin_email'] = $_SESSION['user_email'] ?? '';
        }
        return true;
    }
    return false;
}

/**
 * Redirige vers la page de login si non connecté
 * Utilise le login principal (../login.php) au lieu d'un login admin séparé
 */
function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        // Rediriger vers le login principal (un niveau au-dessus)
        header('Location: ../login.php');
        exit();
    }
}

/**
 * Vérifie les identifiants admin
 * @param string $email
 * @param string $password
 * @return array|false Retourne les données de l'admin ou false
 * Note: Vérifie que is_admin = TRUE (seuls les admins peuvent se connecter)
 * 
 * ⚠️ DEPRECATED: Cette fonction n'est plus utilisée car le login principal gère tout.
 * Conservée pour compatibilité si nécessaire.
 */
function verifyAdminCredentials($email, $password) {
    $pdo = getPDO();
    
    // Récupérer uniquement les utilisateurs avec is_admin = TRUE
    $stmt = $pdo->prepare('SELECT id, nom, prenom, email, mot_de_passe, is_admin FROM utilisateur WHERE email = :email AND is_admin = TRUE LIMIT 1');
    $stmt->execute(['email' => $email]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['mot_de_passe'])) {
        return $admin;
    }
    
    return false;
}

/**
 * Démarre la session admin
 * 
 * ⚠️ DEPRECATED: Cette fonction n'est plus utilisée car le login principal gère tout.
 * Conservée pour compatibilité si nécessaire.
 */
function startAdminSession($adminData) {
    $_SESSION['admin_id'] = $adminData['id'];
    $_SESSION['admin_nom'] = $adminData['nom'];
    $_SESSION['admin_prenom'] = $adminData['prenom'];
    $_SESSION['admin_email'] = $adminData['email'];
    $_SESSION['is_admin'] = (bool)$adminData['is_admin']; // Convertir en booléen PHP
}

/**
 * Déconnecte l'admin
 */
function logoutAdmin() {
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

