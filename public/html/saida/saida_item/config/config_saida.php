<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
include "../../../../../databases/conexao.php";

$user_criador = $_SESSION['nome_com'] ?? 'Desconhecido';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();

        // Dados gerais da nota
        $notaFiscal = $_POST['nota_fiscal'] ?? 'SP-' . time();
        $motivo = $_POST['motivo'];
        $observacoes = $_POST['observacoes'];
        $centroCusto = $_POST['id_custo'];
        $data_saida = date('Y-m-d');

        $valor_total_geral_real = 0; // Valor real baseado no FIFO

        // Array para armazenar os valores reais calculados por item
        $itens_processados = [];

        // Processa cada item primeiro para verificar estoque e preparar baixas
        foreach ($_POST['itens'] as $index => $item) {
            $id_produtos = $item['id_produtos'];
            $produto = $item['produto'];
            $quantidadeSaida = (int) $item['quantidade'];
            $preco_unit_usuario = (float) str_replace(',', '.', $item['preco_unit']); // Preço do usuário
            $marca = $item['marca'];
            $modelo = $item['modelo'];

            // ✅ Controle de baixa FIFO - Apenas para quantidade
            $stmt = $pdo->prepare("
                SELECT id, quantidade
                FROM estoque 
                WHERE id_produtos = :id_produtos AND marca = :marca AND modelo = :modelo AND quantidade > 0
                ORDER BY data_cadastro ASC
            ");
            $stmt->execute([
                ':id_produtos' => $id_produtos,
                ':marca' => $marca,
                ':modelo' => $modelo
            ]);

            $estoques = $stmt->fetchAll();
            $quantidade_restante = $quantidadeSaida;
            $detalhes_baixa = []; // Para rastrear as baixas

            // Simula a baixa para verificar disponibilidade
            foreach ($estoques as $estoque) {
                $quantidadeDisponivel = $estoque['quantidade'];

                if ($quantidade_restante <= 0) {
                    break;
                }

                if ($quantidadeDisponivel >= $quantidade_restante) {
                    $detalhes_baixa[] = [
                        'id' => $estoque['id'],
                        'quantidade_baixa' => $quantidade_restante,
                        'zerar_linha' => false
                    ];
                    $quantidade_restante = 0;
                } else {
                    $detalhes_baixa[] = [
                        'id' => $estoque['id'],
                        'quantidade_baixa' => $quantidadeDisponivel,
                        'zerar_linha' => true
                    ];
                    $quantidade_restante -= $quantidadeDisponivel;
                }
            }

            if ($quantidade_restante > 0) {
                throw new Exception("Estoque insuficiente para o produto: $produto. Faltam $quantidade_restante unidades.");
            }

            // Calcula valor total baseado no preço informado pelo usuário
            $valor_total_item = $quantidadeSaida * $preco_unit_usuario;

            // Armazena dados processados
            $itens_processados[$index] = [
                'item' => $item,
                'preco_unit_usuario' => $preco_unit_usuario,
                'valor_total_item' => $valor_total_item,
                'detalhes_baixa' => $detalhes_baixa
            ];

            $valor_total_geral_real += $valor_total_item;
        }

        // Insere na tabela nf_saida (cabeçalho da nota) com valores reais
        $stmtNF = $pdo->prepare("INSERT INTO nf_saida 
            (nota_fiscal_saida, valor_unit, valor_total, cetro_custo, motivo_saida, observacao, data_saida, usuario_cadastro, marca, modelo) 
            VALUES (:nf, :valor_unit, :valor_total, :centro_custo, :motivo, :obs, :data_saida, :usuario, :marca, :modelo)");

        // Pega o primeiro item como referência para marca e modelo
        $primeiroItem = $_POST['itens'][0];
        $marca = $primeiroItem['marca'];
        $modelo = $primeiroItem['modelo'];
        $primeiro_preco_usuario = $itens_processados[0]['preco_unit_usuario'];

        $stmtNF->execute([
            ':nf' => $notaFiscal,
            ':valor_unit' => $primeiro_preco_usuario,
            ':valor_total' => $valor_total_geral_real, // Valor baseado no preço do usuário
            ':centro_custo' => $centroCusto,
            ':motivo' => $motivo,
            ':obs' => $observacoes,
            ':data_saida' => $data_saida,
            ':usuario' => $user_criador,
            ':marca' => $marca,
            ':modelo' => $modelo
        ]);

        // Prepara inserção de itens na saída
        $stmtItem = $pdo->prepare("INSERT INTO saida 
            (nota_fiscal_saida, id_produtos, produto, quantidade, preco_unit, valor_total, data_cadastro, data_saida, marca, modelo)
            VALUES (:nf, :id_produtos, :produto, :quantidade, :preco_unit, :valor_total, CURDATE(), :data_saida, :marca, :modelo)");

        // Processa cada item novamente para fazer as baixas reais
        foreach ($itens_processados as $item_processado) {
            $item = $item_processado['item'];
            $preco_unit_usuario = $item_processado['preco_unit_usuario'];
            $valor_total_item = $item_processado['valor_total_item'];
            $detalhes_baixa = $item_processado['detalhes_baixa'];

            $id_produtos = $item['id_produtos'];
            $produto = $item['produto'];
            $quantidadeSaida = (int) $item['quantidade'];
            $marca = $item['marca'];
            $modelo = $item['modelo'];

            // Insere na tabela de saída com preços informados pelo usuário
            $stmtItem->execute([
                ':nf' => $notaFiscal,
                ':id_produtos' => $id_produtos,
                ':produto' => $produto,
                ':quantidade' => $quantidadeSaida,
                ':preco_unit' => $preco_unit_usuario, // Preço informado pelo usuário
                ':valor_total' => $valor_total_item, // Valor baseado no preço do usuário
                ':data_saida' => $data_saida,
                ':marca' => $marca,
                ':modelo' => $modelo
            ]);

            // Executa as baixas no estoque - CORRIGIDO: Atualiza apenas quantidade
            foreach ($detalhes_baixa as $baixa) {
                if ($baixa['zerar_linha']) {
                    $stmtUpdate = $pdo->prepare("
                        UPDATE estoque 
                        SET quantidade = 0
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([':id' => $baixa['id']]);
                } else {
                    $stmtUpdate = $pdo->prepare("
                        UPDATE estoque 
                        SET quantidade = quantidade - :qtd
                        WHERE id = :id
                    ");
                    $stmtUpdate->execute([
                        ':qtd' => $baixa['quantidade_baixa'],
                        ':id' => $baixa['id']
                    ]);
                }
            }
        }

        $pdo->commit();

        $_SESSION['aviso'] = [
            'icon' => "success",
            'title' => "Saída registrada com sucesso",
            'text' => "Nota fiscal $notaFiscal foi salva. Valor total real: R$ " . number_format($valor_total_geral_real, 2, ',', '.')
        ];
        header("Location: ../saida_produtos.php");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Erro ao registrar saída",
            'text' => $e->getMessage()
        ];
        header("Location: ../saida_produtos.php");
        exit;
    }
}
?>