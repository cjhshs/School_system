<?php
// Simple bootstrap to expose a DB wrapper globally
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

// Initialize DB wrapper
$db = new DB($host, $username, $password, $database);
$GLOBALS['db'] = $db;
?>
