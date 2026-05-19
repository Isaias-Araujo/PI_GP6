<?php
require_once __DIR__ . '/../config/db.php';
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || $email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } else {
        $check = $mysqli->prepare("SELECT id FROM usuarios WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows > 0) {
            $erro = 'E-mail já cadastrado.';
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $nome, $email, $senha_hash);
            $stmt->execute();
            $sucesso = 'Cadastro realizado com sucesso! <a href="index.php">Clique aqui para entrar</a>.';
        }
        $check->close();
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Cadastro - Agenda de Estudos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="light-mode">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">Agenda de Estudos</a>
    <div class="d-flex align-items-center">
      <a href="index.php" class="btn btn-outline-light btn-sm ms-2">Voltar</a>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h3 class="card-title text-center mb-3">Criar Conta</h3>

          <?php if($erro): ?>
            <div class="alert alert-danger"><?=$erro?></div>
          <?php endif; ?>
          <?php if($sucesso): ?>
            <div class="alert alert-success"><?=$sucesso?></div>
          <?php endif; ?>

          <form method="post">
            <div class="mb-3">
              <label class="form-label">Nome completo</label>
              <input type="text" class="form-control" name="nome" required>
            </div>
            <div class="mb-3">
              <label class="form-label">E-mail</label>
              <input type="email" class="form-control" name="email" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Senha</label>
              <input type="password" class="form-control" name="senha" required>
            </div>
            <div class="d-grid">
              <button type="submit" class="btn btn-primary">Cadastrar</button>
            </div>
          </form>

          <hr>
          <p class="text-center mb-0"><a href="index.php">Voltar ao login</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/theme.js"></script>
</body>
</html>
