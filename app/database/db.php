<?php

$host = 'localhost';
$dbname = 'portfolio';
$user = 'root';
$password = 'Root1234!';
$charset = "utf8mb4";

$dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=$charset";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    throw new Exception('Erreur de connexion à la base de données : ' . $e->getMessage());
}

return $pdo;
