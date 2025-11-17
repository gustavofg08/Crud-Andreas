<?php
session_start();
require_once 'db.php';

$logado    = isset($_SESSION['logado']) && $_SESSION['logado'] === true;
$usuario   = ($logado && isset($_SESSION['usuario']))   ? $_SESSION['usuario']   : null;
$idUsuario = ($logado && isset($_SESSION['idUsuario'])) ? $_SESSION['idUsuario'] : null;

// Log debug info
error_log("DEBUG index.php — logado: " . ($logado ? 'true' : 'false'));
error_log("DEBUG index.php — usuario: " . var_export($usuario, true));
error_log("DEBUG index.php — idUsuario: " . var_export($idUsuario, true));

$fotoPerfil = null;

if ($logado && $idUsuario) {
    // Busca foto de perfil no banco - IMPROVED QUERY
    $stmt = $conn->prepare("
        SELECT fotoPerfil 
        FROM uploads 
        WHERE idUsuario = ? AND fotoPerfil IS NOT NULL AND fotoPerfil != ''
        ORDER BY dataUpload DESC 
        LIMIT 1
    ");
    
    if ($stmt) {
        $stmt->bind_param("i", $idUsuario);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $fotoPerfil = $row['fotoPerfil'];
                error_log("DEBUG: Found profile photo: " . $fotoPerfil);
                
                // Verify file actually exists
                $filePath = __DIR__ . '/uploads/' . $fotoPerfil;
                if (!file_exists($filePath)) {
                    error_log("ERROR: Profile photo file doesn't exist: " . $filePath);
                    $fotoPerfil = null;
                }
            } else {
                error_log("DEBUG: No profile photo found in database for user: " . $idUsuario);
            }
        } else {
            error_log("ERROR: Query execution failed: " . $stmt->error);
        }
        $stmt->close();
    } else {
        error_log("ERROR: Prepare failed: " . $conn->error);
    }
} else {
    error_log("DEBUG: Not logged in or no user ID");
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="https://i.imgur.com/l8NOfCE.png">
  <title>Touch Your Butt-on</title>
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

    /* --- Container de Botões --- */
    .container-botoes {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
    width: 80%;
    max-width: 800px;
    margin: 40px auto;
    padding-top: 20px;
}
    .botao {
      padding: 10px;
      background: linear-gradient(135deg, #3B1C32, #060673a4);
      color: #fff;
      border-radius: 15px;
      transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.18);
      box-shadow: 0 8px 32px 0 rgba(0,0,0,0.37);
      cursor: pointer;
      font-size: 0.9rem;
    }
    .botao:hover {
      transform: scale(1.1);
      box-shadow: 0 0 8px rgba(255,255,255,0.6);
    }
    @media (max-width: 600px) {
      .container-botoes {
        width: 100%;
        padding: 0 20px;
      }
      .botao {
        width: 90%;
      }
    }

    #rick-gif {
      display: none;
      margin: 20px auto;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      z-index: 10;
      max-width: 90%;
      height: auto;
    }
    .sound-box-index {
    background: linear-gradient(135deg, #3B1C32, #060673a4);
    border-radius: 15px;
    padding: 12px;
    border: 1px solid rgba(255,255,255,0.18);
    display: flex;
    flex-direction: column;
    gap: 10px;
    position: relative;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    box-shadow: 0 8px 32px 0 rgba(0,0,0,0.37);
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.sound-box-index:hover {
    transform: scale(1.05);
    box-shadow: 0 0 8px rgba(255,255,255,0.6);
}

.sound-header-index {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}

.botao-index {
    background: transparent;
    border: none;
    border-radius: 8px;
    padding: 10px 12px;
    color: #fff;
    cursor: pointer;
    min-height: 44px;
    font-weight: 600;
    word-wrap: break-word;
    flex: 1;
    transition: background 0.2s ease;
    font-size: 0.9rem;
}

.botao-index:hover {
    background: rgba(255, 255, 255, 0.1);
}

.button-group-index {
    display: flex;
    gap: 5px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.sound-box-index:hover .button-group-index {
    opacity: 1;
}

.delete-btn-index {
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

.delete-btn-index:hover {
    transform: scale(1.12);
    background: rgba(255, 0, 0, 0.4);
}

.chat-btn-index {
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

.chat-btn-index:hover {
    transform: scale(1.12);
    background: rgba(255, 255, 255, 0.2);
}

.comment-section-index {
    display: none;
    margin-top: 8px;
}

.comment-section-index.active {
    display: block;
}

.comment-text-index {
    color: #cfe6f5;
    font-size: 12px;
    text-align: left;
    background: rgba(255, 255, 255, 0.05);
    padding: 8px;
    border-radius: 6px;
    border-left: 3px solid #66C0F4;
    word-break: break-word;
}

.no-comment-index {
    color: #9fbcd3;
    font-size: 11px;
    text-align: center;
    padding: 8px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 6px;
}

/* Replies CSS */
.replies-section {
    margin-top: 10px;
    border-top: 1px solid rgba(255,255,255,0.1);
    padding-top: 10px;
}

.reply-item {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 6px;
    padding: 8px;
    margin-bottom: 8px;
    border-left: 2px solid #66C0F4;
}

.reply-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.reply-user {
    font-weight: 600;
    color: #66C0F4;
    font-size: 11px;
}

.reply-time {
    color: #9fbcd3;
    font-size: 10px;
}

.reply-text {
    color: #cfe6f5;
    font-size: 11px;
    word-break: break-word;
}

.reply-form {
    margin-top: 10px;
}

.reply-input {
    width: 100%;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    padding: 8px;
    color: #fff;
    font-size: 11px;
    resize: vertical;
    min-height: 40px;
    font-family: "Poppins", sans-serif;
    margin-bottom: 5px;
}

.reply-input::placeholder {
    color: #9fbcd3;
}

.reply-btn {
    background: #66C0F4;
    border: none;
    border-radius: 4px;
    padding: 6px 12px;
    color: #fff;
    font-size: 11px;
    cursor: pointer;
    transition: background 0.2s ease;
}

.reply-btn:hover {
    background: #4aa1e0;
}

.login-to-reply {
    text-align: center;
    margin-top: 10px;
}

.login-to-reply a {
    color: #66C0F4;
    font-size: 11px;
    text-decoration: none;
}

.login-to-reply a:hover {
    text-decoration: underline;
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
        <?php if ($logado): ?>
          <a href="profile.php">
            <?php 
            error_log("DEBUG - Profile pic check:");
            error_log("  - logado: " . ($logado ? 'true' : 'false'));
            error_log("  - fotoPerfil: " . var_export($fotoPerfil, true));
            error_log("  - idUsuario: " . var_export($idUsuario, true));
            error_log("  - Final image path: " . (!empty($fotoPerfil) ? 'uploads/' . $fotoPerfil : 'https://i.imgur.com/ipPga81.png'));
            ?>
            <img src="<?= htmlspecialchars(!empty($fotoPerfil) ? 'uploads/' . $fotoPerfil : 'https://i.imgur.com/ipPga81.png') ?>" 
                 alt="Foto de Perfil" class="pfp"
                 onerror="console.error('Failed to load image:', this.src)">
          </a>
        <?php else: ?>
          <a id="logintext" href="login.php">Log-in</a>
        <?php endif; ?>
      </li>
    </ul>
  </nav>
</header>

<img id="rick-gif" src="https://i.imgur.com/AopnOfg.gif" alt="Rick Roll GIF">

<div class="container-botoes">
  <button class="botao" onclick="playSound('sound1')">to be continued</button>
  <button class="botao" onclick="playSound('sound2')">the office</button>
  <button class="botao" onclick="playSound('sound5')">gay</button>
  <button class="botao" onclick="playSound('sound6')">nigerun dayo</button>
  <button class="botao" onclick="playSound('sound8')"><img src="https://i.imgur.com/O5uHcE9.png" width="30" height="30" alt="icon"></button>
  <button class="botao" onclick="playSound('sound9', true)">surpresa</button>
  <button class="botao" onclick="playSound('sound10')">windows xp</button>
  <button class="botao" onclick="playSound('sound11')">bluetooth</button>
  <button class="botao" onclick="playSound('sound12')">wide Putin</button>
  <button class="botao" onclick="playSound('sound14')">Sax</button>
  <button class="botao" onclick="playSound('sound15')">HUH</button>
  <button class="botao" onclick="playSound('sound16')">io fone linging</button>
  <button class="botao" onclick="playSound('sound17')">6:30</button>
  <button class="botao" onclick="playSound('sound18')">oh my god</button>
  <button class="botao" onclick="playSound('sound20')">???</button>
  <button class="botao" onclick="playSound('sound21')">BURRO BURRO</button>
  <button class="botao" onclick="playSound('sound22')">super xandão</button>
  <button class="botao" onclick="playSound('sound23')">esperando...</button>
  <button class="botao" onclick="playSound('sound24')"><img src="https://i.imgur.com/gzwGUud.png" width="80" height="80" alt="icon"></button>
  <button class="botao" onclick="playSound('sound26')">grito</button>
  <button class="botao" onclick="playSound('sound27')">pewpew?</button>
  <button class="botao" onclick="playSound('sound28')">miranha</button>
  <button class="botao" onclick="playSound('sound29')">a few moments later</button>
  <button class="botao" onclick="playSound('sound30')">FIM</button>
  <button class="botao" onclick="playTeacherVideo()">GIBA</button>
    <audio id="sound1" src="audio/jojoending.mp3"></audio>
    <audio id="sound2" src="audio/The-Office-US-Intro.mp3"></audio>
    <audio id="sound4" src="audio/bob esponja.mp3"></audio>
    <audio id="sound5" src="audio/gay-echo.mp3"></audio>
    <audio id="sound6" src="audio/N I G E R U N D A Y O.mp3"></audio>
    <audio id="sound7" src="audio/elon-musk-1.mp3"></audio>
    <audio id="sound8" src="audio/chinese-dream.mp3"></audio>
    <audio id="sound9" src="audio/rick roll.mp3"></audio>
    <audio id="sound10" src="audio/windowsxp.mp3"></audio>
    <audio id="sound11" src="audio/the-bluetooth-device-is-ready-to-pair.mp3"></audio>
    <audio id="sound12" src="audio/wide-putin-walking-but-hes-always-in-frame-full-version-mp3cut.mp3"></audio>
    <audio id="sound13" src="audio/wtf-is-a-kilometer.mp3"></audio>
    <audio id="sound14" src="audio/sexy sax.mp3"></audio>
    <audio id="sound15" src="audio/huh.mp3"></audio>
    <audio id="sound16" src="audio/your-phone-ringing_TKtb5bz.mp3"></audio>
    <audio id="sound17" src="audio/samsung.mp3"></audio>
    <audio id="sound18" src="audio/omgwow.mp3"></audio>
    <audio id="sound19" src="audio/FBI.mp3"></audio>
    <audio id="sound20" src="audio/Instagram thud Sound Effect HD.mp3"></audio>
    <audio id="sound21" src="audio/Psicologicamente destruído moralmente abalado e tecnicamente.mp3"></audio>
    <audio id="sound22" src="audio/xandao-pichau.mp3"></audio>
    <audio id="sound23" src="audio/musica-elevador-short.mp3"></audio>
    <audio id="sound24" src="audio/among-us-role-reveal-sound.mp3"></audio>
    <audio id="sound25" src="audio/In Nomine Patris Et Filli Et Spiritus Sancti Sound Effect.mp3"></audio>
    <audio id="sound26" src="audio/wilhelmscream.mp3"></audio>
    <audio id="sound27" src="audio/pew_pew-dknight556-1379997159.mp3"></audio>
    <audio id="sound28" src="audio/spiderman-meme-song.mp3"></audio>
    <audio id="sound29" src="audio/a-few-moments-later-hd.mp3"></audio>
    <audio id="sound30" src="audio/outro-song_oqu8zAg.mp3"></audio>
    <?php
// Só tenta se $logado estiver setado no index.php e $idUsuario definido
if (isset($logado) && $logado && isset($idUsuario) && $idUsuario) {
    $stmt = $conn->prepare("SELECT id, audio, comentario FROM uploads WHERE idUsuario = ? AND audio IS NOT NULL ORDER BY dataUpload DESC");
    if ($stmt) {
        $stmt->bind_param("i", $idUsuario);
        if ($stmt->execute()) {
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $arquivo = $row['audio'];
                $id = $row['id'];
                $comentario = $row['comentario'] ?? '';
                $nome = pathinfo($arquivo, PATHINFO_FILENAME);
                $nomeEx = mb_strlen($nome,'UTF-8') > 20 ? mb_substr($nome,0,20,'UTF-8').'...' : $nome;
                
                // Check if user is AdminGrande or AdminPequeno
                $isAdmin = ($usuario == 'AdminGrande' || $usuario == 'AdminPequeno');
                
                echo "<!-- DEBUG: Generating sound box for $nomeEx -->";
                echo "
                <div class='sound-box-index' id='sound-index-$id'>
                    <div class='sound-header-index'>
                        <button class='botao-index' onclick=\"playSoundFromUploads('uploads/".htmlspecialchars($arquivo,ENT_QUOTES)."')\">
                            ".htmlspecialchars($nomeEx)."
                        </button>
                        <div class='button-group-index'>
                            <!-- Chat Button (for everyone) -->
                            <button class='chat-btn-index' onclick=\"toggleCommentIndex($id)\">
                                <svg width='16' height='16' viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'>
                                    <path fill='#ffffff' d='M306.265,206.421c17.836,0,32.349-14.51,32.349-32.348s-14.512-32.348-32.349-32.348 c-17.836,0-32.348,14.51-32.348,32.348S288.429,206.421,306.265,206.421z M306.265,157.918c8.909,0,16.158,7.248,16.158,16.157 s-7.248,16.157-16.158,16.157c-8.908,0-16.157-7.248-16.157-16.157S297.358,157.918,306.265,157.918z'/>
                                    <path fill='#ffffff' d='M456.241,232.053h-31.19V94.105c0-30.746-25.013-55.759-55.758-55.759H55.759C25.014,38.345,0,63.359,0,94.105v159.82 c0,30.746,25.014,55.759,55.759,55.759h0.079c-4.496,13.234-11.42,25.638-20.327,36.326c-6.933,8.319-7.413,19.868-1.196,28.735 c4.565,6.512,11.72,10.122,19.234,10.121c2.718,0,5.482-0.473,8.188-1.45c35.631-12.881,64.98-39.589,81.258-73.733h73.822 v56.903c0,30.746,25.013,55.759,55.759,55.759h137.67c11.53,23.205,31.747,41.327,56.182,50.16 c2.141,0.774,4.327,1.147,6.478,1.147c5.942,0,11.603-2.856,15.214-8.006c4.918-7.015,4.537-16.149-0.948-22.73 c-5.763-6.916-10.261-14.906-13.229-23.431c22.509-7.441,38.058-28.437,38.058-52.9V287.81 C512,257.065,486.986,232.053,456.241,232.053z M216.816,293.493H137.78c-3.232,0-6.153,1.921-7.434,4.889 c-13.973,32.39-40.987,57.834-74.115,69.807c-4.99,1.809-7.891-1.641-8.659-2.737c-0.771-1.099-3.019-5,0.379-9.077 c12.7-15.24,21.871-33.531,26.522-52.895c0.579-2.411,0.022-4.956-1.514-6.903c-1.535-1.948-3.878-3.084-6.358-3.084H55.759 c-21.818,0-39.568-17.75-39.568-39.568V94.105c0-21.818,17.75-39.568,39.568-39.568h313.534c21.817,0,39.567,17.75,39.567,39.568 v137.947H272.575c-30.746,0-55.759,25.013-55.759,55.758V293.493z M462.553,405.649c-2.216,0.354-4.185,1.614-5.437,3.477 c-1.252,1.863-1.676,4.161-1.168,6.348c3.213,13.844,9.71,26.918,18.785,37.808c0.823,0.987,0.865,2.02,0.127,3.071 c-0.737,1.051-1.725,1.363-2.93,0.926c-21.94-7.93-39.831-24.782-49.087-46.234c-1.281-2.967-4.203-4.889-7.434-4.889H272.575 c-21.818,0-39.568-17.75-39.568-39.568v-78.777c0-21.817,17.75-39.567,39.568-39.567H456.24c21.818,0,39.568,17.75,39.568,39.567 v78.776h0.001C495.809,386.134,481.823,402.561,462.553,405.649z'/>
                                    <path fill='#ffffff' d='M244.873,174.075c0-17.836-14.512-32.348-32.349-32.348s-32.348,14.51-32.348,32.348s14.51,32.348,32.348,32.348 S244.873,191.911,244.873,174.075z M212.526,190.23c-8.908,0-16.157-7.248-16.157-16.157c0-8.908,7.248-16.157,16.157-16.157 c8.909,0,16.158,7.248,16.158,16.157C228.683,182.982,221.435,190.23,212.526,190.23z'/>
                                    <path fill='#ffffff' d='M118.786,141.727c-17.836,0-32.348,14.51-32.348,32.348s14.51,32.348,32.348,32.348s32.349-14.51,32.349-32.348 S136.622,141.727,118.786,141.727z M118.786,190.23c-8.908,0-16.157-7.248-16.157-16.157c0-8.908,7.248-16.157,16.157-16.157 c8.909,0,16.158,7.248,16.158,16.157C134.943,182.982,127.695,190.23,118.786,190.23z'/>
                                </svg>
                            </button>
                            " . ($isAdmin ? "
                            <!-- Delete Button (only for admins) -->
                            <button class='delete-btn-index' onclick=\"deleteSoundIndex($id)\">
                                <svg fill='#ff6b6b' viewBox='0 0 24 24' width='16' height='16'>
                                    <path d='M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z'/>
                                </svg>
                            </button>
                            " : "") . "
                        </div>
                    </div>
                    
                    <!-- COMMENT & REPLY SECTION -->
                    <div class='comment-section-index' id='comment-section-index-$id'>
                        " . (!empty($comentario) ? "
                        <div class='comment-text-index'>
                            <strong>Comentário:</strong> ".htmlspecialchars($comentario)."
                        </div>
                        " : "
                        <div class='no-comment-index'>
                            Sem comentário do autor
                        </div>
                        ") . "
                        
                        <!-- REPLIES SECTION -->
                        <div class='replies-section' id='replies-section-$id'>
                            <div class='replies-list' id='replies-list-$id'>
                                <!-- Replies will be loaded here via JavaScript -->
                            </div>
                            
                            <!-- REPLY FORM (for logged in users) -->
                            " . ($logado ? "
                            <div class='reply-form'>
                                <textarea class='reply-input' id='reply-input-$id' placeholder='Escreva uma resposta...'></textarea>
                                <button class='reply-btn' onclick='submitReply($id)'>Responder</button>
                            </div>
                            " : "
                            <div class='login-to-reply'>
                                <a href='login.php'>Faça login para responder</a>
                            </div>
                            ") . "
                        </div>
                    </div>
                </div>";
            }
        }
        $stmt->close();
    } else {
        echo "<!-- DEBUG: Failed to prepare statement -->";
    }
} else {
    echo "<!-- DEBUG: Not logged in or no user ID -->";
}
?>

</div>

<script>
function playSound(soundId, showGif = false) {
  const audio = document.getElementById(soundId);
  if (audio) {
    audio.currentTime = 0;
    audio.play();
  }
  if (showGif) {
    const rickGif = document.getElementById('rick-gif');
    rickGif.style.display = 'block';
    setTimeout(() => {
      rickGif.style.display = 'none';
    }, 7000);
  }
}

const hamburguer = document.querySelector('.hamburguer');
const navLinks = document.querySelector('.nav_links');
hamburguer.addEventListener('click', () => {
  hamburguer.classList.toggle('active');
  navLinks.classList.toggle('active');
});
</script>
<script>
// ===============================
// AUTO LOGIN AUTOMÁTICO (localStorage + AJAX)
// ===============================
document.addEventListener("DOMContentLoaded", async () => {
  const usuarioLocal = localStorage.getItem("usuarioLogado");
  const phpLogado = <?= json_encode($logado) ?>;
  console.log("AutoLogin check → Local:", usuarioLocal, "| PHP logado:", phpLogado);

  if (phpLogado) return; // já logado → ignora
  if (!usuarioLocal) return; // nada no localStorage → ignora
  if (sessionStorage.getItem("autoLoginFeito")) return; // já fez auto login → ignora

  try {
    const res = await fetch("auto_login.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ usuario: usuarioLocal }),
      credentials: "same-origin"
    });
    const data = await res.json();
    console.log("auto_login.php =>", data);

    if (data.sucesso) {
      sessionStorage.setItem("autoLoginFeito", "true");
      setTimeout(() => location.reload(), 300);
    }
  } catch (err) {
    console.error("Erro no autoLogin:", err);
  }
});

// ===============================
// CLIQUE NO BOTÃO DE PERFIL
// ===============================
document.addEventListener("DOMContentLoaded", () => {
  // botão de perfil precisa ter id="pfpButton" no HTML
  document.getElementById("pfpButton")?.addEventListener("click", async function(e) {
    e.preventDefault();
    const usuarioLocal = localStorage.getItem("usuarioLogado");

    // se não tiver localStorage, manda ao login
    if (!usuarioLocal) {
      window.location.href = "login.php";
      return;
    }

    try {
      // chama auto_login e espera resposta
      const res = await fetch("auto_login.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({ usuario: usuarioLocal }),
        credentials: 'same-origin'
      });
      const data = await res.json();
      console.log('pfp click auto_login =>', data);

      if (data.sucesso) {
        // pequeno delay para o cookie do servidor "bater"
        setTimeout(() => { window.location.href = "profile.php"; }, 250);
      } else {
        // erro: manda para login
        window.location.href = "login.php";
      }
    } catch (err) {
      console.error(err);
      window.location.href = "login.php";
    }
  });
});
</script>
<script>
// função usada só para sons gerados dinamicamente (uploads)
function playSoundFromUploads(url) {
  try {
    // tenta pausar todos os <audio> existentes com data-generated
    document.querySelectorAll('audio[data-generated]').forEach(a => { try{ a.pause(); } catch(e){} });
    const a = new Audio(url);
    a.setAttribute('data-generated','1');
    a.play().catch(err => console.error('play error', err));
  } catch(e){ console.error(e); alert('Erro ao reproduzir som'); }
}
</script>
<script>
// tenta auto-login só UMA vez por sessão do browser
document.addEventListener('DOMContentLoaded', async () => {
  try {
    const usuarioLocal = localStorage.getItem('usuarioLogado');
    const phpLogado = <?= json_encode($logado ?? false) ?>; // já no PHP

    console.log('AutoLogin check → local:', usuarioLocal, 'phpLogado:', phpLogado);

    if (!usuarioLocal || phpLogado) return;
    if (sessionStorage.getItem('autoLoginFeito')) return;

    const res = await fetch('auto_login.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ usuario: usuarioLocal }),
      credentials: 'same-origin'   // **CRUCIAL** para enviar/receber cookie de sessão
    });

    const data = await res.json();
    console.log('auto_login resposta:', data);

    if (data.sucesso) {
      sessionStorage.setItem('autoLoginFeito', 'true');
      // esperar um pouco para o cookie do servidor existir no browser
      setTimeout(() => location.reload(), 250);
    } else {
      console.warn('auto_login falhou:', data.mensagem);
    }
  } catch (err) {
    console.error('Erro no autoLogin:', err);
  }
});

// clique no avatar (id="pfpButton")
document.addEventListener('DOMContentLoaded', () => {
  const pfp = document.getElementById('pfpButton');
  if (!pfp) return;

  pfp.addEventListener('click', async (e) => {
    e.preventDefault();

    const usuarioLocal = localStorage.getItem('usuarioLogado');
    if (!usuarioLocal) {
      return window.location.href = 'login.php';
    }

    try {
      const res = await fetch('auto_login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ usuario: usuarioLocal }),
        credentials: 'same-origin'
      });
      const data = await res.json();
      console.log('pfp click -> auto_login:', data);

      if (data.sucesso) {
        // pequena espera para garantir cookie/sessão
        setTimeout(() => { window.location.href = 'profile.php'; }, 200);
      } else {
        window.location.href = 'login.php';
      }
    } catch (err) {
      console.error('Erro no pfp auto-login:', err);
      window.location.href = 'login.php';
    }
  });
});
</script>
<script>
  // Special button for teacher's video
function playTeacherVideo() {
    // Create video element if it doesn't exist
    let videoOverlay = document.getElementById('teacherVideoOverlay');
    
    if (!videoOverlay) {
        // Create overlay
        videoOverlay = document.createElement('div');
        videoOverlay.id = 'teacherVideoOverlay';
        videoOverlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        `;

        // Create video element
        const video = document.createElement('video');
        video.id = 'teacherVideo';
        video.style.cssText = `
            max-width: 90%;
            max-height: 90%;
            border-radius: 10px;
            box-shadow: 0 0 30px rgba(255, 255, 255, 0.3);
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        `;
        video.controls = true;
        video.autoplay = true;

        // Set video source - replace with your actual video path
        const source = document.createElement('source');
        source.src = 'video/videoplayback.mp4'; // Change this path to your video
        source.type = 'video/mp4';
        video.appendChild(source);

        // Add error handling
        video.addEventListener('error', function() {
            console.error('Error loading video');
            alert('Erro ao carregar o vídeo. Verifique se o arquivo existe.');
            closeVideo();
        });

        // Close video when it ends
        video.addEventListener('ended', closeVideo);

        // Close video when clicking outside
        videoOverlay.addEventListener('click', function(e) {
            if (e.target === videoOverlay) {
                closeVideo();
            }
        });

        // Close with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && videoOverlay.style.display !== 'none') {
                closeVideo();
            }
        });

        videoOverlay.appendChild(video);
        document.body.appendChild(videoOverlay);
    }

    const video = document.getElementById('teacherVideo');
    
    // Fade in
    videoOverlay.style.display = 'flex';
    setTimeout(() => {
        videoOverlay.style.opacity = '1';
        setTimeout(() => {
            video.style.opacity = '1';
        }, 100);
    }, 10);

    // Play video
    video.play().catch(error => {
        console.error('Error playing video:', error);
        alert('Erro ao reproduzir o vídeo.');
    });
}

function closeVideo() {
    const videoOverlay = document.getElementById('teacherVideoOverlay');
    const video = document.getElementById('teacherVideo');
    
    if (videoOverlay && video) {
        // Fade out
        video.style.opacity = '0';
        setTimeout(() => {
            videoOverlay.style.opacity = '0';
            setTimeout(() => {
                videoOverlay.style.display = 'none';
                video.pause();
                video.currentTime = 0;
            }, 500);
        }, 100);
    }
}

// Add CSS styles for the video
const videoStyles = `
    #teacherVideoOverlay {
        backdrop-filter: blur(5px);
        -webkit-backdrop-filter: blur(5px);
    }
    
    #teacherVideo {
        transform: scale(0.9);
        transition: all 0.5s ease-in-out;
    }
    
    #teacherVideoOverlay[style*="display: flex"] #teacherVideo {
        transform: scale(1);
    }
