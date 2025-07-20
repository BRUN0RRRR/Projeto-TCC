<?php
if (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest") {
    include "../../../../databases/conexao.php";
    try {
        $sql = "SELECT nota_fiscal_saida FROM saida ORDER BY nota_fiscal_saida DESC LIMIT 1"; // Order by ID is safer for sequential increment
        $stmt = $pdo->query($sql);
        $ultimoCodigoCompleto = $stmt->fetchColumn();

        if ($ultimoCodigoCompleto && preg_match("/NF-(\d{6})$/", $ultimoCodigoCompleto, $matches)) {
            $ultimoNumero = (int) $matches[1];
            $proximoNumero = $ultimoNumero + 1;
        } else {
            // If no previous record or format doesn't match, start from 1
            $proximoNumero = 1;
        }

        $proximoCodigoFormatado = "NF-" . str_pad($proximoNumero, 6, "0", STR_PAD_LEFT);

        header("Content-Type: application/json");
        echo json_encode(["proximoCodigo" => $proximoCodigoFormatado]);
        exit; // Importante para encerrar o script aqui
    } catch (PDOException $e) {
        header("Content-Type: application/json");
        echo json_encode(["error" => "Erro ao gerar código: " . $e->getMessage()]);
        exit;
    }
}

ob_start();
// Conexão com o banco de dados
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);

include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";


$moduloNecessario = "baixa_produtos";
verificarAcessoPagina($moduloNecessario);

try {
    // Busca todos os itens com estoque disponível e preço médio
    $sql = "SELECT 
    i.id_produtos,
    i.nome, 
    i.categoria, 
    SUM(e.quantidade) AS quantidade_disponivel,
    e.preco_unit AS preco_unit,
    e.fornecedor,
    e.marca,
    e.modelo
FROM itens i
JOIN estoque e ON i.id_produtos = e.id_produtos
WHERE i.status_item = 'Ativo'
GROUP BY i.id_produtos, i.nome, i.categoria, e.preco_unit, e.fornecedor,e.marca, e.modelo
HAVING SUM(e.quantidade) > 0
ORDER BY i.nome;"; // Fixed syntax error here

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $produtos_disponiveis = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao executar a consulta: " . $e->getMessage());
}




$custo_select = "SELECT * FROM custo where status = \"Ativo\"";
$stmt = $pdo->prepare($custo_select);
$stmt->execute();
$custo_select = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_end_flush();

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Saída de Produtos</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/pagina_saida.css">

</head>

