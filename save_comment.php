<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não logado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$soundId = intval($input['soundId'] ?? 0);
$comment = trim($input['comment'] ?? '');

if ($soundId <= 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido']);
    exit;
}

if (empty($comment)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Comentário não pode estar vazio']);
    exit;
}

// Verifica se o som pertence ao usuário
$stmt = $conn->prepare("SELECT audio FROM uploads WHERE id = ? AND idUsuario = ?");
if (!$stmt) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no banco de dados']);
    exit;
}

$stmt->bind_param("ii", $soundId, $_SESSION['idUsuario']);
$stmt->execute();
$result = $stmt->get_result();
$soundData = $result->fetch_assoc();

if ($result->num_rows === 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Som não encontrado']);
    $stmt->close();
    exit;
}

// Check if comment already exists to determine if it's new or edit
$stmtCheck = $conn->prepare("SELECT comentario FROM uploads WHERE id = ?");
$stmtCheck->bind_param("i", $soundId);
$stmtCheck->execute();
$stmtCheck->bind_result($existingComment);
$stmtCheck->fetch();
$stmtCheck->close();

// Atualiza o comentário
$stmt = $conn->prepare("UPDATE uploads SET comentario = ? WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("si", $comment, $soundId);
    
    if ($stmt->execute()) {
        // LOG: Comment action
        if (empty($existingComment)) {
            logAction($_SESSION['idUsuario'], "Adicionou comentário ao som: " . $soundData['audio']);
        } else {
            logAction($_SESSION['idUsuario'], "Editou comentário do som: " . $soundData['audio']);
        }
        
        echo json_encode(['sucesso' => true, 'mensagem' => 'Comentário salvo com sucesso']);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao salvar comentário: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao preparar query']);
}
?>