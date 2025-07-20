<?php
ob_start();
// Conexão com o banco de dados
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";

$moduloNecessario = "estoque";
verificarAcessoPagina($moduloNecessario);

// Populating dropdowns - these are fetched before filtering logic
$fornecedor_select_sql = 'SELECT id_fornecedor, razao_social FROM fornecedores WHERE status = "ativo" ORDER BY razao_social';
$stmt_forn = $pdo->prepare($fornecedor_select_sql);
$stmt_forn->execute();
$fornecedor_select = $stmt_forn->fetchAll(PDO::FETCH_ASSOC);

$categoria_select_sql = 'SELECT nome FROM categoria WHERE status = "Ativo" ORDER BY nome'; // Assuming 'nome' is the column for category name
$stmt_cat = $pdo->prepare($categoria_select_sql);
$stmt_cat->execute();
$categoria_select = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);


// --- SQL Query Construction with Filters ---
$selectClause = "
   SELECT
    MIN(T01.nota_fiscal) AS nota_fiscal,
    T01.id_produtos,
    T01.produto,
    SUM(T01.quantidade) AS quantidade,
    AVG(T01.preco_unit) AS preco_medio,
    SUM(T01.quantidade * T01.preco_unit) AS valor_total,
    T02.categoria,
    T02.quantidade_minima,
    T02.codigo_completo,
    T02.status_item,
    T01.marca,
    T01.modelo
";

$fromClause = "
FROM estoque T01
INNER JOIN itens T02 ON T01.id_produtos = T02.id_produtos
";

$groupByClause = "
GROUP BY 
    T01.id_produtos,
    T01.produto,
    T02.categoria,
    T02.codigo_completo,
    T02.quantidade_minima,
    T02.status_item,
    T01.marca,
    T01.modelo
";

$where = [];
$params = [];

// Search filter
if (!empty($_GET['search'])) {
    $searchTerm = '%' . $_GET['search'] . '%';
    $where[] = "(T01.produto LIKE :search OR T01.id_produtos LIKE :search OR T02.categoria LIKE :search)";
    $params[':search'] = $searchTerm;
}

// Status filter
if (!empty($_GET['status']) && in_array($_GET['status'], ['ativo', 'inativo', 'bloqueado'])) {
    $where[] = "T02.status_item = :status";
    $params[':status'] = $_GET['status'];
}

// Category filter
if (!empty($_GET['categoria'])) {
    $where[] = "T02.categoria = :categoria";
    $params[':categoria'] = $_GET['categoria'];
}

// Supplier filter — pode REMOVER esta parte também, já que não vai mais usar fornecedores.
if (!empty($_GET['id_fornecedor'])) {
    $where[] = "T01.id_fornecedor = :id_fornecedor";
    $params[':id_fornecedor'] = $_GET['id_fornecedor'];
}

// Date range filter
if (!empty($_GET['data_inicio'])) {
    $where[] = "DATE(T01.data_cadastro) >= :data_inicio";
    $params[':data_inicio'] = $_GET['data_inicio'];
}
if (!empty($_GET['data_fim'])) {
    $where[] = "DATE(T01.data_cadastro) <= :data_fim";
    $params[':data_fim'] = $_GET['data_fim'];
}

$sql = $selectClause . $fromClause;
if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}
$sql .= $groupByClause;
$sql .= " ORDER BY T01.produto";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro ao executar consulta: " . $e->getMessage());
    die("Erro ao executar consulta. Verifique os logs para mais detalhes. " . $e->getMessage());
}

// Busca categorias distintas para o filtro (original code, seems fine for populating a different filter if needed, but current category filter uses $categoria_select)
/* $categorias_distinct = [];
try {
    $stmt_distinct_cat = $pdo->query("SELECT DISTINCT categoria FROM itens ORDER BY categoria");
    $categorias_distinct = $stmt_distinct_cat->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    // Não interrompe a execução se falhar
} */

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estoque | Visualização</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/pagina_estoque.css">
</head>

