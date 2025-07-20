<?php

ob_start();
// Database connection configuration
include "../../../../databases/conexao.php"; // Adjusted path assuming relative structure
include "../../../../includes/sidebar.php"; // Included sidebar
include "../../../../api/alerta/alert_erro.php"; // Error alert API

// Check if $pdo exists, handle error if not
if (!isset($pdo)) {
    die("Erro: Conexão com o banco de dados não estabelecida.");
}

$moduloNecessario = "entrada_produtos";
verificarAcessoPagina($moduloNecessario);

// --- Helper Functions ---
function formatMoney($value)
{
    return is_numeric($value) ? "R$ " . number_format($value, 2, ",", ".") : "R$ 0,00";
}

function formatDate($date)
{
    $timestamp = strtotime($date);
    return $timestamp ? date("d/m/Y", $timestamp) : "Data inválida";
}

// --- Get Parameters ---
$action = isset($_GET['action']) ? $_GET['action'] : 'list'; // 'list' or 'report'
$selected_invoice_id = isset($_GET["id"]) ? $_GET["id"] : null;
$limit = 10; // Number of invoices per page for list view
$page = isset($_GET["page"]) ? (int) $_GET["page"] : 1;
$offset = ($page - 1) * $limit;

// Filters
$search = isset($_GET["search"]) ? trim($_GET["search"]) : "";
//$status_filter = isset($_GET["status"]) ? $_GET["status"] : "";
$fornecedor_filter = isset($_GET["fornecedor_filter"]) ? trim($_GET["fornecedor_filter"]) : "";
$data_inicio = isset($_GET["data_inicio"]) ? $_GET["data_inicio"] : "";
$data_fim = isset($_GET["data_fim"]) ? $_GET["data_fim"] : "";
$item_filter = isset($_GET["item_filter"]) ? trim($_GET["item_filter"]) : "";

// --- Build Base Query Parts (Filters) ---
$baseFromClause = "FROM nf_entrada nf";
$baseJoinClause = "";
$baseWhereClause = [];
$baseParams = [];

// General Search (NF Number or Fornecedor)
if (!empty($search)) {
    $baseWhereClause[] = "(nf.nota_fiscal LIKE :search OR nf.fornecedor LIKE :search)";
    $baseParams["search"] = "%{$search}%";
}

// Specific Fornecedor Filter
if (!empty($fornecedor_filter)) {
    $baseWhereClause[] = "nf.fornecedor LIKE :fornecedor_filter";
    $baseParams["fornecedor_filter"] = "%{$fornecedor_filter}%";
}

// Date Range Filter
if (!empty($data_inicio)) {
    $baseWhereClause[] = "nf.data_entrada >= :data_inicio";
    $baseParams["data_inicio"] = $data_inicio;
}
if (!empty($data_fim)) {
    $baseWhereClause[] = "nf.data_entrada <= :data_fim";
    $baseParams["data_fim"] = $data_fim;
}

/* Status Filter
if (!empty($status_filter) && $status_filter != "Todos") {
    $baseWhereClause[] = "nf.status = :status";
    $baseParams["status"] = $status_filter;
}
*/
// Item Filter (Requires JOIN with 'entradas')
$itemJoinNeeded = !empty($item_filter);
if ($itemJoinNeeded) {
    $baseJoinClause = " LEFT JOIN entradas e ON nf.nota_fiscal = e.nota_fiscal";
    $baseWhereClause[] = "e.nome_produto LIKE :item_filter";
    $baseParams["item_filter"] = "%{$item_filter}%";
}

$baseWhereStr = !empty($baseWhereClause) ? "WHERE " . implode(" AND ", $baseWhereClause) : "";

// --- Data Fetching Logic ---
$invoices = [];
$invoice_detail_header = null;
$invoice_detail_products = [];
$reportData = [];
$totalInvoices = 0;
$totalPages = 0;

