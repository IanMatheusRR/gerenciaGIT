<?php
// seed_db.php
require 'db.php';

$stmt = $pdo->prepare(
  'INSERT INTO entregaveis (
     EXECUTIVA, TEMA, ENTREGA, QUEM_RECEBE, FREQUENCIA,
     "DATA DA ENTREGA 1°", "DATA DA ENTREGA 2°", OBSERVACAO
   ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)' );

$params = [
  'EXECUTIVA DE BRR',
  'Teste PHP + SQLite',
  'Entrega inicial',
  'Gerente Interno',
  'Mensal',
  '2025-05-01',
  '2025-06-01',
  'Observação de exemplo'
];

try {
    $stmt->execute($params);
    echo "Dados de teste inseridos.
";
} catch (PDOException $e) {
    die('Erro ao inserir dados: ' . $e->getMessage());
}