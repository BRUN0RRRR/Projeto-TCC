<?php
session_start();
include "../../../../../databases/conexao.php";

header('Content-Type: application/json');

if (!isset($_POST['idUsuario']) || empty($_POST['idUsuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'ID do usuário não informado']);
    exit;
}

$idUsuario = trim($_POST['idUsuario']);

try {
    $stmt = $pdo->prepare("DELETE FROM cadastro_user WHERE cod_usuario = :id");
    $stmt->bindParam(':id', $idUsuario, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'success', 'message' => 'Usuário excluído com sucesso']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuário não encontrado']);
    }
} catch (PDOException $e) {
    // Para produção, não exponha o erro real
    echo json_encode(['status' => 'error', 'message' => 'Erro no banco de dados']);
}
exit;
