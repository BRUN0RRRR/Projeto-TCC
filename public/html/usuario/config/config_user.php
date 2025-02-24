<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../../../databases/conexao.php";

// Captura os dados do formulário
$nome = trim($_POST["nome"]);
$login = trim($_POST["login"]);
$email = trim($_POST["email"]);
$setor = trim($_POST["setor"]);
$senha = trim($_POST["senha"]);
$senhaCon = trim($_POST["senhaCon"]);
$grupo = trim($_POST["grupo"]);
$perfil = trim($_POST["perfil"]);
$status_user = "ativo"; // Definindo status padrão
$user_criador = $_SESSION['nome_com'] ?? 'Desconhecido'; // Pegando da sessão ou definindo um valor padrão

// Exibe os dados para depuração
echo "Nome: $nome <br>";
echo "Login: $login <br>";
echo "Email: $email <br>";
echo "Setor: $setor <br>";
echo "senha: $senha <br>";
echo "SenhaConi: $senhaCon <br>";
echo "grupo: $grupo <br>";
echo "perfil: $perfil <br>";

$status_user = "ativo"; // Definindo status padrão
$user_criador = $_SESSION['nome_com'] ?? 'Desconhecido'; // Pegando da sessão ou definindo um valor padrão

// Verifica se os campos obrigatórios estão preenchidos
if (empty($login) || empty($nome) || empty($email) || empty($senha)) {
    die("Erro: Campos obrigatórios não podem estar vazios.");
}

// Validação de senha
if ($senha !== $senhaCon) {
    $_SESSION['aviso'] = [
        'icon' => "error",
        'title' => "Senha não condiz",
        'text' => "A senha inserida não é igual, por favor tentar novamente!!"
    ];
    header("location: ../criar_user.php");
    exit;
}

// Criptografa a senha
$senhaCrip = password_hash($senha, PASSWORD_DEFAULT);
echo "<br>Senha Criptografada: $senhaCrip <br>";

// Consulta para verificar se o login já existe
$stmt = $pdo->prepare("SELECT * FROM cadastro_user WHERE login = :login");
$stmt->bindParam(":login", $login, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo "o valor é: ";
var_dump($result);
//exit; // Pare aqui para verificar a saída antes de prosseguir

// Verifica se o usuário já existe
if ($result) {
    if ($result['nome'] === $nome) {
        if ($result['email'] === $email) {
            echo "Usuario já existente!";
        } else {
            echo "Email existente!";
        }
    } else {
        echo "Usuário poderá ser cadastrado.";
    }
} else {
    echo "Não retornou nada.";


    $stmt = $pdo->prepare("INSERT INTO cadastro_user VALUES (NULL, :login, :nome, :email, :senha, CURDATE(), 1, :grupo, :setor, :perfil, :status_user, :user_criador)");

    $stmt->bindParam(":login", $login, PDO::PARAM_STR);
    $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
    $stmt->bindParam(":senha", $senhaCrip, PDO::PARAM_STR);
    $stmt->bindParam(":grupo", $grupos, PDO::PARAM_STR);
    $stmt->bindParam(":setor", $setor, PDO::PARAM_STR);
    $stmt->bindParam(":perfil", $perfil, PDO::PARAM_STR);
    $stmt->bindParam(":status_user", $status_user, PDO::PARAM_STR);
    $stmt->bindParam(":user_criador", $user_criador, PDO::PARAM_STR);




    if ($stmt->execute()) {
        echo "Usuário cadastrado com sucesso!";
    } else {
        echo "Erro ao cadastrar usuário.";
    }
}




?>