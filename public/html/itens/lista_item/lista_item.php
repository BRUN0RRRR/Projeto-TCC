<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui arquivos necessários para a página
include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";

$moduloNecessario = "produtos";

$categoria_select = 'SELECT * FROM categoria WHERE status = "Ativo"';
$stmt = $pdo->prepare($categoria_select);
$stmt->execute();
$categoria_select = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar os depósitos
$depositos_select = "SELECT * FROM depositos WHERE status = 'Ativo'";
$stmt = $pdo->prepare($depositos_select);
$stmt->execute();
$depositos_select = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Buscar TODAS as localizações ativas
$localizacoes_todas = "SELECT l.*, d.nome AS nome_deposito,
    CONCAT_WS('-', l.predio, l.andar_setor, l.corredor, l.prateleira, l.compartimento) AS posicao_unica
FROM localizacoes l
INNER JOIN depositos d ON l.id_deposito = d.id_deposito
WHERE l.status = 'Ativo' AND d.status = 'Ativo'
ORDER BY l.id_deposito, l.codigo_completo;";

$stmt = $pdo->prepare($localizacoes_todas);
$stmt->execute();
$localizacoes_todas = $stmt->fetchAll(PDO::FETCH_ASSOC);

verificarAcessoPagina($moduloNecessario);
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infostock - Lista de Itens</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="css/pagina_lista_item.css">
    <script src="https://cdn.jsdelivr

.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/js_liste_item.js"></script>
</head>

