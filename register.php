<?php
session_start();
// assume you set $_SESSION['usuario_id'], $_SESSION['usuario_nome'] (and later $_SESSION['usuario_foto'])
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $conn = new mysqli('localhost', 'root', '', 'touchyourbutton');

    if ($conn->connect_error) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao conectar com o banco']);
        exit;
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    $usuario = $dados['usuario'] ?? '';
    $senha = $dados['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos']);
        exit;
    }

    $check = $conn->prepare("SELECT id FROM usuario WHERE nome = ?");
    $check->bind_param("s", $usuario);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário já existe']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO usuario (nome, senha) VALUES (?, ?)");
    $stmt->bind_param("ss", $usuario, $senha);

    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário registrado com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao registrar usuário']);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrar - Touch Your Butt-on</title>
  <link rel="icon" type="image/png" href="https://i.imgur.com/l8NOfCE.png">
  <style>
   /* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

/* BODY */
body {
    background: #1A1A1D;
    color: #fff;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    flex-direction: column;
    min-height: 100vh;
    padding-top: 120px;
}


/* NAVBAR */
header {
    background-color: #0F0F0F;
    padding: 20px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: fixed; /* fixa no topo */
    top: 0;
    left: 50%; /* centraliza horizontalmente */
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

/* PROFILE */
.profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

#logintext {
    color: #fff;
    font-weight: 500;
    padding: 6px 12px;
    border: 2px solid #fff;
    border-radius: 6px;
    transition: 0.3s;
}

#logintext:hover {
    transform: scale(1.08);
    box-shadow: 0 0 8px #fff;
}

.pfp {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 0 6px rgba(0, 136, 162, 0.4);
    cursor: pointer;
    transition: 0.3s;
}

.pfp:hover {
    transform: scale(1.05);
    box-shadow: 0 0 12px rgba(255, 255, 255, 0.822);
}

/* HAMBURGUER - RESPONSIVO */
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

/* FORM CONTAINER */
.container {
    background: #0D0D0D;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
    width: 400px;
    padding: 40px 35px;
    text-align: center;
}

