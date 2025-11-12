<?php
session_start();

// Conexão com banco MySQL (ajuste conforme seu ambiente)
$pdo = new PDO("mysql:host=127.0.0.1;dbname=touchyourbutton;charset=utf8mb4", "root", "", [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Lê dados enviados via fetch (JSON)
$dados   = json_decode(file_get_contents('php://input'), true);
$usuario = trim($dados['usuario'] ?? '');
$senha   = trim($dados['senha']   ?? '');

header('Content-Type: application/json');

// Verificação simples de preenchimento
if ($usuario === '' || $senha === '') {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos.']);
    exit;
}

// Busca usuário na tabela `usuario`
$stmt = $pdo->prepare("SELECT id, nome, senha FROM usuario WHERE nome = :nome LIMIT 1");
$stmt->execute([':nome' => $usuario]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário não encontrado.']);
    exit;
}

// Verifica senha (compatível com hashes e senhas antigas em texto)
$senhaCorreta = false;
if (password_verify($senha, $user['senha'])) {
    $senhaCorreta = true;
} elseif ($user['senha'] === $senha) {
    $senhaCorreta = true;
    // Atualiza a senha antiga para hash moderno
    $novoHash = password_hash($senha, PASSWORD_DEFAULT);
    $update  = $pdo->prepare("UPDATE usuario SET senha = ? WHERE id = ?");
    $update->execute([$novoHash, $user['id']]);
}

if (!$senhaCorreta) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Senha incorreta.']);
    exit;
}

// Login bem‑sucedido → cria sessão
$_SESSION['logado']  = true;
$_SESSION['usuario'] = $user['nome'];
$_SESSION['idUsuario'] = $user['id'];

// Retorna sucesso em JSON, com dados úteis para o cliente
echo json_encode([
    'sucesso'   => true,
    'mensagem'  => 'Login realizado com sucesso.',
    'usuario'   => $user['nome'],
    'idUsuario' => $user['id']
]);
exit;
