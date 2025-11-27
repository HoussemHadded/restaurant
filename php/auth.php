<?php
/**
 * php/auth.php
 * ------------
 * Authentication helper functions (registration and logout only)
 * Note: Login is handled by login.php in the root directory
 */

require_once __DIR__ . '/../db/config.php';

/**
 * Handle registration (Client only)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    // Redirect if already logged in
    if (isLoggedIn()) {
        header('Location: ../dashboard.php');
        exit();
    }
    
    // Get form data (support both name and nom/prenom formats)
    $name = trim($_POST['name'] ?? '');
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telephone = trim($_POST['telephone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // If we have a single "name" field, split it into nom and prenom
    if (!empty($name) && empty($nom) && empty($prenom)) {
        $nameParts = explode(' ', $name, 2);
        $prenom = $nameParts[0];
        $nom = $nameParts[1] ?? $nameParts[0]; // If no last name, use first name
    }
    
    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
        header('Location: ../register.php');
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Format d\'email invalide.';
        header('Location: ../register.php');
        exit();
    }
    
    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Le mot de passe doit contenir au moins 8 caractères.';
        header('Location: ../register.php');
        exit();
    }
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Les mots de passe ne correspondent pas.';
        header('Location: ../register.php');
        exit();
    }
    
    $conn = getDBConnection();
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT id FROM utilisateur WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Cet email est déjà enregistré.';
        $stmt->close();
        $conn->close();
        header('Location: ../register.php');
        exit();
    }
    $stmt->close();
    
    // Hash password and insert user (is_admin = FALSE for clients)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $is_admin = 0; // FALSE = client
    
    // Insert user (telephone can be NULL)
    $stmt = $conn->prepare("INSERT INTO utilisateur (nom, prenom, email, telephone, mot_de_passe, is_admin) VALUES (?, ?, ?, ?, ?, ?)");
    $telephone = empty($telephone) ? null : $telephone;
    $stmt->bind_param("sssssi", $nom, $prenom, $email, $telephone, $hashed_password, $is_admin);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Inscription réussie ! Veuillez vous connecter.';
        $stmt->close();
        $conn->close();
        header('Location: ../login.php');
        exit();
    } else {
        $_SESSION['error'] = 'Erreur lors de l\'inscription. Veuillez réessayer.';
        $stmt->close();
        $conn->close();
        header('Location: ../register.php');
        exit();
    }
}

/**
 * Handle logout
 */
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    // Destroy session
    $_SESSION = [];
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
    header('Location: ../login.php');
    exit();
}
?>
