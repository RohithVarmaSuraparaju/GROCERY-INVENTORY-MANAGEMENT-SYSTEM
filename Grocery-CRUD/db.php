<?php
$host = 'localhost';
$db = 'grocery_db';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo("Connected");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
