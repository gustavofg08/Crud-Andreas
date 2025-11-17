<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(['sucesso' => false, 'mensagem' => 'Não logado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$soundId = intval($input['id'] ?? 0);

if ($soundId <= 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido']);
    exit;
}

// Get sound info before deleting for logging
$stmt = $conn->prepare("SELECT audio FROM uploads WHERE id = ? AND idUsuario = ?");
$stmt->bind_param("ii", $soundId, $_SESSION['idUsuario']);
$stmt->execute();
$result = $stmt->get_result();
$soundData = $result->fetch_assoc();

if ($result->num_rows === 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Som não encontrado']);
    exit;
}

// Delete the sound
$stmt = $conn->prepare("DELETE FROM uploads WHERE id = ?");
$stmt->bind_param("i", $soundId);

if ($stmt->execute()) {
    // LOG: Delete action
    logAction($_SESSION['idUsuario'], "Excluiu som: " . $soundData['audio']);
    
    // Also delete the actual file
    $filePath = __DIR__ . '/uploads/' . $soundData['audio'];
    if (file_exists($filePath)) {
        unlink($filePath);
    }
    
    echo json_encode(['sucesso' => true, 'mensagem' => 'Som excluído com sucesso']);
} else {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao excluir som']);
}

$stmt->close();
?>