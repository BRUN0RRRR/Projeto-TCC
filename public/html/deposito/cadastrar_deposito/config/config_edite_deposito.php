<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include "../../../../../databases/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_deposito = $_POST['id_deposito'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];
    $id_original = $_POST['id_original']; // ID original antes da edição

    try {
        // Se o ID foi alterado, verifica se o novo ID já existe
        if ($id_deposito != $id_original) {
            $sqlVerifica = "SELECT id_deposito FROM depositos WHERE id_deposito = :id_deposito";
            $stmtVerifica = $pdo->prepare($sqlVerifica);
            $stmtVerifica->bindParam(":id_deposito", $id_deposito, PDO::PARAM_STR);
            $stmtVerifica->execute();

            if ($stmtVerifica->fetch(PDO::FETCH_ASSOC)) {
                $_SESSION['aviso'] = [
                    'icon' => "error",
                    'title' => "Código já utilizado!",
                    'text' => "Já existe um depósito com esse código. Verifique e tente novamente."
                ];
                header("Location: ../cadastro_deposito.php");
                exit;
            }
        }

        // Atualiza o depósito
        $sqlAtualizarDeposito = "UPDATE depositos SET 
            id_deposito = :id_deposito,
            nome = :nome,
            descricao = :descricao,
            status = :status
            WHERE id_deposito = :id_original";

        $stmtAtualizarDeposito = $pdo->prepare($sqlAtualizarDeposito);
        $stmtAtualizarDeposito->execute([
            ':id_deposito' => $id_deposito,
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':status' => $status,
            ':id_original' => $id_original
        ]);

        // Atualiza o nome e id_deposito na tabela localizacoes
        $sqlAtualizarLocalizacoes = "UPDATE localizacoes SET 
            id_deposito = :id_deposito, 
            nome = :nome
            WHERE id_deposito = :id_original";

        $stmtAtualizarLocalizacoes = $pdo->prepare($sqlAtualizarLocalizacoes);
        $stmtAtualizarLocalizacoes->execute([
            ':id_deposito' => $id_deposito,
            ':nome' => $nome,
            ':id_original' => $id_original
        ]);

        $_SESSION['aviso'] = [
            'icon' => "success",
            'title' => "Depósito Alterado",
            'text' => "O depósito foi atualizado com sucesso!"
        ];
        header("Location: ../cadastro_deposito.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Erro",
            'text' => "Erro ao atualizar depósito: " . $e->getMessage()
        ];
        header("Location: ../cadastro_deposito.php");
        exit;
    }
}
?>