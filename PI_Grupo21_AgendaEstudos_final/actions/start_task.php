<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$task_id = intval($_POST['task_id'] ?? 0);
if ($task_id <= 0) {
    echo json_encode(['error' => 'Invalid task id']);
    exit;
}

$mysqli->begin_transaction();

try {
    $stmt = $mysqli->prepare("
        SELECT usuario_id, data_inicio, status 
        FROM tarefas 
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$task) throw new Exception("Task not found");
    if ($task['usuario_id'] != $_SESSION['user_id']) throw new Exception("Forbidden");

    // Se já está rodando → não faz nada
    if ($task['status'] === 'running') {
        $mysqli->commit();
        echo json_encode([
            'ok' => true,
            'started_at' => $task['data_inicio'],
            'message' => 'already_running'
        ]);
        exit;
    }

    // Se nunca iniciou, MySQL preenche data_inicio com CURRENT_TIMESTAMP
    if (empty($task['data_inicio'])) {
        $stmt = $mysqli->prepare("
            UPDATE tarefas 
            SET status='running', data_inicio=CURRENT_TIMESTAMP 
            WHERE id = ?
        ");
    } else {
        // Já iniciou antes → retomada sem alterar data_inicio
        $stmt = $mysqli->prepare("
            UPDATE tarefas 
            SET status='running' 
            WHERE id = ?
        ");
    }

    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $stmt->close();

    // Obter data real salva
    $res = $mysqli->query("SELECT data_inicio FROM tarefas WHERE id = $task_id");
    $row = $res->fetch_assoc();

    $mysqli->commit();

    echo json_encode([
        'ok' => true,
        'started_at' => $row['data_inicio']
    ]);

} catch (Exception $e) {
    $mysqli->rollback();
    echo json_encode(['error' => $e->getMessage()]);
}
?>
