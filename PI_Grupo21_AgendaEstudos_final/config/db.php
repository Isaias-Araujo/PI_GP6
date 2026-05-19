<?php
// db.php - conexão e criação automática do banco/tabelas
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'db_estudos';

// conecta ao servidor (sem banco)
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS);
if ($mysqli->connect_error) {
    die('Erro de conexão: ' . $mysqli->connect_error);
}

// cria o banco se não existir
$mysqli->query("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
$mysqli->select_db($DB_NAME);

/* ========================= TABLE usuarios ========================= */
$create_users = "
CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  senha_hash VARCHAR(255) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$mysqli->query($create_users);

/* ========================= TABLE tarefas ========================= */
$create_tasks = "
CREATE TABLE IF NOT EXISTS tarefas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  usuario_id INT NOT NULL,
  titulo VARCHAR(255) NOT NULL,
  descricao TEXT,

  /* NOVO: data_criacao — salva ao criar a tarefa */
  data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,

  /* Mantido: só deve ser salva ao clicar em INICIAR */
  data_inicio DATETIME DEFAULT NULL,

  data_entrega DATETIME DEFAULT NULL,
  data_fim DATETIME DEFAULT NULL,

  tempo_gasto INT DEFAULT 0,
  status ENUM('pending','running','paused','completed') DEFAULT 'pending',
  concluido TINYINT(1) DEFAULT 0,

  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$mysqli->query($create_tasks);

/* ========================= USUÁRIO PADRÃO ========================= */
$check = $mysqli->query("SELECT COUNT(*) AS c FROM usuarios")->fetch_assoc();
if (isset($check['c']) && intval($check['c']) === 0) {
    $pw = password_hash('senha123', PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)");
    $nome = 'Admin';
    $email = 'admin@example.com';
    $stmt->bind_param('sss', $nome, $email, $pw);
    $stmt->execute();
}
?>
