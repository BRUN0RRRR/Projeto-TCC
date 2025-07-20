<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "../../../../../databases/conexao.php";

// Captura e valida os dados do formulário
$codigoProduto = $_POST["codigoProduto"] ?? null;
$nomeItem = $_POST["nomeItem"] ?? null;
$categoria = $_POST["categoria"] ?? null;
$quantidadeMinima = $_POST["quantidadeMinima"] ?? 0;
$code_deposito = $_POST["code_deposito"] ?? null;
$localizacao = $_POST["localizacao"] ?? null;
$precoUnitario = str_replace(',', '.', $_POST['precoUnitario'] ?? '0');
$user_criador = $_SESSION['nome_com'] ?? 'Desconhecido';

if (!$codigoProduto || !$nomeItem || !$categoria || !$localizacao || !$code_deposito) {
    $_SESSION['aviso'] = [
        'icon' => "error",
        'title' => "Dados incompletos",
        'text' => "Por favor, preencha todos os campos obrigatórios."
    ];
    header("Location: ../criar_item.php");

    exit;
}

// Explode a localização
$partes = explode('-', $localizacao);
if (count($partes) < 5) {
    $_SESSION['aviso'] = [
        'icon' => "error",
        'title' => "Localização inválida",
        'text' => "O código de localização está incompleto."
    ];
    exit;
}

list($predio, $andarSetor, $corredor, $prateleira, $compartimento) = $partes;

// Verifica se a posição já está ocupada
$sqlVerificaPosicao = "SELECT COUNT(*) FROM localizacoes 
    WHERE id_deposito = :id_deposito 
      AND predio = :predio 
      AND andar_setor = :andar_setor 
      AND corredor = :corredor 
      AND prateleira = :prateleira 
      AND compartimento = :compartimento 
      AND id_produtos IS NOT NULL";

$stmtVerifica = $pdo->prepare($sqlVerificaPosicao);
$stmtVerifica->execute([
    ':id_deposito' => $code_deposito,
    ':predio' => $predio,
    ':andar_setor' => $andarSetor,
    ':corredor' => $corredor,
    ':prateleira' => $prateleira,
    ':compartimento' => $compartimento
]);

if ($stmtVerifica->fetchColumn() > 0) {
    $_SESSION['aviso'] = [
        'icon' => "error",
        'title' => "Posição já ocupada",
        'text' => "Já existe um produto nessa posição! Verifique novamente!"
    ];
    header("Location: ../criar_item.php");

    exit;
}

// Verifica se o produto já existe
$stmt = $pdo->prepare("SELECT 1 FROM itens WHERE id_produtos = :id_produtos");
$stmt->bindParam(":id_produtos", $codigoProduto);
$stmt->execute();

if ($stmt->fetch()) {
    $_SESSION['aviso'] = [
        'icon' => "error",
        'title' => "Produto já Cadastrado",
        'text' => "O produto já foi cadastrado anteriormente."
    ];
    header("Location: ../criar_item.php");
    exit;
}

// Insere o novo item
$sqlInserirItem = "INSERT INTO itens (
    id_produtos, nome, categoria, quantidade_minima, preco_unit, status_item, 
    id_deposito, codigo_completo, usuario_cadastro, data_criacao
) VALUES (
    :id_produtos, :nome, :categoria, :quantidade_minima, :preco_unit, 'Ativo', 
    :id_deposito, :codigo_completo, :usuario_cadastro, CURDATE()
)";
$stmtInserir = $pdo->prepare($sqlInserirItem);
$stmtInserir->execute([
    ':id_produtos' => $codigoProduto,
    ':nome' => $nomeItem,
    ':categoria' => $categoria,
    ':quantidade_minima' => $quantidadeMinima,
    ':preco_unit' => $precoUnitario,
    ':id_deposito' => $code_deposito,
    ':codigo_completo' => $localizacao,
    ':usuario_cadastro' => $user_criador
]);

// Atualiza a localização
$sqlAtualizar = "UPDATE localizacoes SET 
    id_produtos = :id_produtos,
    nome = :nome, 
    status = :status
    WHERE id_deposito = :id_deposito AND codigo_completo = :codigo_completo";

$stmtAtualizar = $pdo->prepare($sqlAtualizar);
$stmtAtualizar->execute([
    ':id_deposito' => $code_deposito,
    ':id_produtos' => $codigoProduto,
    ':codigo_completo' => $localizacao,
    ':nome' => $nomeItem,
    ':status' => 'Inativo'
]);

$_SESSION['aviso'] = [
    'icon' => "success",
    'title' => "Produto Cadastrado",
    'text' => "O produto foi cadastrado e a posição atualizada com sucesso!"
];

header("Location: ../criar_item.php");
exit;
?>