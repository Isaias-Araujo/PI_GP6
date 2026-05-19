<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$task_id = intval($_POST['task_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($task_id <= 0 || !in_array($action, ['pause','stop','resume'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid parameters']);
    exit;
}

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare("SELECT usuario_id, data_inicio, data_fim, time_spent_seconds, status FROM tarefas WHERE id = ? FOR UPDATE");
    $stmt->bind_param('i', $task_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $task = $res->fetch_assoc();
    $stmt->close();

    if (!$task) throw new Exception('Task not found');
    if ($task['usuario_id'] != $_SESSION['user_id']) throw new Exception('Forbidden');

    $now = new DateTime();

    if ($action === 'pause' || $action === 'stop') {
        // Pausar ou finalizar: calcular tempo decorrido desde data_inicio (se válida) e somar ao total
        if ($task['status'] !== 'running') {
            throw new Exception('Task not running');
        }

        // Validar data_inicio
        if (empty($task['data_inicio']) || $task['data_inicio'] === '0000-00-00 00:00:00') {
            // Se não existe data_inicio, não somamos tempo (evita negativos)
            $elapsed = 0;
        } else {
            $start = new DateTime($task['data_inicio']);
            $diff = $now->getTimestamp() - $start->getTimestamp();
            // Garantir que diff seja não-negativo (caso relógio tenha sido alterado)
            $elapsed = $diff >= 0 ? $diff : 0;
        }

        $prev_total = intval($task['time_spent_seconds'] ?? 0);
        $new_total = $prev_total + $elapsed;

        $data_fim = ($action === 'stop') ? $now->format('Y-m-d H:i:s') : $task['data_fim'];
        $new_status = ($action === 'stop') ? 'completed' : 'paused';

        $stmt = $mysqli->prepare(\"UPDATE tarefas SET time_spent_seconds = ?, data_fim = ?, status = ? WHERE id = ?\");
        $stmt->bind_param('issi', $new_total, $data_fim, $new_status, $task_id);
        $stmt->execute();
        $stmt->close();

        $mysqli->commit();
        echo json_encode(['ok' => true, 'time_spent_seconds' => $new_total, 'status' => $new_status]);
        exit;
    } elseif ($action === 'resume') {
        // Retomar tarefa: registrar nova data_inicio e manter tempo acumulado
        if ($task['status'] !== 'paused') {
            throw new Exception('Task not paused');
        }
        $now_str = $now->format('Y-m-d H:i:s');
        $stmt = $mysqli->prepare(\"UPDATE tarefas SET data_inicio = ?, status = 'running' WHERE id = ?\");
        $stmt->bind_param('si', $now_str, $task_id);
        $stmt->execute();
        $stmt->close();

        $mysqli->commit();
        echo json_encode(['ok' => true, 'resumed_at' => $now_str]);
        exit;
    }

        if ($task['status'] !== 'paused') {
            throw new Exception('Task not paused');
        }
        $now_str = $now->format('Y-m-d H:i:s');
        $stmt = $mysqli->prepare("UPDATE tarefas SET data_inicio = ?, status = 'running' WHERE id = ?");
        $stmt->bind_param('si', $now_str, $task_id);
        $stmt->execute();
        $stmt->close();

        $mysqli->commit();
        echo json_encode(['ok' => true, 'resumed_at' => $now_str]);
        exit;
    }
} catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>