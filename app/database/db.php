<?php

$envPath = dirname(__DIR__) . '/.env';

if (file_exists($envPath)) {
    $env = parse_ini_file($envPath);
} else {
    $env = [];
    error_log("Le fichier .env n'a pas été trouvé à : " . $envPath);
}

$host = $env['DB_HOST'];
$dbname = $env['DB_NAME'];
$user = $env['DB_USER'];
$password = $env['DB_PASSWORD'];
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
