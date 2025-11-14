<?php
session_start();
require_once "db.php";

$dados = json_decode(file_get_contents("php://input"), true);
$id = intval($dados["id"] ?? 0);

if (!$id) {
    echo json_encode(["sucesso" => false, "mensagem" => "ID inválido"]);
    exit;
}

if (!isset($_SESSION["idUsuario"])) {
    echo json_encode(["sucesso" => false, "mensagem" => "Não logado"]);
    exit;
}

$idUser = $_SESSION["idUsuario"];

// buscar arquivo
$stmt = $conn->prepare("SELECT audio FROM uploads WHERE id = ? AND idUsuario = ? LIMIT 1");
$stmt->bind_param("ii", $id, $idUser);
$stmt->execute();
$res = $stmt->get_result();
$up = $res->fetch_assoc();
$stmt->close();

if (!$up) {
    echo json_encode(["sucesso" => false, "mensagem" => "Som não encontrado"]);
    exit;
}

$file = __DIR__ . "/uploads/" . $up["audio"];
if (file_exists($file)) unlink($file);

$stmt = $conn->prepare("DELETE FROM uploads WHERE id = ? AND idUsuario = ?");
$stmt->bind_param("ii", $id, $idUser);
$stmt->execute();
$stmt->close();

echo json_encode(["sucesso" => true]);
exit;
?>
