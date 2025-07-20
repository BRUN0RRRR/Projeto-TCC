<?php
// Configurações de erro
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

// Incluindo arquivos necessários
include "../../../../../databases/conexao.php";

session_start();

// Função para validar CNPJ (básica)
function validarCNPJ($cnpj)
{
    // Remove caracteres não numéricos
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

    // Verifica se tem 14 dígitos
    if (strlen($cnpj) != 14) {
        return false;
    }

    // Verifica se não é uma sequência de números iguais
    if (preg_match('/(\d)\1{13}/', $cnpj)) {
        return false;
    }

    return true;
}

// Função para validar email
function validarEmail($email)
{
    return empty($email) || filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Função para limpar e formatar telefone
function formatarTelefone($telefone)
{
    // Remove tudo que não é número
    $telefone = preg_replace('/[^0-9]/', '', $telefone);

    // Aplica máscara baseada no tamanho
    if (strlen($telefone) == 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) == 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }

    return $telefone;
}

// Função para formatar CNPJ
function formatarCNPJ($cnpj)
{
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) == 14) {
        return preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $cnpj);
    }
    return $cnpj;
}

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    try {
        // Verificar se a conexão existe
        if (!isset($pdo)) {
            throw new Exception("Erro na conexão com o banco de dados.");
        }

        // Validar dados obrigatórios
        $codigo = trim(filter_input(INPUT_POST, 'codigo'));
        $razao_social = trim(filter_input(INPUT_POST, 'razao_social'));
        $nome_fantasia = trim(filter_input(INPUT_POST, 'nome_fantasia'));
        $cnpj = trim(filter_input(INPUT_POST, 'cnpj'));
        $telefone = trim(filter_input(INPUT_POST, 'telefone'));
        $email = trim(filter_input(INPUT_POST, 'email'));
        $cidade = trim(filter_input(INPUT_POST, 'cidade'));
        $estado = trim(filter_input(INPUT_POST, 'estado'));
        $status = trim(filter_input(INPUT_POST, 'status'));

        // Debug dos dados recebidos
        error_log("Debug - Código recebido: '$codigo' (tipo: " . gettype($codigo) . ")");
        error_log("Debug - Dados: razao_social=$razao_social, cnpj=$cnpj");

        // Validações
        $erros = [];

        // Validação do código mais flexível
        if (empty($codigo) || (!is_numeric($codigo) && !preg_match('/^\d+$/', $codigo))) {
            $erros[] = "Código do fornecedor inválido.";
        }

        // Converter código para inteiro para consultas
        $codigoInt = (int) $codigo;
        if ($codigoInt <= 0) {
            $erros[] = "Código do fornecedor deve ser maior que zero.";
        }

        if (empty($razao_social)) {
            $erros[] = "Razão Social é obrigatória.";
        }

        if (empty($nome_fantasia)) {
            $erros[] = "Nome Fantasia é obrigatório.";
        }

        if (empty($cnpj) || !validarCNPJ($cnpj)) {
            $erros[] = "CNPJ inválido.";
        }

        if (empty($telefone)) {
            $erros[] = "Telefone é obrigatório.";
        }

        if (!validarEmail($email)) {
            $erros[] = "Email inválido.";
        }

        if (empty($cidade)) {
            $erros[] = "Cidade é obrigatória.";
        }

        if (empty($estado)) {
            $erros[] = "Estado é obrigatório.";
        }

        if (!in_array($status, ['Ativo', 'Inativo', 'Bloqueado'])) {
            $erros[] = "Status inválido.";
        }

        if (!empty($erros)) {
            throw new Exception(implode('<br>', $erros));
        }

        // Verificar se o fornecedor existe
        $sqlCheck = "SELECT id_fornecedor FROM fornecedores WHERE id_fornecedor = :codigo";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->bindValue(':codigo', $codigoInt, PDO::PARAM_INT);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() == 0) {
            throw new Exception("Fornecedor com código '$codigo' não encontrado.");
        }

        // Verificar se já existe outro fornecedor com o mesmo CNPJ
        $cnpjLimpo = preg_replace('/[^0-9]/', '', $cnpj);
        $sqlCNPJ = "SELECT id_fornecedor FROM fornecedores WHERE cnpj = :cnpj AND id_fornecedor != :codigo";
        $stmtCNPJ = $pdo->prepare($sqlCNPJ);
        $stmtCNPJ->bindValue(':cnpj', $cnpjLimpo, PDO::PARAM_STR);
        $stmtCNPJ->bindValue(':codigo', $codigoInt, PDO::PARAM_INT);
        $stmtCNPJ->execute();

        if ($stmtCNPJ->rowCount() > 0) {
            throw new Exception("Já existe outro fornecedor cadastrado com este CNPJ.");
        }

        // Formatar dados
        $cnpjFormatado = formatarCNPJ($cnpj);
        $telefoneFormatado = formatarTelefone($telefone);

        // SQL de atualização
        $sql = "UPDATE fornecedores SET 
                    razao_social = :razao_social,
                    nome_fantasia = :nome_fantasia,
                    cnpj = :cnpj,
                    telefone = :telefone,
                    email = :email,
                    cidade = :cidade,
                    estado = :estado,
                    status = :status,
                    data_cadastro = NOW()
                WHERE id_fornecedor = :codigo";

        $stmt = $pdo->prepare($sql);

        // Bind dos parâmetros usando bindValue para melhor controle
        $stmt->bindValue(':razao_social', $razao_social, PDO::PARAM_STR);
        $stmt->bindValue(':nome_fantasia', $nome_fantasia, PDO::PARAM_STR);
        $stmt->bindValue(':cnpj', $cnpjFormatado, PDO::PARAM_STR);
        $stmt->bindValue(':telefone', $telefoneFormatado, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':cidade', $cidade, PDO::PARAM_STR);
        $stmt->bindValue(':estado', $estado, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':codigo', $codigoInt, PDO::PARAM_INT);

        // Log da query para debug
        error_log("SQL Query: " . $sql);
        error_log("Parâmetros: codigo=$codigoInt, razao_social=$razao_social");

        if ($stmt->execute()) {
            $rowsAffected = $stmt->rowCount();
            error_log("Linhas afetadas: $rowsAffected");

            if ($rowsAffected > 0) {
                $response['success'] = true;
                $response['message'] = "Fornecedor atualizado com sucesso!";

                // Log da operação
                error_log("Fornecedor ID {$codigoInt} atualizado com sucesso pelo usuário " . ($_SESSION['usuario_nome'] ?? 'N/A'));

                $_SESSION['aviso'] = [
                    'icon' => "success",
                    'title' => "Fornecedor atualizado!",
                    'text' => "Fornecedor código $codigo atualizado com sucesso!"
                ];
            } else {
                throw new Exception("Nenhuma linha foi atualizada. Verifique se o fornecedor existe.");
            }
        } else {
            throw new Exception("Erro ao executar a query de atualização.");
        }

    } catch (PDOException $e) {
        $response['message'] = "Erro no banco de dados: " . $e->getMessage();
        error_log("Erro PDO ao atualizar fornecedor: " . $e->getMessage());

        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Erro na atualização!",
            'text' => "Erro ao atualizar fornecedor: " . $e->getMessage()
        ];

    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
        error_log("Erro ao atualizar fornecedor: " . $e->getMessage());

        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Erro na atualização!",
            'text' => $e->getMessage()
        ];
    }

    // Se for uma requisição AJAX, retorna JSON
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Redirecionar de volta para a página principal
    header("Location: ../lista_fornecedor.php");
    exit;
}

// Se não for POST, redirecionar
 header("Location: ../lista_fornecedor.php");
exit;
?>