<body>
    <div class="container mt-4">
        <h2 class="mb-4"> Saída de Produtos</h2>

        <!-- Formulário de Baixa com Múltiplos Itens -->
        <div class="card p-4 mb-4">
            <h5><i class="bi bi-file-earmark-minus"></i> Registrar Saída de Produtos</h5>
            <form method="post" action="config/config_saida.php" id="form-saida">

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="nota_fiscal" class="form-label">Número da Saida</label>
                        <input type="text" class="form-control" id="nota_fiscal" name="nota_fiscal" readonly required>

                    </div>
                    <div class="col-md-4">
                        <label for="motivo" class="form-label">Tipo da Saída*</label>
                        <select class="form-select" id="motivo" name="motivo" required>
                            <option value="">Tipo o motivo</option>
                            <option value="Uso Interno">Uso Interno</option>
                            <option value="Troca">Troca</option>
                            <option value="Perda">Perda</option>
                            <option value="Ajuste de Estoque">Ajuste de Estoque</option>
                            <option value="Outro">Outro</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="centroCusto" class="form-label">Centro de Custo*</label>
                        <select class="form-select fornecedor-select" name="id_custo" required>
                            <option value="">Selecione um Centro de Custo</option>
                            <?php foreach ($custo_select as $custo): ?>
                                <option value="<?= $custo["codigo"] ?>"
                                    data-nome="<?= htmlspecialchars($custo["codigo"]) ?>">
                                    <?= $custo["codigo"] . " - " . $custo["nome"] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="observacoes" class="form-label">Observações</label>
                        <input type="text" class="form-control" id="observacoes" name="observacoes">
                    </div>
                </div>

                <h5 class="mt-4 mb-3"><i class="bi bi-list-check"></i> Itens para Baixa</h5>

                <!-- Cabeçalho dos itens -->
                <div class="row fw-bold text-white p-2 rounded text-center text-md-start"
                    style="background-color: #1f2937;">

                    <div class="col-md-3">Produto</div>
                    <div class="col-md-2">Marca</div>
                    <div class="col-md-2">Modelo</div>
                    <div class="col-md-1">Disp.</div>
                    <div class="col-md-1">Saída</div>
                    <div class="col-md-2">Preço Unitário</div>
                    <div class="col-md-1">Ações</div>
                </div>

                <!-- Container com rolagem para os itens -->
                <div id="linhas-produtos" class="mb-4">
                    <div class="linha-produto card p-3 mb-2">
                        <div class="row g-3 align-items-center">
                            <!-- Produto -->
                            <div class="col-12 col-md-4 col-lg-3">
                                <label for="produto-0" class="form-label d-block d-md-none mb-1">Produto</label>
                                <select id="produto-0" class="form-select produto-select" name="itens[0][id_produtos]"
                                    required>
                                    <option value="">Selecione um produto</option>
                                    <?php foreach ($produtos_disponiveis as $produto): ?>
                                        <option value="<?= $produto["id_produtos"] ?>"
                                            data-quantidade="<?= $produto["quantidade_disponivel"] ?>"
                                            data-fornecedor="<?= $produto["fornecedor"] ?>"
                                            data-preco="<?= number_format($produto["preco_unit"], 2, ".", "") ?>"
                                            data-nome="<?= htmlspecialchars($produto["nome"]) ?>"
                                            data-marca="<?= htmlspecialchars($produto["marca"]) ?>"
                                            data-modelo="<?= htmlspecialchars($produto["modelo"] ?? '') ?>">
                                            <?= htmlspecialchars($produto["nome"]) ?>
                                            (<?= $produto["quantidade_disponivel"] ?> disp.) -
                                            <?= $produto["marca"] . " - " . $produto["modelo"] . " - " . $produto["preco_unit"] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="itens[0][produto]" class="item-nome-input">
                                <input type="hidden" name="itens[0][fornecedor]" class="item-fornecedor-input">
                            </div>

                            <!-- Marca -->
                            <div class="col-6 col-md-2 col-lg-2">
                                <label class="form-label d-block d-md-none mb-1">Marca</label>
                                <input type="text" class="form-control marca-input bg-light" name="itens[0][marca]"
                                    readonly>
                            </div>

                            <!-- Modelo -->
                            <div class="col-6 col-md-2 col-lg-2">
                                <label class="form-label d-block d-md-none mb-1">Modelo</label>
                                <input type="text" class="form-control modelo-input bg-light" name="itens[0][modelo]"
                                    readonly>
                            </div>

                            <!-- Quantidade Disponível -->
                            <div class="col-6 col-md-2 col-lg-1">
                                <label class="form-label d-block d-md-none mb-1">Disponível</label>
                                <input type="text" class="form-control quantidade-disponivel-input bg-light" readonly>
                            </div>

                            <!-- Quantidade Saída -->
                            <div class="col-6 col-md-1 col-lg-1">
                                <label for="quantidade-0" class="form-label d-block d-md-none mb-1">Quantidade</label>
                                <input id="quantidade-0" type="number" class="form-control quantidade-input"
                                    name="itens[0][quantidade]" value="1" min="1" required>
                            </div>

                            <!-- Preço Unitário -->
                            <div class="col-6 col-md-3 col-lg-2">
                                <label for="preco-0" class="form-label d-block d-md-none mb-1">Preço Unitário</label>
                                <div class="input-group">
                                    <input id="preco-0" type="text" class="form-control preco-input"
                                        name="itens[0][preco_unit]" required>
                                </div>
                            </div>

                            <!-- Botão Remover -->
                            <div class="col-12 col-md-1 d-flex align-items-center justify-content-center">
                                <button type="button" class="btn btn-danger btn-sm remover-linha">
                                    <i class="bi bi-trash"></i>
                                </button>
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


                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Registrar Baixa
                    </button>
                </div>
            </form>
        </div>

        <!-- Bootstrap JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        <script>

            // Função para calcular o total da nota
            function calcularTotalNota() {
                let total = 0;

                document.querySelectorAll(".linha-produto").forEach(linha => {
                    const precoInput = linha.querySelector(".preco-input");
                    const quantidadeInput = linha.querySelector(".quantidade-input");

                    const preco = parseFloat(precoInput.value.replace(",", ".") || 0);
                    const quantidade = parseFloat(quantidadeInput.value || 0);

                    if (!isNaN(preco) && !isNaN(quantidade)) {
                        total += preco * quantidade;
                    }
                });

                document.getElementById("total-nota").textContent = total.toFixed(2).replace(".", ",");
            }

            calcularTotalNota();
            document.addEventListener('input', function (e) {
                if (e.target.classList.contains('preco-input') || e.target.classList.contains('quantidade-input')) {
                    calcularTotalNota();
                }
            });

            document.addEventListener("DOMContentLoaded", function () {
                carregarProximoCodigo();
                async function carregarProximoCodigo() {
                    try {
                        const response = await fetch(window.location.href, {
                            headers: {
                                "X-Requested-With": "XMLHttpRequest"
                            }
                        });

                        if (!response.ok) {
                            throw new Error(`Erro na requisição: ${response.status}`);
                        }

                        const data = await response.json();

                        if (data.error) {
                            console.error(data.error);
                            return;
                        }

                        document.getElementById("nota_fiscal").value = data.proximoCodigo;

                    } catch (error) {
                        console.error("Erro ao carregar código:", error);
                    }
                }


                let contadorLinhas = 0;

                // Função para formatar valor monetário
                function formatarMoeda(valor) {
                    return parseFloat(valor).toFixed(2).replace(".", ",");
                }

                // Função para atualizar os campos quando um produto é selecionado
                function atualizarCamposProduto(select) {
                    const option = select.options[select.selectedIndex];
                    const linha = select.closest(".linha-produto");

                    if (option && option.dataset.quantidade) {
                        // Atualiza campos ocultos
                        linha.querySelector(".item-nome-input").value = option.dataset.nome || "";
                        linha.querySelector(".item-fornecedor-input").value = option.dataset.fornecedor || "";

                        // Atualiza campos visíveis
                        linha.querySelector(".marca-input").value = option.dataset.marca || "";
                        linha.querySelector(".modelo-input").value = option.dataset.modelo || "";

                        linha.querySelector(".quantidade-disponivel-input").value = option.dataset.quantidade;

                        // Atualiza o preço
                        const precoInput = linha.querySelector(".preco-input");
                        if (precoInput && option.dataset.preco) {
                            precoInput.value = formatarMoeda(option.dataset.preco);
                        }

                        // Define o valor máximo para a quantidade de saída
                        const quantidadeInput = linha.querySelector(".quantidade-input");
                        quantidadeInput.max = option.dataset.quantidade;
                        quantidadeInput.value = Math.min(parseInt(quantidadeInput.value) || 1, parseInt(option.dataset.quantidade));

                    } else {
                        // Limpa campos se nenhum produto selecionado
                        linha.querySelector(".item-nome-input").value = "";
                        linha.querySelector(".item-fornecedor-input").value = "";
                        linha.querySelector(".marca-input").value = "";
                        linha.querySelector(".modelo-input").value = "";
                        linha.querySelector(".quantidade-disponivel-input").value = "";
                        linha.querySelector(".preco-input").value = "";
                        linha.querySelector(".quantidade-input").removeAttribute("max");
                    }
                }

                // Inicializar os campos dos produtos existentes (se houver)
                document.querySelectorAll(".produto-select").forEach(select => {
                    select.addEventListener("change", function () {
                        atualizarCamposProduto(this);
                    });

                    // Atualiza campos se já houver um produto selecionado na carga inicial da página
                    if (select.value) {
                        atualizarCamposProduto(select);
                    }
                });

                // Adicionar nova linha
                document.getElementById("adicionar-linha").addEventListener("click", function () {
                    contadorLinhas++;
                    const novoProduto = document.createElement("div");
                    novoProduto.className = "linha-produto card p-3 mb-3"; // Adiciona classes do card para consistência

                    // Clone a estrutura das opções de produtos do primeiro select existente
                    const opcoesOriginal = document.querySelector(".produto-select").innerHTML;

                    novoProduto.innerHTML = `
                        <div class="row g-3 align-items-center">
                            <!-- Seleção de Produto -->
                            <div class="col-12 col-md-6 col-lg-3">
                                <label for="produto-${contadorLinhas}" class="form-label d-block d-md-none mb-1">Produto</label>
                                <select id="produto-${contadorLinhas}" class="form-select produto-select" name="itens[${contadorLinhas}][id_produtos]" required>
                                    ${opcoesOriginal}
                                </select>
                                <input type="hidden" name="itens[${contadorLinhas}][produto]" class="item-nome-input">
                                <input type="hidden" name="itens[${contadorLinhas}][fornecedor]" class="item-fornecedor-input">
                                <input type="hidden" name="itens[${contadorLinhas}][modelo]" class="item-fornecedor-input">
                            </div>

                            <!-- Marca -->
                            <div class="col-6 col-md-3 col-lg-2">
                                <label class="form-label d-block d-md-none mb-1">Marca</label>
                                <input type="text" class="form-control marca-input bg-light" name="itens[${contadorLinhas}][marca]" readonly>
                            </div>

                            <!-- Modelo -->
                            <div class="col-6 col-md-2 col-lg-2">
                                <label class="form-label d-block d-md-none mb-1">Modelo</label>
                                <input type="text" class="form-control modelo-input bg-light" name="itens[0][modelo]"
                                    readonly>
                            </div>

                            <!-- Quantidade Disponível -->
                           <div class="col-6 col-md-2 col-lg-1">
                                <label class="form-label d-block d-md-none mb-1">Disponível</label>
                                <input type="text" class="form-control quantidade-disponivel-input bg-light" readonly>
                            </div>

                            <!-- Quantidade Saída -->
                            <div class="col-6 col-md-2 col-lg-1">
                                <label for="quantidade-${contadorLinhas}" class="form-label d-block d-md-none mb-1">Quantidade</label>
                                <input id="quantidade-${contadorLinhas}" type="number" class="form-control quantidade-input"
                                    name="itens[${contadorLinhas}][quantidade]" value="1" min="1" required>
                            </div>

                            <!-- Preço Unitário -->
                            <div class="col-6 col-md-3 col-lg-2">
                                <label for="preco-${contadorLinhas}" class="form-label d-block d-md-none mb-1">Preço Unitário</label>
                                <div class="input-group">
                                    <input id="preco-${contadorLinhas}" type="text" class="form-control preco-input"
                                        name="itens[${contadorLinhas}][preco_unit]" required>
                                </div>
                            </div>

                            <!-- Botão Remover -->
                             <div class="col-12 col-md-1 d-flex align-items-center justify-content-center">
                                <button type="button" class="btn btn-danger btn-sm remover-linha"><i
                                        class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    `;

                    document.getElementById("linhas-produtos").appendChild(novoProduto);

                    // Adicionar event listener para o novo select de produto
                    const novoProdutoSelect = novoProduto.querySelector(".produto-select");
                    novoProdutoSelect.addEventListener("change", function () {
                        atualizarCamposProduto(this);
                    });

                    // Adicionar event listener para o botão de remover linha
                    const removerLinhaBtn = novoProduto.querySelector(".remover-linha");
                    removerLinhaBtn.addEventListener("click", function () {
                        this.closest(".linha-produto").remove();
                    });


                });

                $(document).on('change', '.produto-select', function () {
                    const selectedOption = $(this).find('option:selected');
                    const marca = selectedOption.data('marca') || '';
                    // procura o input marca-input no mesmo container
                    $(this).closest('.linha-produto').find('.marca-input').val(marca);
                });

                $(document).on('change', '.produto-select', function () {
                    const selectedOption = $(this).find('option:selected');
                    const modelo = selectedOption.data('modelo') || '';
                    // procura o input marca-input no mesmo container
                    $(this).closest('.linha-produto').find('.modelo-input').val(modelo);
                });


                // Adicionar event listener para os botões de remover linha existentes
                document.querySelectorAll(".remover-linha").forEach(button => {
                    button.addEventListener("click", function () {
                        this.closest(".linha-produto").remove();
                    });
                });
            });


        </script>
    </div>
</body>
</html>