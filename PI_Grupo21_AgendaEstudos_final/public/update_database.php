<?php
require_once __DIR__ . '/../config/db.php';
echo "<h2>🔧 Atualizando estrutura do banco de dados...</h2>";
try {
    if (!$mysqli) throw new Exception("Falha na conexão com o banco de dados.");
    $check = $mysqli->query("SHOW COLUMNS FROM tarefas LIKE 'status'");
    if ($check->num_rows === 0) {
        $sql = "ALTER TABLE tarefas ADD COLUMN status ENUM('pending','running','paused','completed') DEFAULT 'pending'";
        if ($mysqli->query($sql) === TRUE) echo "<p>✅ Coluna <strong>status</strong> criada com sucesso.</p>";
        else echo "<p style='color:red;'>Erro criando status: " . htmlspecialchars($mysqli->error) . "</p>";
    } else echo "<p>ℹ️ Coluna <strong>status</strong> já existe, ignorando.</p>";

    $check2 = $mysqli->query("SHOW COLUMNS FROM tarefas LIKE 'tempo_gasto'");
    if ($check2->num_rows === 0) {
        $sql2 = "ALTER TABLE tarefas ADD COLUMN tempo_gasto INT DEFAULT 0";
        if ($mysqli->query($sql2) === TRUE) echo "<p>✅ Coluna <strong>tempo_gasto</strong> criada com sucesso.</p>";
        else echo "<p style='color:red;'>Erro criando tempo_gasto: " . htmlspecialchars($mysqli->error) . "</p>";
    } else echo "<p>ℹ️ Coluna <strong>tempo_gasto</strong> já existe, ignorando.</p>";

    echo "<hr><p><strong>Banco de dados atualizado com sucesso!</strong></p>";
    echo "<p>Apague este arquivo por segurança.</p>";
    echo "<p><a href='dashboard.php'>Voltar ao painel</a></p>";
} catch (Exception $e) {
    echo "<p style='color:red;'>Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>