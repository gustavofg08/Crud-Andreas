<?php
$host = "127.0.0.1:3308"; 
$user = "root";
$pass = "";
$db   = "touchyourbutton";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}
