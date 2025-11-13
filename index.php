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

if ($logado && $usuario && $idUsuario) {
    // Busca foto de perfil no banco
    $stmt = $conn->prepare("
        SELECT fotoPerfil 
        FROM uploads 
        WHERE idUsuario = ? 
        ORDER BY dataUpload DESC 
        LIMIT 1
    ");
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $fotoPerfil = $row['fotoPerfil'] ?? null;
    $stmt->close();

    error_log("DEBUG index.php — fotoPerfil fetched: " . var_export($fotoPerfil, true));
} else {
    error_log("DEBUG index.php — no fotoPerfil fetch attempted (logado && usuario && idUsuario failed)");
}

$conn->close();
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
      grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
      gap: 10px;
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
            <img src="<?= htmlspecialchars(!empty($fotoPerfil) ? 'uploads/' . $fotoPerfil : 'https://i.imgur.com/ipPga81.png') ?>" 
                 alt="Foto de Perfil" class="pfp">
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
</body>
</html>