<?php
session_start();

// Lê o JSON com os usuários cadastrados
$usuarios = json_decode(file_get_contents('users.json'), true);

// Lê o corpo da requisição (via formulário ou JSON)
$dados = $_POST;
// Caso esteja usando fetch com JSON:
// $dados = json_decode(file_get_contents('php://input'), true);

$usuario = $dados['usuario'] ?? '';
$senha = $dados['senha'] ?? '';

$logado = false;

// Verifica se o usuário existe no JSON
foreach ($usuarios as $u) {
    if ($u['usuario'] === $usuario && $u['senha'] === $senha) {
        $logado = true;
        break;
    }
}

if ($logado) {
    $_SESSION['logado'] = true;
    $_SESSION['usuario'] = $usuario;
    header('Location: home.php');
    exit;
} else {
    $_SESSION['logado'] = false;
    echo "Usuário ou senha incorretos!";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - Touch Your Butt-on</title>
  <link rel="icon" type="image/png" href="https://i.imgur.com/l8NOfCE.png">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: "Poppins", sans-serif;
    }

    body {
      background: #1A1A1D;
      min-height: 100vh;
      margin: 0;
      padding: 0;
    }

    .main-content {
      display: flex;
      justify-content: center;
      align-items: center;
      height: calc(100vh - 80px);
    }

    .container {
      background: #0D0D0D;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
      width: 380px;
      padding: 40px 35px;
      text-align: center;
      position: relative;
      z-index: 2;
    }

    h1 {
      color: #FFF;
      font-size: 1.8em;
      margin-bottom: 10px;
    }

    p.subtitle {
      color: #FFF;
      font-size: 0.9em;
      margin-bottom: 25px;
    }

    label {
      display: block;
      text-align: left;
      font-weight: 600;
      color: #FFF;
      margin-bottom: 6px;
      font-size: 0.9em;
    }

    input {
      width: 100%;
      padding: 12px 14px;
      border-radius: 8px;
      border: 1px solid #cfd6dd;
      margin-bottom: 18px;
      font-size: 0.95em;
      transition: 0.3s;
      outline: none;
    }

    input:focus {
      border-color: #0047ab;
      box-shadow: 0 0 6px rgba(0, 71, 171, 0.3);
    }

    button {
      width: 100%;
      padding: 12px;
      background: #0047ab;
      color: #fff;
      border: none;
      border-radius: 8px;
      font-size: 1em;
      font-weight: 600;
      cursor: pointer;
      transition: 0.3s;
    }

    button:hover {
      background: #003b90;
    }

    .footer-text {
      margin-top: 25px;
      font-size: 0.85em;
      color: #6c757d;
    }

    .footer-text a {
      color: #0047ab;
      text-decoration: none;
      font-weight: 600;
    }

    .footer-text a:hover {
      text-decoration: underline;
    }

    header {
      background-color: #0F0F0F;
      padding: 20px 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: fixed;
      top: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 90%;
      max-width: 1200px;
      border-radius: 15px;
      box-shadow: 0 0 12px rgb(255, 255, 255);
      z-index: 9999;
    }

    .navbar-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
      color: #edf0f1;
      font-weight: bold;
      font-size: 18px;
    }

    .navbar-brand img {
      width: 40px;
      height: 40px;
    }

    .nav_links {
      list-style: none;
      display: flex;
      align-items: center;
      gap: 20px;
      margin: 0;
      padding: 0;
    }

    .nav-item a {
      text-decoration: none;
      color: #edf0f1;
      font-weight: 500;
      transition: 0.3s;
    }

    .nav-item a:hover {
      color: #0088a2;
    }

    .hamburguer {
      display: none;
      cursor: pointer;
      position: absolute;
      top: 25px;
      right: 40px;
      z-index: 1000;
    }

    .bar {
      display: block;
      width: 25px;
      height: 3px;
      margin: 5px;
      background-color: #edf0f1;
      transition: 0.3s;
    }

    @media (max-width: 768px) {
      .hamburguer { display: block; }
      .hamburguer.active .bar:nth-child(2) { opacity: 0; }
      .hamburguer.active .bar:nth-child(1) { transform: translateY(8px) rotate(45deg); }
      .hamburguer.active .bar:nth-child(3) { transform: translateY(-8px) rotate(-45deg); }
      .nav_links {
        position: fixed;
        left: -100%;
        top: 70px;
        flex-direction: column;
        background-color: #0F0F0F;
        width: 100%;
        text-align: center;
        transition: 0.3s;
        padding: 20px 0;
      }
      .nav_links.active { left: 0; }
      .nav-item { margin: 16px 0; }
    }

    a {
      text-decoration: none;
      color: white;
    }
  </style>
</head>

<body>
  <header>
    <a class="navbar-brand" href="#">
      <img src="https://i.imgur.com/l8NOfCE.png" alt="Logo">
      Touch Your Butt-on
    </a>

    <div class="hamburguer">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </div>

    <nav>
      <ul class="nav_links">
        <li class="nav-item"><a href="index.php">Home</a></li>
        <li class="nav-item"><a href="about.html">About</a></li>
        <li class="nav-item"><a href="https://api.whatsapp.com/send/?phone=92155305">Contact</a></li>
      </ul>
    </nav>
  </header>

  <div class="main-content">
    <div class="container">
      <h1>Touch Your Butt-on</h1>
      <p class="subtitle">Digite os seus dados de acesso no campo abaixo.</p>

      <form id="loginForm">
        <label for="usuario">Usuário</label>
        <input type="text" id="usuario" placeholder="Nome de Usuário" required />

        <label for="senha">Senha</label>
        <input type="password" id="senha" placeholder="Digite sua senha" required />

        <button type="submit">Acessar</button>
      </form>

      <p class="footer-text">
        Não tem uma conta? <a href="register.php">Cadastre-se</a>
      </p>
    </div>
  </div>

  <script>
    const hamburger = document.querySelector('.hamburguer');
    const navLinks = document.querySelector('.nav_links');

    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      navLinks.classList.toggle('active');
    });

    const form = document.getElementById('loginForm');
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const usuario = document.getElementById('usuario').value;
      const senha = document.getElementById('senha').value;

      fetch('validar_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ usuario, senha })
      })
      .then(res => res.json())
      .then(data => {
        if (data.sucesso) {
          window.location.href = 'index.php';
        } else {
          alert(data.mensagem);
        }
      })
      .catch(err => console.error(err));
    });
  </script>
</body>
</html>
