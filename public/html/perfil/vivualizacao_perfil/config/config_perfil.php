<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include "../../../../../databases/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['nova_foto'])) {
    $foto = $_FILES['nova_foto'];
    $usuario_id = $_SESSION['codi_usuario'];

    if ($foto['error'] === 0 && in_array($foto['type'], ['image/jpeg', 'image/png', 'image/jpg'])) {
        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
        $novo_nome = 'foto_' . uniqid() . '.' . $ext;

        // Diretório e caminhos
        $diretorio_destino = __DIR__ . '/fotos/';
        $caminho_fisico = $diretorio_destino . $novo_nome;
        $caminho_publico = '/estoque/public/html/perfil/vivualizacao_perfil/config/fotos/' . $novo_nome;

        // Garante que o diretório exista
        if (!is_dir($diretorio_destino)) {
            mkdir($diretorio_destino, 0755, true);
        }

        if (move_uploaded_file($foto['tmp_name'], $caminho_fisico)) {
            $sql = "UPDATE cadastro_user SET fotos = ? WHERE cod_usuario = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$caminho_publico, $usuario_id]);

            
            header("Location: ../perfil.php");
            exit;
        } else {
            echo "Erro ao mover o arquivo.";
        }
    } else {
        echo "Arquivo inválido ou erro no upload.";
    }
}
?>
