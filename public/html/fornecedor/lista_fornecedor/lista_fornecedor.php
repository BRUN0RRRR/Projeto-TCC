<?php
ob_start();
// Configurações de erro
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

// Incluindo arquivos necessários
// NOTE: Ensure these paths are correct relative to the new file location or use absolute paths.
include __DIR__ . "/../../../../databases/conexao.php";
include __DIR__ . "/../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";

$moduloNecessario = "fornecedor";
verificarAcessoPagina($moduloNecessario);

// Função para buscar fornecedores
function buscarFornecedores($pdo)
{
    try {
        $sql = "SELECT * FROM fornecedores";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Adicionar status simulado
        foreach ($results as &$row) {
            if (!isset($row["status"])) {
                if ($row["id_fornecedor"] % 3 == 0) {
                    $row["status"] = "Inativo";
                } elseif ($row["id_fornecedor"] % 5 == 0) {
                    $row["status"] = "Bloqueado";
                } else {
                    $row["status"] = "Ativo";
                }
            }
        }
        unset($row); // Unset reference

        return $results;

    } catch (PDOException $e) {
        error_log("Erro ao consultar fornecedores: " . $e->getMessage());
        return []; // Return empty array on error
    }
}

// Fetch data
$fornecedores = [];
$estados = []; // Array para guardar estados únicos para o filtro
if (isset($pdo)) {
    $fornecedores = buscarFornecedores($pdo);
    if (!empty($fornecedores)) {
        $estados = array_unique(array_column($fornecedores, "estado"));
        sort($estados);
    }
} else {
    $erro_db = "Falha na conexão com o banco de dados.";
}

ob_end_flush();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infostock - Fornecedores </title>
    <!-- Bootstrap for Grid and JS Components -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Link to the NEW Alternative CSS file -->
    <link rel="stylesheet" href="css/pagina_lista_fornecedor.css">
</head>

