<?php
/**
 * index.php
 * ---------
 * Entry point - redirects based on login status
 */

require_once __DIR__ . '/db/config.php';

// Redirect based on login status
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>
