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
if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

// trata upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // foto de perfil
    if (!empty($_FILES['fotoPerfil']) && $_FILES['fotoPerfil']['error'] === UPLOAD_ERR_OK) {
        $orig = $_FILES['fotoPerfil']['name'];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

        if (in_array($ext, $allowedImageExt, true)) {
            $base = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
            $novo = "pfp_{$idUsuario}_" . time() . "_" . substr($base, 0, 30) . "." . $ext;
            $dest = $uploadDir . $novo;

            if (move_uploaded_file($_FILES['fotoPerfil']['tmp_name'], $dest)) {
                $stmt = $conn->prepare("INSERT INTO uploads (idUsuario, fotoPerfil, dataUpload) VALUES (?, ?, NOW())");
                $stmt->bind_param("is", $idUsuario, $novo);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    // upload de áudio
    if (!empty($_FILES['audio']) && $_FILES['audio']['error'] === UPLOAD_ERR_OK) {
        $orig = $_FILES['audio']['name'];
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

        if (in_array($ext, $allowedAudioExt, true)) {
            $base = preg_replace('/[^A-Za-z0-9_\-]/', '_', pathinfo($orig, PATHINFO_FILENAME));
            $novo = $base . "." . $ext;
            $dest = $uploadDir . $novo;

            if (move_uploaded_file($_FILES['audio']['tmp_name'], $dest)) {
                $stmt = $conn->prepare("INSERT INTO uploads (idUsuario, audio, dataUpload) VALUES (?, ?, NOW())");
                $stmt->bind_param("is", $idUsuario, $novo);
                $stmt->execute();
                $stmt->close();
            }
        }
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


// busca sons
$sons = [];
$stmt = $conn->prepare("
    SELECT id, audio
    FROM uploads
    WHERE idUsuario = ? AND audio IS NOT NULL
    ORDER BY dataUpload DESC
");
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$res = $stmt->get_result();
while ($r = $res->fetch_assoc()) $sons[] = $r;
$stmt->close();

?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Perfil — Touch Your Butt-on</title>
<link rel="icon" href="https://i.imgur.com/l8NOfCE.png">

<style>
body{margin:0;font-family:Poppins,Arial;background:#0f1113;color:#cfe6f5}
header{position:fixed;left:50%;transform:translateX(-50%);top:10px;width:90%;max-width:1200px;background:#0f0f0f;border-radius:12px;padding:12px 18px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 6px 24px rgba(0,0,0,0.6)}
.navbar-brand{display:flex;align-items:center;gap:10px;color:#fff;font-weight:700}
.navbar-brand img{width:36px;height:36px}
.container{max-width:800px;margin:120px auto;padding:28px;background:rgba(0,0,0,0.55);border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.6);text-align:center}
.avatar{width:120px;height:120px;border-radius:50%;overflow:hidden;border:4px solid #66C0F4;margin:0 auto 12px;box-shadow:0 6px 18px rgba(0,0,0,0.6)}
.avatar img{width:100%;height:100%;object-fit:cover}
.form-upload{display:flex;gap:10px;justify-content:center;flex-wrap:wrap;margin:12px 0}
.section-title{margin-top:26px;font-weight:700;color:#e6f7ff}

.container-botoes{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(140px,1fr));
    gap:14px;
    width:100%;
}

.sound-box{
    position:relative;
}

.botao{
    background:linear-gradient(135deg,#3B1C32,#060673a4);
    border-radius:12px;
    padding:12px;
    color:#fff;
    border:1px solid rgba(255,255,255,.12);
    cursor:pointer;
    min-height:44px;
    font-weight:600;
    word-wrap:break-word;
}

.delete-btn{
    position:absolute;
    top:5px;
    right:5px;
    opacity:0;
    background:rgba(0,0,0,0.45);
    border:none;
    padding:3px;
    border-radius:5px;
    cursor:pointer;
    transition:.2s;
}

.sound-box:hover .delete-btn{
    opacity:1;
}

.delete-btn:hover{
    transform:scale(1.12);
}
.form-upload {
    display: flex;
    gap: 10px;
    justify-content: center;
    flex-wrap: wrap;
    margin: 12px 0;
}

.upload-group {
    display: flex;
    flex-direction: column;
    align-items: center;
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
}

.submit-btn:hover {
    background: #4aa1e0;
}

</style>
</head>

<body>

<header>
    <div class="navbar-brand">
        <img src="https://i.imgur.com/l8NOfCE.png">
        <a href="index.php" style="color:#fff;text-decoration:none">Touch Your Butt-on</a>
    </div>
    <div style="color:#cfe6f5;font-weight:600">Olá, <?= htmlspecialchars($usuario) ?></div>
</header>

<main class="container">

    <div class="avatar"><img src="<?= $fotoSrc ?>"></div>

    <h1><?= htmlspecialchars($usuario) ?></h1>

    <form class="form-upload" method="POST" enctype="multipart/form-data">

    <!-- Selecionar foto de perfil -->
    <div class="upload-group">
        <input type="file" name="fotoPerfil" id="fotoPerfil" accept="image/*" hidden>
        <label for="fotoPerfil" class="custom-file-btn">Selecionar Foto de Perfil</label>
    </div>

    <!-- Selecionar áudio -->
    <div class="upload-group">
        <input type="file" name="audio" id="audio" accept="audio/*" hidden>
        <label for="audio" class="custom-file-btn">Selecionar Som</label>
    </div>

    <!-- Botão de enviar -->
    <button type="submit" class="submit-btn">Enviar</button>

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

                $exib = (mb_strlen($nome,"UTF-8") > $maxChars)
                        ? mb_substr($nome,0,$maxChars,"UTF-8")."..."
                        : $nome;
        ?>

        <div class="sound-box">
            <button class="botao"
                onclick="playUploadedSound('uploads/<?= htmlspecialchars($arquivo) ?>')">
                <?= htmlspecialchars($exib) ?>
            </button>

            <button class="delete-btn" onclick="deleteSound(<?= $id ?>)">
                <svg fill="#ff0000" viewBox="0 0 408.483 408.483" width="17" height="17">
                    <path d="M87.7 388.8c0.5 11 9.5 19.7 20.5 19.7h191.9c11 0 20.1-8.7 20.5-19.7l13.7-289.3H74l13.7 289.3zM247.7 171.3c0-4.6 3.7-8.3 8.3-8.3h13.4c4.6 0 8.3 3.7 8.3 8.3v165.3c0 4.6-3.7 8.3-8.3 8.3H256c-4.6 0-8.3-3.7-8.3-8.3V171.3zM189.2 171.3c0-4.6 3.7-8.3 8.3-8.3H211c4.6 0 8.3 3.7 8.3 8.3v165.3c0 4.6-3.7 8.3-8.3 8.3h-13.4c-4.6 0-8.3-3.7-8.3-8.3V171.3zM130.8 171.3c0-4.6 3.7-8.3 8.3-8.3h13.4c4.6 0 8.3 3.7 8.3 8.3v165.3c0 4.6-3.7 8.3-8.3 8.3h-13.4c-4.6 0-8.3-3.7-8.3-8.3V171.3z"/>
                    <path d="M343.6 21h-88.5V4.3c0-2.4-1.9-4.3-4.3-4.3h-93c-2.4 0-4.3 1.9-4.3 4.3V21H64.9c-7.1 0-12.9 5.8-12.9 12.9V74.5h304.5V33.9c0-7.1-5.8-12.9-12.9-12.9z"/>
                </svg>
            </button>
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
</script>

</body>
</html>
