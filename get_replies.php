<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

$sound_id = intval($_GET['sound_id'] ?? 0);

if ($sound_id <= 0) {
    echo json_encode([]);
    exit;
}

$stmt = $conn->prepare("
    SELECT r.reply_text, r.usuario_nome, r.created_at 
    FROM replies r 
    WHERE r.sound_id = ? 
    ORDER BY r.created_at ASC
");
$stmt->bind_param("i", $sound_id);
$stmt->execute();
$result = $stmt->get_result();

$replies = [];
while ($row = $result->fetch_assoc()) {
    $replies[] = $row;
}

echo json_encode($replies);
$stmt->close();
?>