/* FORM ELEMENTS */
h1 { color: #FFF; font-size: 1.8em; margin-bottom: 10px; }
p.subtitle { color: #FFF; font-size: 0.9em; margin-bottom: 25px; }
label { display: block; text-align: left; font-weight: 600; color: #FFF; margin-bottom: 6px; font-size: 0.9em; }

input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 8px;
    border: 1px solid #cfd6dd;
    margin-bottom: 18px;
    font-size: 0.95em;
    outline: none;
    transition: 0.3s;
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

button:hover { background: #003b90; }

/* FOOTER TEXT */
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

.footer-text a:hover { text-decoration: underline; }

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
        <li class="nav-item"><a href="https://api.whatsapp.com/send/?phone=92155305&text&type=phone_number&app_absent=0">Contact</a></li>
      </ul>
    </nav>
</header>

<div class="container">
  <h1>Crie sua conta</h1>
  <p class="subtitle">Preencha os campos abaixo para se registrar.</p>

  <form id="registerForm">
    <label for="usuario">Usuário</label>
    <input type="text" id="usuario" placeholder="Nome de usuário" required>

    <label for="senha">Senha</label>
    <input type="password" id="senha" placeholder="Digite sua senha" required>

    <button type="submit">Registrar</button>
  </form>

  <p class="footer-text">
    Já tem uma conta? <a href="login.php">Fazer login</a>
  </p>
</div>

<script>
  const hamburguer = document.querySelector('.hamburguer');
  const navLinks = document.querySelector('.nav_links');

  hamburguer.addEventListener('click', () => {
    hamburguer.classList.toggle('active');
    navLinks.classList.toggle('active');
  });

  document.getElementById("registerForm").addEventListener("submit", e => {
    e.preventDefault();
    const usuario = document.getElementById("usuario").value;
    const senha = document.getElementById("senha").value;

    fetch("register.php", {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body:JSON.stringify({ usuario, senha })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.mensagem);
      if(data.sucesso) window.location.href = "login.php";
    })
    .catch(err => console.error(err));
  });
</script>
</body>
</html>
=======
<?php
session_start();
// assume you set $_SESSION['usuario_id'], $_SESSION['usuario_nome'] (and later $_SESSION['usuario_foto'])
?>
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: text/html; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $conn = new mysqli('localhost', 'root', '', 'touchyourbutton');

    if ($conn->connect_error) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao conectar com o banco']);
        exit;
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    $usuario = $dados['usuario'] ?? '';
    $senha = $dados['senha'] ?? '';

    if (empty($usuario) || empty($senha)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos']);
        exit;
    }

    $check = $conn->prepare("SELECT id FROM usuario WHERE nome = ?");
    $check->bind_param("s", $usuario);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário já existe']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO usuario (nome, senha) VALUES (?, ?)");
    $stmt->bind_param("ss", $usuario, $senha);

    if ($stmt->execute()) {
        echo json_encode(['sucesso' => true, 'mensagem' => 'Usuário registrado com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao registrar usuário']);
    }

    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrar - Touch Your Butt-on</title>
  <link rel="icon" type="image/png" href="https://i.imgur.com/l8NOfCE.png">
  <style>
   /* RESET */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Poppins", sans-serif;
}

/* BODY */
body {
    background: #1A1A1D;
    color: #fff;
    display: flex;
    justify-content: flex-start;
    align-items: center;
    flex-direction: column;
    min-height: 100vh;
    padding-top: 120px;
}


/* NAVBAR */
header {
    background-color: #0F0F0F;
    padding: 20px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: fixed; /* fixa no topo */
    top: 0;
    left: 50%; /* centraliza horizontalmente */
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

/* PROFILE */
.profile {
    display: flex;
    align-items: center;
    gap: 10px;
}

#logintext {
    color: #fff;
    font-weight: 500;
    padding: 6px 12px;
    border: 2px solid #fff;
    border-radius: 6px;
    transition: 0.3s;
}

#logintext:hover {
    transform: scale(1.08);
    box-shadow: 0 0 8px #fff;
}

.pfp {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 0 6px rgba(0, 136, 162, 0.4);
    cursor: pointer;
    transition: 0.3s;
}

.pfp:hover {
    transform: scale(1.05);
    box-shadow: 0 0 12px rgba(255, 255, 255, 0.822);
}

/* HAMBURGUER - RESPONSIVO */
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

/* FORM CONTAINER */
.container {
    background: #0D0D0D;
    border-radius: 16px;
    box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
    width: 400px;
    padding: 40px 35px;
    text-align: center;
}

/* FORM ELEMENTS */
h1 { color: #FFF; font-size: 1.8em; margin-bottom: 10px; }
p.subtitle { color: #FFF; font-size: 0.9em; margin-bottom: 25px; }
label { display: block; text-align: left; font-weight: 600; color: #FFF; margin-bottom: 6px; font-size: 0.9em; }

input {
    width: 100%;
    padding: 12px 14px;
    border-radius: 8px;
    border: 1px solid #cfd6dd;
    margin-bottom: 18px;
    font-size: 0.95em;
    outline: none;
    transition: 0.3s;
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

button:hover { background: #003b90; }

/* FOOTER TEXT */
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

.footer-text a:hover { text-decoration: underline; }

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
        <li class="nav-item"><a href="https://api.whatsapp.com/send/?phone=92155305&text&type=phone_number&app_absent=0">Contact</a></li>
      </ul>
    </nav>
</header>

<div class="container">
  <h1>Crie sua conta</h1>
  <p class="subtitle">Preencha os campos abaixo para se registrar.</p>

  <form id="registerForm">
    <label for="usuario">Usuário</label>
    <input type="text" id="usuario" placeholder="Nome de usuário" required>

    <label for="senha">Senha</label>
    <input type="password" id="senha" placeholder="Digite sua senha" required>

    <button type="submit">Registrar</button>
  </form>

  <p class="footer-text">
    Já tem uma conta? <a href="login.php">Fazer login</a>
  </p>
</div>

<script>
  const hamburguer = document.querySelector('.hamburguer');
  const navLinks = document.querySelector('.nav_links');

  hamburguer.addEventListener('click', () => {
    hamburguer.classList.toggle('active');
    navLinks.classList.toggle('active');
  });

  document.getElementById("registerForm").addEventListener("submit", e => {
    e.preventDefault();
    const usuario = document.getElementById("usuario").value;
    const senha = document.getElementById("senha").value;

    fetch("register.php", {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body:JSON.stringify({ usuario, senha })
    })
    .then(res => res.json())
    .then(data => {
      alert(data.mensagem);
      if(data.sucesso) window.location.href = "login.php";
    })
    .catch(err => console.error(err));
  });
</script>
</body>
</html>