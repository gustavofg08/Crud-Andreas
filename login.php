<?php
session_start();
if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login – Touch Your Butt-on</title>
  <link rel="icon" type="image/png" href="https://i.imgur.com/l8NOfCE.png">
  <style>
*,
*::before,
*::after {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: "Poppins", sans-serif;
  font-size: 1rem;
  line-height: 1.5;
  color: #fff;
  background-color: #1A1A1D;
  min-height: 100vh;
}

/* --- Header & Navigation --- */
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
  box-shadow: 0 0 12px rgba(255,255,255,0.2);
  z-index: 1000;
}

.navbar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  text-decoration: none;
  color: #edf0f1;
  font-weight: bold;
  font-size: 1.125rem; /* ~18px */
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
}

.nav-item a {
  text-decoration: none;
  color: #edf0f1;
  font-weight: 500;
  transition: color 0.3s ease;
}

.nav-item a:hover {
  color: #0088a2;
}

.hamburguer {
  display: none;
  cursor: pointer;
}

.hamburguer .bar {
  display: block;
  width: 25px;
  height: 3px;
  margin: 5px 0;
  background-color: #edf0f1;
  transition: transform 0.3s ease, opacity 0.3s ease;
}

@media (max-width: 768px) {
  .hamburguer {
    display: block;
  }
  .nav_links {
    position: fixed;
    left: -100%;
    top: 70px;
    flex-direction: column;
    background-color: #0F0F0F;
    width: 100%;
    text-align: center;
    transition: left 0.3s ease;
    padding: 20px 0;
  }
  .nav_links.active {
    left: 0;
  }
  .nav-item {
    margin: 16px 0;
  }
}

/* --- Main Content / Login Container --- */
.main-content {
  display: flex;
  justify-content: center;
  align-items: center;
  height: 100vh;
  padding-top: 80px; /* to push below header */
}

.container {
  background: #0D0D0D;
  border-radius: 16px;
  box-shadow: 0 10px 30px rgba(0,0,0,0.6);
  width: 100%;
  max-width: 380px;
  padding: 40px 35px;
  text-align: center;
}

/* Headings & Subtitle */
.container h1 {
  font-size: 1.8rem;
  margin-bottom: 10px;
}

.container p.subtitle {
  color: #ccc;
  font-size: 0.9rem;
  margin-bottom: 25px;
}

/* Form Elements */
label {
  display: block;
  text-align: left;
  font-weight: 600;
  color: #fff;
  margin-bottom: 6px;
  font-size: 0.9rem;
}

input[type="text"],
input[type="password"] {
  width: 100%;
  padding: 12px 14px;
  border-radius: 8px;
  border: 1px solid #555;
  margin-bottom: 18px;
  font-size: 0.95rem;
  background-color: #1f1f1f;
  color: #fff;
  transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

input[type="text"]:focus,
input[type="password"]:focus {
  border-color: #0047ab;
  box-shadow: 0 0 6px rgba(0,71,171,0.3);
  outline: none;
}

/* Button */
button[type="submit"] {
  width: 100%;
  padding: 12px;
  background-color: #0047ab;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s ease, transform 0.2s ease;
}

button[type="submit"]:hover {
  background-color: #003b90;
  transform: scale(1.02);
}

/* Footer Text / Link */
.footer-text {
  margin-top: 25px;
  font-size: 0.85rem;
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

/* Responsive tweaks */
@media (max-width: 600px) {
  .container {
    padding: 30px 20px;
  }
  button[type="submit"] {
    padding: 14px;
    font-size: 1.05rem;
  }
}
  </style>
</head>
<body>
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
    const form = document.getElementById('loginForm');
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const usuario = document.getElementById('usuario').value;
      const senha   = document.getElementById('senha').value;

      fetch('validar_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ usuario, senha })
      })
      .then(res => {
        if (!res.ok) throw res;
        return res.json();
      })
      .then(data => {
        if (data.sucesso) {
          // guarda no localStorage
          localStorage.setItem('usuarioLogado',   data.usuario);
          localStorage.setItem('idUsuarioLogado', data.idUsuario);
          // redireciona
          window.location.href = 'index.php';
        } else {
          alert(data.mensagem);
        }
      })
      .catch(async err => {
        let mensagem = 'Erro na requisição.';
        try {
          const errorData = await err.json();
          if (errorData && errorData.mensagem) mensagem = errorData.mensagem;
        } catch(e) {}
        alert(mensagem);
        console.error(err);
      });
    });
  </script>
</body>
</html>
