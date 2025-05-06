<?php
     $path = __DIR__ . '/data/gerencia_ativos.db';
     $db = new PDO('sqlite:' . $path);
     $columns = $db->query("PRAGMA table_info('entregaveis')")->fetchAll(PDO::FETCH_COLUMN, 1);
     echo in_array('DATA DA ENTREGA 2°', $columns)
       ? 'Coluna presente' : 'Coluna ausente';
