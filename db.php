<?php
$servername = "localhost";
$username = "root";  // padrão do XAMPP
$password = "";      // padrão do XAMPP (sem senha)
$dbname = "touchyourbutton"; // nome do banco de dados (ajuste se for outro)

// Criar conexão
$conn = new mysqli($servername, $username, $password, $dbname);

// Checar conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}
?>
