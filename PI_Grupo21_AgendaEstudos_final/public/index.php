<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$login_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $login_error = 'Preencha e-mail e senha.';
    } else {
        $stmt = $mysqli->prepare("SELECT id, nome, senha_hash FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($senha, $row['senha_hash'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['user_name'] = $row['nome'];
                header('Location: dashboard.php');
                exit;
            } else {
                $login_error = 'Senha incorreta.';
            }
        } else {
            $login_error = 'Usuário não encontrado.';
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Login - Agenda de Estudos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <style>
    /* Dark mode custom */
    body.dark {
      background-color: #121212;
      color: #e0e0e0;
    }
    body.dark .card {
      background-color: #1e1e1e;
      color: #e0e0e0;
    }
    body.dark .form-control {
      background-color: #2b2b2b;
      color: #e0e0e0;
      border-color: #444;
    }
    body.dark .form-control:focus {
      background-color: #2b2b2b;
      color: #fff;
      border-color: #0d6efd;
      box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25);
    }
    body.dark .btn-primary {
      background-color: #0d6efd;
      border-color: #0d6efd;
    }
    body.dark .navbar {
      background-color: #1a1a1a !important;
    }
    body.dark a {
      color: #0d6efd;
    }
  </style>
</head>
<body>
  <nav class="navbar navbar-dark bg-primary px-3">
    <span class="navbar-brand mb-0 h1">Agenda de Estudos</span>
    <button id="theme-toggle" class="btn btn-outline-light btn-sm">🌙</button>
  </nav>

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h3 class="card-title mb-3 text-center">Login</h3>
            <?php if($login_error): ?>
              <div class="alert alert-danger"><?=htmlspecialchars($login_error)?></div>
            <?php endif; ?>
            <form method="post" novalidate>
              <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
              </div>
              <div class="d-grid">
                <button class="btn btn-primary" type="submit">Entrar</button>
              </div>
            </form>
            <hr>
            <p class="text-center mb-0">Não tem conta? <a href="register.php">Cadastre-se</a></p>
          </div>
        </div>
      </div>
    </div>
  </div>

<script>
  // Alternar tema dark/light
  const btn = document.getElementById('theme-toggle');
  const body = document.body;

  // Checar preferência anterior
  if(localStorage.getItem('theme') === 'dark') {
    body.classList.add('dark');
    btn.textContent = '☀️';
  }

  btn.addEventListener('click', () => {
    body.classList.toggle('dark');
    if(body.classList.contains('dark')) {
      localStorage.setItem('theme', 'dark');
      btn.textContent = '☀️';
    } else {
      localStorage.setItem('theme', 'light');
      btn.textContent = '🌙';
    }
  });
</script>
</body>
</html>