`;

// Inject styles
const styleSheet = document.createElement('style');
styleSheet.textContent = videoStyles;
document.head.appendChild(styleSheet);
</script>
<script>
  function toggleCommentIndex(soundId) {
    const commentSection = document.getElementById('comment-section-index-' + soundId);
    commentSection.classList.toggle('active');
}

function deleteSoundIndex(id) {
    if(!confirm("Tem certeza que deseja excluir este som?")) return;

    fetch("delete_sound.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({id})
    })
    .then(r=>r.json())
    .then(d=>{
        if(d.sucesso){ 
            location.reload();
        } else { 
            alert("Erro: "+d.mensagem); 
        }
    })
}
</script>
<script>// Load replies when comment section is opened
function toggleCommentIndex(soundId) {
    const commentSection = document.getElementById('comment-section-index-' + soundId);
    const isOpening = !commentSection.classList.contains('active');
    
    commentSection.classList.toggle('active');
    
    // Load replies if opening the section
    if (isOpening) {
        loadReplies(soundId);
    }
}

// Load replies for a sound
function loadReplies(soundId) {
    fetch('get_replies.php?sound_id=' + soundId)
    .then(response => response.json())
    .then(replies => {
        const repliesList = document.getElementById('replies-list-' + soundId);
        if (replies.length > 0) {
            repliesList.innerHTML = replies.map(reply => `
                <div class="reply-item">
                    <div class="reply-header">
                        <span class="reply-user">${reply.usuario_nome}</span>
                        <span class="reply-time">${reply.created_at}</span>
                    </div>
                    <div class="reply-text">${reply.reply_text}</div>
                </div>
            `).join('');
        } else {
            repliesList.innerHTML = '<div style="color: #9fbcd3; font-size: 11px; text-align: center;">Nenhuma resposta ainda</div>';
        }
    })
    .catch(error => {
        console.error('Error loading replies:', error);
    });
}

// Submit a new reply
function submitReply(soundId) {
    const replyInput = document.getElementById('reply-input-' + soundId);
    const replyText = replyInput.value.trim();
    
    if (replyText === '') {
        alert('Por favor, escreva uma resposta.');
        return;
    }

    fetch('submit_reply.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            sound_id: soundId,
            reply_text: replyText
        })
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            replyInput.value = '';
            loadReplies(soundId); // Reload replies
        } else {
            alert('Erro ao enviar resposta: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error submitting reply:', error);
        alert('Erro ao enviar resposta.');
    });
}</script>
</body>
</html>