<body>
    <div class="container py-4">
        <div class="header-actions">
            <div>
                <h1 class="page-title">Visualização de Estoque</h1>
                <p class="text-sm text-secondary-dark">Gerencie e acompanhe os itens em estoque</p>
            </div>
            <div class="action-buttons">
                <a href="../../itens/criar_item/criar_item.php "> <button class="btn btn-primary">
                        <i class="fas fa-plus btn-icon"></i>
                        <span class="btn-text">Novo Item</span>
                    </button></a>
                <button class="btn btn-secondary" onclick="imprimirRelatorio()">
                    <i class="fas fa-file-export btn-icon"></i>
                    <span class="btn-text">Exportar</span>
                </button>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Filtros</h5>
            </div>
            <div class="card-body">
                <form method="get" action="">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="search_input" class="form-label">Busca Rápida</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" id="search_input" name="search" class="form-control"
                                    placeholder="Buscar por produto, código, categoria, fornecedor..."
                                    value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">

                    <!--    <div class="col-md-4">
                            <label for="status_select" class="form-label">Status</label>
                            <select id="status_select" name="status" class="form-select">
                                <option value="">Todos os status</option>
                                <option value="ativo" <?php echo (isset($_GET['status']) && $_GET['status'] == 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                                <option value="inativo" <?php echo (isset($_GET['status']) && $_GET['status'] == 'inativo') ? 'selected' : ''; ?>>Inativo</option>
                                <option value="bloqueado" <?php echo (isset($_GET['status']) && $_GET['status'] == 'bloqueado') ? 'selected' : ''; ?>>Bloqueado</option>
                            </select>
                        </div>-->    
                    

                        <div class="col-md-6">
                            <label for="categoria_select" class="form-label">Categoria</label>
                            <select id="categoria_select" name="categoria" class="form-select">
                                <option value="">Selecione uma Categoria</option>
                                <?php foreach ($categoria_select as $cat): ?>
                                    <option value="<?= htmlspecialchars($cat['nome']) ?>" <?php echo (isset($_GET['categoria']) && $_GET['categoria'] == $cat['nome']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($cat['nome']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="fornecedor_select" class="form-label">Fornecedor</label>
                            <select id="fornecedor_select" name="id_fornecedor" class="form-select">
                                <option value="">Selecione um fornecedor</option>
                                <?php foreach ($fornecedor_select as $forn): ?>
                                    <option value="<?= $forn['id_fornecedor'] ?>" <?php echo (isset($_GET['id_fornecedor']) && $_GET['id_fornecedor'] == $forn['id_fornecedor']) ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($forn['razao_social']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Data de Entrada</label>
                            <div class="input-group">
                                <input type="date" name="data_inicio" class="form-control"
                                    value="<?php echo isset($_GET['data_inicio']) ? htmlspecialchars($_GET['data_inicio']) : ''; ?>">
                                <span class="input-group-text">até</span>
                                <input type="date" name="data_fim" class="form-control"
                                    value="<?php echo isset($_GET['data_fim']) ? htmlspecialchars($_GET['data_fim']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="reset" class="btn btn-outline-secondary me-2"
                            onclick="window.location.href=window.location.pathname">Limpar Filtros</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function imprimirRelatorio() {
                const janela = window.open('', '_blank');
                janela.document.write(`
        <html>
        <head>
            <title>Relatório de Estoque</title>
            <style>
                body { font-family: Arial; margin: 20px; }
                h1 { color: #333; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .header { display: flex; justify-content: space-between; margin-bottom: 20px; }
                .filtros { margin-bottom: 20px; font-size: 0.9em; }
                .filtros span { font-weight: bold; }
                @media print {
                    .no-print { display: none; }
                    body { margin: 0; padding: 10px; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Relatório de Estoque</h1>
                <div class="no-print">
                    <button onclick="window.print()" style="padding: 5px 10px; background: #4CAF50; color: white; border: none; cursor: pointer;">Imprimir</button>
                    <button onclick="window.close()" style="padding: 5px 10px; background: #f44336; color: white; border: none; cursor: pointer;">Fechar</button>
                </div>
            </div>
            <div class="filtros">
                <h3>Filtros Aplicados:</h3>
                <p><span>Data da Geração:</span> ${new Date().toLocaleString()}</p>
                <?php
                $filtrosAplicados = false;
                if (!empty($_GET['search'])) {
                    echo "<p><span>Busca:</span> " . htmlspecialchars($_GET['search']) . "</p>";
                    $filtrosAplicados = true;
                }
                if (!empty($_GET['status'])) {
                    echo "<p><span>Status:</span> " . htmlspecialchars($_GET['status']) . "</p>";
                    $filtrosAplicados = true;
                }
                if (!empty($_GET['categoria'])) {
                    echo "<p><span>Categoria:</span> " . htmlspecialchars($_GET['categoria']) . "</p>";
                    $filtrosAplicados = true;
                }
                if (!empty($_GET['id_fornecedor'])) {
                    // Find supplier name for display
                    $fornecedorNome = '';
                    foreach ($fornecedor_select as $forn) {
                        if ($forn['id_fornecedor'] == $_GET['id_fornecedor']) {
                            $fornecedorNome = $forn['razao_social'];
                            break;
                        }
                    }
                    if ($fornecedorNome)
                        echo "<p><span>Fornecedor:</span> " . htmlspecialchars($fornecedorNome) . "</p>";
                    $filtrosAplicados = true;
                }
                if (!empty($_GET['data_inicio'])) {
                    echo "<p><span>Data de Entrada (Início):</span> " . htmlspecialchars($_GET['data_inicio']) . "</p>";
                    $filtrosAplicados = true;
                }
                if (!empty($_GET['data_fim'])) {
                    echo "<p><span>Data de Entrada (Fim):</span> " . htmlspecialchars($_GET['data_fim']) . "</p>";
                    $filtrosAplicados = true;
                }
                if (!$filtrosAplicados) {
                    echo "<p>Nenhum filtro aplicado - Todos os itens</p>";
                }
                ?>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Produto</th>
                        <th>Categoria</th>
                        <th>Quantidade</th>
                        <th>Valor Total</th>
                        <th>Marca</th>
                        <th>Modelo</th>
                        <th>Posição</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($itens)): ?>
                        <tr><td colspan="7" style="text-align:center;">Nenhum item encontrado com os filtros aplicados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($itens as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['id_produtos']) ?></td>
                                <td><?= htmlspecialchars($item['produto']) ?></td>
                                <td><?= htmlspecialchars($item['categoria']) ?></td>
                                <td><?= htmlspecialchars($item['quantidade']) ?></td>
                                <td>R$ <?= number_format($item['valor_total'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($item['marca']) ?></td>
                                <td><?= htmlspecialchars($item['modelo']) ?></td>
                                <td><?= htmlspecialchars($item['codigo_completo']) ?></td>                        
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </body>
        </html>
    `);
                janela.document.close();
            }
        </script>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Quantidade</th>
                                <th>Total do Produto</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Localização</th>                            
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($itens)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">Nenhum item encontrado com os filtros
                                    aplicados.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($itens as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['id_produtos']) ?></td>
                                <td><?= htmlspecialchars($item['produto']) ?></td>
                                <td><?= htmlspecialchars($item['categoria']) ?></td>
                                <td><?= htmlspecialchars($item['quantidade']) ?></td>
                                <td>R$ <?= number_format($item['valor_total'], 2, ',', '.') ?></td>
                                <td><?= htmlspecialchars($item['marca']) ?></td>
                                <td><?= htmlspecialchars($item['modelo']) ?></td>
                                <td><?= htmlspecialchars($item['codigo_completo']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="text-sm">Mostrando <?= count($itens) ?> de <?= count($itens) ?> itens</span>
                <!-- Basic pagination, can be improved -->
                <div class="d-flex gap-2">
                    <button class="btn btn-secondary" disabled><i class="fas fa-chevron-left"></i></button>
                    <button class="btn btn-primary">1</button>
                    <button class="btn btn-secondary" disabled><i class="fas fa-chevron-right"></i></button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>