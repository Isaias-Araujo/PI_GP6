<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed';
    exit;
}

// Basic validation and sanitization
$titulo = trim($_POST['titulo'] ?? '');
$descricao = trim($_POST['descricao'] ?? '');
$data_entrega = trim($_POST['data_entrega'] ?? null);
$disciplina_id = intval($_POST['disciplina_id'] ?? 0);

$errors = [];
if ($titulo === '') $errors[] = 'Título é obrigatório';
if ($disciplina_id <= 0) $disciplina_id = null;

// validate date format (simple)
if ($data_entrega) {
    $d = DateTime::createFromFormat('Y-m-d H:i:s', $data_entrega);
    if (!$d) {
        $errors[] = 'Data de entrega inválida. Use YYYY-MM-DD HH:MM:SS';
    } else {
        $data_entrega = $d->format('Y-m-d H:i:s');
    }
}

if (count($errors) > 0) {
    $_SESSION['errors'] = $errors;
    header('Location: ../public/dashboard.php');
    exit;
}

// Prepared statement to insert
$stmt = $mysqli->prepare("INSERT INTO tarefas (usuario_id, disciplina_id, titulo, descricao, data_entrega, criado_em) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param('iisss', $_SESSION['user_id'], $disciplina_id, $titulo, $descricao, $data_entrega);
$stmt->execute();
$stmt->close();

header('Location: ../public/dashboard.php');
exit;
?>