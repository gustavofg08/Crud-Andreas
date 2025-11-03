<?php
session_start();
header('Content-Type: application/json');

// Conexão com o banco
$conn = new mysqli('localhost', 'root', '', 'touchyourbutton');
if ($conn->connect_error) {
    die(json_encode(['sucesso' => false, 'mensagem' => 'Erro na conexão com o banco']));
}

// Pega os dados enviados em JSON
$dados = json_decode(file_get_contents('php://input'), true);
$usuario = $dados['usuario'] ?? '';
$senha = $dados['senha'] ?? '';

if (empty($usuario) || empty($senha)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Preencha todos os campos']);
    exit;
}

// Consulta o banco
$stmt = $conn->prepare("SELECT id, nome, senha FROM usuario WHERE nome = ?");
$stmt->bind_param('s', $usuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // ⚠️ Aqui: se você quiser segurança real, use password_hash / password_verify
    if ($senha === $user['senha']) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['usuario_nome'] = $user['nome'];

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Login realizado com sucesso',
            'nome' => $user['nome']
        ]);
        exit;
    }
}

echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário ou senha incorretos']);
?>
