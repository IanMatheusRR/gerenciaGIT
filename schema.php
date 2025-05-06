<?php
require __DIR__ . '/db.php';
// Obtém informações do esquema
$cols = $pdo->query("PRAGMA table_info('entregaveis')")->fetchAll(PDO::FETCH_ASSOC);
echo '<pre>' . print_r($cols, true) . '</pre>';
