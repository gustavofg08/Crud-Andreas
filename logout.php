<?php
session_start();
require_once 'db.php';

// Log logout action before destroying session
if (isset($_SESSION['idUsuario'])) {
    logAction($_SESSION['idUsuario'], "Logout realizado");
}

// Limpa todas as variáveis de sessão
$_SESSION = array();

// Destrói a sessão
session_destroy();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Saindo...</title>
    <script>
        // Limpa o localStorage
        localStorage.removeItem('usuarioLogado');
        // Redireciona para a página inicial
        window.location.href = 'index.php';
    </script>
</head>
<body>
    <p>Saindo...</p>
</body>
</html>