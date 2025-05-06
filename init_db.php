<?php
// public/init_db.php
require __DIR__ . '/db.php';

// 1) Garante que a tabela existe (sem tocar no esquema)
$pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS entregaveis (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  EXECUTIVA TEXT NOT NULL,
  TEMA TEXT NOT NULL,
  ENTREGA TEXT NOT NULL,
  QUEM_RECEBE TEXT,
  FREQUENCIA TEXT,
  OBSERVACAO TEXT
);
SQL
);

// 2) Busca quais colunas já estão presentes
$existingCols = $pdo
  ->query("PRAGMA table_info('entregaveis')")
  ->fetchAll(PDO::FETCH_COLUMN, 1);

// 3) Para cada “DATA DA ENTREGA N°”, adiciona se faltar
for ($i = 1; $i <= 12; $i++) {
    $col = "DATA DA ENTREGA {$i}°";
    if (!in_array($col, $existingCols, true)) {
        $pdo->exec("ALTER TABLE entregaveis ADD COLUMN \"{$col}\" TEXT;");
        echo "Coluna adicionada: {$col}\n";
    }
}

echo "Inicialização concluída.\n";
