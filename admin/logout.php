<?php
/**
 * admin/logout.php
 * ---------------
 * Déconnexion admin
 */

require_once __DIR__ . '/includes/auth.php';

logoutAdmin();
// Rediriger vers le login principal (un niveau au-dessus)
header('Location: ../login.php');
exit();

