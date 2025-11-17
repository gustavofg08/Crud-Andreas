<?php
$host = "127.0.0.1:3308"; 
$user = "root";
$pass = "";
$db   = "touchyourbutton";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

// Function to log user actions
function logAction($idUsuario, $acao) {
    global $conn;
    
    $ip = $_SERVER['REMOTE_ADDR'];
    
    // Check if the user ID exists in the usuario table
    if ($idUsuario > 0) {
        $checkStmt = $conn->prepare("SELECT id FROM usuario WHERE id = ?");
        $checkStmt->bind_param("i", $idUsuario);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $userExists = $checkResult->num_rows > 0;
        $checkStmt->close();
        
        if (!$userExists) {
            // If user doesn't exist, don't log to avoid foreign key violation
            error_log("DEBUG: User ID $idUsuario doesn't exist, skipping log: $acao");
            return false;
        }
    } else {
        // For guest actions (idUsuario = 0), we need to handle differently
        // Since foreign key requires a valid user ID, we'll skip logging guest actions
        error_log("DEBUG: Guest action skipped due to foreign key: $acao");
        return false;
    }
    
    // Insert the log
    $stmt = $conn->prepare("INSERT INTO logs (ip, idUsuario, acao) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sis", $ip, $idUsuario, $acao);
        if ($stmt->execute()) {
            error_log("DEBUG: Log created successfully - User: $idUsuario, Action: $acao");
            $stmt->close();
            return true;
        } else {
            error_log("ERROR: Failed to execute log - " . $stmt->error);
            $stmt->close();
            return false;
        }
    } else {
        error_log("ERROR: Failed to prepare log statement - " . $conn->error);
        return false;
    }
}
?>