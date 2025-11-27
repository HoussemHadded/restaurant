<?php
/**
 * db/config.php
 * -------------
 * Database configuration and helper functions
 * Provides both mysqli and PDO connections
 */

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'restaurant_db');

/**
 * Create mysqli database connection
 * Used by client-facing pages
 */
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log('Database connection error: ' . $conn->connect_error);
            die('Erreur de connexion à la base de données.');
        }
        
        // Set charset
        $conn->set_charset('utf8mb4');
        
        return $conn;
    } catch (Exception $e) {
        error_log('Database connection error: ' . $e->getMessage());
        die('Erreur de connexion à la base de données.');
    }
}

/**
 * Create PDO database connection
 * Used by admin pages (more secure, better error handling)
 */
function getPDO() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            die('Erreur de connexion à la base de données.');
        }
    }
    
    return $pdo;
}

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin (is_admin = TRUE)
 */
function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Check if user is client (is_admin = FALSE)
 */
function isClient() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === false;
}

/**
 * Legacy function for backward compatibility (deprecated - use isAdmin() instead)
 */
function isManager() {
    return isAdmin();
}

/**
 * Redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Redirect if not admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit();
    }
}

/**
 * Legacy function for backward compatibility (deprecated - use requireAdmin() instead)
 */
function requireManager() {
    requireAdmin();
}

/**
 * Redirect if not client
 */
function requireClient() {
    requireLogin();
    if (!isClient()) {
        header('Location: dashboard.php');
        exit();
    }
}
?>
