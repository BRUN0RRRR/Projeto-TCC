'''
<?php
ob_start();
// Database connection configuration
include "../../../../databases/conexao.php"; // Adjusted path assuming relative structure
include "../../../../includes/sidebar.php"; // Included sidebar
include "../../../../api/alerta/alert_erro.php"; // Error alert API

$moduloNecessario = "baixa_produtos";
verificarAcessoPagina($moduloNecessario);

// Check if $pdo exists, handle error if not
if (!isset($pdo)) {
    die("Erro: Conexão com o banco de dados não estabelecida.");
}

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

// Filters (New structure based on lista_ent_item.php)
$search = isset($_GET["search"]) ? trim($_GET["search"]) : ""; // Buscar NF
$centro_custo_filter = isset($_GET["centro_custo_filter"]) ? trim($_GET["centro_custo_filter"]) : "";
$data_inicio = isset($_GET["data_inicio"]) ? $_GET["data_inicio"] : "";
$data_fim = isset($_GET["data_fim"]) ? $_GET["data_fim"] : "";
$item_filter = isset($_GET["item_filter"]) ? trim($_GET["item_filter"]) : "";

// --- Build Base Query Parts (Filters) ---
$baseFromClause = "FROM nf_saida nf";
$baseJoinClause = "";
$baseWhereClause = [];
$baseParams = [];

// General Search (NF Number)
if (!empty($search)) {
    $baseWhereClause[] = "nf.nota_fiscal_saida LIKE :search";
    $baseParams["search"] = "%{$search}%";
}

// Specific Centro de Custo Filter
if (!empty($centro_custo_filter)) {
    // Assuming the column name is 'centro_custo'
    $baseWhereClause[] = "nf.cetro_custo LIKE :centro_custo_filter";
    $baseParams["centro_custo_filter"] = "%{$centro_custo_filter}%";
}

// Date Range Filter
if (!empty($data_inicio)) {
    $baseWhereClause[] = "nf.data_saida >= :data_inicio";
    $baseParams["data_inicio"] = $data_inicio;
}
if (!empty($data_fim)) {
    $baseWhereClause[] = "nf.data_saida <= :data_fim";
    $baseParams["data_fim"] = $data_fim;
}

// Item Filter (Requires JOIN with 'saida')
$itemJoinNeeded = !empty($item_filter);
if ($itemJoinNeeded) {
    // Assuming the join column is 'nota_fiscal_saida' and product name is 'nome_produto' in 'saida' table
    $baseJoinClause = " LEFT JOIN saida s ON nf.nota_fiscal_saida = s.nota_fiscal_saida";
    $baseWhereClause[] = "s.nome_produto LIKE :item_filter"; // Adjust 'nome_produto' if needed
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
    $groupByList = $itemJoinNeeded ? " GROUP BY nf.nota_fiscal_saida" : "";

    // Count total invoices for pagination
    $countField = $itemJoinNeeded ? "COUNT(DISTINCT nf.nota_fiscal_saida)" : "COUNT(*)";
    $sqlCount = "SELECT $countField $baseFromClause $baseJoinClause $baseWhereStr";
    $stmtCount = $pdo->prepare($sqlCount);
    $stmtCount->execute($baseParams);
    $totalInvoices = $stmtCount->fetchColumn();
    $totalPages = $totalInvoices > 0 ? ceil($totalInvoices / $limit) : 0;

    // Fetch invoices for the current page
    $sqlAll = "SELECT $selectList $baseFromClause $baseJoinClause $baseWhereStr $groupByList ORDER BY nf.data_saida DESC LIMIT :limit OFFSET :offset";
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
        $sqlHeader = "SELECT * FROM nf_saida WHERE nota_fiscal_saida = :id";
        $stmtHeader = $pdo->prepare($sqlHeader);
        $stmtHeader->execute(["id" => $selected_invoice_id]);
        $invoice_detail_header = $stmtHeader->fetch(PDO::FETCH_ASSOC);

        $sqlProducts = "SELECT * FROM saida WHERE nota_fiscal_saida = :id";
        $stmtProducts = $pdo->prepare($sqlProducts);
        $stmtProducts->execute(["id" => $selected_invoice_id]);
        $invoice_detail_products = $stmtProducts->fetchAll(PDO::FETCH_ASSOC);
    }

} elseif ($action === 'report') {
    // --- Fetch Data for Report View ---
    // Adjust select fields and join based on 'saida' table structure
    $selectReport = "nf.data_saida, nf.nota_fiscal_saida, nf.cetro_custo, s.produto, s.quantidade, s.preco_unit, nf.marca"; // Adjust field names if needed
    // Report always needs the join with saida
    $reportFromClause = "FROM saida s";
    $reportJoinClause = " LEFT JOIN nf_saida nf ON s.nota_fiscal_saida = nf.nota_fiscal_saida";

    // Rebuild WHERE clause and params specifically for the report query structure (s vs nf prefixes)
    $reportWhereClause = [];
    $reportParams = [];
    if (!empty($search)) {
        $reportWhereClause[] = "nf.nota_fiscal_saida LIKE :search";
        $reportParams["search"] = "%{$search}%";
    }
    if (!empty($centro_custo_filter)) {
        $reportWhereClause[] = "nf.cetro_custo LIKE :centro_custo_filter";
        $reportParams["centro_custo_filter"] = "%{$centro_custo_filter}%";
    }
    if (!empty($data_inicio)) {
        $reportWhereClause[] = "nf.data_saida >= :data_inicio";
        $reportParams["data_inicio"] = $data_inicio;
    }
    if (!empty($data_fim)) {
        $reportWhereClause[] = "nf.data_saida <= :data_fim";
        $reportParams["data_fim"] = $data_fim;
    }
    if (!empty($item_filter)) {
        $reportWhereClause[] = "s.produto LIKE :item_filter"; // Adjust field name if needed
        $reportParams["item_filter"] = "%{$item_filter}%";
    }

    $reportWhereStr = !empty($reportWhereClause) ? "WHERE " . implode(" AND ", $reportWhereClause) : "";

    $sqlReport = "SELECT $selectReport $reportFromClause $reportJoinClause $reportWhereStr ORDER BY nf.data_saida DESC, nf.nota_fiscal_saida, s.produto";
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
    'centro_custo_filter' => $centro_custo_filter,
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

// Build filter summary string for report view
$filter_summary_parts = [];
if (!empty($search))
    $filter_summary_parts[] = "NF: '" . htmlspecialchars($search) . "'";
if (!empty($centro_custo_filter))
    $filter_summary_parts[] = "Centro Custo: '" . htmlspecialchars($centro_custo_filter) . "'";
if (!empty($data_inicio))
    $filter_summary_parts[] = "Data Início: '" . formatDate($data_inicio) . "'";
if (!empty($data_fim))
    $filter_summary_parts[] = "Data Fim: '" . formatDate($data_fim) . "'";
if (!empty($item_filter))
    $filter_summary_parts[] = "Item: '" . htmlspecialchars($item_filter) . "'";
$filter_summary = !empty($filter_summary_parts) ? implode('; ', $filter_summary_parts) : "Nenhum filtro aplicado.";

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Saídas de Produtos <?php echo $action === 'report' ? '- Relatório' : ''; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Assuming you might have custom styles -->
    <link rel="stylesheet" href="css/pagina_saida_item.css"> <!-- Adjust path if needed -->
    <style>

    </style>
</head>

<body>
    <!-- Sidebar included by PHP -->
    <div class="main-content">
        <div class="header mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="fs-4 mb-0">Saídas de Produtos
                    <?php echo $action === 'report' ? '<span class="badge bg-info ms-2">Relatório</span>' : ''; ?>
                </h1>
                <div>
                    <a href="<?php echo htmlspecialchars($report_toggle_link); ?>"
                        class="btn btn-sm <?php echo $report_toggle_class; ?> me-2"><i
                            class="bi <?php echo $report_toggle_icon; ?>"></i> <?php echo $report_toggle_text; ?></a>
                    <a href="../saida_item/saida_produtos.php" class="btn btn-sm btn-success">
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
                <!-- Hidden fields for list view state (only relevant for list view) -->
                <?php if ($action === 'list'): ?>
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($selected_invoice_id ?? ''); ?>">
                <?php endif; ?>
                <input type="hidden" name="page" value="1"> <!-- Reset page on filter change -->

                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label for="centro_custo_filter" class="form-label">Centro de Custo:</label>
                        <input type="text" id="centro_custo_filter" name="centro_custo_filter"
                            class="form-control form-control-sm" placeholder="Nome do centro de custo..."
                            value="<?php echo htmlspecialchars($centro_custo_filter); ?>">
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
                        <!-- Clear link should also preserve action state -->
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
                                    // Include all current filters in the link
                                    $link_params = array_merge($base_query_params_filtered, [
                                        'action' => 'list', // Keep action as list for item selection
                                        "id" => $invoice["nota_fiscal_saida"],
                                        "page" => $page
                                    ]);
                                    $query_string = http_build_query($link_params);
                                    ?>
                                    <div
                                        class="invoice-item <?php echo $selected_invoice_id == $invoice["nota_fiscal_saida"] ? "active" : ""; ?>">
                                        <a href="?<?php echo $query_string; ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <div class="fw-bold mb-1">
                                                        <?php echo htmlspecialchars($invoice["nota_fiscal_saida"]); ?>
                                                    </div>
                                                    <div class="text-muted small mb-1">
                                                        <i class="bi bi-calendar-event me-1"></i>
                                                        <?php echo formatDate($invoice["data_saida"]); ?>
                                                    </div>
                                                    <!--   <div class="text-muted small">
                                                        <label for="">fornecedor </label>
                                                        <?php echo htmlspecialchars($invoice["cetro_custo"] ?? "N/A"); // Assuming 'centro_custo' ?>
                                                    </div> -->
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-bold mb-1"><?php echo formatMoney($invoice["valor_total"]); ?>
                                                    </div>
                                                </div>
                                                <i class="bi bi-chevron-right ms-2 text-muted"></i>
                                            </div>
                                        </a>
                                    </div>
                                <?php endforeach; ?>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <div class="p-3 border-top">
                                        <nav aria-label="Navegação de páginas">
                                            <ul class="pagination pagination-sm justify-content-center mb-0">
                                                <?php
                                                // Use the filtered base params for pagination links
                                                $page_params = $base_query_params_filtered;
                                                $page_params['action'] = 'list'; // Keep action as list for pagination
                                                ?>
                                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                                    <a class="page-link"
                                                        href="?<?php echo http_build_query(array_merge($page_params, ['page' => $page - 1, 'id' => $selected_invoice_id])); ?>">&laquo;</a>
                                                </li>
                                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                                        <a class="page-link"
                                                            href="?<?php echo http_build_query(array_merge($page_params, ['page' => $i, 'id' => $selected_invoice_id])); ?>"><?php echo $i; ?></a>
                                                    </li>
                                                <?php endfor; ?>
                                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                                    <a class="page-link"
                                                        href="?<?php echo http_build_query(array_merge($page_params, ['page' => $page + 1, 'id' => $selected_invoice_id])); ?>">&raquo;</a>
                                                </li>
                                            </ul>
                                        </nav>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div> <!-- End card-body -->
                    </div> <!-- End card -->
                </div> <!-- End col-lg-4 -->

                <!-- Invoice Detail Column -->
                <div class="col-lg-8">
                    <?php if ($invoice_detail_header): ?>
                        <div class="card invoice-detail shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h3 class="h5 mb-0">Detalhes da
                                    <?php echo htmlspecialchars($invoice_detail_header["nota_fiscal_saida"]); ?>
                                </h3>
                                
                            </div>
                            <div class="card-body">
                                <!-- Display Header Info -->
                                <div class="row mb-4">
                                    <div class="col-md-6 mb-3 mb-md-0">
                                        <strong>Centro de Custo:</strong>
                                        <?php echo htmlspecialchars($invoice_detail_header["cetro_custo"] ?? 'N/A'); // Assuming 'centro_custo' ?><br>
                                        <strong>Data Saída:</strong>
                                        <?php echo formatDate($invoice_detail_header["data_saida"]); ?><br>

                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <strong>Valor Total:</strong>
                                        <?php echo formatMoney($invoice_detail_header["valor_total"]); ?><br>
                                        <?php if (!empty($invoice_detail_header["motivo_saida"])): ?>
                                            <strong>Motivo da Saída:</strong>
                                            <?php echo htmlspecialchars($invoice_detail_header["motivo_saida"]); ?><br>
                                            <strong>Operador: </strong>
                                            <?php echo htmlspecialchars($invoice_detail_header["usuario_cadastro"]); ?><br>

                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md- text-md-end">
                                        <?php if (!empty($invoice_detail_header["motivo_saida"])): ?>
                                            <strong>Observação: </strong>
                                            <?php echo htmlspecialchars($invoice_detail_header["observacao"] ?? "Sem Observação") ; ?><br>  
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Display Product Details -->
                                <h4 class="h6 mb-3">Itens da Nota</h4>
                                <?php if (empty($invoice_detail_products)): ?>
                                    <p class="text-muted">Nenhum item encontrado para esta nota.</p>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Produto</th>
                                                    <th>Marca</th>
                                                    <th>Quantidade</th>
                                                    <th>Preço Unit.</th>
                                                    <th>Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($invoice_detail_products as $product): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($product['produto'] ?? 'N/A'); // Adjust field name if needed ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($product['marca'] ?? 'N/A'); // Adjust field name if needed ?>
                                                        </td>
                                                        <td><?php echo htmlspecialchars($product['quantidade'] ?? '0'); ?></td>
                                                        <td><?php echo formatMoney($product['preco_unit'] ?? 0); // Adjust field name if needed ?>
                                                        </td>
                                                        <td><?php echo formatMoney(($product['quantidade'] ?? 0) * ($product['preco_unit'] ?? 0)); ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <?php if (!empty($invoice_detail_header["data_saida"])): ?>
                                            <strong>Data Emissão:</strong>
                                            <?php echo formatDate($invoice_detail_header["data_saida"]); ?><br>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                            </div> <!-- End card-body -->
                        </div> <!-- End card -->
                    <?php else: ?>
                        <!-- Empty State for Detail View -->
                        <div class="card shadow-sm">
                            <div class="card-body empty-state">
                                <div class="empty-state-icon"><i class="bi bi-arrow-left-square"></i></div>
                                <h5>Selecione uma Nota Fiscal</h5>
                                <p class="text-muted small">Clique em uma nota fiscal na lista à esquerda para ver os detalhes.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div> <!-- End col-lg-8 -->
            </div> <!-- End row -->

        <?php elseif ($action === 'report'): ?>
            <!-- Report View -->
            <div class="filter-summary">
                <strong>Filtros Aplicados no Relatório:</strong> <?php echo $filter_summary; ?>
            </div>
            <div class="report-container card shadow-sm">
                <div class="card-header bg-light">
                    <h2 class="h5 mb-0">Itens de Saída do Almoxarifado</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($reportData)): ?>
                        <div class="empty-state">
                            <div class="empty-state-icon"><i class="bi bi-table"></i></div>
                            <h5>Nenhum item encontrado para o relatório</h5>
                            <p class="text-muted small">Verifique os filtros aplicados.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Data Saída</th>
                                        <th>Nota Fiscal</th>
                                        <th>Centro Custo</th>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>marca</th>
                                        <th>Valor Unitario</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reportData as $item): ?>
                                        <tr>
                                            <td><?php echo formatDate($item['data_saida']); ?></td>
                                            <td><?php echo htmlspecialchars($item['nota_fiscal_saida']); ?></td>
                                            <td><?php echo htmlspecialchars($item['cetro_custo'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($item['produto'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($item['quantidade'] ?? '0'); ?></td>
                                            <td><?php echo htmlspecialchars($item['marca'] ?? '0'); ?></td>
                                            <td><?php echo formatMoney($item['preco_unit'] ?? 0); ?></td>
                                            <td><?php echo formatMoney(($item['quantidade'] ?? 0) * ($item['preco_unit'] ?? 0)); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div> <!-- End main-content -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add any custom JS if needed -->
</body>

</html>