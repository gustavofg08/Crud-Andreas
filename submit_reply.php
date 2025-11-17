<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    echo json_encode(['success' => false, 'message' => 'Não logado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$sound_id = intval($input['sound_id'] ?? 0);
$reply_text = trim($input['reply_text'] ?? '');

if ($sound_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

if (empty($reply_text)) {
    echo json_encode(['success' => false, 'message' => 'Resposta não pode estar vazia']);
    exit;
}

// Insert reply
$stmt = $conn->prepare("
    INSERT INTO replies (sound_id, user_id, usuario_nome, reply_text) 
    VALUES (?, ?, ?, ?)
");
$stmt->bind_param("iiss", $sound_id, $_SESSION['idUsuario'], $_SESSION['usuario'], $reply_text);

if ($stmt->execute()) {
    // Log the reply action
    logAction($_SESSION['idUsuario'], "Respondeu ao som ID: " . $sound_id);
    echo json_encode(['success' => true, 'message' => 'Resposta enviada!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar resposta']);
}

$stmt->close();
?>