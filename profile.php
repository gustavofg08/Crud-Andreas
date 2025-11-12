<?php
session_start();
require_once 'db.php';

// ===============================
// VERIFICA LOGIN / AUTO LOGIN
// ===============================
$logado    = isset($_SESSION['logado']) && $_SESSION['logado'] === true;
$usuario   = $logado ? $_SESSION['usuario'] : null;
$idUsuario = $logado ? $_SESSION['idUsuario'] : null;

$fotoPerfil = null;

if ($logado && $idUsuario) {
    $stmt = $conn->prepare("SELECT fotoPerfil FROM uploads WHERE idUsuario = ? ORDER BY dataUpload DESC LIMIT 1");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $fotoPerfil = $row['fotoPerfil'] ?? null;
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perfil - Touch Your Butt-on</title>
<link rel="icon" type="image/png" href="https://i.imgur.com/l8NOfCE.png">
<style>
body {
  background: #1A1A1D;
  color: white;
  font-family: "Poppins", sans-serif;
  text-align: center;
  padding-top: 100px;
}

/* NAVBAR */
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
.navbar-brand img { width: 40px; height: 40px; }
.nav_links {
  list-style: none;
  display: flex;
  align-items: center;
  gap: 20px;
  margin: 0; padding: 0;
}
.nav-item a {
  text-decoration: none;
  color: #edf0f1;
  font-weight: 500;
  transition: 0.3s;
}
.nav-item a:hover { color: #0088a2; }

.pfp {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #0088a2;
  box-shadow: 0 0 15px rgba(0,136,162,0.4);
  margin-top: 20px;
}

/* FORM */
form {
  margin-top: 30px;
  background: #0F0F0F;
  display: inline-block;
  padding: 25px 35px;
  border-radius: 12px;
  box-shadow: 0 0 15px rgba(255,255,255,0.1);
}
input[type="file"] {
  display: block;
  margin: 10px auto 20px;
  color: #fff;
}
button {
  background: #0088a2;
  color: #fff;
  border: none;
  padding: 10px 20px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
}
button:hover { background: #006b83; }

@media (max-width: 768px) {
  .pfp { width: 80px; height: 80px; }
  form { width: 90%; }
}
</style>
</head>
<body>

<header>
  <a class="navbar-brand" href="index.php">
    <img src="https://i.imgur.com/l8NOfCE.png" alt="Logo">
    Touch Your Butt-on
  </a>
  <nav>
    <ul class="nav_links">
      <li class="nav-item"><a href="index.php">Home</a></li>
      <li class="nav-item"><a href="about.html">About</a></li>
      <li class="nav-item"><a href="https://api.whatsapp.com/send/?phone=92155305">Contact</a></li>
    </ul>
  </nav>
</header>

<?php if ($logado): ?>
  <h1>Olá, <?= htmlspecialchars($usuario) ?>!</h1>
  <img class="pfp" src="<?= htmlspecialchars(!empty($fotoPerfil) ? 'uploads/' . $fotoPerfil : 'https://i.imgur.com/ipPga81.png') ?>" alt="Foto de Perfil">
  
  <form id="uploadForm" enctype="multipart/form-data" method="POST" action="upload.php">
    <h3>Alterar Foto de Perfil ou Enviar Som</h3>
    <input type="file" name="fotoPerfil" accept="image/*">
    <input type="file" name="audio" accept="audio/*">
    <button type="submit">Enviar</button>
  </form>

<?php else: ?>
  <h2>Você não está logado!</h2>
  <p>Redirecionando para o login...</p>
  <script>
    setTimeout(() => window.location.href = "login.php", 1500);
  </script>
<?php endif; ?>

<script>
// AUTO LOGIN VIA localStorage + AJAX
document.addEventListener("DOMContentLoaded", async () => {
  const usuarioLocal = localStorage.getItem("usuarioLogado");
  const phpLogado = <?= json_encode($logado) ?>;
  console.log("Profile autologin → Local:", usuarioLocal, "| PHP logado:", phpLogado);

  if (phpLogado) return;
  if (!usuarioLocal) {
    window.location.href = "login.php";
    return;
  }

  try {
    const res = await fetch("auto_login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ usuario: usuarioLocal }),
      credentials: "same-origin"
    });
    const data = await res.json();
    console.log("profile auto_login.php =>", data);
    if (data.sucesso) {
      setTimeout(() => location.reload(), 300);
    } else {
      window.location.href = "login.php";
    }
  } catch (err) {
    console.error("Erro no autoLogin:", err);
    window.location.href = "login.php";
  }
});
</script>

</body>
</html>
