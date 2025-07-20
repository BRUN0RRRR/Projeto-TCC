<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include "../../../../../databases/conexao.php";

// Cast explícito para evitar erro de tipo
$codigoFornecedor = $_POST['codigoFornecedor'];
$razao_social = $_POST['razao_social'] ?? '';
$nome_fantasia = $_POST['nome_fantasia'] ?? '';
$cnpj = $_POST['cnpj'] ?? '';
$inscricao_estadual = $_POST['inscricao_estadual'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$telefone_secundario = $_POST['telefone_secundario'] ?? '';
$email = $_POST['email'] ?? '';
$site = $_POST['site'] ?? '';
$nome_contato = $_POST['nome_contato'] ?? '';
$cargo = $_POST['cargo_contato'] ?? '';
$cep = $_POST['cep'] ?? '';
$rua = $_POST['rua'] ?? '';
$numero = $_POST['numero'] ?? '';
$complemento = $_POST['complemento'] ?? '';
$bairro = $_POST['bairro'] ?? '';
$cidade = $_POST['cidade'] ?? '';
$estado = $_POST['estado'] ?? '';
$ibge = $_POST['ibge'] ?? '';
$user_criador = $_SESSION['nome_com'] ?? 'Desconhecido';


echo $codigoFornecedor . "<br>";
echo $razao_social . "<br>";
echo $nome_fantasia . "<br>";
echo $cnpj . "<br>";
echo $inscricao_estadual . "<br>";
echo $telefone . "<br>";
echo $telefone_secundario . "<br>";
echo $email . "<br>";
echo $nome_contato . "<br>";
echo $cargo . "<br>";
echo $cep . "<br>";
echo $rua . "<br>";
echo $complemento . "<br>";
echo $bairro . "<br>";
echo $cidade . "<br>";
echo $estado . "<br>";
echo $ibge . "<br>";
echo $user_criador . "<br>";


// Verificação do código
if ($codigoFornecedor <= 0) {
    die("Código do fornecedor inválido.");
}

try {
    // Verifica se o ID do fornecedor já existe
    $stmt = $pdo->prepare("SELECT * FROM fornecedores WHERE id_fornecedor = :codigoFornecedor");
    $stmt->bindParam(":codigoFornecedor", $codigoFornecedor, PDO::PARAM_INT);
    $stmt->execute();
    $resultId = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultId) {
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "ID já cadastrado!",
            'text' => "Código de fornecedor já cadastrado, tente outro!"
        ];
        header("location: ../cadastrar_fornecedor.php");
        exit();
    }

    // Verifica se o CNPJ já existe
    $stmt = $pdo->prepare("SELECT * FROM fornecedores WHERE cnpj = :cnpj");
    $stmt->bindParam(":cnpj", $cnpj);
    $stmt->execute();
    $resultCnpj = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultCnpj) {
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "CNPJ já cadastrado!",
            'text' => "Já existe um fornecedor com esse CNPJ."
        ];
        header("location: ../cadastrar_fornecedor.php");
        exit();
    } else {
        $sql = "INSERT INTO fornecedores (
                    id_fornecedor, razao_social, nome_fantasia, cnpj, inscricao_estadual,
                    telefone, telefone_seg, email, site, nome_contado, cargo, cep, rua, numero, complemento, bairro,
                    cidade, estado, user_criador, data_cadastro, status
                ) VALUES (
                    :id_fornecedor, :razao_social, :nome_fantasia, :cnpj, :inscricao_estadual,
                    :telefone, :telefone_seg, :email, :site, :nome_contado, :cargo, :cep, :rua, :numero, :complemento, :bairro,
                    :cidade, :estado, :user_criador, CURDATE(), 'Ativo'
                )";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id_fornecedor' => $codigoFornecedor,
            ':razao_social' => $razao_social,
            ':nome_fantasia' => $nome_fantasia,
            ':cnpj' => $cnpj,
            ':inscricao_estadual' => $inscricao_estadual,
            ':telefone' => $telefone,
            ':telefone_seg' => $telefone_secundario,
            ':email' => $email,
            ':site' => $site,
            ':nome_contado' => $nome_contato,
            ':cargo' => $cargo,
            ':cep' => $cep,
            ':rua' => $rua,
            ':numero' => $numero,
            ':complemento' => $complemento,
            ':bairro' => $bairro,
            ':cidade' => $cidade,
            ':estado' => $estado,
            ':user_criador' => $user_criador
        ]);

        $_SESSION['aviso'] = [
            'icon' => "success",
            'title' => "Fornecedor Cadastrado!",
            'text' => "Cadastro realizado com sucesso!"
        ];
        header("location: ../cadastrar_fornecedor.php");
        exit();
    }

} catch (Exception $e) {
    echo "Erro: " . $e->getMessage();
}
?>