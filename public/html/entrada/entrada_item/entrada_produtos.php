<?php
ob_start();
// Simulação de dados das últimas entradas
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";

$moduloNecessario = "entrada_produtos";
verificarAcessoPagina($moduloNecessario);

try {
    // Busca todos os itens ativos
    $itens_select = 'SELECT * FROM itens where status_item = "Ativo"';
    $stmt = $pdo->prepare($itens_select);
    $stmt->execute();
    $resultados_items = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $fornecedor_select = 'SELECT * FROM fornecedores where status = "ativo"';
    $stmt = $pdo->prepare($fornecedor_select);
    $stmt->execute();
    $fornecedor_select = $stmt->fetchAll(PDO::FETCH_ASSOC);


    // Busca as últimas entradas para exibir na tabela
    $entradas_select = 'SELECT * FROM entradas';
    $stmt_entradas = $pdo->prepare($entradas_select);
    $stmt_entradas->execute();
    $entradas = $stmt_entradas->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao executar a consulta: " . $e->getMessage());
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Entrada de Produtos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/pagina_entrada.css">
</head>

<body>
    <div class="container mt-4">
        <h2 class="mb-4"> Entrada de Produtos</h2>

        <!-- Formulário de Nova Entrada com Múltiplas Linhas -->
        <div class="card p-4 mb-4">
            <h5> Nova Entrada de Nota Fiscal</h5>
            <form method="post" action="config/config_entrada.php" id="form-entrada">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="nota_fiscal" class="form-label" required>Número da Nota Fiscal</label>
                        <input type="text" class="form-control" id="nota_fiscal" name="nota_fiscal" value="NF-"
                            required>
                    </div>
                    <div class="col-md-4">
                        <label for="fornecedor" class="form-label">Fornecedor</label>
                        <select class="form-select fornecedor-select" name="id_fornecedor" required>
                            <option value="">Selecione um fornecedor</option>
                            <?php foreach ($fornecedor_select as $fornecedor): ?>
                                <option value="<?= $fornecedor['id_fornecedor'] ?>"
                                    data-nome="<?= htmlspecialchars($fornecedor['razao_social']) ?>">
                                    <?= htmlspecialchars($fornecedor['razao_social']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="fornecedor_nome" class="fornecedor-nome-input">
                    </div>
                </div>

                <h5 class="mt-4 mb-3"><i class="bi bi-list-check"></i> Itens da Nota Fiscal</h5>


                <!-- Cabeçalho dos Itens -->
                <div class="row cabecalho-itens d-none d-md-flex fw-bold border-bottom pb-2 mb-2">
                    <div class="col-md-3">Produto</div>
                    <div class="col-md-2">Marca</div>
                    <div class="col-md-2">Modelo</div>
                    <div class="col-md-1">Quant</div>
                    <div class="col-md-2">Preço Unitário</div>
                    <div class="col-md-1">Total</div>
                    <div class="col-md-1">Ações</div>
                </div>

                <!-- Container com os Itens -->
                <div id="linhas-produtos">
                    <div class="linha-produto mb-3">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label d-md-none">Produto</label>
                                <select class="form-select produto-select" name="itens[0][id_produtos]" required>
                                    <option value="">Selecione um produto</option>
                                    <?php foreach ($resultados_items as $item): ?>
                                        <option value="<?= $item['id_produtos'] ?>"
                                            data-nome="<?= htmlspecialchars($item['nome']) ?>">
                                            <?= htmlspecialchars($item['nome']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="itens[0][produto]" class="item-nome-input">
                            </div>

                            <div class="col-md-2">
                                <label class="form-label d-md-none">Marca</label>
                                <input type="text" class="form-control marca-input" name="itens[0][marca]"
                                    placeholder="Marca" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label d-md-none">Modelo</label>
                                <input type="text" class="form-control modelo-input" id="" name="itens[0][modelo]"
                                    placeholder="Modelo" required>
                            </div>

                            <div class="col-md-1">
                                <label class="form-label d-md-none">Quantidade</label>
                                <input type="number" class="form-control quantidade-input" name="itens[0][quantidade]"
                                    value="1" min="1" required>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label d-md-none">Preço Unitário</label>
                                <input type="text" class="form-control preco-input" name="itens[0][preco_unit]"
                                    required>
                            </div>

                            <div class="col-md-1">
                                <label class="form-label d-md-none">Total</label>
                                <input type="text" class="form-control total-linha" readonly>
                            </div>

                            <div class="col-md-1 d-flex align-items-center">
                                <button type="button" class="btn btn-danger btn-sm remover-linha mt-md-2"><i
                                        class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="d-flex justify-content-between mt-3">
                    <button type="button" class="btn btn-secondary" id="adicionar-linha">
                        <i class="bi bi-plus-circle"></i> Adicionar Item
                    </button>
                    <div class="total-nota">
                        Total da Nota: R$ <span id="total-nota"></span>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Salvar Nota Fiscal
                    </button>
                </div>
            </form>
        </div>
        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script>

            document.addEventListener('DOMContentLoaded', function () {
                let contadorLinhas = 0;

                // Função para atualizar o nome do item no campo oculto
                function atualizarNomeItem(select) {
                    const option = select.options[select.selectedIndex];
                    const hiddenInput = select.closest('.row').querySelector('.item-nome-input');
                    if (option && option.dataset.nome) {
                        hiddenInput.value = option.dataset.nome;
                    } else {
                        hiddenInput.value = '';
                    }
                }

                const fornecedorSelect = document.querySelector('.fornecedor-select');
                const fornecedorNomeInput = document.querySelector('.fornecedor-nome-input');

                fornecedorSelect.addEventListener('change', function () {
                    const selectedOption = this.options[this.selectedIndex];
                    fornecedorNomeInput.value = selectedOption.dataset.nome || '';
                });


                // Inicializar os campos de nome do item
                document.querySelectorAll('.produto-select').forEach(select => {
                    select.addEventListener('change', function () {
                        atualizarNomeItem(this);
                    });
                });

                // Função para calcular o total de uma linha
                function calcularTotalLinha(linha) {
                    const quantidade = parseFloat(linha.querySelector('.quantidade-input').value) || 0;
                    const precoUnitario = parseFloat(linha.querySelector('.preco-input').value.replace(',', '.')) || 0;
                    const totalLinha = quantidade * precoUnitario;
                    linha.querySelector('.total-linha').value = totalLinha.toFixed(2);
                    return totalLinha;
                }

                // Função para calcular o total da nota
                function calcularTotalNota() {
                    let totalNota = 0;
                    document.querySelectorAll('.linha-produto').forEach(linha => {
                        totalNota += calcularTotalLinha(linha);
                    });
                    document.getElementById('total-nota').textContent = totalNota.toFixed(2).replace('.', ',');
                }

                // Atualiza os cálculos iniciais
                calcularTotalNota();

                // Atualiza os totais quando os valores são alterados
                document.getElementById('linhas-produtos').addEventListener('input', function (e) {
                    if (e.target.classList.contains('quantidade-input') || e.target.classList.contains('preco-input')) {
                        calcularTotalNota();
                    }
                });

                // Adicionar nova linha
                document.getElementById('adicionar-linha').addEventListener('click', function () {
                    contadorLinhas++;
                    const novoProduto = document.createElement('div');
                    novoProduto.className = 'linha-produto';

                    // Clone a estrutura das opções de produtos
                    const opcoesOriginal = document.querySelector('.produto-select').innerHTML;

                    novoProduto.innerHTML = `
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label d-md-none">Produto</label>
                            <select class="form-select produto-select" name="itens[${contadorLinhas}][id_produtos]" required>
                                ${opcoesOriginal}
                            </select>
                            <input type="hidden" name="itens[${contadorLinhas}][produto]" class="item-nome-input">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label d-md-none">Marca</label>
                            <input type="text" class="form-control marca-input" name="itens[${contadorLinhas}][marca]" placeholder="Marca" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label d-md-none">Modelo</label>
                            <input type="text" class="form-control modelo-input" name="itens[${contadorLinhas}][modelo]" placeholder="Modelo" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label d-md-none">Quantidade</label>
                            <input type="number" class="form-control quantidade-input" name="itens[${contadorLinhas}][quantidade]" value="1" min="1" required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label d-md-none">Preço Unitário</label>
                            <input type="text" class="form-control preco-input" name="itens[${contadorLinhas}][preco_unit]" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label d-md-none">Total</label>
                            <input type="text" class="form-control total-linha" readonly>
                        </div>
                        <div class="col-md-1 d-flex align-items-center">
                            <button type="button" class="btn btn-danger btn-sm remover-linha mt-md-2"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    `;


                    document.getElementById('linhas-produtos').appendChild(novoProduto);

                    // Adiciona o evento de change para o novo select
                    const novoSelect = novoProduto.querySelector('.produto-select');
                    novoSelect.addEventListener('change', function () {
                        atualizarNomeItem(this);
                    });

                    calcularTotalNota();

                    // Rola automaticamente para o final da lista quando adiciona um novo item
                    const containerItens = document.getElementById('linhas-produtos');
                    containerItens.scrollTop = containerItens.scrollHeight;
                });

                // Remover linha
                document.getElementById('linhas-produtos').addEventListener('click', function (e) {
                    if (e.target.classList.contains('remover-linha') || e.target.parentElement.classList.contains('remover-linha')) {
                        const botao = e.target.closest('.remover-linha');
                        const linha = botao.closest('.linha-produto');

                        // Verifica se existe mais de uma linha antes de remover
                        if (document.querySelectorAll('.linha-produto').length > 1) {
                            linha.remove();
                            calcularTotalNota();
                        } else {
                            alert('É necessário manter pelo menos um item na nota fiscal.');
                        }
                    }
                });

                // Máscara para preço unitário (substituir vírgula por ponto)
                document.getElementById('linhas-produtos').addEventListener('blur', function (e) {
                    if (e.target.classList.contains('preco-input')) {
                        let valor = e.target.value.replace(',', '.');
                        e.target.value = parseFloat(valor).toFixed(2);
                    }
                }, true);

                // Validação antes de enviar o formulário
                document.getElementById('form-entrada').addEventListener('submit', function (e) {
                    let valido = true;

                    // Verifica se pelo menos um item foi adicionado
                    const itens = document.querySelectorAll('.linha-produto');
                    if (itens.length === 0) {
                        alert('Adicione pelo menos um item à nota fiscal.');
                        e.preventDefault();
                        return false;
                    }

                    // Verifica se todos os itens têm produto selecionado, quantidade e preço
                    itens.forEach(item => {
                        const produto = item.querySelector('.produto-select').value;
                        const quantidade = item.querySelector('.quantidade-input').value;
                        const preco = item.querySelector(".preco-input").value;
                        const marca = item.querySelector(".marca-input").value;
                        const modelo = item.querySelector(".modelo-input").value;

                        if (!produto || !quantidade || quantidade <= 0 || !preco || !marca || !modelo || preco <= 0) {
                            valido = false;
                        }

                        // Atualiza o nome do item antes de enviar
                        if (produto) {
                            atualizarNomeItem(item.querySelector('.produto-select'));
                        }
                    });

                    if (!valido) {
                        alert('Preencha corretamente todos os campos de todos os itens.');
                        e.preventDefault();
                        return false;
                    }
                });

                // Inicializa os nomes dos itens ao carregar a página
                document.querySelectorAll('.produto-select').forEach(select => {
                    atualizarNomeItem(select);
                });

                document.getElementById('form-entrada').addEventListener('submit', function (e) {
                    let valido = true;

                    document.querySelectorAll('.preco-input').forEach(input => {
                        const valor = input.value.replace(',', '.');
                        if (isNaN(parseFloat(valor)) || parseFloat(valor) <= 0) {
                            valido = false;
                            input.classList.add('is-invalid');
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });

                    if (!valido) {
                        e.preventDefault();
                        alert("Verifique os valores de preço unitário. Devem ser maiores que zero.");
                    }
                });
            });

            const input = document.getElementById('nota_fiscal');
            const prefix = "NF-";

            // Impede que o usuário apague o prefixo
            input.addEventListener('keydown', function (e) {
                // Bloqueia backspace e delete se o cursor estiver antes ou dentro do prefixo
                if ((e.key === "Backspace" || e.key === "Delete") && input.selectionStart <= prefix.length) {
                    e.preventDefault();
                }

                // Impede mover o cursor para antes do prefixo
                if ((e.key === "ArrowLeft" || e.key === "Home") && input.selectionStart <= prefix.length) {
                    e.preventDefault();
                }
            });

            // Garante que o valor sempre comece com o prefixo
            input.addEventListener('input', function (e) {
                if (!input.value.startsWith(prefix)) {
                    const cursorPos = input.selectionStart;
                    input.value = prefix + input.value.slice(prefix.length);
                    input.setSelectionRange(cursorPos < prefix.length ? prefix.length : cursorPos, cursorPos < prefix.length ? prefix.length : cursorPos);
                }
            });

            // Impede colar antes do prefixo
            input.addEventListener('paste', function (e) {
                const pasteText = e.clipboardData.getData('text');
                const cursorPos = input.selectionStart;
                if (cursorPos < prefix.length) {
                    e.preventDefault();
                }
            });
        </script>
    </div>
</body>

</html>