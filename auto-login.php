<?php
// auto_login.php
// NÃO enviar qualquer echo antes do session_start()
session_start();

// se já logado
if (!empty($_SESSION['logado']) && $_SESSION['logado'] === true) {
    echo json_encode(['sucesso' => true]);
    exit;
}

$dados = json_decode(file_get_contents('php://input'), true);
$usuario = trim($dados['usuario'] ?? '');

if ($usuario === '') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não informado.']);
    exit;
}

// conectar DB (use PDO ou mysqli conforme seu projeto)
$pdo = new PDO("mysql:host=127.0.0.1;dbname=touchyourbutton;charset=utf8mb4","root","",[
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

$stmt = $pdo->prepare("SELECT id, nome FROM usuario WHERE nome = :nome LIMIT 1");
$stmt->execute([':nome' => $usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não encontrado.']);
    exit;
}

// seta sessão
$_SESSION['logado'] = true;
$_SESSION['usuario'] = $user['nome'];
$_SESSION['idUsuario'] = (int)$user['id'];

// garante gravação imediata
session_write_close();

echo json_encode(['sucesso' => true, 'usuario' => $user['nome'], 'idUsuario' => (int)$user['id']]);
exit;
