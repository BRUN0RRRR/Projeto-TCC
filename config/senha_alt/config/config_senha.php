<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include "../../../databases/conexao.php";

// Verifica se os campos foram enviados
if (!isset($_POST['senha'], $_POST['senhaConfi'])) {
    die("Erro: Todos os campos são obrigatórios.");
}

$senha = trim($_POST['senha']);
$senhaConfi = trim($_POST['senhaConfi']);
$login_usuario = $_SESSION['login_usuario'] ?? null;

// Verifica se o usuário está logado
if (!$login_usuario) {
    die("Erro: Usuário não autenticado.");
}

// Verifica se as senhas coincidem
if ($senha !== $senhaConfi) {
    header("location: ../../mensagem/mensagem_login.php");
    $_SESSION['aviso'] = [
        'icon' => "error",
        'title' => "Senha coincidem",
        'text' => " As senhas não coincidem, Tente novamente!."
    ];
    die("Erro: As senhas não coincidem.");
}


//Criptografa a senha
$senhaCrip = password_hash($senha, PASSWORD_DEFAULT);
//echo $senha . "<br>" . $senhaConfi . "<br>" . $senhaCrip . "<br>" . $login_usuario;

try {
    $stmt = $pdo->prepare("SELECT * FROM cadastro_user WHERE login = :nome");
    $stmt->bindParam(':nome', $login_usuario, PDO::PARAM_STR);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (password_verify($senha, $result['senha'])) {
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Senha incorreta",
            'text' => "A nova senha não pode ser igual a senha anterior!!"
        ];
        echo "senha igual";
        header("location: ../alt_senha.php");
    } else {
        // Atualiza a senha no banco de dados
        $stmt = $pdo->prepare("UPDATE cadastro_user SET senha = :senha, alt_senha = 0 WHERE login = :login ");
        $stmt->bindParam(":senha", $senhaCrip, PDO::PARAM_STR);
        $stmt->bindParam(":login", $login_usuario, PDO::PARAM_STR);

        if ($stmt->execute()) {
            //echo "Senha alterada com sucesso!";
            $_SESSION['logado'] = true;
            $_SESSION['nome_com'] = $result['nome'];
            $_SESSION['login_usuario'] = $result['login'];
            $_SESSION['status'] = $result['status_user'];
            $_SESSION['codi_usuario'] = $result['cod_usuario'];
            $_SESSION['perfil'] = $result['perfil'];
            $_SESSION['fotos'] = $result['fotos'];
            header("location: ../../mensagem/mensagem_login.php");
            exit;
        } else {
            $_SESSION['aviso'] = [
                'icon' => "error",
                'title' => "Senha incorreta",
                'text' => "Senha incorreta, Tente novamente!!"
            ];
            header("location: ../alt_senha.php");
        }
    }
} catch (PDOException $e) {
    echo "Erro no banco de dados: " . $e->getMessage();
}

?>