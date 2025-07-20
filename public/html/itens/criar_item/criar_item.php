<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verifica se é uma requisição AJAX para gerar o código
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_SERVER['HTTP_X_REQUESTED_WITH'])) {
    include "../../../../databases/conexao.php";
    try {
        $sql = "SELECT id_produtos FROM itens ORDER BY id_produtos DESC LIMIT 1";
        $stmt = $pdo->query($sql);
        $ultimoCodigo = $stmt->fetchColumn();

        $proximoCodigo = $ultimoCodigo
            ? str_pad((int) $ultimoCodigo + 1, 6, '000000', STR_PAD_LEFT)
            : '000001';

        header('Content-Type: application/json');
        echo json_encode(['proximoCodigo' => $proximoCodigo]);
        exit;
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao gerar código: ' . $e->getMessage()]);
        exit;
    }
}

// NOVA FUNCIONALIDADE: Verifica se é uma requisição AJAX para buscar localizações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buscar_localizacoes']) && isset($_POST['id_deposito'])) {
    include "../../../../databases/conexao.php";

    $idDeposito = $_POST['id_deposito'];

    try {
        // Debug: verificar se o ID do depósito está chegando
        error_log("ID Depósito recebido: " . $idDeposito);

        $sql = "SELECT id_deposito, codigo_completo FROM localizacoes WHERE status = 'Ativo' AND id_deposito = :id_deposito  ";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id_deposito', $idDeposito, PDO::PARAM_STR);
        $stmt->execute();
        $localizacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug: verificar quantas localizações foram encontradas
        error_log("Localizações encontradas: " . count($localizacoes));
        if (count($localizacoes) > 0) {
            error_log("Primeira localização: " . print_r($localizacoes[0], true));
        }

        header('Content-Type: application/json');
        echo json_encode($localizacoes);
        exit;
    } catch (PDOException $e) {
        error_log("Erro SQL: " . $e->getMessage());
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao buscar localizações: ' . $e->getMessage()]);
        exit;
    }
}

// Inclui arquivos necessários para a página
include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";

$moduloNecessario = "produtos";
verificarAcessoPagina($moduloNecessario);

// Busca categorias
$categoria_select = 'SELECT * FROM categoria WHERE status = "Ativo" ORDER BY nome';
$stmt = $pdo->prepare($categoria_select);
$stmt->execute();
$categoria_select = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca depósitos
$depositos_select = "SELECT * FROM depositos WHERE status = 'Ativo' ORDER BY nome";
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
    <title>Infostock - Cadastro Item</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="css/pagina_criar_item.css">
</head>

