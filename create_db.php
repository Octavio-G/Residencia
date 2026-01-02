<?php
try {
    $pdo = new PDO("mysql:host=localhost;port=3306", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "CREATE DATABASE IF NOT EXISTS residencia_db";
    $pdo->exec($sql);
    echo "Database 'residencia_db' created successfully or already exists.";
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>