if ($action === 'list') {
    // --- Fetch Invoice List for Display ---
    $selectList = $itemJoinNeeded ? "DISTINCT nf.*" : "nf.*";
    $groupByList = $itemJoinNeeded ? " GROUP BY nf.nota_fiscal" : "";

    // Count total invoices for pagination
    $countField = $itemJoinNeeded ? "COUNT(DISTINCT nf.nota_fiscal)" : "COUNT(*)";
    $sqlCount = "SELECT $countField $baseFromClause $baseJoinClause $baseWhereStr";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($baseParams);
    $totalInvoices = $stmtCount->fetchColumn();
    $totalPages = $totalInvoices > 0 ? ceil($totalInvoices / $limit) : 0;

    // Fetch invoices for the current page
    $sqlAll = "SELECT $selectList $baseFromClause $baseJoinClause $baseWhereStr $groupByList ORDER BY nf.data_entrada DESC LIMIT :limit OFFSET :offset";
    $stmtAll = $pdo->prepare($sqlAll);
    $stmtAll->bindValue(":limit", $limit, PDO::PARAM_INT);
    $stmtAll->bindValue(":offset", $offset, PDO::PARAM_INT);
    foreach ($baseParams as $key => $value) {
        $stmtAll->bindValue(":" . $key, $value);
    }
    $stmtAll->execute();
    $invoices = $stmtAll->fetchAll(PDO::FETCH_ASSOC);

    // Fetch details for the selected invoice (if any)
    if ($selected_invoice_id) {
        $sqlHeader = "SELECT * FROM nf_entrada WHERE nota_fiscal = :id";
        $stmtHeader = $pdo->prepare($sqlHeader);
        $stmtHeader->execute(["id" => $selected_invoice_id]);
        $invoice_detail_header = $stmtHeader->fetch(PDO::FETCH_ASSOC);

        $sqlProducts = "SELECT * FROM entradas WHERE nota_fiscal = :id";
        $stmtProducts = $pdo->prepare($sqlProducts);
        $stmtProducts->execute(["id" => $selected_invoice_id]);
        $invoice_detail_products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
    }

} elseif ($action === 'report') {
    // --- Fetch Data for Report View ---
    $selectReport = "nf.data_entrada, nf.nota_fiscal, nf.fornecedor, e.produto, e.quantidade, e.preco_unit";
    // Report always needs the join with entradas
    $reportFromClause = "FROM entradas e";
    $reportJoinClause = " LEFT JOIN nf_entrada nf ON e.nota_fiscal = nf.nota_fiscal";

    // Rebuild WHERE clause and params specifically for the report query structure (e vs nf prefixes)
    $reportWhereClause = [];
    $reportParams = [];
    if (!empty($search)) {
        $reportWhereClause[] = "nf.nota_fiscal LIKE :search";
        $reportParams["search"] = "%{$search}%";
    }
    if (!empty($fornecedor_filter)) {
        $reportWhereClause[] = "nf.fornecedor LIKE :fornecedor_filter";
        $reportParams["fornecedor_filter"] = "%{$fornecedor_filter}%";
    }
    if (!empty($data_inicio)) {
        $reportWhereClause[] = "nf.data_entrada >= :data_inicio";
        $reportParams["data_inicio"] = $data_inicio;
    }
    if (!empty($data_fim)) {
        $reportWhereClause[] = "nf.data_entrada <= :data_fim";
        $reportParams["data_fim"] = $data_fim;
    }

    if (!empty($item_filter)) {
        $reportWhereClause[] = "e.produto LIKE :item_filter";
        $reportParams["item_filter"] = "%{$item_filter}%";
    }

    $reportWhereStr = !empty($reportWhereClause) ? "WHERE " . implode(" AND ", $reportWhereClause) : "";

    $sqlReport = "SELECT $selectReport $reportFromClause $reportJoinClause $reportWhereStr ORDER BY nf.data_entrada DESC, nf.nota_fiscal, e.produto";
    $stmtReport = $pdo->prepare($sqlReport);
    foreach ($reportParams as $key => $value) {
        $stmtReport->bindValue(":" . $key, $value);
    }
    $stmtReport->execute();
    $reportData = $stmtReport->fetchAll(PDO::FETCH_ASSOC);
}

ob_end_flush();

// Prepare base query parameters for links (pagination, item selection, report toggle)
$base_query_params = [
    'search' => $search,
    'fornecedor_filter' => $fornecedor_filter,
    'data_inicio' => $data_inicio,
    'data_fim' => $data_fim,
    'item_filter' => $item_filter
];
$base_query_params_filtered = array_filter($base_query_params, function ($v) {
    return $v !== '' && $v !== null;
});

