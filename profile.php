<?php
session_start();
require_once 'db.php';

// proteção
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Location: login.php');
    exit;
}

// config
$uploadDir = __DIR__ . '/uploads/';
$allowedImageExt = ['jpg','jpeg','png','gif','webp'];
$allowedAudioExt = ['mp3','wav','ogg','m4a'];
$maxChars = 20;

// usuario
$idUsuario = intval($_SESSION['idUsuario']);
$usuario = $_SESSION['usuario'] ?? 'Usuário';

// garante pasta uploads
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// DEBUG: Verificar se o upload está funcionando
error_log("DEBUG: POST data: " . print_r($_POST, true));
error_log("DEBUG: FILES data: " . print_r($_FILES, true));

// trata upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("DEBUG: Processing POST request");

    // foto de perfil
    if (!empty($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
        error_log("DEBUG: Foto de perfil file detected");
        
        $orig = $_FILES['fotoPerfil']['name'];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $fileSize = $_FILES['fotoPerfil']['size'];
        $tmpName = $_FILES['fotoPerfil']['tmp_name'];

        error_log("DEBUG: File info - Name: $orig, Ext: $ext, Size: $fileSize, Tmp: $tmpName");

        if (in_array($ext, $allowedImageExt, true)) {
            $base = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
            $novo = "pfp_{$idUsuario}_" . time() . "_" . substr($base, 0, 30) . "." . $ext;
            $dest = $uploadDir . $novo;

            error_log("DEBUG: Attempting to move file to: $dest");

            // Verifica se o diretório é gravável
            if (!is_writable($uploadDir)) {
                error_log("ERROR: Upload directory is not writable");
            }

            if (move_uploaded_file($tmpName, $dest)) {
                error_log("DEBUG: File moved successfully");
                
                $stmt = $conn->prepare("INSERT INTO uploads (idUsuario, fotoPerfil, dataUpload) VALUES (?, ?, NOW())");
                if ($stmt) {
                    $stmt->bind_param("is", $idUsuario, $novo);
                    if ($stmt->execute()) {
                        error_log("DEBUG: Database record created successfully");
                    } else {
                        error_log("ERROR: Database execute failed: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    error_log("ERROR: Prepare failed: " . $conn->error);
                }
            } else {
                error_log("ERROR: move_uploaded_file failed");
                error_log("ERROR: upload error: " . $_FILES['fotoPerfil']['error']);
                error_log("ERROR: file_uploads: " . ini_get('file_uploads'));
                error_log("ERROR: upload_max_filesize: " . ini_get('upload_max_filesize'));
                error_log("ERROR: post_max_size: " . ini_get('post_max_size'));
            }
        } else {
            error_log("ERROR: Invalid file extension: $ext");
        }
    } else {
        error_log("DEBUG: No fotoPerfil file or upload error: " . ($_FILES['fotoPerfil']['error'] ?? 'No file'));
    }

    // upload de áudio
    if (!empty($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
        error_log("DEBUG: Audio file detected");
        
        $orig = $_FILES['audio']['name'];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
        $tmpName = $_FILES['audio']['tmp_name'];

        if (in_array($ext, $allowedAudioExt, true)) {
            $base = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
            $novo = $base . "." . $ext;
            $dest = $uploadDir . $novo;

            if (move_uploaded_file($tmpName, $dest)) {
                $stmt = $conn->prepare("INSERT INTO uploads (idUsuario, audio, dataUpload) VALUES (?, ?, NOW())");
                $stmt->bind_param("is", $idUsuario, $novo);
                $stmt->execute();
                $stmt->close();
                error_log("DEBUG: Audio uploaded successfully");
            }
        }
    } else {
        error_log("DEBUG: No audio file or upload error: " . ($_FILES['audio']['error'] ?? 'No file'));
    }

    header('Location: profile.php');
    exit;
}

// busca foto atual
$stmt = $conn->prepare("
    SELECT fotoPerfil
    FROM uploads
    WHERE idUsuario = ? AND fotoPerfil IS NOT NULL
    ORDER BY dataUpload DESC
    LIMIT 1
");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

$fotoSrc = $row ? 'uploads/' . htmlspecialchars($row['fotoPerfil']) : 'https://i.imgur.com/ipPga81.png';
error_log("DEBUG: Current profile photo: " . $fotoSrc);

// busca sons com comentários
$sons = [];
$stmt = $conn->prepare("
    SELECT id, audio, comentario
    FROM uploads
    WHERE idUsuario = ? AND audio IS NOT NULL
    ORDER BY dataUpload DESC
");

if ($stmt) {
    $stmt->bind_param("i", $idUsuario);
    if ($stmt->execute()) {
        $res = $stmt->get_result();
        while ($r = $res->fetch_assoc()) {
            $sons[] = $r;
        }
    } else {
        error_log("ERROR: Failed to execute query: " . $stmt->error);
    }
    $stmt->close();
} else {
    error_log("ERROR: Failed to prepare query: " . $conn->error);
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Perfil — Touch Your Butt-on</title>
<link rel="icon" href="https://i.imgur.com/l8NOfCE.png">

<style>
/* --- Reset / Base --- */
*, *::before, *::after, * a {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  text-decoration: none;
}
body {
  font-family: "Poppins", sans-serif;
  background-color: #1A1A1D;
  color: #fff;
  text-align: center;
  min-height: 100vh;
  padding-top: 100px; /* espaço para header */
}
a {
  text-decoration: none;
  color: inherit;
}

/* --- Header / Nav --- */
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
  box-shadow: 0 0 12px rgba(255,255,255,0.1);
  z-index: 9999;
}
.navbar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
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
}
.nav-item a {
  color: #edf0f1;
  font-weight: 500;
  transition: color 0.3s ease;
}
.nav-item a:hover {
  color: #0088a2;
}
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
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
#logintext:hover {
  transform: scale(1.08);
  box-shadow: 0 0 8px #fff;
}
.logout-btn {
  color: #fff;
  font-weight: 500;
  padding: 6px 12px;
  border: 2px solid #ff4444;
  border-radius: 6px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  background: transparent;
  cursor: pointer;
  font-family: "Poppins", sans-serif;
  font-size: 14px;
}
.logout-btn:hover {
  transform: scale(1.08);
  box-shadow: 0 0 8px #ff4444;
  background: rgba(255, 68, 68, 0.1);
}
.pfp {
  width: 45px;
  height: 45px;
  border-radius: 50%;
  object-fit: cover;
  box-shadow: 0 0 6px rgba(0,136,162,0.4);
  cursor: pointer;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.pfp:hover {
  transform: scale(1.05);
  box-shadow: 0 0 12px rgba(255,255,255,0.822);
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
  margin: 5px 0;
  background-color: #edf0f1;
  transition: transform 0.3s ease, opacity 0.3s ease;
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
    transition: left 0.3s ease;
    padding: 20px 0;
  }
  .nav_links.active { left: 0; }
  .nav-item { margin: 16px 0; }
}

/* --- Profile Content --- */
.container {
  max-width: 800px;
  margin: 40px auto;
  padding: 28px;
  background: rgba(0, 0, 0, 0.55);
  border-radius: 10px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
  text-align: center;
}
.avatar {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  overflow: hidden;
  border: 4px solid #66C0F4;
  margin: 0 auto 12px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.6);
}
.avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.form-upload {
  display: flex;
  gap: 10px;
  justify-content: center;
  flex-wrap: wrap;
  margin: 12px 0;
}
.section-title {
  margin-top: 26px;
  font-weight: 700;
  color: #e6f7ff;
}

.container-botoes {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
  gap: 14px;
  width: 100%;
}

.sound-box {
  position: relative;
  background: linear-gradient(135deg, #3B1C32, #060673a4);
  border-radius: 12px;
  padding: 12px;
  border: 1px solid rgba(255,255,255,.12);
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.sound-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 8px;
}

.botao {
  background: transparent;
  border: none;
  border-radius: 8px;
  padding: 8px 12px;
  color: #fff;
  cursor: pointer;
  min-height: 44px;
  font-weight: 600;
  word-wrap: break-word;
  flex: 1;
  transition: background 0.2s ease;
}

.botao:hover {
  background: rgba(255, 255, 255, 0.1);
}

.button-group {
  display: flex;
  gap: 5px;
}

.delete-btn {
  background: rgba(255, 0, 0, 0.2);
  border: 1px solid rgba(255, 0, 0, 0.3);
  padding: 6px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
}

.delete-btn:hover {
  transform: scale(1.12);
  background: rgba(255, 0, 0, 0.4);
}

.delete-btn svg {
  fill: #ff6b6b;
  width: 16px;
  height: 16px;
}

.chat-btn {
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
  padding: 6px;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 32px;
  height: 32px;
}

.chat-btn:hover {
  transform: scale(1.12);
  background: rgba(255, 255, 255, 0.2);
}

.chat-btn svg {
  fill: #ffffff;
  width: 16px;
  height: 16px;
}

.comment-section {
  margin-top: 8px;
  display: none;
}

.comment-section.active {
  display: block;
}

.comment-text {
  color: #cfe6f5;
  font-size: 12px;
  text-align: left;
  background: rgba(255, 255, 255, 0.05);
  padding: 8px;
  border-radius: 6px;
  border-left: 3px solid #66C0F4;
  min-height: 40px;
  word-break: break-word;
}

.comment-input {
  width: 100%;
  background: rgba(255, 255, 255, 0.05);
  border: 1px solid rgba(255, 255, 255, 0.1);
  border-radius: 6px;
  padding: 8px;
  color: #fff;
  font-size: 12px;
  resize: vertical;
  min-height: 40px;
  font-family: "Poppins", sans-serif;
}

.comment-input::placeholder {
  color: #9fbcd3;
}

.comment-input:focus {
  outline: none;
  border-color: #66C0F4;
}

.comment-actions {
  display: flex;
  gap: 8px;
  margin-top: 5px;
}

.comment-save-btn {
  background: #66C0F4;
  border: none;
  border-radius: 4px;
  padding: 4px 8px;
  color: #fff;
  font-size: 11px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.comment-save-btn:hover {
  background: #4aa1e0;
}

.comment-cancel-btn {
  background: rgba(255, 255, 255, 0.1);
  border: none;
  border-radius: 4px;
  padding: 4px 8px;
  color: #fff;
  font-size: 11px;
  cursor: pointer;
  transition: background 0.2s ease;
}

.comment-cancel-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

.edit-comment-btn {
  background: transparent;
  border: none;
  color: #66C0F4;
  font-size: 11px;
  cursor: pointer;
  margin-top: 4px;
  opacity: 0.7;
  transition: opacity 0.2s ease;
}

.edit-comment-btn:hover {
  opacity: 1;
}

.upload-group {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 8px;
}

.custom-file-btn {
  display: inline-block;
  padding: 10px 20px;
  background: linear-gradient(135deg,#3B1C32,#060673a4);
  color: #fff;
  font-weight: 600;
  border-radius: 12px;
  cursor: pointer;
  transition: 0.2s;
  text-align: center;
}

.custom-file-btn:hover {
  transform: scale(1.05);
  background: linear-gradient(135deg,#5b2c50,#0b0a8a);
}

.submit-btn {
  padding: 10px 20px;
  background: linear-gradient(135deg,#5b2c50,#0b0a8a);
  border: none;
  border-radius: 12px;
  color: #fff;
  font-weight: 600;
  cursor: pointer;
  transition: 0.2s;
  margin-top: 10px;
}

.submit-btn:hover {
  background: #4aa1e0;
}

.file-name {
  color: #cfe6f5;
  font-size: 14px;
  margin-top: 5px;
}

.upload-status {
  margin: 10px 0;
  padding: 10px;
  border-radius: 5px;
  background: rgba(255,255,255,0.1);
}
</style>
</head>

<body>

<header>
  <a class="navbar-brand" href="index.php">
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
      <li class="nav-item profile">
        <a href="profile.php">
          <img src="<?= $fotoSrc ?>" alt="Foto de Perfil" class="pfp">
        </a>
        <a href="logout.php" class="logout-btn">Logout</a>
      </li>
    </ul>
  </nav>
</header>

<main class="container">

    <div class="avatar"><img src="<?= $fotoSrc ?>" id="currentAvatar"></div>

    <h1><?= htmlspecialchars($usuario) ?></h1>

    <form class="form-upload" method="POST" enctype="multipart/form-data" id="uploadForm">

    <!-- Selecionar foto de perfil -->
    <div class="upload-group">
        <input type="file" name="fotoPerfil" id="fotoPerfil" accept="image/*" hidden>
        <label for="fotoPerfil" class="custom-file-btn">Selecionar Foto de Perfil</label>
        <div class="file-name" id="fotoFileName">Nenhum arquivo selecionado</div>
    </div>

    <!-- Selecionar áudio -->
    <div class="upload-group">
        <input type="file" name="audio" id="audio" accept="audio/*" hidden>
        <label for="audio" class="custom-file-btn">Selecionar Som</label>
        <div class="file-name" id="audioFileName">Nenhum arquivo selecionado</div>
    </div>

    <!-- Botão de enviar -->
    <button type="submit" class="submit-btn">Enviar Arquivos</button>

</form>

    <h2 class="section-title">Seus Sons</h2>

    <div class="container-botoes">

        <?php if (empty($sons)): ?>
            <div style="grid-column:1/-1;color:#9fbcd3">
                Você ainda não enviou sons.
            </div>

        <?php else:
            foreach ($sons as $s):

                $arquivo = $s["audio"];
                $id      = intval($s["id"]);
                $nome    = pathinfo($arquivo, PATHINFO_FILENAME);
                $comentario = $s["comentario"] ?? '';

                $exib = (mb_strlen($nome,"UTF-8") > $maxChars)
                        ? mb_substr($nome,0,$maxChars,"UTF-8")."..."
                        : $nome;
        ?>

        <div class="sound-box" id="sound-<?= $id ?>">
            <div class="sound-header">
                <button class="botao" onclick="playUploadedSound('uploads/<?= htmlspecialchars($arquivo) ?>')">
                    <?= htmlspecialchars($exib) ?>
                </button>
                <div class="button-group">
                    <!-- Chat Button -->
                    <button class="chat-btn" onclick="toggleComment(<?= $id ?>)">
                        <svg width="16" height="16" viewBox="0 0 512 512" xmlns="http://www.w3.org/2000/svg">
                            <path d="M306.265,206.421c17.836,0,32.349-14.51,32.349-32.348s-14.512-32.348-32.349-32.348 c-17.836,0-32.348,14.51-32.348,32.348S288.429,206.421,306.265,206.421z M306.265,157.918c8.909,0,16.158,7.248,16.158,16.157 s-7.248,16.157-16.158,16.157c-8.908,0-16.157-7.248-16.157-16.157S297.358,157.918,306.265,157.918z"/>
                            <path d="M456.241,232.053h-31.19V94.105c0-30.746-25.013-55.759-55.758-55.759H55.759C25.014,38.345,0,63.359,0,94.105v159.82 c0,30.746,25.014,55.759,55.759,55.759h0.079c-4.496,13.234-11.42,25.638-20.327,36.326c-6.933,8.319-7.413,19.868-1.196,28.735 c4.565,6.512,11.72,10.122,19.234,10.121c2.718,0,5.482-0.473,8.188-1.45c35.631-12.881,64.98-39.589,81.258-73.733h73.822 v56.903c0,30.746,25.013,55.759,55.759,55.759h137.67c11.53,23.205,31.747,41.327,56.182,50.16 c2.141,0.774,4.327,1.147,6.478,1.147c5.942,0,11.603-2.856,15.214-8.006c4.918-7.015,4.537-16.149-0.948-22.73 c-5.763-6.916-10.261-14.906-13.229-23.431c22.509-7.441,38.058-28.437,38.058-52.9V287.81 C512,257.065,486.986,232.053,456.241,232.053z M216.816,293.493H137.78c-3.232,0-6.153,1.921-7.434,4.889 c-13.973,32.39-40.987,57.834-74.115,69.807c-4.99,1.809-7.891-1.641-8.659-2.737c-0.771-1.099-3.019-5,0.379-9.077 c12.7-15.24,21.871-33.531,26.522-52.895c0.579-2.411,0.022-4.956-1.514-6.903c-1.535-1.948-3.878-3.084-6.358-3.084H55.759 c-21.818,0-39.568-17.75-39.568-39.568V94.105c0-21.818,17.75-39.568,39.568-39.568h313.534c21.817,0,39.567,17.75,39.567,39.568 v137.947H272.575c-30.746,0-55.759,25.013-55.759,55.758V293.493z M462.553,405.649c-2.216,0.354-4.185,1.614-5.437,3.477 c-1.252,1.863-1.676,4.161-1.168,6.348c3.213,13.844,9.71,26.918,18.785,37.808c0.823,0.987,0.865,2.02,0.127,3.071 c-0.737,1.051-1.725,1.363-2.93,0.926c-21.94-7.93-39.831-24.782-49.087-46.234c-1.281-2.967-4.203-4.889-7.434-4.889H272.575 c-21.818,0-39.568-17.75-39.568-39.568v-78.777c0-21.817,17.75-39.567,39.568-39.567H456.24c21.818,0,39.568,17.75,39.568,39.567 v78.776h0.001C495.809,386.134,481.823,402.561,462.553,405.649z"/>
                            <path d="M244.873,174.075c0-17.836-14.512-32.348-32.349-32.348s-32.348,14.51-32.348,32.348s14.51,32.348,32.348,32.348 S244.873,191.911,244.873,174.075z M212.526,190.23c-8.908,0-16.157-7.248-16.157-16.157c0-8.908,7.248-16.157,16.157-16.157 c8.909,0,16.158,7.248,16.158,16.157C228.683,182.982,221.435,190.23,212.526,190.23z"/>
                            <path d="M118.786,141.727c-17.836,0-32.348,14.51-32.348,32.348s14.51,32.348,32.348,32.348s32.349-14.51,32.349-32.348 S136.622,141.727,118.786,141.727z M118.786,190.23c-8.908,0-16.157-7.248-16.157-16.157c0-8.908,7.248-16.157,16.157-16.157 c8.909,0,16.158,7.248,16.158,16.157C134.943,182.982,127.695,190.23,118.786,190.23z"/>
                        </svg>
                    </button>
                    
                    <!-- Delete Button -->
                    <button class="delete-btn" onclick="deleteSound(<?= $id ?>)">
                        <svg fill="#ff6b6b" viewBox="0 0 24 24" width="16" height="16">
                            <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- COMMENT SECTION -->
            <div class="comment-section" id="comment-section-<?= $id ?>">
                <?php if (empty($comentario)): ?>
                    <textarea class="comment-input" placeholder="Adicione um comentário..." 
                              id="comment-input-<?= $id ?>"></textarea>
                    <div class="comment-actions">
                        <button class="comment-save-btn" onclick="saveComment(<?= $id ?>)">Salvar</button>
                    </div>
                <?php else: ?>
                    <div class="comment-text" id="comment-text-<?= $id ?>">
                        <?= htmlspecialchars($comentario) ?>
                    </div>
                    <button class="edit-comment-btn" onclick="editComment(<?= $id ?>)">Editar</button>
                <?php endif; ?>
            </div>
        </div>

        <?php endforeach; endif; ?>

    </div>

</main>

<script>
function playUploadedSound(url){
    const a = new Audio(url);
    a.play();
}

function deleteSound(id){
    if(!confirm("Tem certeza que deseja excluir este som?")) return;

    fetch("delete_sound.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({id})
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.sucesso){ location.reload(); }
        else{ alert("Erro: "+d.mensagem); }
    })
}

function toggleComment(soundId) {
    const commentSection = document.getElementById('comment-section-' + soundId);
    commentSection.classList.toggle('active');
    
    // Focus on input if showing
    if (commentSection.classList.contains('active')) {
        const commentInput = document.getElementById('comment-input-' + soundId);
        const editInput = document.getElementById('edit-comment-input-' + soundId);
        
        if (commentInput) {
            commentInput.focus();
        } else if (editInput) {
            editInput.focus();
        }
    }
}

function saveComment(soundId) {
    const commentInput = document.getElementById('comment-input-' + soundId);
    const comment = commentInput.value.trim();
    
    if (comment === '') {
        alert('Por favor, digite um comentário.');
        return;
    }

    fetch("save_comment.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({
            soundId: soundId,
            comment: comment
        })
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.sucesso){ 
            location.reload();
        } else { 
            alert("Erro ao salvar comentário: "+d.mensagem); 
        }
    })
}

function editComment(soundId) {
    const commentText = document.getElementById('comment-text-' + soundId);
    const currentComment = commentText.textContent;
    
    // Replace text with textarea
    commentText.outerHTML = `
        <textarea class="comment-input" id="edit-comment-input-${soundId}">${currentComment}</textarea>
        <div class="comment-actions">
            <button class="comment-save-btn" onclick="updateComment(${soundId})">Atualizar</button>
            <button class="comment-cancel-btn" onclick="cancelEdit(${soundId})">Cancelar</button>
        </div>
    `;
}

function updateComment(soundId) {
    const commentInput = document.getElementById('edit-comment-input-' + soundId);
    const comment = commentInput.value.trim();
    
    if (comment === '') {
        alert('Por favor, digite um comentário.');
        return;
    }

    fetch("save_comment.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({
            soundId: soundId,
            comment: comment
        })
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.sucesso){ 
            location.reload();
        } else { 
            alert("Erro ao atualizar comentário: "+d.mensagem); 
        }
    })
}

function cancelEdit(soundId) {
    location.reload();
}

// Hamburger menu functionality
const hamburguer = document.querySelector('.hamburguer');
const navLinks = document.querySelector('.nav_links');
hamburguer.addEventListener('click', () => {
  hamburguer.classList.toggle('active');
  navLinks.classList.toggle('active');
});

// Mostrar nome do arquivo selecionado
document.getElementById('fotoPerfil').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Nenhum arquivo selecionado';
    document.getElementById('fotoFileName').textContent = fileName;
});

document.getElementById('audio').addEventListener('change', function(e) {
    const fileName = e.target.files[0] ? e.target.files[0].name : 'Nenhum arquivo selecionado';
    document.getElementById('audioFileName').textContent = fileName;
});

// Verificar se há mensagens de erro na URL
window.addEventListener('load', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const error = urlParams.get('error');
    if (error) {
        alert('Erro no upload: ' + error);
    }
});
</script>
</body>
</html>