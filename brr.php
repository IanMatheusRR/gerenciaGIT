<?php
// public/brr.php
require __DIR__ . '/db.php';

$error_message = '';
$rows = [];

// Exclusão
if (isset($_GET['delete_id'])) {
  $id = (int)$_GET['delete_id'];
  $pdo->prepare("DELETE FROM entregaveis WHERE id = ?")->execute([$id]);
  header('Location: brr.php');
  exit;
}

// Atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
  $id = (int)$_POST['update_id'];
  // Agora usamos as chaves EXATAS dos inputs do form de edição (maiúsculas)
  $fields = ['TEMA','ENTREGA','QUEM_RECEBE','FREQUENCIA','OBSERVACAO'];
  $sets   = [];
  $values = [];

  foreach ($fields as $col) {
      $sets[]      = "$col = ?";
      // $_POST["TEMA"], $_POST["ENTREGA"], etc.
      $values[]    = $_POST[$col] ?? '';
  }
  // Datas de entrega 1° a 12°
  for ($i = 1; $i <= 12; $i++) {
      $col = "DATA DA ENTREGA {$i}°";
      $sets[]   = "\"$col\" = ?";
      $values[] = $_POST["date_{$i}"] ?: null;
  }

  $values[] = $id;
  $sql = "UPDATE entregaveis SET ".implode(', ', $sets)." WHERE id = ?";
  $pdo->prepare($sql)->execute($values);

  header('Location: brr.php');
  exit;
}

// Inserção
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['update_id'])) {
    $fields = ['EXECUTIVA','TEMA','ENTREGA','QUEM_RECEBE','FREQUENCIA','OBSERVACAO'];
    $placeholders = implode(',', array_fill(0, count($fields)+12, '?'));
    $cols = implode(',', array_merge(
      $fields,
      array_map(fn($i)=> "\"DATA DA ENTREGA {$i}°\"", range(1,12))
    ));
    $stmt = $pdo->prepare("INSERT INTO entregaveis ($cols) VALUES ($placeholders)");
    $vals = [];
    foreach ($fields as $f) $vals[] = $_POST[strtolower($f)] ?? '';
    for ($i=1; $i<=12; $i++) $vals[] = $_POST["data_entrega_$i"] ?: null;
    $stmt->execute($vals);
    header('Location: brr.php');
    exit;
}

