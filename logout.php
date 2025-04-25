<?php

$pdo = require_once 'database/db.php';
$authDB = require_once __DIR__ . '/database/security.php';
$sessionId = $_COOKIE['session'];

if ($sessionId) {
    $authDB->logOut($sessionId);
    header('Location: /');
}
