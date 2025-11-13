<?php
session_start();
require_once 'db.php';

// --- BLOQUEIA NÃO LOGADOS ---
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: login.php");
    exit;
}

$idUsuario = $_SESSION['idUsuario'];
$usuario   = $_SESSION['usuario'];

// --- CRIA PASTA UPLOADS SE NÃO EXISTIR ---
$uploadDir = __DIR__ . "/uploads/";
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// --- TRATA UPLOADS ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FOTO
    if (!empty($_FILES['fotoPerfil']['name'])) {
        $ext = strtolower(pathinfo($_FILES['fotoPerfil']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $nomeFoto = "pfp_{$idUsuario}_" . time() . ".$ext";
            $caminhoFoto = $uploadDir . $nomeFoto;
            if (move_uploaded_file($_FILES['fotoPerfil']['tmp_name'], $caminhoFoto)) {
                // Salva ou atualiza no banco
                $stmt = $conn->prepare("INSERT INTO uploads (idUsuario, fotoPerfil, dataUpload) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE fotoPerfil = VALUES(fotoPerfil)");
                $stmt->bind_param("is", $idUsuario, $nomeFoto);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // ÁUDIO
    if (!empty($_FILES['audio']['name'])) {
        $ext = strtolower(pathinfo($_FILES['audio']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['mp3', 'wav', 'ogg'])) {
            $nomeAudio = "audio_{$idUsuario}_" . time() . ".$ext";
            $caminhoAudio = $uploadDir . $nomeAudio;
            if (move_uploaded_file($_FILES['audio']['tmp_name'], $caminhoAudio)) {
                $stmt = $conn->prepare("INSERT INTO uploads (idUsuario, audio, dataUpload) VALUES (?, ?, NOW())");
                $stmt->bind_param("is", $idUsuario, $nomeAudio);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: profile.php");
    exit;
}

// --- BUSCA FOTO DE PERFIL ---
$stmt = $conn->prepare("SELECT fotoPerfil FROM uploads WHERE idUsuario = ? AND fotoPerfil IS NOT NULL ORDER BY dataUpload DESC LIMIT 1");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$res = $stmt->get_result();
$foto = $res->fetch_assoc();
$stmt->close();

$fotoPerfil = $foto ? 'uploads/' . $foto['fotoPerfil'] : 'https://i.imgur.com/ipPga81.png';

// --- BUSCA SONS DO USUÁRIO ---
$stmt = $conn->prepare("SELECT audio FROM uploads WHERE idUsuario = ? AND audio IS NOT NULL ORDER BY dataUpload DESC");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$res = $stmt->get_result();

$sons = [];
while ($row = $res->fetch_assoc()) {
    $sons[] = $row['audio'];
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>Perfil - Touch Your Butt-on</title>
<link rel="icon" href="https://i.imgur.com/l8NOfCE.png" type="image/png">
<style>
body {
  background: linear-gradient(180deg, #171A21 0%, #0B0C10 100%);
  font-family: "Poppins", sans-serif;
  color: #C7D5E0;
  margin: 0;
  padding: 0;
  text-align: center;
}

/* HEADER */
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
  z-index: 9999;
}
.navbar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  color: #fff;
  font-weight: bold;
  font-size: 18px;
}
.navbar-brand img {
  width: 40px;
  height: 40px;
}

/* PERFIL */
.profile-header {
  margin-top: 120px;
  background: rgba(0, 0, 0, 0.5);
  border-radius: 12px;
  width: 80%;
  max-width: 800px;
  margin-left: auto;
  margin-right: auto;
  padding: 30px;
  box-shadow: 0 0 20px rgba(0,0,0,0.6);
}
#fotoPerfil {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  border: 3px solid #66C0F4;
  object-fit: cover;
  margin-bottom: 10px;
}
h2 {
  margin: 0;
  color: #66C0F4;
  font-size: 1.8em;
}

/* FORM DE UPLOAD */
form {
  margin-top: 20px;
}
input[type=file] {
  background: #1B2838;
  border: 1px solid #66C0F4;
  border-radius: 8px;
  padding: 6px;
  color: #C7D5E0;
  width: 60%;
  margin: 8px 0;
}
button {
  background: #66C0F4;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  color: #000;
  font-weight: bold;
  cursor: pointer;
  transition: 0.3s;
}
button:hover {
  background: #8AE5FF;
}

/* SONS DO USUÁRIO */
.container-botoes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
  gap: 10px;
  width: 80%;
  margin: 40px auto;
}
.botao {
  background: linear-gradient(135deg, #3B1C32, #060673a4);
  border-radius: 15px;
  color: #FFF;
  padding: 12px;
  transition: 0.2s;
  cursor: pointer;
  border: 1px solid rgba(255,255,255,0.15);
}
.botao:hover {
  transform: scale(1.08);
  box-shadow: 0 0 10px #66C0F4;
}
</style>
</head>
<body>
<header>
  <a class="navbar-brand" href="index.php">
    <img src="https://i.imgur.com/l8NOfCE.png" alt="Logo"> Touch Your Butt-on
  </a>
</header>

<div class="profile-header">
  <img id="fotoPerfil" src="<?= htmlspecialchars($fotoPerfil) ?>" alt="Foto de Perfil">
  <h2><?= htmlspecialchars($usuario) ?></h2>

  <form method="POST" enctype="multipart/form-data">
    <p>Alterar foto ou enviar novo som:</p>
    <input type="file" name="fotoPerfil" accept="image/*" placeholder="Foto de Perfil"><br>
    <input type="file" name="audio" accept="audio/*" placeholder="Sons"><br>
    <button type="submit">Enviar</button>
  </form>
</div>

<h3 style="margin-top:40px;">Seus Sons</h3>
<div class="container-botoes">
  <?php if (empty($sons)): ?>
    <p>Nenhum som enviado ainda.</p>
  <?php else: ?>
    <?php foreach ($sons as $i => $audio): ?>
      <button class="botao" onclick="playUserSound('userSound<?= $i ?>')">Som <?= $i + 1 ?></button>
      <audio id="userSound<?= $i ?>" src="uploads/<?= htmlspecialchars($audio) ?>"></audio>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<script>
function playUserSound(id) {
  const audio = document.getElementById(id);
  if (audio) {
    audio.currentTime = 0;
    audio.play();
  }
}
</script>
</body>
</html>
