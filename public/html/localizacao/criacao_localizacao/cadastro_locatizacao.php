<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";

$moduloNecessario = "deposito";

verificarAcessoPagina($moduloNecessario);

$localizacoes_select = "SELECT * FROM localizacoes;";
$stmt = $pdo->prepare($localizacoes_select);
$stmt->execute();
$localizacoes_select = $stmt->fetchAll(PDO::FETCH_ASSOC);

$localizacoes_total = "SELECT COUNT(*) AS total FROM depositos;";
$stmt = $pdo->prepare($localizacoes_total);
$stmt->execute();
$localizacoes_total = $stmt->fetch(PDO::FETCH_ASSOC);

$depositos_select = "SELECT * FROM depositos where status = 'Ativo'";
$stmt = $pdo->prepare($depositos_select);
$stmt->execute();
$depositos_select = $stmt->fetchAll(PDO::FETCH_ASSOC);



ob_end_flush();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Localização</title>
    <link rel="stylesheet" href="css/pagina_localizacao.css">
    <!-- Adicionando Font Awesome para ícones mais bonitos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="js/script.js"></script>

</head>

<body>
    <div class="main-content">
        <div class="header">
            <div class="justify-content-between align-items-center">
                <h2> Posições de Depósito</h2>
            </div>

            <button id="btnGerarPosicoes" class="btn btn-primary">
                <i class="fas fa-plus-circle"></i> Gerar Posições
            </button>
        </div>

        <div class="container-fluid">
            <div class="card">
                <div class="card-body">

                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <span>Total de depositos: 
                            <strong id="totalPosicoes"><?php echo $localizacoes_total["total"]; ?></strong>
                        </span>
                    </div>

                    <!-- Nav tabs para os depósitos -->
                    <ul class="nav nav-tabs" id="depositoTabs" role="tablist">
                        <?php foreach ($depositos_select as $index => $deposito): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo $index === 0 ? 'active' : ''; ?>"
                                    id="tab-<?php echo $deposito['id_deposito']; ?>" data-bs-toggle="tab"
                                    data-bs-target="#deposito-<?php echo $deposito['id_deposito']; ?>" type="button"
                                    role="tab" aria-controls="deposito-<?php echo $deposito['id_deposito']; ?>"
                                    aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>">
                                    <?php echo htmlspecialchars($deposito['nome']); ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Conteúdo das tabs -->
                    <div class="tab-content" id="depositoTabsContent">
                        <?php foreach ($depositos_select as $index => $deposito): ?>
                            <div class="tab-pane fade <?php echo $index === 0 ? 'show active' : ''; ?>"
                                id="deposito-<?php echo $deposito['id_deposito']; ?>" role="tabpanel"
                                aria-labelledby="tab-<?php echo $deposito['id_deposito']; ?>">

                                <!-- Filtro dentro da aba -->
                                <div class="mb-3 mt-3">
                                    <input type="text" class="form-control filtro-tabela"
                                        placeholder="Filtrar nesta tabela..."
                                        data-table-id="tabela-<?php echo $deposito['id_deposito']; ?>">
                                </div>

                                <!-- Tabela específica para o depósito -->
                                <div class="table-responsive">
                                    <table class="table table-striped" id="tabela-<?php echo $deposito['id_deposito']; ?>">
                                        <thead>
                                            <tr>
                                                <th>CÓD DEPÓSITO</th>
                                                <th>PRÉDIO</th>
                                                <th>ANDAR</th>
                                                <th>CORREDOR</th>
                                                <th>PRATELEIRA</th>
                                                <th>COMPARTIMENTO</th>
                                                <th>CÓDIGO COMPLETO</th>
                                                <th>ITEM</th>
                                                <th>STATUS</th>
                                                <th>AÇÕES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($localizacoes_select as $localizacao): ?>
                                                <?php if ($localizacao['id_deposito'] == $deposito['id_deposito']): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($localizacao['id_deposito']); ?></td>
                                                        <td><?php echo htmlspecialchars($localizacao['predio']); ?></td>
                                                        <td><?php echo htmlspecialchars($localizacao['andar_setor']); ?></td>
                                                        <td><?php echo htmlspecialchars($localizacao['corredor']); ?></td>
                                                        <td><?php echo htmlspecialchars($localizacao['prateleira']); ?></td>
                                                        <td><?php echo htmlspecialchars($localizacao['compartimento']); ?></td>
                                                        <td><?php echo htmlspecialchars($localizacao['codigo_completo']); ?></td>
                                                        <td><?php echo htmlspecialchars($localizacao['nome']); ?></td>
                                                        <td>
                                                            <span
                                                                class="status-badge <?php echo strtolower($localizacao['status']) === 'ativo' ? 'status-ativo' : 'status-inativo'; ?>">
                                                                <?php echo htmlspecialchars($localizacao['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <button class="btn btn-sm btn-danger">
                                                                <i class="fas fa-trash"></i> Excluir
                                                            </button>
                                                        </td>
                                                    </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>


    <!-- Modal Gerador de Posições -->
    <div class="modal fade" id="modalGerador" tabindex="-1" aria-labelledby="modalGeradorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalGeradorLabel"><i class="fas fa-cogs"></i> Gerador de Posições de
                        Depósito</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formGerador" action="config/config_criar_localizacao.php" method="POST">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Configure os intervalos para cada nível da localização. Use o formato "LetraNúmero" (ex: A1,
                            B2).
                            Serão geradas todas as combinações possíveis entre os intervalos.
                        </div>
                        <div class="col-md-12">
                            <label for="predio" class="form-label required-field">Codigo do Deposito</label>
                            <div class="input-group">
                                <select class="form-select fornecedor-select" name="code_deposito" required>
                                    <option value="" required>Selecione um Codigo</option>
                                    <?php foreach ($depositos_select as $depositos): ?>
                                        <option value="<?= $depositos['id_deposito'] ?>"
                                            data-nome="<?= htmlspecialchars($depositos['id_deposito']) ?>">
                                            <?= $depositos['id_deposito'] . " - " . $depositos['nome'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="buildingInicio" class="form-label">Predio Início</label>
                                <input type="text" class="form-control" id="buildingInicio" name="predioInicio"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="buildingFim" class="form-label">Predio Fim</label>
                                <input type="text" class="form-control" id="buildingFim" name="predioFim" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="floorInicio" class="form-label">Andar Início</label>
                                <input type="text" class="form-control" id="floorInicio" name="andarInicio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="floorFim" class="form-label">Andar Fim</label>
                                <input type="text" class="form-control" id="floorFim" name="andarFim" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="corridorInicio" class="form-label">Corredor Início</label>
                                <input type="text" class="form-control" id="corridorInicio" name="corredorInicio"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="corridorFim" class="form-label">Corredor Fim</label>
                                <input type="text" class="form-control" id="corridorFim" name="corredorFim" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="shelfInicio" class="form-label">Prateleira Início</label>
                                <input type="text" class="form-control" id="shelfInicio" name="prateleiraInicio"
                                    required>
                            </div>
                            <div class="col-md-6">
                                <label for="shelfFim" class="form-label">Prateleira Fim</label>
                                <input type="text" class="form-control" id="shelfFim" name="prateleiraFim" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="compartmentInicio" class="form-label">Compartimento Início</label>
                                <input type="text" class="form-control" id="compartmentInicio"
                                    name="compartimentoInicio" required>
                            </div>
                            <div class="col-md-6">
                                <label for="compartmentFim" class="form-label">Compartimento Fim</label>
                                <input type="text" class="form-control" id="compartmentFim" name="compartimentoFim"
                                    required>
                            </div>
                        </div>

                        <div class="total-positions mt-4">
                            <p>Total de posições a serem geradas: <span id="totalCalculado"
                                    class="highlight-total">0</span>
                            </p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="sumit" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="sumit" class="btn btn-primary" id="btnConfirmarGeracao">Gerar Posições</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>


        document.querySelectorAll('.filtro-tabela').forEach(input => {
            input.addEventListener('input', function () {
                const tableId = this.getAttribute('data-table-id');
                const searchText = this.value.toLowerCase();
                const rows = document.querySelectorAll(`#${tableId} tbody tr`);

                rows.forEach(row => {
                    const rowText = row.innerText.toLowerCase();
                    row.style.display = rowText.includes(searchText) ? '' : 'none';
                });
            });
        });


        // Passar mensagens de sessão para o JavaScript
        document.addEventListener('DOMContentLoaded', function () {
            // Botão para abrir o modal de geração de posições
            const btnGerarPosicoes = document.getElementById('btnGerarPosicoes');
            const modalGerador = new bootstrap.Modal(document.getElementById('modalGerador'));

            if (btnGerarPosicoes) {
                btnGerarPosicoes.addEventListener('click', function () {
                    modalGerador.show();
                });
            }

            // Cálculo do total de posições a serem geradas
            const inputs = [
                'buildingInicio', 'buildingFim',
                'floorInicio', 'floorFim',
                'corridorInicio', 'corridorFim',
                'shelfInicio', 'shelfFim',
                'compartmentInicio', 'compartmentFim'
            ];

            const totalCalculado = document.getElementById('totalCalculado');

            // Adicionar event listeners para todos os inputs
            inputs.forEach(function (inputId) {
                const input = document.getElementById(inputId);
                if (input) {
                    input.addEventListener('input', calcularTotalPosicoes);
                }
            });

            // Função para calcular o total de posições
            function calcularTotalPosicoes() {
                let total = 1;
                let valido = true;

                // Verificar pares de inputs
                for (let i = 0; i < inputs.length; i += 2) {
                    const inicio = document.getElementById(inputs[i]).value;
                    const fim = document.getElementById(inputs[i + 1]).value;

                    if (inicio && fim) {
                        // Extrair letra e número (formato: A1, B2, etc.)
                        const inicioMatch = inicio.match(/([A-Za-z]*)(\d*)/);
                        const fimMatch = fim.match(/([A-Za-z]*)(\d*)/);

                        if (inicioMatch && fimMatch) {
                            const inicioLetra = inicioMatch[1].toUpperCase();
                            const inicioNumero = parseInt(inicioMatch[2] || '0');
                            const fimLetra = fimMatch[1].toUpperCase();
                            const fimNumero = parseInt(fimMatch[2] || '0');

                            // Calcular diferença
                            if (inicioLetra === fimLetra) {
                                // Apenas números diferentes
                                const diff = fimNumero - inicioNumero + 1;
                                if (diff > 0) {
                                    total *= diff;
                                } else {
                                    valido = false;
                                }
                            } else {
                                // Letras diferentes
                                const inicioCharCode = inicioLetra.charCodeAt(0);
                                const fimCharCode = fimLetra.charCodeAt(0);
                                const diffLetras = fimCharCode - inicioCharCode + 1;

                                if (diffLetras > 0) {
                                    total *= diffLetras;
                                } else {
                                    valido = false;
                                }
                            }
                        }
                    }
                }

                // Atualizar o total
                if (valido && total > 1) {
                    totalCalculado.textContent = total.toLocaleString();
                } else {
                    totalCalculado.textContent = '0';
                }
            }

            // Botão para confirmar a geração de posições
            const btnConfirmarGeracao = document.getElementById('btnConfirmarGeracao');
            if (btnConfirmarGeracao) {
                btnConfirmarGeracao.addEventListener('click', function () {
                    const form = document.getElementById('formGerador');
                    if (form) {
                        form.submit();
                    }
                });
            }

            // Exibir mensagens de sessão (sucesso/erro)
            if (typeof sessionMessages !== 'undefined') {
                if (sessionMessages.success) {
                    alert(sessionMessages.success);
                }
                if (sessionMessages.error) {
                    alert(sessionMessages.error);
                }
            }
        });

    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>