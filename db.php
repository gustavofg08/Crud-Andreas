<?php
$host  = "127.0.0.1";
$user  = "root";
$pass  = "";
$db    = "touchyourbutton";
$porta = 3308; // use 3308 se for sua porta atual — ou troque pra 3306 no outro PC

$conn = new mysqli($host, $user, $pass, $db, $porta);

if ($conn->connect_error) {
    die("Erro na conexão com o banco: " . $conn->connect_error);
}

// Opcional, mas ajuda em alguns casos
$conn->set_charset("utf8mb4");
?>
