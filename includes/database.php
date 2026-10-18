<?php
// Database connection. Change these settings only if your MySQL settings are different.
$host = 'localhost';
$dbname = 'jayarathne_construction';
$username = 'root';
$password = '';
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $error) {
    exit('Database connection failed. Please import database.sql in phpMyAdmin.');
}
