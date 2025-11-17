<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);
$usuario = trim($input['usuario'] ?? '');
$senha = $input['senha'] ?? '';

// Validation
if (empty($usuario) || empty($senha)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário e senha são obrigatórios.']);
    exit;
}

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT id, nome, senha FROM usuario WHERE nome = ?");
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // LOG: Failed login attempt (user not found)
        logAction(0, "Tentativa de login com usuário não encontrado: " . $usuario);
        echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário ou senha incorretos.']);
        exit;
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Verify password
    if (password_verify($senha, $user['senha'])) {
        // Set session variables
        $_SESSION['logado'] = true;
        $_SESSION['usuario'] = $user['nome'];
        $_SESSION['idUsuario'] = $user['id'];
        
        // LOG: Successful login
        logAction($user['id'], "Login realizado com sucesso");
        
        echo json_encode([
            'sucesso' => true, 
            'mensagem' => 'Login realizado com sucesso!',
            'usuario' => $user['nome'],
            'idUsuario' => $user['id']
        ]);
    } else {
        // LOG: Failed login attempt (wrong password)
        logAction($user['id'], "Tentativa de login com senha incorreta");
        echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário ou senha incorretos.']);
    }
    
} catch (Exception $e) {
    // LOG: Login error
    logAction(0, "Erro durante tentativa de login: " . $e->getMessage());
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro interno do servidor.']);
}
?>