<?php
// auto_login.php
// Recebe { usuario } e recria sessão server-side; garante cookie de sessão.
header('Content-Type: application/json; charset=utf-8');

// forçar parâmetros do cookie antes de session_start
session_set_cookie_params([
    'lifetime' => 3600, // 1 hora
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);
$usuario = trim($data['usuario'] ?? '');

if ($usuario === '') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'usuario vazio']);
    exit;
}

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=touchyourbutton;charset=utf8mb4","root","",[
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    echo json_encode(['sucesso'=>false,'mensagem'=>'erro db']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, nome FROM usuario WHERE nome = :nome LIMIT 1");
$stmt->execute([':nome' => $usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['sucesso'=>false,'mensagem'=>'usuario nao encontrado']);
    exit;
}

// Regenera id da sessão por segurança e escreve os dados
session_regenerate_id(true);
$_SESSION['logado']    = true;
$_SESSION['usuario']   = $user['nome'];
$_SESSION['idUsuario'] = (int)$user['id'];

// Força envio do cookie (apenas para garantir)
if (!headers_sent()) {
    // cookie já será enviado no fim da resposta de session_start/regenerate
}

// Resposta
echo json_encode(['sucesso'=>true,'mensagem'=>'sessao criada','usuario'=>$user['nome']]);
exit;