// Busca registros
try {
    $rows = $pdo->query("SELECT * FROM entregaveis WHERE EXECUTIVA='EXECUTIVA DE BRR'")
                ->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Entregáveis BRR</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* apenas para garantir: só .visible ocupa espaço */
    .data-field { display: none; }
    .data-field.visible { display: block; }
  </style>
</head>
<body>
  <div id="page">
    <header>
      <img src="imagens/logo-branco.12a7b411.png" class="logo" alt="Logo">
      <h1>ENTREGÁVEIS EXECUTIVA DE BRR</h1>
      <button id="voltar" class="back-button">Voltar</button>
    </header>
    <main class="main-container">
      <?php if ($error_message): ?>
        <div class="error">Erro: <?= htmlspecialchars($error_message) ?></div>
      <?php endif; ?>

      <!-- Novo registro -->
      <button id="new-task" class="action-button">+ Nova Tarefa</button>
      <form id="new-task-form" class="animated-form" method="POST" action="brr.php">
        <div class="form-grid">
          <label>Executiva
            <input type="text" name="executiva" value="EXECUTIVA DE BRR" readonly>
          </label>
          <label>Tema
            <input type="text" name="tema" required>
          </label>
          <label>Entrega
            <input type="text" name="entrega" required>
          </label>
          <label>Quem Recebe?
            <input type="text" name="quem_recebe">
          </label>
          <label>Frequência
            <input type="text" name="frequencia">
          </label>

          <?php for($i=1;$i<=12;$i++): ?>
            <label class="data-field<?= $i===1?' visible':'' ?>" id="data-field-<?= $i ?>">
              Data <?= $i ?>°
              <input type="date" name="data_entrega_<?= $i ?>">
            </label>
          <?php endfor; ?>

          <label>Observação
            <textarea name="observacao"></textarea>
          </label>
        </div>
        <div class="date-button-group">
          <button type="button" id="add-date-btn">Acrescentar Data</button>
          <button type="button" id="remove-date-btn" style="display:none;">Remover Última Data</button>
        </div>
        <button type="submit" class="action-button">Salvar</button>
      </form>

      <!-- Tabela -->
      <?php if ($rows): ?>
      <table class="deliverables-table">
        <thead>
          <tr>
            <th>ID</th><th>Tema</th><th>Entrega</th><th>Quem Recebe?</th>
            <th>Frequência</th><th>Datas</th><th>Obs</th><th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r): 
          // monta array de datas para JSON
          $dates = [];
          for($j=1;$j<=12;$j++){
            $col = "DATA DA ENTREGA {$j}°";
            if ($r[$col]) $dates[] = $r[$col];
          }
          $jsonDates = htmlspecialchars(json_encode($dates), ENT_QUOTES);
        ?>
          <tr data-id="<?= $r['id'] ?>"
              data-tema="<?= htmlspecialchars($r['TEMA'],ENT_QUOTES) ?>"
              data-entrega="<?= htmlspecialchars($r['ENTREGA'],ENT_QUOTES) ?>"
              data-quem="<?= htmlspecialchars($r['QUEM_RECEBE'],ENT_QUOTES) ?>"
              data-frq="<?= htmlspecialchars($r['FREQUENCIA'],ENT_QUOTES) ?>"
              data-obs="<?= htmlspecialchars($r['OBSERVACAO'],ENT_QUOTES) ?>"
              data-dates='<?= $jsonDates ?>'
          >
            <td><?= $r['id'] ?></td>
            <td><?= htmlspecialchars($r['TEMA']) ?></td>
            <td><?= htmlspecialchars($r['ENTREGA']) ?></td>
            <td><?= htmlspecialchars($r['QUEM_RECEBE']) ?></td>
            <td><?= htmlspecialchars($r['FREQUENCIA']) ?></td>
            <td><?= implode('<br>', $dates) ?></td>
            <td><?= htmlspecialchars($r['OBSERVACAO']) ?></td>
            <td>
              <button class="table-action-btn edit">Editar</button>
              <button class="table-action-btn trash-btn" title="Excluir">
                <svg class="trash-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.-->
                  <path fill="#a00808" d="M135.2 17.7L128 32 32 32C14.3 32 0 46.3 0 64S14.3 96 32 96l384 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-96 0-7.2-14.3C307.4 6.8 296.3 0 284.2 0L163.8 0c-12.1 0-23.2 6.8-28.6 17.7zM416 128L32 128 53.2 467c1.6 25.3 22.6 45 47.9 45l245.8 0c25.3 0 46.3-19.7 47.9-45L416 128z"/>
                </svg>
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </main>
  </div>

  <!-- Modal de edição -->
  <div id="modal" class="modal-overlay">
    <div class="modal">
      <button class="close-btn">&times;</button>
      <h2>Editar Entregável</h2>
      <form id="edit-form" method="POST" action="brr.php">
        <input type="hidden" name="update_id" id="update_id">
        <div class="form-grid">
          <label>Tema<input type="text" name="TEMA" id="edit_tema" required></label>
          <label>Entrega<input type="text" name="ENTREGA" id="edit_entrega" required></label>
          <label>Quem Recebe?<input type="text" name="QUEM_RECEBE" id="edit_quem"></label>
          <label>Frequência<input type="text" name="FREQUENCIA" id="edit_frq"></label>
          <?php for($i=1;$i<=12;$i++): ?>
            <label>Data <?= $i ?>°<input type="date" name="date_<?= $i ?>" id="edit_date_<?= $i ?>"></label>
          <?php endfor; ?>
          <label>Observação<textarea name="OBSERVACAO" id="edit_obs"></textarea></label>
        </div>
        <button type="submit" class="action-button">Atualizar</button>
      </form>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      // Fade-in
      document.getElementById('page').classList.add('visible');

      // Toggle form
      document.getElementById('new-task').onclick = e => {
        e.preventDefault();
        document.getElementById('new-task-form').classList.toggle('visible');
      };

      // Voltar
      document.getElementById('voltar').onclick = e => {
        e.preventDefault();
        document.getElementById('page').classList.remove('visible');
        setTimeout(() => location.href = 'index.php', 400);
      };

      // Datas
      let count = 1, max = 12;
      document.getElementById('add-date-btn').onclick = () => {
        if (count < max) {
          count++;
          document.getElementById('data-field-' + count).classList.add('visible');
        }
        document.getElementById('remove-date-btn').style.display = count > 1 ? 'inline-block' : 'none';
        document.getElementById('add-date-btn').disabled = count === max;
      };
      document.getElementById('remove-date-btn').onclick = () => {
        if (count > 1) {
          document.getElementById('data-field-' + count).classList.remove('visible');
          document.getElementById('data-field-' + count).querySelector('input').value = '';
          count--;
        }
        document.getElementById('remove-date-btn').style.display = count > 1 ? 'inline-block' : 'none';
        document.getElementById('add-date-btn').disabled = false;
      };

      // Modal edição
      const modal = document.getElementById('modal');
      const closeBtn = modal.querySelector('.close-btn');
      closeBtn.onclick = () => modal.classList.remove('visible');

      document.querySelectorAll('.edit').forEach(btn => {
        btn.onclick = () => {
          const tr = btn.closest('tr');
          document.getElementById('update_id').value = tr.dataset.id;
          document.getElementById('edit_tema').value = tr.dataset.tema;
          document.getElementById('edit_entrega').value = tr.dataset.entrega;
          document.getElementById('edit_quem').value = tr.dataset.quem;
          document.getElementById('edit_frq').value = tr.dataset.frq;
          document.getElementById('edit_obs').value = tr.dataset.obs;
          const dates = JSON.parse(tr.dataset.dates || '[]');
          dates.forEach((d, i) => {
            if (d) {
              const fld = document.getElementById('edit_date_' + (i + 1));
              fld.value = d;
            }
          });
          modal.classList.add('visible');
        };
      });

      // Exclusão com animação de lixeira
      document.querySelectorAll('.trash-btn').forEach(btn => {
        btn.onclick = () => {
          const icon = btn.querySelector('.trash-icon');
          icon.classList.add('throwing');

          setTimeout(() => {
            const tr = btn.closest('tr');
            if (tr) tr.remove(); // remove visualmente da tabela

            // Se desejar redirecionar ou deletar no backend:
            const id = tr?.dataset?.id;
            if (id) location.href = '?delete_id=' + id;
          }, 600); // tempo da animação
        };
      });
    });
  </script>
</body>
</html>
