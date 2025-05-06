<?php
$databaseFile = __DIR__ . '\data\gerencia_ativos.db';
$dsn = 'sqlite:' . $databaseFile;
try {
  $pdo = new PDO($dsn);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die('Erro ao conectar ao SQLite: ' . $e->getMessage());
}