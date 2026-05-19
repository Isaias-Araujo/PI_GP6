<?php
session_start();
require_once __DIR__ . '/../config/db.php';

/* ====== Correção de fuso horário ====== */
date_default_timezone_set('America/Sao_Paulo');
$mysqli->query("SET time_zone = '-03:00'");

if (!isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$nome_usuario = $_SESSION['user_name'] ?? 'Usuário';

function formatarData($data) {
    if (!$data || $data === '0000-00-00 00:00:00') return '—';
    return date('d/m/Y H:i', strtotime($data));
}
function formatarDuracaoSegundos($segundos) {
    if ($segundos === null || $segundos === '') return '—';
    $h = floor($segundos / 3600);
    $m = floor(($segundos % 3600) / 60);
    $s = $segundos % 60;
    return sprintf('%02dh %02dm %02ds', $h, $m, $s);
}

/* Handle POST actions (add/start/pause/resume/finish/edit/delete) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = $_SESSION['user_id'];

    /* ========================= NOVA TAREFA ========================= */
    if (isset($_POST['nova_tarefa'])) {
        $titulo = trim($_POST['titulo']);
        $descricao = trim($_POST['descricao']);
        $data_entrega = !empty($_POST['data_entrega']) ? $_POST['data_entrega'] . " 00:00:00" : null;

        $stmt = $mysqli->prepare("
            INSERT INTO tarefas (usuario_id, titulo, descricao, data_entrega, concluido, status, tempo_gasto)
            VALUES (?, ?, ?, ?, 0, 'pending', 0)
        ");
        $stmt->bind_param('isss', $uid, $titulo, $descricao, $data_entrega);
        $stmt->execute();
    }

    /* ========================= INICIAR ========================= */
    if (isset($_POST['iniciar_tarefa'])) {
        $id = (int)$_POST['iniciar_tarefa'];

        $stmt = $mysqli->prepare("
            UPDATE tarefas
            SET status = 'running',
                data_inicio = COALESCE(data_inicio, NOW())
            WHERE id = ? AND usuario_id = ? AND concluido = 0
        ");
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
    }

    /* ========================= PAUSAR ========================= */
    if (isset($_POST['pausar_tarefa'])) {
        $id = (int)$_POST['pausar_tarefa'];

        $stmt = $mysqli->prepare("
            UPDATE tarefas
            SET tempo_gasto = tempo_gasto + TIMESTAMPDIFF(SECOND, data_inicio, NOW()),
                status = 'paused'
            WHERE id = ? AND usuario_id = ? AND status = 'running'
        ");
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
    }

    /* ========================= RETOMAR ========================= */
    if (isset($_POST['retomar_tarefa'])) {
        $id = (int)$_POST['retomar_tarefa'];

        $stmt = $mysqli->prepare("
            UPDATE tarefas
            SET status = 'running'
            WHERE id = ? AND usuario_id = ? AND status = 'paused'
        ");
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
    }

    /* ========================= CONCLUIR ========================= */
    if (isset($_POST['concluir_tarefa'])) {
        $id = (int)$_POST['concluir_tarefa'];

        $stmt = $mysqli->prepare("
            UPDATE tarefas
            SET tempo_gasto = tempo_gasto +
                CASE WHEN status = 'running'
                     THEN TIMESTAMPDIFF(SECOND, data_inicio, NOW())
                     ELSE 0 END,
                data_fim = NOW(),
                status = 'completed',
                concluido = 1
            WHERE id = ? AND usuario_id = ?
        ");
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
    }

    /* ========================= EDITAR ========================= */
    if (isset($_POST['editar_tarefa'])) {
        $id = (int)$_POST['tarefa_id'];
        $titulo = trim($_POST['titulo']);
        $descricao = trim($_POST['descricao']);
        $data_entrega = !empty($_POST['data_entrega']) ? $_POST['data_entrega'] . " 00:00:00" : null;

        $stmt = $mysqli->prepare("
            UPDATE tarefas
            SET titulo = ?, descricao = ?, data_entrega = ?
            WHERE id = ? AND usuario_id = ?
        ");
        $stmt->bind_param('sssii', $titulo, $descricao, $data_entrega, $id, $uid);
        $stmt->execute();
    }

    /* ========================= EXCLUIR ========================= */
    if (isset($_POST['excluir_tarefa'])) {
        $id = (int)$_POST['excluir_tarefa'];

        $stmt = $mysqli->prepare("DELETE FROM tarefas WHERE id=? AND usuario_id=?");
        $stmt->bind_param('ii', $id, $uid);
        $stmt->execute();
    }

    header("Location: dashboard.php");
    exit;
}

/* Fetch tasks */
$stmt = $mysqli->prepare("SELECT id, titulo, descricao, data_inicio, data_entrega, data_fim, concluido, 
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'tarefas' AND COLUMN_NAME = 'tempo_gasto') AS has_tempo,
    (SELECT tempo_gasto FROM tarefas t2 WHERE t2.id = tarefas.id LIMIT 1) AS tempo_gasto,
    (SELECT status FROM tarefas t3 WHERE t3.id = tarefas.id LIMIT 1) AS status_col
    FROM tarefas WHERE usuario_id = ? ORDER BY concluido ASC, data_entrega ASC");
$stmt->bind_param('i', $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$tarefas = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$agora = new DateTime();
$tarefas_atrasadas = [];
foreach ($tarefas as $t) {
    if (!$t['concluido'] && !empty($t['data_entrega']) && new DateTime($t['data_entrega']) < $agora) {
        $tarefas_atrasadas[] = $t;
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Agenda de Estudos - Painel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="css/style.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="light-mode">
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Agenda de Estudos</a>
    <div class="d-flex align-items-center">
      <span class="navbar-text text-white me-3">Olá, <?=htmlspecialchars($nome_usuario)?></span>
      <a href="logout.php" class="btn btn-outline-light btn-sm ms-2">Sair</a>
    </div>
  </div>
</nav>

<div class="container py-4">
  <div class="top-add d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Minhas Tarefas</h4>
    <div>
      <button class="btn btn-primary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#modalNovaTarefa">➕ Nova Tarefa</button>
    </div>
  </div>

  <?php if (!empty($tarefas_atrasadas)): ?>
  <div class="alert alert-danger">
    ⚠️ <strong>Atenção:</strong> Você tem <?=count($tarefas_atrasadas)?> tarefa<?=count($tarefas_atrasadas)>1?'s':''?> vencida<?=count($tarefas_atrasadas)>1?'s':''?>.
  </div>
  <?php endif; ?>

  <div class="table-responsive">
    <table class="table table-hover align-middle">
      <thead class="table-light">
        <tr>
          <th>Título</th>
          <th>Descrição</th>
          <th>Início</th>
          <th>Entrega</th>
          <th>Conclusão</th>
          <th>Tempo Gasto</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($tarefas as $t):
          $atrasada = (!$t['concluido'] && !empty($t['data_entrega']) && new DateTime($t['data_entrega']) < $agora);
          $rowClass = $t['concluido'] ? 'table-success' : ($atrasada ? 'table-danger' : ($t['status_col']=='paused' ? 'table-paused' : ''));
          $status = 'pending'; if (isset($t['status_col']) && $t['status_col']) $status = $t['status_col'];
          $elapsed = null;
          if ($status === 'running') {
              if (!empty($t['tempo_gasto'])) {
                  $elapsed = intval($t['tempo_gasto']) + (time() - strtotime($t['data_inicio']));
              } elseif (!empty($t['data_inicio'])) {
                  $elapsed = time() - strtotime($t['data_inicio']);
              }
          } else {
              if (!empty($t['tempo_gasto'])) $elapsed = intval($t['tempo_gasto']);
              elseif (!empty($t['data_inicio']) && !empty($t['data_fim'])) $elapsed = strtotime($t['data_fim']) - strtotime($t['data_inicio']);
          }
        ?>
        <tr class="<?= $rowClass ?>">
          <td><?=htmlspecialchars($t['titulo'])?></td>
          <td><?=htmlspecialchars($t['descricao'])?></td>
          <td><?=formatarData($t['data_inicio'])?></td>
          <td><?=formatarData($t['data_entrega'])?></td>
          <td><?=formatarData($t['data_fim'])?></td>
          <td><?= $elapsed !== null ? formatarDuracaoSegundos($elapsed) : '—' ?></td>
          <td>
            <?php if ($t['concluido']): ?>
              <span class="badge bg-secondary">Concluída</span>
            <?php else: ?>
              <?php if ($status === 'running'): ?>
                <span class="badge bg-success">Em andamento</span>
              <?php elseif ($status === 'paused'): ?>
                <span class="badge bg-warning text-dark">Pausada</span>
              <?php else: ?>
                <span class="badge bg-info text-dark">Pendente</span>
              <?php endif; ?>
            <?php endif; ?>
          </td>
          <td>
            <?php if (!$t['concluido']): ?>
              <?php if ($status === 'pending'): ?>
                <form method="post" style="display:inline">
                  <button class="btn btn-sm btn-success" name="iniciar_tarefa" value="<?=$t['id']?>">▶️ Iniciar</button>
                </form>
              <?php elseif ($status === 'running'): ?>
                <form method="post" style="display:inline">
                  <button class="btn btn-sm btn-warning" name="pausar_tarefa" value="<?=$t['id']?>">⏸️ Pausar</button>
                </form>
                <form method="post" style="display:inline">
                  <button class="btn btn-sm btn-danger" name="concluir_tarefa" value="<?=$t['id']?>">✅ Concluir</button>
                </form>
              <?php elseif ($status === 'paused'): ?>
                <form method="post" style="display:inline">
                  <button class="btn btn-sm btn-info" name="retomar_tarefa" value="<?=$t['id']?>">🔁 Retomar</button>
                </form>
                <form method="post" style="display:inline">
                  <button class="btn btn-sm btn-danger" name="concluir_tarefa" value="<?=$t['id']?>">✅ Concluir</button>
                </form>
              <?php endif; ?>

              <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalEditar<?=$t['id']?>">✏️</button>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>

            <form method="post" style="display:inline" onsubmit="return confirm('Deseja excluir esta tarefa?')">
              <button class="btn btn-sm btn-outline-danger" name="excluir_tarefa" value="<?=$t['id']?>">🗑️</button>
            </form>
          </td>
        </tr>
        <!-- Modal editar -->
        <div class="modal fade" id="modalEditar<?=$t['id']?>" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog">
            <div class="modal-content">
              <form method="post">
                <div class="modal-header">
                  <h5 class="modal-title">Editar Tarefa</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                  <input type="hidden" name="tarefa_id" value="<?=$t['id']?>">
                  <div class="mb-3">
                    <label>Título</label>
                    <input type="text" name="titulo" class="form-control" value="<?=htmlspecialchars($t['titulo'])?>" required>
                  </div>
                  <div class="mb-3">
                    <label>Descrição</label>
                    <textarea name="descricao" class="form-control" rows="3"><?=htmlspecialchars($t['descricao'])?></textarea>
                  </div>
                  <div class="mb-3">
                    <label>Data de entrega</label>
                    <input type="date" name="data_entrega" class="form-control" value="<?=!empty($t['data_entrega']) ? date('Y-m-d', strtotime($t['data_entrega'])) : ''?>">
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" name="editar_tarefa" class="btn btn-primary">Salvar</button>
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="mt-3 d-flex justify-content-between align-items-center">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaTarefa">➕ Nova Tarefa</button>
    <small class="text-muted">Tema: <span id="theme-name">Claro</span></small>
  </div>

  <!-- Modal nova tarefa -->
  <div class="modal fade" id="modalNovaTarefa" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form method="post">
          <div class="modal-header">
            <h5 class="modal-title">Nova Tarefa</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label>Título</label>
              <input type="text" name="titulo" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Descrição</label>
              <textarea name="descricao" class="form-control" rows="3"></textarea>
            </div>
            <div class="mb-3">
              <label>Data de entrega</label>
              <input type="date" name="data_entrega" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" name="nova_tarefa" class="btn btn-primary">Adicionar</button>
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          </div>
        </form>
      </div>
    </div>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="js/theme.js"></script>
</body>
</html>