// Link to toggle report view
$report_toggle_params = $base_query_params_filtered;
if ($action === 'list') {
    $report_toggle_params['action'] = 'report';
    $report_toggle_text = 'Gerar Relatório';
    $report_toggle_icon = 'bi-file-earmark-text';
    $report_toggle_class = 'btn-info';
} else { // action === 'report'
    // No action parameter needed to go back to list view
    $report_toggle_text = 'Ver Notas';
    $report_toggle_icon = 'bi-list-ul';
    $report_toggle_class = 'btn-secondary';
}
$report_toggle_link = "?" . http_build_query($report_toggle_params);

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Entradas de Produtos <?php echo $action === 'report' ? '- Relatório' : ''; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/pagina_ent_item.css"> <!-- Adjust path if needed -->
    <style>
        .invoice-list .card-body {
            max-height: calc(100vh - 350px);
            /* Adjust as needed */
            overflow-y: auto;
        }

        .invoice-item a {
            display: block;
            padding: 0.75rem 1rem;
            text-decoration: none;
            color: inherit;
            border-bottom: 1px solid #eee;
        }

        .invoice-item:last-child a {
            border-bottom: none;
        }

        .invoice-item a:hover {
            background-color: #f8f9fa;
        }

        .invoice-item.active a {
            background-color: #e9ecef;
            font-weight: bold;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .filter-section {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .report-container {
            padding-top: 20px;
        }

        .filter-summary {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
            font-size: 0.9em;
        }
    </style>
</head>

<body>
    <!-- Sidebar included by PHP -->
    <div class="main-content">
        <div class="header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="fs-4 mb-0">Entradas de Produtos
                    <?php echo $action === 'report' ? '<span class="badge bg-info ms-2">Relatório</span>' : ''; ?>
                </h1>
                <div>
                    <a href="<?php echo htmlspecialchars($report_toggle_link); ?>"
                        class="btn btn-sm <?php echo $report_toggle_class; ?> me-2"><i
                            class="bi <?php echo $report_toggle_icon; ?>"></i> <?php echo $report_toggle_text; ?></a>
                    <a href="../entrada_item/entrada_produtos.php" class="btn btn-sm btn-success">
                        <i class="bi bi-plus-lg"></i> Nova Nota Fiscal
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter Section - Always Visible -->
        <div class="filter-section mb-4 p-3 border rounded shadow-sm bg-light">
            <form action="" method="GET">
                <!-- Keep current action -->
                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                <!-- Hidden fields for list view state -->
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($selected_invoice_id ?? ''); ?>">
                <input type="hidden" name="page" value="1"> <!-- Reset page on filter change -->

                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="fornecedor_filter" class="form-label">Fornecedor:</label>
                        <input type="text" id="fornecedor_filter" name="fornecedor_filter"
                            class="form-control form-control-sm" placeholder="Nome do fornecedor..."
                            value="<?php echo htmlspecialchars($fornecedor_filter); ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="data_inicio" class="form-label">Data Início:</label>
                        <input type="date" id="data_inicio" name="data_inicio" class="form-control form-control-sm"
                            value="<?php echo htmlspecialchars($data_inicio); ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="data_fim" class="form-label">Data Fim:</label>
                        <input type="date" id="data_fim" name="data_fim" class="form-control form-control-sm"
                            value="<?php echo htmlspecialchars($data_fim); ?>">
                    </div>

                    <div class="col-md-3">
                        <label for="item_filter" class="form-label">Item:</label>
                        <input type="text" id="item_filter" name="item_filter" class="form-control form-control-sm"
                            placeholder="Nome do produto..." value="<?php echo htmlspecialchars($item_filter); ?>">
                    </div>

                    <div class="col-md-2">
                        <label for="search" class="form-label">Buscar NF:</label>
                        <input type="text" id="search" name="search" class="form-control form-control-sm"
                            placeholder="Número da NF..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col text-end">
                        <a href="?action=<?php echo htmlspecialchars($action); ?>"
                            class="btn btn-sm btn-outline-secondary me-2">
                            Limpar
                        </a>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="bi bi-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
            </form>
        </div>


        <?php if ($action === 'list'): ?>
            <!-- Standard List/Detail View -->
            <div class="row">
                <!-- Invoice List Column -->
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <div class="card invoice-list shadow-sm">
                        <div class="card-header bg-light d-flex justify-content-between align-items-center">
                            <h2 class="h5 mb-0">Notas Fiscais</h2>
                            <span class="text-muted small flex-shrink-0">
                                <?php if ($totalInvoices > 0): ?>
                                    <?php echo $totalInvoices; ?> nota(s)
                                <?php else: ?>
                                    Nenhuma nota
                                <?php endif; ?>
                            </span>
                        </div>

                        <!-- Invoice List Items -->
                        <div class="card-body p-0">
                            <?php if (empty($invoices)): ?>
                                <div class="empty-state">
                                    <div class="empty-state-icon"><i class="bi bi-journal-x"></i></div>
                                    <h5>Nenhuma nota encontrada</h5>
                                    <p class="text-muted small">Verifique os filtros aplicados ou cadastre uma nova nota.</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($invoices as $invoice): ?>
                                    <?php
                                    $link_params = array_merge($base_query_params_filtered, [
                                        'action' => 'list', // Ensure we stay in list view
                                        "id" => $invoice["nota_fiscal"],
                                        "page" => $page
                                    ]);
                                    $query_string = http_build_query($link_params);
                                    ?>
                                    <div
                                        class="invoice-item <?php echo $selected_invoice_id == $invoice["nota_fiscal"] ? "active" : ""; ?>">
                                        <a href="?<?php echo $query_string; ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold mb-1">
                                                        <?php echo htmlspecialchars($invoice["nota_fiscal"]); ?>
                                                    </div>
                                                    <div class="text-muted small mb-1"><i class="bi bi-calendar-event me-1"></i>
                                                        <?php echo formatDate($invoice["data_entrada"]); ?></div>
                                                    <div class="text-muted small"><i class="bi bi-building me-1"></i>
                                                        <?php echo htmlspecialchars($invoice["fornecedor"] ?? "N/A"); ?></div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold mb-1"><?php echo formatMoney($invoice["valor_total"]); ?>
                                                    </div>
                                                    <?php if (!empty($invoice['status'])): ?>
                                                        <span
                                                            class="badge bg-secondary"><?php echo htmlspecialchars($invoice['status']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <i class="bi bi-chevron-right ms-2 text-muted"></i>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <div class="card-footer bg-light p-3 border-top">
                                <nav aria-label="Navegação de páginas">
                                    <ul class="pagination pagination-sm justify-content-center mb-0">
                                        <?php
                                        $page_link_params = $base_query_params_filtered;
                                        $page_link_params['action'] = 'list'; // Keep in list view
                                        if ($selected_invoice_id) {
                                            $page_link_params['id'] = $selected_invoice_id;
                                        }
                                        ?>
                                        <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="?<?php echo http_build_query(array_merge($page_link_params, ['page' => $page - 1])); ?>">&laquo;</a>
                                        </li>
                                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                <a class="page-link"
                                                    href="?<?php echo http_build_query(array_merge($page_link_params, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                            </li>
                                        <?php endfor; ?>
                                        <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="?<?php echo http_build_query(array_merge($page_link_params, ['page' => $page + 1])); ?>">&raquo;</a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Invoice Detail Column -->
                <div class="col-lg-8">
                    <?php if ($invoice_detail_header): ?>
                        <div class="card invoice-detail shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h3 class="h5 mb-0">Detalhes
                                    <?php echo htmlspecialchars($invoice_detail_header["nota_fiscal"]); ?>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4 border-bottom pb-3">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <p class="mb-1"><strong>Fornecedor:</strong>
                                            <?php echo htmlspecialchars($invoice_detail_header["fornecedor"] ?? 'N/A'); ?></p>
                                        <?php if (!empty($invoice_detail_header["cnpj_fornecedor"])): ?>
                                            <p class="mb-1"><strong>CNPJ:</strong>
                                                <?php echo htmlspecialchars($invoice_detail_header["cnpj_fornecedor"]); ?></p>
                                        <?php endif; ?>
                                        <p class="mb-0"><strong>Data Entrada:</strong>
                                            <?php echo formatDate($invoice_detail_header["data_entrada"]); ?></p>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <p class="mb-1"><strong>Valor Total:</strong> <span
                                                class="fw-bold fs-5"><?php echo formatMoney($invoice_detail_header["valor_total"]); ?></span>
                                        </p>
                                        <?php if (!empty($invoice_detail_header["chave_nfe"])): ?>
                                            <p class="mb-1"><strong>Chave NFe:</strong>
                                                <small><?php echo htmlspecialchars($invoice_detail_header["chave_nfe"]); ?></small>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($invoice_detail_header["status"])): ?>
                                            <p class="mb-0"><strong>Status:</strong> <span
                                                    class="badge bg-info"><?php echo htmlspecialchars($invoice_detail_header["status"]); ?></span>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <h4 class="h6 mb-3">Itens da Nota</h4>
                                <?php if (empty($invoice_detail_products)): ?>
                                    <p class="text-muted">Nenhum item encontrado para esta nota.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Produto</th>
                                                    <th>Quantidade</th>
                                                    <th>Marca</th>
                                                    <th>Modelo</th>
                                                    <th>Fornecedor</th>
                                                    <th>Valor Unit</th>
                                                    <th>Valor Total</th>

                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($invoice_detail_products as $product): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($product['produto'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($product['quantidade'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($product['marca'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($product['modelo'] ?? 'N/A'); ?></td>
                                                        <td><?php echo htmlspecialchars($product['fornecedor'] ?? 'N/A'); ?></td>
                                                        <td><?php echo formatMoney($product['preco_unit'] ?? 0); ?></td>
                                                        <td><?php echo formatMoney(($product['quantidade'] ?? 0) * ($product['preco_unit'] ?? 0)); ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card shadow-sm">
                            <div class="card-body text-center text-muted" style="padding: 4rem 1rem;">
                                <i class="bi bi-journal-arrow-down fs-1 mb-3"></i>
                                <h5 class="card-title">Selecione uma Nota Fiscal</h5>
                                <p class="card-text">Clique em uma nota fiscal na lista à esquerda para ver os detalhes.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        <?php elseif ($action === 'report'): ?>
            <!-- Report View -->
            <div class="report-container">
                <div class="filter-summary">
                    <strong>Filtros Aplicados no Relatório:</strong><br>
                    <?php
                    $filtersApplied = false;
                    if (!empty($fornecedor_filter)) {
                        echo "Fornecedor: " . htmlspecialchars($fornecedor_filter) . "<br>";
                        $filtersApplied = true;
                    }
                    if (!empty($data_inicio)) {
                        echo "Data Início: " . formatDate($data_inicio) . "<br>";
                        $filtersApplied = true;
                    }
                    if (!empty($data_fim)) {
                        echo "Data Fim: " . formatDate($data_fim) . "<br>";
                        $filtersApplied = true;
                    }
                    if (!empty($item_filter)) {
                        echo "Item: " . htmlspecialchars($item_filter) . "<br>";
                        $filtersApplied = true;
                    }
                    if (!empty($search)) {
                        echo "Busca NF: " . htmlspecialchars($search) . "<br>";
                        $filtersApplied = true;
                    }
                    if (!empty($status_filter) && $status_filter != "Todos") {
                        echo "Status: " . htmlspecialchars($status_filter) . "<br>";
                        $filtersApplied = true;
                    }
                    if (!$filtersApplied) {
                        echo "Nenhum filtro aplicado.";
                    }
                    ?>
                </div>

                <?php if (empty($reportData)): ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Nenhum item encontrado para os filtros selecionados no relatório.
                    </div>
                <?php else: ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-light">
                            <h3 class="h5 mb-0">Itens de Entrada no Almoxarifado</h3>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Data Entrada</th>
                                            <th>Nota Fiscal</th>
                                            <th>Fornecedor</th>
                                            <th>Produto</th>
                                            <th>Quantidade</th>
                                            <th>Valor Unit.</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($reportData as $item): ?>
                                            <tr>
                                                <td><?php echo formatDate($item['data_entrada']); ?></td>
                                                <td><?php echo htmlspecialchars($item['nota_fiscal']); ?></td>
                                                <td><?php echo htmlspecialchars($item['fornecedor'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($item['produto'] ?? 'N/A'); ?></td>
                                                <td><?php echo htmlspecialchars($item['quantidade'] ?? 'N/A'); ?></td>
                                                <td><?php echo formatMoney($item['preco_unit'] ?? 0); ?></td>

                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-light text-end">
                            <small class="text-muted">Total de itens no relatório: <?php echo count($reportData); ?></small>
                        </div>
                    </div>
                    <!-- Optional: Add Export Button Here -->
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </div> <!-- End main-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Optional JS
    </script>
</body>

</html>