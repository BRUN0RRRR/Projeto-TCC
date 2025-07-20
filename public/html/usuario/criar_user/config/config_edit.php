<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../../../../databases/conexao.php";

// Captura os dados do formulário
$login = trim($_POST["idUsuario"]);
$nome = trim($_POST["nome_model"]);
//$login = trim($_POST["login"]);
$email = trim($_POST["email_model"]);
$setor = trim($_POST["setor_model"]);
$senha = trim($_POST["senha_model"]);
$senhaCon = trim($_POST["senhaCon_model"]);
$grupo = trim($_POST["grupo_model"]);
$perfil = trim($_POST["perfil_model"]);
$status_user = trim($_POST["status_model"]); // Definindo status padrão
$user_criador = $_SESSION['nome_com'] ?? 'Desconhecido'; // Pegando da sessão ou definindo um valor padrão
$alt_senha = $_POST['alterarSenhaCheck'] ?? 0;
// Converte para inteiro (opcional)
$alt_senha = intval($alt_senha);




//Exibe os dados para depuração
echo "id: $login <br>";
echo "Nome: $nome <br>";
//echo "Login: $login <br>";
echo "Email: $email <br>";
echo "Setor: $setor <br>";
echo "senha: $senha <br>";
echo "SenhaConi: $senhaCon <br>";
echo "grupo: $grupo <br>";
echo "perfil: $perfil <br>";
echo "check: $alt_senha <br>";

/**/

if (empty($nome) || empty($email)) {
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
$senhaCrip = password_hash($senha, PASSWORD_DEFAULT);
echo "<br>Senha Criptografada: $senhaCrip <br>";


$stmt = $pdo->prepare("SELECT * FROM cadastro_user WHERE login = :login");
$stmt->bindParam(":login", $login, PDO::PARAM_STR);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if ($result) {
    if ($result['nome'] === $nome) {
        if ($result['email'] === $email) {
            if (empty($senha) && empty($senhaCon)) {
                $stmt = $pdo->prepare("UPDATE cadastro_user SET nome = :nome, email = :email, grupo = :grupo, setor = :setor, perfil = :perfil,  status_user = :status_user, alt_senha = :alt_senha  WHERE login = :login");
                $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
                $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                //$stmt->bindParam(":senha", $senhaCrip, PDO::PARAM_STR); validação sem senha
                $stmt->bindParam(":grupo", $grupo, PDO::PARAM_STR);
                $stmt->bindParam(":setor", $setor, PDO::PARAM_STR);
                $stmt->bindParam(":fotos", $caminho, PDO::PARAM_STR);
                $stmt->bindParam(":perfil", $perfil, PDO::PARAM_STR);
                $stmt->bindParam(":status_user", $status_user, PDO::PARAM_STR);
                $stmt->bindParam(":alt_senha", $alt_senha, PDO::PARAM_STR);
                $stmt->bindParam(":login", $login, PDO::PARAM_STR);




                if ($stmt->execute()) {
                    $_SESSION['aviso'] = [
                        'icon' => "success",
                        'title' => "Usuário Atualizado",
                        'text' => "Usuário atualizado com sucesso!!"
                    ];
                    echo 'aqui deu boa, alteração sem senha ';
                    header("Location: ../criar_user.php");
                    exit;
                } else {
                    $_SESSION['aviso'] = [
                        'icon' => "error",
                        'title' => "Erro ao Atualizar",
                        'text' => "Erro ao atualizar o usuário."
                    ];
                    echo 'aqui deu erro';
                    header("Location: ../criar_user.php");
                    exit;
                }
            }
            echo "Usuario já existente!";
            $stmt = $pdo->prepare("UPDATE cadastro_user SET nome = :nome, email = :email, senha = :senha, grupo = :grupo, setor = :setor, perfil = :perfil, status_user = :status_user, alt_senha = :alt_senha WHERE login = :login");

            $stmt->bindParam(":nome", $nome, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->bindParam(":senha", $senhaCrip, PDO::PARAM_STR);
            $stmt->bindParam(":grupo", $grupo, PDO::PARAM_STR);
            $stmt->bindParam(":setor", $setor, PDO::PARAM_STR);
            $stmt->bindParam(":perfil", $perfil, PDO::PARAM_STR);
            $stmt->bindParam(":status_user", $status_user, PDO::PARAM_STR);
            $stmt->bindParam(":alt_senha", $alt_senha, PDO::PARAM_STR);
            $stmt->bindParam(":login", $login, PDO::PARAM_STR);


            if ($stmt->execute()) {
                $_SESSION['aviso'] = [
                    'icon' => "success",
                    'title' => "Usuário Atualizado",
                    'text' => "Usuário atualizado com sucesso!!"
                ];
                echo 'aqui deu boa, alteração com senha ';
                header("Location: ../criar_user.php");
                exit;
            } else {
                $_SESSION['aviso'] = [
                    'icon' => "error",
                    'title' => "Erro ao Atualizar",
                    'text' => "Erro ao atualizar o usuário."
                ];
                echo 'aqui deu erro';
                header("Location: ../criar_user.php");
                exit;
            }
        } else {
            $_SESSION['aviso'] = [
                'icon' => "error",
                'title' => "Usuário Não Encontrado",
                'text' => "O usuário não foi encontrado no banco de dados."
            ];
            echo 'aqui não vai mesmos';

            header("Location: ../criar_user.php");
            exit;
        }
    }
} else {
    echo 'Não achei nada no banco';

}

?>