<body>
    <!-- container-fluid includes the 5% left padding from alternative_style.css -->
    <div class="container-fluid">

        <?php if (isset($erro_db)): ?>
            <div class="alert alert-danger mt-3" role="alert"> <!-- Added margin top -->
                <?php echo htmlspecialchars($erro_db); ?>
            </div>
        <?php endif; ?>

        <!-- Header Actions -->
        <div class="header-actions">
            <h1 class="page-title">
                Lista de Fornecedores
            </h1>
            <div class="action-buttons">
                <a href="../cadastrar_fornecedor/cadastrar_fornecedor.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> <span class="btn-text">Novo Fornecedor</span>
                </a>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section mb-4">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label for="searchFornecedor" class="form-label">Buscar</label>
                    <div class="search-box">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="searchFornecedor" class="form-control"
                            placeholder="Buscar por nome, CNPJ, etc...">
                    </div>
                </div>
                <div class="col-md-3">
                    <label for="filterStatus" class="form-label">Status</label>
                    <select id="filterStatus" class="form-select">
                        <option value="">Todos</option>
                        <option value="Ativo">Ativo</option>
                        <option value="Inativo">Inativo</option>
                        <option value="Bloqueado">Bloqueado</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filterEstado" class="form-label">Estado (UF)</label>
                    <select id="filterEstado" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($estados as $estado): ?>
                            <option value="<?php echo htmlspecialchars($estado); ?>">
                                <?php echo htmlspecialchars($estado); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <button id="applyFiltersBtn" class="btn btn-secondary w-100">Filtrar</button>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="card">
            <!-- Removed card-body p-0, let card-body style apply -->
            <div class="card-body">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>CÓDIGO</th>
                                <th>RAZÃO SOCIAL</th>
                                <th>NOME FANTASIA</th>
                                <th>CNPJ</th>
                                <th>TELEFONE</th>
                                <th>E-MAIL</th>
                                <th>CIDADE/UF</th>
                                <th>STATUS</th>
                                <th class="text-center">AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody id="fornecedorTableBody">
                            <?php if (!empty($fornecedores)): ?>
                                <?php foreach ($fornecedores as $fornecedor): ?>
                                    <?php
                                    $status = $fornecedor["status"] ?? "Indefinido";
                                    $statusClass = "status-inativo"; // Default class from alternative_style.css
                                    switch ($status) {
                                        case "Ativo":
                                            $statusClass = "status-ativo";
                                            break;
                                        case "Inativo":
                                            $statusClass = "status-inativo";
                                            break;
                                        case "Bloqueado":
                                            $statusClass = "status-bloqueado";
                                            break;
                                    }
                                    ?>
                                    <tr id="fornecedor-<?php echo $fornecedor["id_fornecedor"]; ?>"
                                        data-codigo="<?php echo $fornecedor["id_fornecedor"]; ?>"
                                        data-razao-social="<?php echo htmlspecialchars($fornecedor["razao_social"]); ?>"
                                        data-nome-fantasia="<?php echo htmlspecialchars($fornecedor["nome_fantasia"]); ?>"
                                        data-cnpj="<?php echo htmlspecialchars($fornecedor["cnpj"]); ?>"
                                        data-telefone="<?php echo htmlspecialchars($fornecedor["telefone"]); ?>"
                                        data-email="<?php echo htmlspecialchars($fornecedor["email"]); ?>"
                                        data-cidade="<?php echo htmlspecialchars($fornecedor["cidade"]); ?>"
                                        data-estado="<?php echo htmlspecialchars($fornecedor["estado"]); ?>"
                                        data-status="<?php echo htmlspecialchars($status); ?>">
                                        <td><?php echo $fornecedor["id_fornecedor"]; ?></td>
                                        <td><?php echo htmlspecialchars($fornecedor["razao_social"]); ?></td>
                                        <td><?php echo htmlspecialchars($fornecedor["nome_fantasia"]); ?></td>
                                        <td class="cnpj"><?php echo htmlspecialchars($fornecedor["cnpj"]); ?></td>
                                        <td class="telefone"><?php echo htmlspecialchars($fornecedor["telefone"]); ?></td>
                                        <td><?php echo htmlspecialchars($fornecedor["email"]); ?></td>
                                        <td><?php echo htmlspecialchars($fornecedor["cidade"] . "/" . $fornecedor["estado"]); ?>
                                        </td>
                                        <td><span
                                                class="status-badge <?php echo $statusClass; ?>"><?php echo htmlspecialchars($status); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <!-- Using btn-edit class from alternative_style.css -->
                                            <button class="btn-edit" data-bs-toggle="modal"
                                                data-bs-target="#editarFornecedorModal"
                                                data-codigo="<?php echo $fornecedor["id_fornecedor"]; ?>"
                                                data-razao-social="<?php echo htmlspecialchars($fornecedor["razao_social"]); ?>"
                                                data-nome-fantasia="<?php echo htmlspecialchars($fornecedor["nome_fantasia"]); ?>"
                                                data-cnpj="<?php echo htmlspecialchars($fornecedor["cnpj"]); ?>"
                                                data-telefone="<?php echo htmlspecialchars($fornecedor["telefone"]); ?>"
                                                data-email="<?php echo htmlspecialchars($fornecedor["email"]); ?>"
                                                data-cidade="<?php echo htmlspecialchars($fornecedor["cidade"]); ?>"
                                                data-estado="<?php echo htmlspecialchars($fornecedor["estado"]); ?>"
                                                data-status="<?php echo htmlspecialchars($status); ?>">
                                                <i class="fas fa-edit"></i> <span class="btn-text">Editar</span>
                                            </button>
                                            <!-- Using btn btn-danger btn-sm classes (Bootstrap base + custom styling) -->

                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr class="empty-table-message">
                                    <td colspan="9">
                                        <i class="fas fa-box-open"></i>
                                        <p>Nenhum fornecedor cadastrado.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                            <tr id="noResultsRow" style="display: none;">
                                <td colspan="9">
                                    <i class="fas fa-search"></i>
                                    <p>Nenhum fornecedor encontrado com os filtros aplicados.</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container (Placeholder) -->
    <div class="toast-container">
        <!-- Toast structure goes here if needed -->
    </div>

    <!-- Modal de Edição de Fornecedor -->
    <div class="modal fade" id="editarFornecedorModal" tabindex="-1" aria-labelledby="editarFornecedorModalLabel"
        aria-hidden="true">
        <!-- Using modal-lg for a wider modal -->
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarFornecedorModalLabel">Editar Fornecedor</h5>
                    <!-- Using Bootstrap's btn-close, styled by alternative_style.css -->
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <form id="editarFornecedorForm" action="config/atualizar_fornecedor.php" method="POST">
                        <input type="hidden" id="editarCodigoFornecedor" name="codigo">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editarRazaoSocial" class="form-label">Razão Social*</label>
                                <input type="text" class="form-control" id="editarRazaoSocial" name="razao_social"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="editarNomeFantasia" class="form-label">Nome Fantasia*</label>
                                <input type="text" class="form-control" id="editarNomeFantasia" name="nome_fantasia"
                                    required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editarCnpj" class="form-label">CNPJ*</label>
                                <input type="text" class="form-control" id="editarCnpj" name="cnpj" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editarTelefone" class="form-label">Telefone*</label>
                                <input type="text" class="form-control" id="editarTelefone" name="telefone" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editarEmail" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="editarEmail" name="email">
                            </div>
                            <div class="col-md-6">
                                <label for="fornecedor_status" class="form-label">Status*</label>
                                <select name="status" id="fornecedor_status" class="form-select">
                                    <option value="Ativo">Ativo</option>
                                    <option value="Inativo">Inativo</option>
                                    <option value="Bloqueado">Bloqueado</option>
                                </select>
                            </div>
                        </div>

                        <h5 class="mb-3 form-section-title">Endereço</h5> <!-- Added class -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="editarCidade" class="form-label">Cidade*</label>
                                <input type="text" class="form-control" id="editarCidade" name="cidade" required>
                            </div>
                            <div class="col-md-6">
                                <label for="editarEstado" class="form-label">Estado*</label>
                                <select class="form-select" id="editarEstado" name="estado" required>
                                    <option value="">Selecione...</option>
                                    <option value="AC">Acre</option>
                                    <option value="AL">Alagoas</option>
                                    <option value="AP">Amapá</option>
                                    <option value="AM">Amazonas</option>
                                    <option value="BA">Bahia</option>
                                    <option value="CE">Ceará</option>
                                    <option value="DF">Distrito Federal</option>
                                    <option value="ES">Espírito Santo</option>
                                    <option value="GO">Goiás</option>
                                    <option value="MA">Maranhão</option>
                                    <option value="MT">Mato Grosso</option>
                                    <option value="MS">Mato Grosso do Sul</option>
                                    <option value="MG">Minas Gerais</option>
                                    <option value="PA">Pará</option>
                                    <option value="PB">Paraíba</option>
                                    <option value="PR">Paraná</option>
                                    <option value="PE">Pernambuco</option>
                                    <option value="PI">Piauí</option>
                                    <option value="RJ">Rio de Janeiro</option>
                                    <option value="RN">Rio Grande do Norte</option>
                                    <option value="RS">Rio Grande do Sul</option>
                                    <option value="RO">Rondônia</option>
                                    <option value="RR">Roraima</option>
                                    <option value="SC">Santa Catarina</option>
                                    <option value="SP">São Paulo</option>
                                    <option value="SE">Sergipe</option>
                                    <option value="TO">Tocantins</option>
                                </select>
                            </div>
                        </div>

                        <!-- Modal Footer is part of the form -->
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                            <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                            <button class="btn btn-danger btn-sm"
                                onclick="confirmarExclusao(<?php echo $fornecedor ["id_fornecedor"]; ?>, '<?php echo htmlspecialchars(addslashes($fornecedor["razao_social"])); ?>')">
                                <i class="fas fa-trash"></i> <span class="btn-text">Excluir</span>
                            </button>
                        </div>
                    </form>
                </div>
                <!-- Removed redundant modal-footer outside form -->
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS for Filtering & Modal (No changes needed) -->
    <script>
        // Filter Elements
        const searchInput = document.getElementById("searchFornecedor");
        const statusFilter = document.getElementById("filterStatus");
        const estadoFilter = document.getElementById("filterEstado");
        const applyFiltersBtn = document.getElementById("applyFiltersBtn");
        const tableBody = document.getElementById("fornecedorTableBody");
        const rows = tableBody.querySelectorAll("tr:not(.empty-table-message):not(#noResultsRow)");
        const noResultsRow = document.getElementById("noResultsRow");

        // Filter Function
        function filterTable() {
            const searchTerm = searchInput.value.toUpperCase();
            const selectedStatus = statusFilter.value;
            const selectedEstado = estadoFilter.value;
            let visibleRowCount = 0;

            rows.forEach(row => {
                const codigo = row.cells[0]?.textContent.toUpperCase() || '';
                const razaoSocial = row.cells[1]?.textContent.toUpperCase() || '';
                const nomeFantasia = row.cells[2]?.textContent.toUpperCase() || '';
                const cnpj = row.cells[3]?.textContent.toUpperCase() || '';
                const telefone = row.cells[4]?.textContent.toUpperCase() || '';
                const email = row.cells[5]?.textContent.toUpperCase() || '';
                const cidadeUf = row.cells[6]?.textContent.toUpperCase() || '';
                const statusBadge = row.cells[7]?.querySelector(".status-badge");
                const status = statusBadge ? statusBadge.textContent : '';
                const estado = row.getAttribute("data-estado") || '';

                const searchMatch = searchTerm === "" ||
                    razaoSocial.includes(searchTerm) ||
                    nomeFantasia.includes(searchTerm) ||
                    cnpj.includes(searchTerm) ||
                    email.includes(searchTerm) ||
                    cidadeUf.includes(searchTerm) ||
                    codigo.includes(searchTerm);

                const statusMatch = selectedStatus === "" || status === selectedStatus;
                const estadoMatch = selectedEstado === "" || estado === selectedEstado;

                if (searchMatch && statusMatch && estadoMatch) {
                    row.style.display = "";
                    visibleRowCount++;
                } else {
                    row.style.display = "none";
                }
            });

            noResultsRow.style.display = (visibleRowCount === 0 && rows.length > 0) ? "" : "none";
        }

        // Filter Event Listener
        applyFiltersBtn.addEventListener("click", filterTable);

        // Modal Population Logic
        var editarModal = document.getElementById("editarFornecedorModal");
        if (editarModal) {
            editarModal.addEventListener("show.bs.modal", function (event) {
                var button = event.relatedTarget; // Button that triggered the modal
                var codigo = button.getAttribute("data-codigo");
                var razaoSocial = button.getAttribute("data-razao-social");
                var nomeFantasia = button.getAttribute("data-nome-fantasia");
                var cnpj = button.getAttribute("data-cnpj");
                var telefone = button.getAttribute("data-telefone");
                var email = button.getAttribute("data-email");
                var cidade = button.getAttribute("data-cidade");
                var estado = button.getAttribute("data-estado");
                var status = button.getAttribute("data-status");

                var modalTitle = editarModal.querySelector(".modal-title");
                var codigoInput = editarModal.querySelector("#editarCodigoFornecedor");
                var razaoSocialInput = editarModal.querySelector("#editarRazaoSocial");
                var nomeFantasiaInput = editarModal.querySelector("#editarNomeFantasia");
                var cnpjInput = editarModal.querySelector("#editarCnpj");
                var telefoneInput = editarModal.querySelector("#editarTelefone");
                var emailInput = editarModal.querySelector("#editarEmail");
                var cidadeInput = editarModal.querySelector("#editarCidade");
                var estadoSelect = editarModal.querySelector("#editarEstado");
                var statusSelect = editarModal.querySelector("#fornecedor_status");

                if (modalTitle) modalTitle.textContent = "Editar Fornecedor: " + nomeFantasia;
                if (codigoInput) codigoInput.value = codigo;
                if (razaoSocialInput) razaoSocialInput.value = razaoSocial;
                if (nomeFantasiaInput) nomeFantasiaInput.value = nomeFantasia;
                if (cnpjInput) cnpjInput.value = cnpj;
                if (telefoneInput) telefoneInput.value = telefone;
                if (emailInput) emailInput.value = email;
                if (cidadeInput) cidadeInput.value = cidade;
                if (estadoSelect) estadoSelect.value = estado;
                if (statusSelect) statusSelect.value = status;
            });
        }

        // Delete Confirmation Logic
        function confirmarExclusao(id, nome) {
            if (confirm("Tem certeza que deseja excluir o fornecedor \"" + nome + "\"?")) {
                let form = document.createElement("form");
                form.method = "POST";
                form.action = ""; // Submit to the same page

                let hiddenFieldId = document.createElement("input");
                hiddenFieldId.type = "hidden";
                hiddenFieldId.name = "id_fornecedor";
                hiddenFieldId.value = id;
                form.appendChild(hiddenFieldId);

                let hiddenFieldAction = document.createElement("input");
                hiddenFieldAction.type = "hidden";
                hiddenFieldAction.name = "excluir_fornecedor";
                hiddenFieldAction.value = "1";
                form.appendChild(hiddenFieldAction);

                document.body.appendChild(form);
                form.submit();
            }
        }

    </script>

</body>

</html>