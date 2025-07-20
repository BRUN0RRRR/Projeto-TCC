<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include "../../../../../databases/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_categoria = $_POST['id_categoria'];
    $codigo = $_POST['edit_code'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $status = $_POST['status'];
    /* 
    echo $codigo;
    echo $nome;
    echo $descricao;
    echo $status;
    echo "id: " .  $id_categoria;*/

    try {
        // Verifica se já existe uma categoria com o mesmo código (exceto a atual)
        $sqlVerifica = "SELECT COUNT(*) FROM categoria WHERE codigo = :codigo AND id_categoria != :id_categoria";
        $stmtVerifica = $pdo->prepare($sqlVerifica);
        $stmtVerifica->bindParam(":codigo", $codigo, PDO::PARAM_STR);
        $stmtVerifica->bindParam(":id_categoria", $id_categoria, PDO::PARAM_INT);
        $stmtVerifica->execute();

        $count = $stmtVerifica->fetchColumn();

        if ($count > 0) {
            $_SESSION['aviso'] = [
                'icon' => "error",
                'title' => "Código já utilizado!",
                'text' => "Já existe uma categoria com esse código. Verifique e tente novamente."
            ];
           // header("Location: ../categoria_item.php");
            exit;
        }

        // Atualiza a categoria
        $sqlAtualizar = "UPDATE categoria SET 
                            codigo = :codigo,
                            nome = :nome,
                            descricao = :descricao,
                            status = :status
                         WHERE id_categoria = :id_categoria";

        $stmtAtualizar = $pdo->prepare($sqlAtualizar);
        $stmtAtualizar->execute([
            ':codigo' => $codigo,
            ':nome' => $nome,
            ':descricao' => $descricao,
            ':status' => $status,
            ':id_categoria' => $id_categoria
        ]);

        $_SESSION['aviso'] = [
            'icon' => "success",
            'title' => "Categoria Alterada",
            'text' => "A categoria foi atualizada com sucesso!"
        ];

       header("Location: ../categoria_item.php");
        echo "Cadastrado ";
        exit;

    } catch (PDOException $e) {
        echo "Erro ao atualizar categoria: " . $e->getMessage();
        // Aqui você pode também registrar esse erro em um log
    }
}
?>