<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../../../../databases/conexao.php";

// Captura os dados do formulário
$id_deposito = $_POST['id_deposito'];
$nome = $_POST['nome'];
$descricao = $_POST['descricao'];
$user_criador = $_SESSION['nome_com'] ?? 'Desconhecido'; // Pegando da sessão ou definindo um valor padrão


echo $id_deposito;
echo $nome;
echo $descricao;
echo $user_criador;



// Consulta para verificar se o login já existe
$stmt = $pdo->prepare("SELECT * FROM depositos WHERE id_deposito = :id_deposito");
$stmt->bindParam(":id_deposito", $id_deposito, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o usuário já existe
if ($result) {
    if ($result['id_deposito'] === $id_deposito) {
        //  echo "Usuario já existente!";
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Código já utilizado!",
            'text' => "Já existe uma categoria com esse código. Verifique e tente novamente."
        ];
        header("Location: ../cadastro_deposito.php");
        exit;
    } else {
        //echo "Usuário poderá ser cadastrado.";
    }

} else {
    //echo "Não retornou nada.";
    $stmt = $pdo->prepare("
    INSERT INTO depositos (id_deposito, nome, descricao, status, data_criacao, user_criardor) 
    VALUES (:id_deposito, :nome, :descricao, 'Ativo', CURRENT_TIMESTAMP, :user_criardor)
");

    $stmt->bindParam(":id_deposito", $id_deposito, PDO::PARAM_STR);
    $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
    $stmt->bindParam(":descricao", $descricao, PDO::PARAM_STR);
    $stmt->bindParam(":user_criardor", $user_criador, PDO::PARAM_STR);


    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso!";
        $_SESSION['aviso'] = [
            'icon' => "success",
            'title' => "Categoria Cadastrada",
            'text' => "A categoria foi cadastrada com sucesso!"
        ];

     header("Location: ../cadastro_deposito.php");
    } else {
        echo "Erro ao cadastrar usuário.";
    }
}

?>