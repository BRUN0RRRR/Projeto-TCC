<?php
//config
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include "../../../../../databases/conexao.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $codigo = $_POST['codigo'];
    $nome = $_POST['nomeItem'];
    $categoria = $_POST['categoria'];
    $quantidadeMinima = $_POST['quantidadeMinima'];
    $modelo = $_POST['modelo'];
    $deposito = $_POST['code_deposito'];
    $marca = $_POST['marca'];
    $localizacao = $_POST['localizacao'];
    $status_item = $_POST['item_status'];
    $user_criador = $_SESSION['nome_com'] ?? 'Desconhecido';
    $precoUnitario = str_replace(',', '.', $_POST['precoUnitario']);

    echo 'Código: ' . $codigo . '<br>';
    echo 'Nome do Item: ' . $nome . '<br>';
    echo 'Categoria: ' . $categoria . '<br>';
    echo 'Quantidade Mínima: ' . $quantidadeMinima . '<br>';
    echo 'Modelo: ' . $modelo . '<br>';
    echo 'Marca: ' . $marca . '<br>';
    echo 'Localização: ' . $localizacao . '<br>';
    echo 'Status do Item: ' . $status_item . '<br>';
    echo 'Usuário Criador: ' . $user_criador . '<br>';
    echo 'Preço Unitário: ' . $precoUnitario . '<br>';

    try {
        // Primeiro, busca os dados atuais do item para comparação
        $sqlAtual = "SELECT * FROM itens WHERE id_produtos = :id_produtos";
        $stmtAtual = $pdo->prepare($sqlAtual);
        $stmtAtual->execute([':id_produtos' => $codigo]);
        $dadosAtuais = $stmtAtual->fetch(PDO::FETCH_ASSOC);

        if (!$dadosAtuais) {
            $_SESSION['aviso'] = [
                'icon' => "error",
                'title' => "Erro!",
                'text' => "Item não encontrado!"
            ];
            header("Location: ../lista_item.php");
            exit;
        }

        $localizacaoAnterior = $dadosAtuais['codigo_completo'];
        $depositoAnterior = $dadosAtuais['id_deposito'];

        // Verifica se já existe um item com a mesma localização, excluindo o item atual
        $sqlVerifica = "SELECT COUNT(*) FROM itens 
                        WHERE codigo_completo = :codigo_completo
                        AND id_produtos = :id_produtos";
        $stmtVerifica = $pdo->prepare($sqlVerifica);
        $stmtVerifica->execute([
            ':codigo_completo' => $localizacao,
            ':id_produtos' => $codigo
        ]);

        $count = $stmtVerifica->fetchColumn();

        if ($count > 0) {
            $_SESSION['aviso'] = [
                'icon' => "error",
                'title' => "Produto já Cadastrado!",
                'text' => "O Produto já foi cadastrado! Verifique Novamente!"
            ];
            header("Location: ../lista_item.php");
            exit;
        }
        // Libera APENAS a localização anterior específica (localização + depósito anterior)

        if ($localizacaoAnterior && $depositoAnterior) {
            $sqlLimpar = "UPDATE localizacoes SET 
                            id_produtos = NULL, 
                            nome = 'Disponivel', 
                            status = 'Ativo'
                          WHERE codigo_completo = :codigo_completo_anterior 
                          AND id_deposito = :id_deposito_anterior";

            $stmtLimpar = $pdo->prepare($sqlLimpar);
            $stmtLimpar->execute([
                ':codigo_completo_anterior' => $localizacaoAnterior,
                ':id_deposito_anterior' => $depositoAnterior
            ]);
        }

        // Atualiza o item
        $sqlAtualizar = "UPDATE itens SET 
                            nome = :nome,
                            codigo_completo = :codigo_completo, 
                            categoria = :categoria, 
                            quantidade_minima = :quantidadeMinima, 
                            preco_unit = :preco_unit, 
                            status_item = :status_item,
                            id_deposito = :id_deposito
                         WHERE id_produtos = :id_produtos";
        $stmtAtualizar = $pdo->prepare($sqlAtualizar);
        $stmtAtualizar->execute([
            ':nome' => $nome,
            ':categoria' => $categoria,
            ':quantidadeMinima' => $quantidadeMinima,
            ':preco_unit' => $precoUnitario,
            ':status_item' => $status_item,
            ':id_deposito' => $deposito,
            ':codigo_completo' => $localizacao,
            ':id_produtos' => $codigo
        ]);


        // Atualiza a nova localização no novo depósito
        $sqlAtualizarLoc = "UPDATE localizacoes SET 
                               id_produtos = :id_produtos,
                               nome = :nome, 
                               status = :status
                            WHERE codigo_completo = :codigo_completo 
                            AND id_deposito = :id_deposito";
        $stmtAtualizarLoc = $pdo->prepare($sqlAtualizarLoc);
        $stmtAtualizarLoc->execute([
            ':codigo_completo' => $localizacao,
            ':nome' => $nome,
            ':status' => 'Inativo',
            ':id_produtos' => $codigo,
            ':id_deposito' => $deposito
        ]);

        $_SESSION['aviso'] = [
            'icon' => "success",
            'title' => "Produto Alterado",
            'text' => "O Produto foi alterado com sucesso!"
        ];
        header("Location: ../lista_item.php");
        exit;

    } catch (PDOException $e) {
        echo "Erro ao atualizar item: " . $e->getMessage();
        // Log do erro seria melhor que exibir diretamente
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Erro!",
            'text' => "Erro ao alterar o produto. Tente novamente."
        ];
        header("Location: ../lista_item.php");
        exit;
    }
}
?>