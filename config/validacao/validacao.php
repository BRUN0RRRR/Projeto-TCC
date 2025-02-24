<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../databases/conexao.php";

try {

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $nome = isset($_POST["login"]) ? trim($_POST["login"]) : "";
        $senha = isset($_POST["senha"]) ? trim($_POST["senha"]) : "";

        if (empty($nome) || empty($senha)) {
            throw new Exception("Nome ou Senha não pode ser vazio, tente novamente.");
        }
      //$senhaCrip = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        //$senhaCrip = md5($senha);

    }


    $stmt = $pdo->prepare("SELECT * FROM cadastro_user WHERE login = :nome");
    $stmt->bindParam(':nome', $nome, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        if ($result['status_user'] == "ativo") {
            if (password_verify($senha, $result['senha'])) {
                // Inicia a sessão com os dados do usuário
                $_SESSION['logado'] = true;
                $_SESSION['nome_com'] = $result['nome'];
                $_SESSION['nome'] = $result['nome'];
                $_SESSION['status'] = $result['status_user'];
                // Redireciona para a página principal
                header("location: ../mensagem/mensagem_login.php");
                exit;
            } else {
                $_SESSION['aviso'] = [
                    'icon' => "error",
                    'title' => "Senha incorreta",
                    'text' => "Usuário ou senha incorreta, tente novamente!!"
                ];
                echo "senha errada";
                header("location: ../../index.php");
                exit;

            }
        } else {
            $_SESSION['aviso'] = [
                'icon' => "error",
                'title' => "Usuário inativo",
                'text' => "Usuário inativo, verifique com o Administrador do Sistema!"
            ];
            header("location: ../../index.php");
            exit;
        }
    } else {
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Usuario não encontrado",
            'text' => "Usuario não encontrado, verificar com o Administrador do Sistema!"
        ];
        header("location: ../../index.php");
        echo "usuario não encontrado";

        exit;
    }



} catch (Exception $e) {
    echo " Erro: " . $e->getMessage();
}

?>