<body>
    <div class="container mt-3">
        <div>
            <div class="card-body">
                <div class="header-actions">
                    <h1 class="mb-0">Itens Cadastrados</h1>
                    <div class="action-buttons">
                        <button type="button" class="btn btn-success" onclick="gerarRelatorio()">
                            <i class="fas fa-file-excel me-1"></i>Relatório
                        </button>
                    </div>
                </div>

                <!-- Container de Filtros -->
                <div class="filter-container p-4 border rounded bg-light">
                    <h5 class="mb-4">
                        <i class="fas fa-filter me-2"></i>Filtros de Pesquisa
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="filtroNome" class="form-label">Nome do Item</label>
                            <input type="text" id="filtroNome" class="form-control" placeholder="Digite o nome...">
                        </div>

                        <div class="col-md-6">
                            <label for="filtroCategoria" class="form-label">Categoria</label>
                            <select id="filtroCategoria" class="form-select">
                                <option value="">Todas as categorias</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="filtroStatus" class="form-label">Status</label>
                            <select id="filtroStatus" class="form-select">
                                <option value="">Todos os status</option>
                                <option value="Ativo">Ativo</option>
                                <option value="Inativo">Inativo</option>
                                <option value="Bloqueado">Bloqueado</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="filtroPrecoMin" class="form-label">Preço Mín. (R$)</label>
                            <input type="number" id="filtroPrecoMin" class="form-control" placeholder="0,00"
                                step="0.01">
                        </div>

                        <div class="col-md-3">
                            <label for="filtroPrecoMax" class="form-label">Preço Máx. (R$)</label>
                            <input type="number" id="filtroPrecoMax" class="form-control" placeholder="999,99"
                                step="0.01">
                        </div>

                        <div class="col-md-2 d-grid mt-4">
                            <button type="button" class="btn btn-primary" onclick="aplicarFiltros()">
                                <i class="fas fa-search me-1"></i>Filtrar
                            </button>
                        </div>

                        <div class="col-md-2 d-grid mt-4">
                            <button type="button" class="btn btn-secondary" onclick="limparFiltros()">
                                <i class="fas fa-times me-1"></i>Limpar
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Informações dos resultados -->
                <div class="search-results-info" id="resultsInfo">
                    Exibindo todos os itens
                </div>

                <!-- Tabela de itens -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="custom-table">
                                <thead>
                                    <tr>
                                        <th>CÓDIGO</th>
                                        <th>NOME</th>
                                        <th>CATEGORIA</th>
                                        <th>QTD. MÍNIMA</th>
                                        <th>Preço Unitário</th>
                                        <th>LOCALIZAÇÃO</th>
                                        <th>STATUS</th>
                                        <th>AÇÕES</th>
                                    </tr>
                                </thead>
                                <tbody id="itemTableBody">
                                    <?php
                                    try {
                                        $sql = "SELECT * FROM itens  ";

                                        $result = $pdo->query($sql);
                                        $categorias = [];

                                        if ($result->rowCount() > 0) {
                                            while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                                                // Coletar categorias únicas
                                                if (!empty($row['categoria']) && !in_array($row['categoria'], $categorias)) {
                                                    $categorias[] = $row['categoria'];
                                                }

                                                $statusClass = match ($row['status_item']) {
                                                    'Ativo' => 'status-ativo',
                                                    'Inativo' => 'status-inativo',
                                                    'Bloqueado' => 'status-bloqueado',
                                                    default => ''
                                                };

                                                // Sanitização
                                                $nome = htmlspecialchars($row['nome'] ?? 'lkjlklkj', ENT_QUOTES);
                                                $categoria = htmlspecialchars($row['categoria'] ?? '', ENT_QUOTES);
                                                $localizacao = htmlspecialchars(($row['id_deposito'] ?? '') . ' - ' . ($row['codigo_completo'] ?? 'Sem localização'), ENT_QUOTES);
                                                $status = htmlspecialchars($row['status_item'] ?? '', ENT_QUOTES);

                                                echo "<tr class='item-row' 
                                                    data-codigo='{$row['id_produtos']}'
                                                    data-nome='{$nome}'
                                                    data-categoria='{$categoria}'
                                                    data-status='{$status}'     
                                                    data-preco='{$row['preco_unit']}'
                                                    data-localizacao='{$localizacao}'
                                                    data-deposito='{$row['id_deposito']}'
                                                    data-quantidade-minima='{$row['quantidade_minima']}'>
                                                    <td>{$row['id_produtos']}</td>
                                                    <td>{$nome}</td>
                                                    <td>{$categoria}</td>
                                                    <td>{$row['quantidade_minima']}</td>
                                                    <td>R$ " . number_format($row['preco_unit'], 2, ',', '.') . "</td>
                                                    <td>{$localizacao}</td>
                                                    <td><span class='status-badge {$statusClass}'>{$status}</span></td>
                                                    <td>
                                                        <button class='btn-edit btn btn-sm btn-primary'
                                                            onclick='abrirModalEdicao(this)'
                                                            data-codigo='{$row['id_produtos']}'
                                                            data-nome='{$nome}'
                                                            data-categoria='{$categoria}'
                                                            data-quantidade-minima='{$row['quantidade_minima']}'
                                                            data-preco='{$row['preco_unit']}'
                                                            data-status='{$status}'
                                                            data-deposito='{$row['id_deposito']}'
                                                            data-localizacao='{$localizacao}'>
                                                            <i class='fas fa-edit me-1'></i>Editar
                                                        </button>
                                                    </td>
                                                </tr>";
                                            }
                                        } else {
                                            echo "<tr><td colspan='8' class='text-center'>Nenhum item cadastrado.</td></tr>";
                                        }
                                    } catch (PDOException $e) {
                                        echo "<tr><td colspan='8' class='text-center'>Erro ao carregar itens: {$e->getMessage()}</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <div id="noResults" class="no-results text-center py-4" style="display: none;">
                            <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                            <h5>Nenhum item encontrado</h5>
                            <p class="text-muted">Tente ajustar os filtros de pesquisa</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Edição -->
        <div class="modal fade" id="editarItemModal" tabindex="-1" aria-labelledby="editarItemModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editarItemModalLabel">Editar Item</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <form id="editarItemForm" action="config/config_edit.php" method="POST">
                            <input type="hidden" id="editarCodigo" name="codigo">

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="editarNomeItem" class="form-label">Nome do Item*</label>
                                    <input type="text" class="form-control" id="editarNomeItem" name="nomeItem"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <label for="editarCategoria" class="form-label">Categoria*</label>
                                    <div class="input-group">
                                        <select class="form-select fornecedor-select" id="editarCategoria"
                                            name="categoria" required>
                                            <option value="">Selecione uma Categoria</option>
                                            <?php foreach ($categoria_select as $categoria): ?>
                                                <option value="<?= htmlspecialchars($categoria['nome']) ?>"
                                                    data-nome="<?= htmlspecialchars($categoria['nome']) ?>">
                                                    <?= htmlspecialchars($categoria['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="editarQuantidadeMinima" class="form-label">Quantidade Mínima*</label>
                                    <input type="number" class="form-control" id="editarQuantidadeMinima"
                                        name="quantidadeMinima" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="editarPrecoUnitario" class="form-label">Preço Unitário (R$)*</label>
                                    <input type="number" class="form-control" id="editarPrecoUnitario"
                                        name="precoUnitario" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="item_status" class="form-label">Status do Item*</label>
                                    <select name="item_status" id="item_status" class="form-select" required>
                                        <option value="Ativo">Ativo</option>
                                        <option value="Inativo">Inativo</option>
                                        <option value="Bloqueado">Bloqueado</option>
                                    </select>
                                </div>
                            </div>

                            <!-- SEÇÃO ATUALIZADA: Depósito e Localização -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="editardepositos" class="form-label required-field">
                                        <i class="fas fa-warehouse me-1"></i>Depósito*
                                    </label>
                                    <div class="input-group">
                                        <select class="form-select" id="editardepositos" name="code_deposito" required>
                                            <option value="">Selecione um Depósito</option>
                                            <?php foreach ($depositos_select as $depositos): ?>
                                                <option value="<?= $depositos['id_deposito'] ?>"
                                                    data-nome="<?= htmlspecialchars($depositos['nome']) ?>">
                                                    <?= $depositos['id_deposito'] . " - " . htmlspecialchars($depositos['nome']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Alterar o depósito atualizará as
                                        localizações disponíveis
                                    </small>
                                </div>

                                <div class="col-md-6">
                                    <label for="editarlocalizacao" class="form-label">
                                        <i class="fas fa-map-marker-alt me-1"></i>Localização*
                                    </label>
                                    <div class="input-group">
                                        <select class="form-select" name="localizacao" id="editarlocalizacao" required>
                                            <option value="">Primeiro selecione um depósito</option>
                                        </select>
                                        <button type="button" class="btn btn-outline-secondary"
                                            onclick="atualizarLocalizacoes()" title="Atualizar localizações">
                                            <i class="fas fa-sync-alt"></i>
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle me-1"></i>Localizações baseadas no depósito
                                        selecionado
                                    </small>
                                </div>
                            </div>

                            <!-- Informações adicionais sobre o item atual -->
                            <div class="alert alert-info d-none" id="infoItemAtual">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Item atual:</strong>
                                <span id="infoDepositoAtual"></span> |
                                <span id="infoLocalizacaoAtual"></span>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Salvar Alterações
                                </button>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                    <i class="fas fa-times me-2"></i>Cancelar
                                </button>
                                <button type="button" class="btn btn-danger"
                                    onclick="confirmarExclusao()">
                                    <i class="fas fa-trash me-2"></i>Excluir
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Relatório -->
        <div class="modal fade" id="relatorioModal" tabindex="-1" aria-labelledby="relatorioModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="relatorioModalLabel">Gerar Relatório</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                    </div>
                    <div class="modal-body">
                        <form id="relatorioForm">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Relatório:</label>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipoRelatorio"
                                        id="relatorioCompleto" value="completo" checked>
                                    <label class="form-check-label" for="relatorioCompleto">
                                        Relatório Completo (todos os itens)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tipoRelatorio"
                                        id="relatorioFiltrado" value="filtrado">
                                    <label class="form-check-label" for="relatorioFiltrado">
                                        Relatório dos Itens Filtrados
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Formato:</label>
                                <select class="form-select" id="formatoRelatorio">
                                    <option value="csv">CSV (Excel)</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" class="btn btn-success" onclick="baixarRelatorio()">
                            <i class="fas fa-download me-2"></i>Gerar Relatório
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <script>

            // Variáveis globais
            let todosItens = [];
            let itensFiltrados = [];
            let todasLocalizacoes = []; // Array para armazenar todas as localizações

            // Inicialização quando a página carrega
            document.addEventListener('DOMContentLoaded', function () {
                carregarDadosIniciais();
                configurarEventos();
                carregarLocalizacoes(); // Carregar localizações do PHP
            });

            // Função para carregar as localizações do PHP
            function carregarLocalizacoes() {
                // Dados vindos do PHP - você precisará adicionar isso no seu PHP
                todasLocalizacoes = <?php echo json_encode($localizacoes_todas); ?>;
            }

            function carregarDadosIniciais() {
                // Carregar todos os itens da tabela
                const rows = document.querySelectorAll('.item-row');
                todosItens = [];

                const categorias = new Set();

                rows.forEach(row => {
                    const item = {
                        elemento: row,
                        codigo: row.dataset.codigo,
                        nome: (row.dataset.nome || '').toLowerCase(),
                        categoria: row.dataset.categoria || '',
                        status: row.dataset.status || '',
                        preco: parseFloat(row.dataset.preco) || 0,
                        localizacao: (row.dataset.localizacao || '').toLowerCase(),
                        deposito: row.dataset.deposito || '',
                        quantidadeMinima: row.dataset.quantidadeMinima || '0'
                    };

                    todosItens.push(item);

                    if (item.categoria) categorias.add(item.categoria);
                });

                // Preencher selects de filtro
                preencherSelect('filtroCategoria', Array.from(categorias));

                itensFiltrados = [...todosItens];
                atualizarInfoResultados();
            }

            function preencherSelect(selectId, opcoes) {
                const select = document.getElementById(selectId);

                // Manter apenas a primeira opção (padrão)
                const primeiraOpcao = select.querySelector('option[value=""]');
                select.innerHTML = '';
                if (primeiraOpcao) {
                    select.appendChild(primeiraOpcao);
                }

                opcoes.sort().forEach(opcao => {
                    if (opcao) {
                        const option = document.createElement('option');
                        option.value = opcao;
                        option.textContent = opcao;
                        select.appendChild(option);
                    }
                });
            }

            function configurarEventos() {
                // Busca rápida com debounce
                const searchInput = document.getElementById('searchItem');
                if (searchInput) {
                    let timeoutId;
                    searchInput.addEventListener('input', function () {
                        clearTimeout(timeoutId);
                        timeoutId = setTimeout(aplicarFiltros, 300);
                    });
                }

                // Aplicar filtros ao pressionar Enter nos campos
                document.querySelectorAll('#filtroNome, #filtroPrecoMin, #filtroPrecoMax').forEach(input => {
                    input.addEventListener('keypress', function (e) {
                        if (e.key === 'Enter') {
                            aplicarFiltros();
                        }
                    });
                });

                // Aplicar filtros ao mudar selects
                document.querySelectorAll('#filtroCategoria, #filtroStatus').forEach(select => {
                    select.addEventListener('change', aplicarFiltros);
                });

                // NOVO: Evento para filtrar localizações quando o depósito é alterado no modal de edição
                const depositoSelect = document.getElementById('editardepositos');
                if (depositoSelect) {
                    depositoSelect.addEventListener('change', function () {
                        filtrarLocalizacoesPorDeposito(this.value);
                    });
                }
            }

            // NOVA FUNÇÃO: Filtrar localizações baseado no depósito selecionado
            function filtrarLocalizacoesPorDeposito(depositoId) {
                const localizacaoSelect = document.getElementById('editarlocalizacao');

                // Limpar opções existentes
                localizacaoSelect.innerHTML = '<option value="">Selecione uma Posição</option>';

                if (!depositoId) {
                    return; // Se nenhum depósito selecionado, manter apenas a opção padrão
                }

                // Filtrar localizações pelo depósito selecionado
                const localizacoesFiltradas = todasLocalizacoes.filter(loc =>
                    loc.id_deposito == depositoId
                );

                // Adicionar as localizações filtradas ao select
                localizacoesFiltradas.forEach(localizacao => {
                    const option = document.createElement('option');
                    option.value = localizacao.codigo_completo;
                    option.textContent = localizacao.codigo_completo;
                    option.dataset.localizacao = localizacao.codigo_completo;
                    localizacaoSelect.appendChild(option);
                });
            }

            function aplicarFiltros() {
                const filtros = obterFiltros();

                itensFiltrados = todosItens.filter(item => {
                    return (
                        filtrarPorTexto(item, filtros.busca) &&
                        filtrarPorNome(item, filtros.nome) &&
                        filtrarPorCategoria(item, filtros.categoria) &&
                        filtrarPorStatus(item, filtros.status) &&
                        filtrarPorPreco(item, filtros.precoMin, filtros.precoMax)
                    );
                });

                exibirResultados();
                atualizarInfoResultados();
            }

            function obterFiltros() {
                const searchInput = document.getElementById('searchItem');
                return {
                    busca: searchInput ? searchInput.value.toLowerCase().trim() : '',
                    nome: document.getElementById('filtroNome').value.toLowerCase().trim(),
                    categoria: document.getElementById('filtroCategoria').value,
                    status: document.getElementById('filtroStatus').value,
                    precoMin: parseFloat(document.getElementById('filtroPrecoMin').value) || 0,
                    precoMax: parseFloat(document.getElementById('filtroPrecoMax').value) || Infinity
                };
            }

            function filtrarPorTexto(item, busca) {
                if (!busca) return true;

                return (
                    item.nome.includes(busca) ||
                    item.categoria.toLowerCase().includes(busca) ||
                    (item.localizacao || '').includes(busca) ||
                    item.codigo.toString().includes(busca) ||
                    item.status.toLowerCase().includes(busca) ||
                    item.deposito.toString().includes(busca)
                );
            }

            function filtrarPorNome(item, nome) {
                return !nome || item.nome.includes(nome);
            }

            function filtrarPorCategoria(item, categoria) {
                return !categoria || item.categoria === categoria;
            }

            function filtrarPorStatus(item, status) {
                return !status || item.status === status;
            }

            function filtrarPorPreco(item, precoMin, precoMax) {
                return item.preco >= precoMin && item.preco <= precoMax;
            }

            function exibirResultados() {
                const tbody = document.getElementById('itemTableBody');
                const noResults = document.getElementById('noResults');

                // Ocultar todas as linhas
                todosItens.forEach(item => {
                    item.elemento.style.display = 'none';
                });

                if (itensFiltrados.length === 0) {
                    noResults.style.display = 'block';
                } else {
                    noResults.style.display = 'none';

                    // Mostrar apenas itens filtrados
                    itensFiltrados.forEach(item => {
                        item.elemento.style.display = '';
                    });
                }
            }

            function atualizarInfoResultados() {
                const info = document.getElementById('resultsInfo');
                const total = todosItens.length;
                const filtrados = itensFiltrados.length;

                if (filtrados === total) {
                    info.textContent = `Exibindo todos os ${total} itens`;
                } else {
                    info.textContent = `Exibindo ${filtrados} de ${total} itens`;
                }
            }

            function limparFiltros() {
                // Limpar todos os campos de filtro
                const searchInput = document.getElementById('searchItem');
                if (searchInput) searchInput.value = '';

                document.getElementById('filtroNome').value = '';
                document.getElementById('filtroCategoria').value = '';
                document.getElementById('filtroStatus').value = '';
                document.getElementById('filtroPrecoMin').value = '';
                document.getElementById('filtroPrecoMax').value = '';

                // Aplicar filtros limpos
                aplicarFiltros();
            }

            // FUNÇÃO ATUALIZADA: Modal de edição com filtro de localização
            function abrirModalEdicao(button) {
                const modal = new bootstrap.Modal(document.getElementById('editarItemModal'));

                // Preencher os campos do modal
                document.getElementById('editarCodigo').value = button.dataset.codigo || '';
                document.getElementById('editarNomeItem').value = button.dataset.nome || '';
                document.getElementById('editarCategoria').value = button.dataset.categoria || '';
                document.getElementById('editarQuantidadeMinima').value = button.dataset.quantidadeMinima || '';
                document.getElementById('editarPrecoUnitario').value = button.dataset.preco || '';

                const depositoAtual = button.dataset.deposito || '';
                const localizacaoAtual = button.dataset.localizacao || '';

                // Definir o depósito atual
                document.getElementById('editardepositos').value = depositoAtual;

                // Filtrar localizações baseado no depósito atual
                filtrarLocalizacoesPorDeposito(depositoAtual);

                // Aguardar um pouco para que as opções sejam carregadas antes de definir a localização
                setTimeout(() => {
                    const localizacaoSelect = document.getElementById('editarlocalizacao');

                    // Tentar definir a localização atual
                    let localizacaoEncontrada = false;
                    for (let i = 0; i < localizacaoSelect.options.length; i++) {
                        if (localizacaoSelect.options[i].value === localizacaoAtual) {
                            localizacaoSelect.selectedIndex = i;
                            localizacaoEncontrada = true;
                            break;
                        }
                    }

                    // Se a localização não foi encontrada (pode ter sido alterada), criar opção temporária
                    if (!localizacaoEncontrada && localizacaoAtual) {
                        const option = document.createElement('option');
                        option.value = localizacaoAtual;
                        option.text = localizacaoAtual + ' (Localização atual)';
                        option.style.fontStyle = 'italic';
                        localizacaoSelect.appendChild(option);
                        localizacaoSelect.value = localizacaoAtual;
                    }
                }, 100);

                // Definir o status
                const statusSelect = document.getElementById('item_status');
                const status = button.dataset.status || 'Ativo';
                for (let i = 0; i < statusSelect.options.length; i++) {
                    if (statusSelect.options[i].value === status) {
                        statusSelect.selectedIndex = i;
                        break;
                    }
                }

                modal.show();
            }

            function gerarRelatorio() {
                const modal = new bootstrap.Modal(document.getElementById('relatorioModal'));
                modal.show();
            }

            function baixarRelatorio() {
                const tipoRelatorio = document.querySelector('input[name="tipoRelatorio"]:checked').value;
                const formato = document.getElementById('formatoRelatorio').value;

                let dados;
                if (tipoRelatorio === 'completo') {
                    dados = todosItens;
                } else {
                    dados = itensFiltrados;
                }

                if (formato === 'csv') {
                    baixarCSV(dados);
                } else {
                    // Para PDF, você precisaria implementar uma biblioteca como jsPDF
                    alert('Funcionalidade PDF será implementada em breve!');
                }

                // Fechar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('relatorioModal'));
                modal.hide();
            }

            function baixarCSV(dados) {
                const headers = ['Código', 'Nome', 'Categoria', 'Qtd. Mínima', 'Preço', 'Depósito', 'Localização', 'Status'];
                let csvContent = headers.join(';') + '\n';

                dados.forEach(item => {
                    const row = [
                        item.codigo,
                        item.elemento.cells[1].textContent, // Nome
                        item.categoria,
                        item.quantidadeMinima,
                        item.elemento.cells[4].textContent, // Preço formatado
                        item.deposito,
                        item.elemento.cells[6].textContent, // Localização
                        item.status
                    ];
                    csvContent += row.join(';') + '\n';
                });

                // Criar e baixar arquivo
                const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
                const link = document.createElement('a');
                const url = URL.createObjectURL(blob);
                link.setAttribute('href', url);
                link.setAttribute('download', `relatorio_itens_${new Date().toISOString().split('T')[0]}.csv`);
                link.style.visibility = 'hidden';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
            function confirmarExclusao() {
                const codigoItem = document.getElementById('editarCodigo').value;
                const nomeItem = document.getElementById('editarNomeItem').value;

                if (!codigoItem) {
                    alert('Erro: Código do item não encontrado!');
                    return;
                }

                if (confirm(`Tem certeza que deseja excluir o item "${nomeItem}" (Código: ${codigoItem})? Esta ação é irreversível.`)) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'config/config_delete.php';

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'codigo';
                    input.value = codigoItem;
                    form.appendChild(input);

                    document.body.appendChild(form);
                    form.submit();
                }
            }

        </script>

        <script>





            // Preencher os selects de categoria e marca com dados do PHP
            <?php
            if (isset($categorias)) {
                echo "const categoriasDB = " . json_encode($categorias) . ";\n";
            }
            ?>
        </script>
    </div>
</body>

</html>