<?php
$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db = getenv('MYSQLDATABASE') ?: 'vite_gourmand';
$port = getenv('MYSQLPORT') ?: '3306';

try {
    $pdo = new PDO(
        'mysql:host='.$host.';port='.$port.';dbname='.$db.';charset=utf8',
        $user,
        $pass
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die(json_encode(['error' => 'Connexion impossible : ' . $e->getMessage()]));
}
?>