<body>
    <div class="container mt-4">
        <div class="page-header">
            <div>
                <h1>Cadastrar Novo Item</h1>
                <p class="text-muted mb-0 mt-2">Preencha os campos abaixo para cadastrar um novo item no estoque</p>
            </div>
        </div>

        <div class="card" id="formItem">
            <form id="itemForm" action="config/config_itens.php" method="POST">
                <!-- Informações básicas do item -->
                <div class="field-group">
                    <div class="field-group-title">
                        <i class="fas fa-info-circle"></i>
                        <span>Informações do Item</span>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="codigoProduto" class="form-label required-field">Código do Produto</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                <input type="text" class="form-control" id="codigoProduto" name="codigoProduto" readonly
                                    required>
                            </div>
                            <div class="form-text">Código gerado automaticamente</div>
                        </div>
                        <div class="col-md-8">
                            <label for="nomeItem" class="form-label required-field">Nome do Item</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                <input type="text" class="form-control" id="nomeItem" name="nomeItem" required
                                    placeholder="Ex: Monitor LED 24 polegadas">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="categoria" class="form-label required-field">Categoria</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-folder"></i></span>
                                <select class="form-select fornecedor-select" name="categoria" required>
                                    <option value="">Selecione uma Categoria</option>
                                    <?php foreach ($categoria_select as $categoria): ?>
                                        <option value="<?= $categoria['nome'] ?>"
                                            data-nome="<?= htmlspecialchars($categoria['nome']) ?>">
                                            <?= htmlspecialchars($categoria['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="quantidadeMinima" class="form-label required-field">Quantidade Mínima</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-sort-numeric-down"></i></span>
                                <input type="number" class="form-control" id="quantidadeMinima" name="quantidadeMinima"
                                    min="0" required placeholder="Ex: 5">
                            </div>
                            <div class="form-text">Alerta será gerado quando o estoque estiver abaixo deste valor</div>
                        </div>
                        <div class="col-md-4">
                            <label for="precoUnitario" class="form-label required-field">Preço Unitário (R$)</label>
                            <div class="preco-input-group">
                                <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                                <input type="text" class="form-control" id="precoUnitario" name="precoUnitario" required
                                    placeholder="Ex: 199,99">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Localização física -->
                <div class="field-group">
                    <div class="field-group-title">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Localização Física</span>
                    </div>

                    <div class="location-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Preencha os campos abaixo para facilitar a localização do item no estoque físico.
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="code_deposito" class="form-label required-field">Depósito</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                <select class="form-select" name="code_deposito" id="code_deposito" required>
                                    <option value="">Selecione um Depósito</option>
                                    <?php foreach ($depositos_select as $deposito): ?>
                                        <option value="<?= htmlspecialchars($deposito['id_deposito']) ?>">
                                            <?= htmlspecialchars($deposito['id_deposito'] . " - " . $deposito['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="localizacao" class="form-label required-field">Localização</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                <select class="form-select" name="localizacao" id="localizacao" required disabled>
                                    <option value="">Primeiro selecione um depósito</option>
                                </select>
                            </div>
                            <div class="form-text" id="loading-localizacao" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-1"></i>Carregando localizações...
                            </div>
                        </div>
                    </div>
                </div>

                <div class="buttons">
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo me-2"></i>Limpar
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Cadastrar Item
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        async function carregarProximoCodigo() {
            try {
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await response.json();

                if (data.error) {
                    console.error(data.error);
                    return;
                }

                document.getElementById('codigoProduto').value = data.proximoCodigo;
            } catch (error) {
                console.error('Erro ao carregar código:', error);
            }
        }

        // Função para formatar valor monetário
        function formatarMoeda(input) {
            let valor = input.value.replace(/[^\d,]/g, '');
            valor = valor.replace(/(\d*),(\d*),?(\d*)/, '$1,$2$3');

            if (valor.includes(',')) {
                const partes = valor.split(',');
                valor = partes[0] + ',' + (partes[1] + '00').substring(0, 2);
            }

            input.value = valor;
        }

        // Função para carregar localizações baseado no depósito selecionado
        function carregarLocalizacoes(idDeposito) {
            const localizacaoSelect = document.getElementById('localizacao');
            const loadingIndicator = document.getElementById('loading-localizacao');

            console.log('Depósito selecionado:', idDeposito); // Debug

            if (!idDeposito) {
                localizacaoSelect.innerHTML = '<option value="">Primeiro selecione um depósito</option>';
                localizacaoSelect.disabled = true;
                return;
            }

            // Mostra indicador de carregamento
            loadingIndicator.style.display = 'block';
            localizacaoSelect.disabled = true;
            localizacaoSelect.innerHTML = '<option value="">Carregando...</option>';

            // Faz a requisição AJAX
            fetch(window.location.href, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `buscar_localizacoes=1&id_deposito=${encodeURIComponent(idDeposito)}`
            })
                .then(response => {
                    console.log('Response status:', response.status); // Debug
                    return response.json();
                })
                .then(data => {
                    console.log('Dados recebidos:', data); // Debug
                    loadingIndicator.style.display = 'none';

                    if (data.error) {
                        console.error('Erro:', data.error);
                        localizacaoSelect.innerHTML = '<option value="">Erro ao carregar localizações</option>';
                        return;
                    }

                    // Limpa e popula as opções
                    localizacaoSelect.innerHTML = '<option value="">Selecione uma Localização</option>';

                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(localizacao => {
                            console.log('Processando localização:', localizacao); // Debug

                            const option = document.createElement('option');
                            option.value = localizacao.codigo_completo || '';

                            // Verifica se os campos existem antes de usar
                            const id = localizacao.codigo_completo || 'S/N';
                          

                            option.textContent = `${id}`;
                            localizacaoSelect.appendChild(option);
                        });
                        localizacaoSelect.disabled = false;
                    } else {
                        localizacaoSelect.innerHTML = '<option value="">Nenhuma localização encontrada</option>';
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição:', error);
                    loadingIndicator.style.display = 'none';
                    localizacaoSelect.innerHTML = '<option value="">Erro ao carregar localizações</option>';
                });
        }

        document.addEventListener("DOMContentLoaded", function () {
            carregarProximoCodigo();

            // Formatação do campo de preço
            const precoInput = document.getElementById('precoUnitario');
            if (precoInput) {
                precoInput.addEventListener('input', function () {
                    formatarMoeda(this);
                });

                precoInput.addEventListener('blur', function () {
                    if (this.value && !this.value.includes(',')) {
                        this.value += ',00';
                    }
                });
            }

            // Event listener para mudança no depósito
            const depositoSelect = document.getElementById('code_deposito');
            if (depositoSelect) {
                depositoSelect.addEventListener('change', function () {
                    carregarLocalizacoes(this.value);
                });
            }

            // Form validation
            const itemForm = document.getElementById('itemForm');
            if (itemForm) {
                itemForm.addEventListener('submit', function (event) {
                    if (!this.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();

                        const invalidFields = this.querySelectorAll(':invalid');
                        invalidFields.forEach(field => {
                            field.classList.add('is-invalid');

                            const parent = field.parentElement;
                            if (!parent.querySelector('.invalid-feedback')) {
                                const feedback = document.createElement('div');
                                feedback.className = 'invalid-feedback';
                                feedback.textContent = 'Este campo é obrigatório';
                                parent.appendChild(feedback);
                            }

                            field.addEventListener('input', function () {
                                if (this.checkValidity()) {
                                    this.classList.remove('is-invalid');
                                    const feedback = parent.querySelector('.invalid-feedback');
                                    if (feedback) {
                                        feedback.remove();
                                    }
                                }
                            });
                        });

                        if (invalidFields.length > 0) {
                            invalidFields[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                            invalidFields[0].focus();
                        }
                    }
                });
            }

            // Reset form functionality
            const resetButton = document.querySelector('button[type="reset"]');
            if (resetButton) {
                resetButton.addEventListener('click', function () {
                    setTimeout(() => {
                        const localizacaoSelect = document.getElementById('localizacao');
                        localizacaoSelect.innerHTML = '<option value="">Primeiro selecione um depósito</option>';
                        localizacaoSelect.disabled = true;
                        carregarProximoCodigo();
                    }, 100);
                });
            }
        });
    </script>
</body>

</html>