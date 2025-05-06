<?php
// public/brr.php
require __DIR__ . '/db.php';

$error_message = '';
$rows = [];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $fields = ['EXECUTIVA', 'TEMA', 'ENTREGA', 'QUEM_RECEBE', 'FREQUENCIA', 'OBSERVACAO'];
        $dates = [];
        for ($i = 1; $i <= 12; $i++) {
            $dates[] = $_POST["data_entrega_$i"] ?? null;
        }

        $cols = implode(', ', array_merge(
            $fields,
            array_map(fn($i) => "\"DATA DA ENTREGA {$i}°\"", range(1, 12))
        ));
        $placeholders = implode(', ', array_merge(
            array_fill(0, count($fields), '?'),
            array_fill(0, 12, '?')
        ));

        $stmtInsert = $pdo->prepare("INSERT INTO entregaveis ($cols) VALUES ($placeholders)");
        $values = [];

        foreach ($fields as $f) {
            $key = strtolower(str_replace(' ', '_', $f));
            $values[] = $_POST[$key] ?? '';
        }
        foreach ($dates as $d) {
            $values[] = $d ?: null;
        }

        $stmtInsert->execute($values);
        header('Location: brr.php');
        exit;
    }

    $stmt = $pdo->query("SELECT * FROM entregaveis WHERE EXECUTIVA = 'EXECUTIVA DE BRR'");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    $error_message = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Entregáveis BRR</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <div id="page">
    <header>
      <img src="imagens/logo-branco.12a7b411.png" alt="Logo da Empresa" class="logo">
      <h1>ENTREGÁVEIS EXECUTIVA DE BRR</h1>
      <button id="voltar" class="back-button">Voltar</button>
    </header>

    <main class="main-container">
      <?php if (!empty($error_message)): ?>
        <div class="error">Erro: <?= htmlspecialchars($error_message); ?></div>
      <?php endif; ?>

      <button id="new-task" class="action-button">+ Nova Tarefa</button>
      <form id="new-task-form" class="task-form animated-form" action="brr.php" method="POST">
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

          <?php for ($i = 1; $i <= 12; $i++): ?>
            <label class="data-field" id="data-field-<?= $i; ?>">
              Data <?= $i; ?>°
              <input type="date" name="data_entrega_<?= $i; ?>">
            </label>
          <?php endfor; ?>

          <label>Observação
            <textarea name="observacao"></textarea>
          </label>
        </div>

        <div class="date-button-group">
          <button type="button" id="add-date-btn">Acrescentar Data</button>
          <button type="button" id="remove-date-btn">Remover Última Data</button>
        </div>
        <button type="submit" class="action-button">Salvar</button>
      </form>

      <?php if (empty($rows)): ?>
        <p class="no-data">Nenhum entregável encontrado.</p>
      <?php else: ?>
        <table class="deliverables-table">
          <thead>
            <tr>
              <th>Executiva</th>
              <th>Tema</th>
              <th>Entrega</th>
              <th>Quem Recebe?</th>
              <th>Frequência</th>
              <th>Datas</th>
              <th>Observação</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
              <tr>
                <td><?= htmlspecialchars($row['EXECUTIVA']); ?></td>
                <td><?= htmlspecialchars($row['TEMA']); ?></td>
                <td><?= htmlspecialchars($row['ENTREGA']); ?></td>
                <td><?= htmlspecialchars($row['QUEM_RECEBE']); ?></td>
                <td><?= htmlspecialchars($row['FREQUENCIA']); ?></td>
                <td>
                  <?php
                    $dlist = [];
                    for ($j = 1; $j <= 12; $j++) {
                      $key = "DATA DA ENTREGA {$j}°";
                      if (!empty($row[$key])) {
                        $dlist[] = htmlspecialchars($row[$key]);
                      }
                    }
                    echo implode('<br>', $dlist);
                  ?>
                </td>
                <td><?= htmlspecialchars($row['OBSERVACAO']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </main>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const page = document.getElementById('page');
      page.classList.add('visible');

      const newTaskBtn = document.getElementById('new-task');
      const newTaskForm = document.getElementById('new-task-form');
      newTaskForm.classList.add('animated-form');

      newTaskBtn.addEventListener('click', function (e) {
        e.preventDefault();
        newTaskForm.classList.toggle('visible');
      });

      document.getElementById('voltar').addEventListener('click', function (e) {
        e.preventDefault();
        page.classList.remove('visible');
        setTimeout(() => {
          window.location.href = 'index.php';
        }, 400);
      });

      const addBtn = document.getElementById('add-date-btn');
      const removeBtn = document.getElementById('remove-date-btn');
      let visibleCount = 1;
      const maxFields = 12;

      addBtn.addEventListener('click', function () {
        if (visibleCount < maxFields) {
          visibleCount++;
          const nextField = document.getElementById(`data-field-${visibleCount}`);
          if (nextField) {
            nextField.classList.add('visible');  // Adiciona classe para exibir com fade
          }
        }

        if (visibleCount > 1) {
          removeBtn.style.display = 'inline-block';
        }

        if (visibleCount === maxFields) {
          addBtn.disabled = true;
        }
      });

      removeBtn.addEventListener('click', function () {
        if (visibleCount > 1) {
          const field = document.getElementById(`data-field-${visibleCount}`);
          if (field) {
            const input = field.querySelector('input');
            if (input) input.value = '';
            field.classList.remove('visible');  // Remove classe para ocultar com fade
          }
          visibleCount--;
        }

        if (visibleCount === 1) {
          removeBtn.style.display = 'none';
        }

        if (visibleCount < maxFields) {
          addBtn.disabled = false;
        }
      });

      // Assegura que o primeiro campo de data seja sempre visível
      const firstDateField = document.getElementById('data-field-1');
      if (firstDateField) {
        firstDateField.classList.add('visible');
      }
    });
  </script>
</body>
</html>
