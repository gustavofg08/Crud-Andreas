<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Registrar - Touch Your Butt-on</title>
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
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .container {
      background: #0D0D0D;
      border-radius: 16px;
      box-shadow: 0 10px 30px rgba(255, 255, 255, 0.1);
      width: 400px;
      padding: 40px 35px;
      text-align: center;
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
  </style>
</head>
<body>
  <div class="container">
    <h1>Crie sua conta</h1>
    <p class="subtitle">Preencha os campos abaixo para se registrar.</p>

    <form>
      <label for="nome">Nome completo</label>
      <input type="text" id="nome" placeholder="Seu nome completo" required />

      <label for="usuario">Usuário</label>
      <input type="text" id="usuario" placeholder="Nome de usuário" required />

      <label for="email">E-mail</label>
      <input type="email" id="email" placeholder="seuemail@exemplo.com" required />

      <label for="senha">Senha</label>
      <input type="password" id="senha" placeholder="Digite sua senha" required />

      <button type="submit">Registrar</button>
    </form>

    <p class="footer-text">
      Já tem uma conta? <a href="login.php">Fazer login</a>
    </p>
  </div>
</body>
</html>
