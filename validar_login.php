<?php
header('Content-Type: application/json');

$dados = json_decode(file_get_contents('php://input'), true);
$usuarios = json_decode(file_get_contents('users.json'), true);

if (!isset($dados['usuario']) || !isset($dados['senha'])) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Dados incompletos']);
    exit;
}

foreach ($usuarios as &$u) {
    if ($u['usuario'] === $dados['usuario'] && $u['senha'] == $dados['senha']) {
        // Marca o usuário como logado
        $u['logado'] = "sim";
        file_put_contents('users.json', json_encode($usuarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        echo json_encode([
            'sucesso' => true,
            'mensagem' => 'Login válido',
            'nome' => $u['nome'],
            'foto' => $u['foto']
        ]);
        exit;
    }
}

// Se não encontrou nenhum usuário
echo json_encode(['sucesso' => false, 'mensagem' => 'Usuário ou senha incorretos']);
?>
