<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include "../../../../../databases/conexao.php";

$nota_fiscal = $_POST['nota_fiscal'] ?? null;
$id_fornecedor = $_POST['id_fornecedor'] ?? null;
$fornecedor_nome = $_POST['fornecedor_nome'] ?? null;
$usuario_cadastro = $_SESSION['usuario'] ?? 'sistema';




echo '<pre>';
print_r($_POST);
echo '</pre>';



$verificaSQL = "SELECT COUNT(*) FROM entradas WHERE nota_fiscal = :nota_fiscal";
$stmtVerifica = $pdo->prepare($verificaSQL);
$stmtVerifica->execute([':nota_fiscal' => $nota_fiscal]);
$existeNota = $stmtVerifica->fetchColumn();

if ($existeNota > 0) {
    $_SESSION['aviso'] = ['icon' => "error", 'title' => "Erro ao Registrar Entrada", 'text' => "A nota fiscal '$nota_fiscal' já foi cadastrada."];
  header("Location: ../entrada_produtos.php");
    exit;
} else if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();
        if (isset($_POST['itens']) && is_array($_POST['itens'])) {

            $entradaSQL = "INSERT INTO entradas (nota_fiscal, id_produtos, produto,  quantidade, preco_unit, fornecedor, data_cadastro, id_fornecedor, marca, modelo)
            VALUES (:nota_fiscal, :id_produtos, :produto,  :quantidade, :preco_unit, :fornecedor, CURDATE(), :id_fornecedor,:marca, :modelo)";


            $estoqueSQL = "INSERT INTO estoque (nota_fiscal, id_produtos, produto,  quantidade, preco_unit, fornecedor, id_fornecedor, data_cadastro, marca, modelo) 
            VALUES (:nota_fiscal, :id_produtos, :produto,:quantidade, :preco_unit, :fornecedor, :id_fornecedor, CURDATE(), :marca, :modelo)";

            $nfEntradaSQL = "INSERT INTO nf_entrada (nota_fiscal, id_produtos, nome_produto, quantidade, fornecedor, valor_unit, valor_total, data_entrada, usuario_cadastro, marca, modelo)
             VALUES (:nota_fiscal, :id_produtos, :nome_produto,  :quantidade, :fornecedor, :valor_unit, :valor_total, CURDATE(), :usuario_cadastro, :marca, :modelo)";

            $stmtEntrada = $pdo->prepare($entradaSQL);
            $stmtEstoque = $pdo->prepare($estoqueSQL);
            $stmtNfEntrada = $pdo->prepare($nfEntradaSQL);
            $contador_nota = 1;
            $valor_total_nota = 0.0;
            foreach ($_POST['itens'] as $item) {
                if (isset($item['id_produtos'], $item['produto'], $item['quantidade'], $item['preco_unit'])) {
                    $id_produtos = $item['id_produtos'];
                    $produto = $item['produto'];
                    $quantidade = (int) $item['quantidade'];
                    $preco_unit = str_replace(',', '.', $item['preco_unit']);
                    $valor_total = $quantidade * floatval($preco_unit);
                    $valor_total_nota += $valor_total;

                    $marca = $item['marca'];
                    $modelo = $item['modelo'];

                    echo "marca:" . $marca . "<br>";

                    echo "modelo:" . $modelo . "<br>";

                    $stmtEntrada->execute([
                        ':nota_fiscal' => $nota_fiscal,
                        ':id_produtos' => $id_produtos,
                        ':produto' => $produto,
                        ':quantidade' => $quantidade,
                        ':preco_unit' => $preco_unit,
                        ':fornecedor' => $fornecedor_nome,
                        ':id_fornecedor' => $id_fornecedor,
                        ':marca' => $marca,
                        ':modelo' => $modelo
                    ]);
                    $stmtEstoque->execute([
                        ':nota_fiscal' => $nota_fiscal,
                        ':id_produtos' => $id_produtos,
                        ':produto' => $produto,
                        ':quantidade' => $quantidade,
                        ':preco_unit' => $preco_unit,
                        ':fornecedor' => $fornecedor_nome,
                        ':id_fornecedor' => $id_fornecedor,
                        ':marca' => $marca,
                        ':modelo' => $modelo
                    ]);
                }
            }
            $nfEntradaSQL = "INSERT INTO nf_entrada (nota_fiscal, fornecedor, valor_unit, valor_total, data_entrada, usuario_cadastro, marca, modelo)
             VALUES (:nota_fiscal, :fornecedor, :valor_unit, :valor_total, CURDATE(), :usuario_cadastro, :marca, :modelo)";
            $stmtNfEntrada = $pdo->prepare($nfEntradaSQL);
            $stmtNfEntrada->execute([
                ':nota_fiscal' => $nota_fiscal,
                ':fornecedor' => $fornecedor_nome,
                ':valor_unit' => $preco_unit,
                ':valor_total' => $valor_total_nota,
                ':usuario_cadastro' => $usuario_cadastro,
                ':marca' => $marca,
                ':modelo' => $modelo
            ]);
            $pdo->commit();
            $_SESSION['aviso'] = ['icon' => "success", 'title' => "Entrada com Sucesso", 'text' => "A entrada foi registrada com sucesso!"];
          header("location: ../entrada_produtos.php");
            exit;
        } else {
            throw new Exception("Nenhum item foi enviado com a nota fiscal.");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['aviso'] = ['icon' => "error", 'title' => "Erro ao Registrar Entrada", 'text' => $e->getMessage()];
     header("location: ../entrada_produtos.php");
        echo 'deu erro: ' . $e->getMessage();
        exit;
    }
} else {
    exit;
}
?>