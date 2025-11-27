<?php
/**
 * admin/includes/db.php
 * ---------------------
 * Database connection for admin (uses PDO from db/config.php)
 * This file now just includes the main config to avoid duplication
 */

require_once __DIR__ . '/../../db/config.php';

// getPDO() is now available from db/config.php
// No need to duplicate the function here
?>
