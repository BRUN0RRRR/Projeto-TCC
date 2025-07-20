<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include "../../../../../databases/conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_produto = $_POST['codigo'];

    try {
        // 🔍 Busca os dados atuais do item
        $sqlAtual = "SELECT * FROM itens WHERE id_produtos = :id_produtos";
        $stmtAtual = $pdo->prepare($sqlAtual);
        $stmtAtual->execute([':id_produtos' => $id_produto]);
        $dadosAtuais = $stmtAtual->fetch(PDO::FETCH_ASSOC);

        if ($dadosAtuais) {
            $localizacaoAnterior = $dadosAtuais['codigo_completo'];
            $depositoAnterior = $dadosAtuais['id_deposito'];

        
            // 🔄 Atualiza a tabela de localizações antes de excluir
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

            // 🚮 Exclui o item
            $sql = "DELETE FROM itens WHERE id_produtos = :id_produto";
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id_produto', $id_produto, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $_SESSION['aviso'] = [
                    'icon' => "success",
                    'title' => "Produto Excluído",
                    'text' => "O Produto foi excluído com sucesso!"
                ];
            } else {
                $_SESSION['aviso'] = [
                    'icon' => "error",
                    'title' => "Produto não Excluído",
                    'text' => "Erro ao excluir o item. Tente novamente."
                ];
            }
        } else {
            $_SESSION['aviso'] = [
                'icon' => "error",
                'title' => "Produto não encontrado",
                'text' => "Não foi possível localizar o item para exclusão."
            ];
        }

    } catch (PDOException $e) {
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Erro de Banco de Dados",
            'text' => "Erro: " . $e->getMessage()
        ];
    }
} else {
    $_SESSION['aviso'] = [
        'icon' => "error",
        'title' => "Requisição Inválida",
        'text' => "Requisição inválida para exclusão."
    ];
}

header("Location: ../lista_item.php");
exit();
?>