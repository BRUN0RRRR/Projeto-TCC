<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../../../../databases/conexao.php";

// Captura os dados do formulário
$id_categoria = $_POST['id_categoria'];
$codigo = $_POST['codigo'];
$nome = $_POST['nome'];
$descricao = $_POST['descricao'];


echo $codigo;
echo $nome;
echo $descricao;




// Consulta para verificar se o login já existe
$stmt = $pdo->prepare("SELECT * FROM categoria WHERE codigo = :codigo");
$stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o usuário já existe
if ($result) {
    if ($result['codigo'] === $codigo) {
        //  echo "Usuario já existente!";
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Código já utilizado!",
            'text' => "Já existe uma categoria com esse código. Verifique e tente novamente."
        ];
        header("Location: ../categoria_item.php");
        exit;
    } else {
        //echo "Usuário poderá ser cadastrado.";
    }

} else {
    //echo "Não retornou nada.";
    $stmt = $pdo->prepare("INSERT INTO categoria (id_categoria, codigo, nome, descricao, status) VALUES (NULL, :codigo, :nome,  :descricao, 'Ativo')");

    $stmt->bindParam(":codigo", $codigo, PDO::PARAM_STR);
    $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
    $stmt->bindParam(":descricao", $descricao, PDO::PARAM_STR);

    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso!";
        $_SESSION['aviso'] = [
            'icon' => "success",
            'title' => "Categoria Cadastrada",
            'text' => "A categoria foi cadastrada com sucesso!"
        ];

        header("Location: ../categoria_item.php");
    } else {
        echo "Erro ao cadastrar usuário.";
    }
}

?>