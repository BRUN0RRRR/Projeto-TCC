<?php

// Configurações de erro melhoradas
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Inclui arquivos necessários
include "../databases/conexao.php";
include "../includes/sidebar.php";

// FUNÇÕES DE BUSCA DE DADOS PARA GRÁFICOS - DEFINIÇÃO CORRIGIDA
function buscarDadosEntradasMensais($pdo)
{
    $query = "SELECT 
            DATE_FORMAT(data_cadastro,'%Y-%m') as mes_ano,
            SUM(quantidade * preco_unit) as total_mensal
            FROM entradas
            WHERE data_cadastro >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
            AND data_cadastro < DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY)
            GROUP BY mes_ano
            ORDER BY mes_ano ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarDadosSaidasMensais($pdo)
{
    $query = "SELECT 
                DATE_FORMAT(data_saida, '%Y-%m') as mes_ano,
                SUM(quantidade * preco_unit) as faturamento_mensal
              FROM saida
              WHERE data_saida >= DATE_FORMAT(DATE_SUB(CURDATE(), INTERVAL 5 MONTH), '%Y-%m-01')
              AND data_saida < DATE_ADD(LAST_DAY(CURDATE()), INTERVAL 1 DAY)
              GROUP BY mes_ano
              ORDER BY mes_ano ASC";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function buscarDadosEstoquePorCategoria($pdo)
{
    // Versão revisada da consulta, assumindo que a tabela 'item' 
    // possui categoria e está ligada à tabela 'estoque'
    $query = "SELECT 
                COALESCE(i.categoria, 'Sem Categoria') as categoria,
                SUM(e.quantidade * i.preco_unit) as valor_categoria
              FROM estoque e
              JOIN itens i ON e.id_produtos = i.id_produtos
              WHERE e.quantidade > 0
              GROUP BY i.categoria
              ORDER BY valor_categoria DESC
              LIMIT 5";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Funções para buscar dados direto do banco
function buscarDadosEntradas($pdo)
{
    $query = "SELECT 
        COALESCE(SUM(quantidade * preco_unit), 0) as total,
        COALESCE(SUM(quantidade), 0) as quantidade,
        COUNT(DISTINCT id_produtos) as produtos_diferentes
        FROM entradas 
        WHERE MONTH(data_cadastro) = MONTH(CURRENT_DATE())
        AND YEAR(data_cadastro) = YEAR(CURRENT_DATE())";

    $stmt = $pdo->query($query);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buscarDadosSaidas($pdo)
{
    $query = "SELECT 
        SUM(quantidade * preco_unit) as faturamento
        FROM saida 
        WHERE MONTH(data_saida) = MONTH(CURRENT_DATE())
        AND YEAR(data_saida) = YEAR(CURRENT_DATE())";

    $stmt = $pdo->query($query);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function buscarDadosEstoque($pdo)
{
    $query = "SELECT 
        SUM(quantidade * preco_unit) as valor_total,
        SUM(quantidade) as itens_total,
        COUNT(*) as produtos_diferentes,
        SUM(CASE WHEN quantidade < 10 THEN 1 ELSE 0 END) as produtos_baixo_estoque
        FROM estoque
        WHERE quantidade > 0";

    $stmt = $pdo->query($query);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Funções auxiliares de formatação
function formatarMoeda($valor, $prefixo = 'R$ ')
{
    if ($valor === null || $valor === '')
        return $prefixo . '0,00';
    return $prefixo . number_format((float) $valor, 2, ',', '.');
}

function formatarNumero($valor, $decimais = 0)
{
    if ($valor === null || $valor === '')
        return '0';
    return number_format((float) $valor, $decimais, ',', '.');
}

// Busca os dados
$entradas = buscarDadosEntradas($pdo) ?: ['total' => 0, 'quantidade' => 0, 'produtos_diferentes' => 0];
$saidas = buscarDadosSaidas($pdo) ?: ['faturamento' => 0];
$estoque = buscarDadosEstoque($pdo) ?: ['valor_total' => 0, 'itens_total' => 0, 'produtos_diferentes' => 0, 'produtos_baixo_estoque' => 0];

// Fetch data for charts
$entradasMensaisData = buscarDadosEntradasMensais($pdo);
$saidasMensaisData = buscarDadosSaidasMensais($pdo);
$estoquePorCategoriaData = buscarDadosEstoquePorCategoria($pdo);

// Prepare data for JS Charts
$mesesAbreviados = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
$labelsComunsChart = [];
$mapaMesesValoresEntradas = [];
$mapaMesesValoresSaidas = [];

// Initialize maps for the last 6 months (current + 5 previous)
for ($i = 5; $i >= 0; $i--) {
    $dataRef = new DateTime("first day of -$i month");
    $chaveMesAno = $dataRef->format('Y-m');
    $labelsComunsChart[] = $mesesAbreviados[(int) $dataRef->format('n') - 1] . '/' . $dataRef->format('y');
    $mapaMesesValoresEntradas[$chaveMesAno] = 0.0; // Initialize with 0.0 float
    $mapaMesesValoresSaidas[$chaveMesAno] = 0.0;   // Initialize with 0.0 float
}

foreach ($entradasMensaisData as $item) {
    if (array_key_exists($item['mes_ano'], $mapaMesesValoresEntradas)) {
        $mapaMesesValoresEntradas[$item['mes_ano']] = (float) $item['total_mensal'];
    }
}
$dataEntradasChart = array_values($mapaMesesValoresEntradas);

foreach ($saidasMensaisData as $item) {
    if (array_key_exists($item['mes_ano'], $mapaMesesValoresSaidas)) {
        $mapaMesesValoresSaidas[$item['mes_ano']] = (float) $item['faturamento_mensal'];
    }
}
$dataSaidasChart = array_values($mapaMesesValoresSaidas);

$labelsEstoqueChart = [];
$dataEstoqueChart = [];
if (!empty($estoquePorCategoriaData)) {
    foreach ($estoquePorCategoriaData as $item) {
        $labelsEstoqueChart[] = $item['categoria'];
        $dataEstoqueChart[] = (float) $item['valor_categoria'];
    }
} else {
    $labelsEstoqueChart = ['Sem dados de categoria'];
    $dataEstoqueChart = [0.0]; // float
}

// Dados para gráficos
$meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
$mesAtual = date('n') - 1;

if (!isset($permissoesUsuario['perfil']) || $permissoesUsuario['perfil'] !== true) {
    // Registra tentativa de acesso não autorizado (opcional)
    error_log("Tentativa de acesso não autorizado ao perfil pelo usuário ID: " . $_SESSION['codi_usuario']);

    // Redireciona ou mostra mensagem de erro
    $_SESSION['mensagem'] = "Você não tem permissão para acessar esta página.";
    $_SESSION['tipo_mensagem'] = "erro";
    //header("Location: ../../../../index.php");
}

// ID do usuário (normalmente viria da sessão após login)
$cod_usuario = $_SESSION['codi_usuario'];

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Dashboard</title>
    <!-- Bootstrap CSS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="assets/css/pagina_home.css">
    <!-- Custom CSS -->

<body>
    <div class="dashboard-container">
        <div class="dashboard-header d-flex justify-content-between align-items-center">
            <h1 class="dashboard-title">InfoStock Dashboard</h1>
            <div class="last-update">
                <i class="fas fa-sync-alt"></i>Atualizado: <span id="data-atual"></span>
            </div>

        </div>

        <div class="row">
            <!-- Card de Entradas -->
            <div class="col-card">
                <div class="panel entradas">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-box-open panel-icon"></i> Entradas</h2>
                        <span class="badge bg-success">Mês Atual</span>
                    </div>
                    <div class="panel-content">
                        <div class="dashboard-metrics">
                            <div class="metric-card">
                                <div class="metric-value"><?= formatarMoeda($entradas['total']) ?></div>
                                <div class="metric-label">Total Investido</div>
                            </div>
                            <div class="metric-card">
                                <div class="metric-value"><?= formatarNumero($entradas['quantidade']) ?></div>
                                <div class="metric-label">Itens Entrados</div>
                            </div>
                            <div class="metric-card">
                                <div class="metric-value"><?= formatarNumero($entradas['produtos_diferentes']) ?></div>
                                <div class="metric-label">Produtos Diferentes</div>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="entradaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card de Saídas -->
            <div class="col-card">
                <div class="panel saidas">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-truck panel-icon"></i> Saídas</h2>
                        <span class="badge bg-success">Mês Atual</span>
                    </div>
                    <div class="panel-content">
                        <div class="dashboard-metrics">
                            <div class="metric-card">
                                <div class="metric-value"><?= formatarMoeda($saidas['faturamento']) ?></div>
                                <div class="metric-label">Faturamento</div>
                            </div>
                            <div class="metric-card">
                                <div class="metric-value">
                                    <?= formatarNumero(($saidas['faturamento'] - $entradas['total'])) ?>
                                </div>
                                <div class="metric-label">Lucro Estimado</div>
                            </div>
                            <div class="metric-card">
                                <div class="metric-value">
                                    <?= $entradas['total'] > 0 ? formatarNumero(($saidas['faturamento'] / $entradas['total']) * 100, 1) . '%' : '0%' ?>
                                </div>
                                <div class="metric-label">ROI</div>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="saidaChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card de Estoque -->
            <div class="col-card">
                <div class="panel estoque">
                    <div class="panel-header">
                        <h2 class="panel-title"><i class="fas fa-warehouse panel-icon"></i> Estoque</h2>
                        <span class="badge <?= $estoque['produtos_baixo_estoque'] > 0 ? 'bg-warning' : 'bg-success' ?>">
                            <?= $estoque['produtos_baixo_estoque'] ?> itens críticos
                        </span>
                    </div>
                    <div class="panel-content">
                        <div class="dashboard-metrics">
                            <div class="metric-card">
                                <div class="metric-value"><?= formatarMoeda($estoque['valor_total']) ?></div>
                                <div class="metric-label">Valor Total</div>
                            </div>
                            <div class="metric-card">
                                <div class="metric-value"><?= formatarNumero($estoque['itens_total']) ?></div>
                                <div class="metric-label">Itens em Estoque</div>
                            </div>
                            <div class="metric-card">
                                <div class="metric-value"><?= formatarNumero($estoque['produtos_diferentes']) ?></div>
                                <div class="metric-label">Produtos Diferentes</div>
                            </div>
                        </div>
                        <div class="chart-container">
                            <canvas id="estoqueChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Configuração comum para todos os gráficos
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 20,
                            font: {
                                family: "'Poppins', sans-serif",
                                size: 11
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(255, 255, 255, 0.9)',
                        titleColor: '#212529',
                        bodyColor: '#212529',
                        borderColor: '#dee2e6',
                        borderWidth: 1,
                        cornerRadius: 8,
                        padding: 12,
                        boxPadding: 6,
                        usePointStyle: true,
                        titleFont: {
                            family: "'Poppins', sans-serif",
                            size: 13,
                            weight: 'bold'
                        },
                        bodyFont: {
                            family: "'Poppins', sans-serif",
                            size: 12
                        },
                        callbacks: {
                            label: function (context) {
                                let label = context.dataset.label || '';
                                if (label) label += ': ';
                                if (context.parsed.y !== undefined) {
                                    label += 'R$ ' + context.parsed.y.toFixed(2).replace('.', ',');
                                } else {
                                    label += 'R$ ' + context.raw.toFixed(2).replace('.', ',');
                                }
                                return label;
                            }
                        }
                    }
                }
            };

            // Gráfico de Entradas (linha)
            new Chart(document.getElementById('entradaChart'), {
                type: 'line',
                data: {
                    labels: <?= json_encode($labelsComunsChart) ?>,
                    datasets: [{
                        label: 'Investimento em Compras (Últimos 6 Meses)',
                        data: <?= json_encode($dataEntradasChart) ?>,
                        borderColor: '#4361ee',
                        backgroundColor: 'rgba(67, 97, 238, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 3,
                        pointBackgroundColor: '#ffffff',
                        pointBorderColor: '#4361ee',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif",
                                    size: 11
                                },
                                callback: function (value) {
                                    return 'R$ ' + value / 1000 + 'k';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif",
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de Saídas (barras)
            new Chart(document.getElementById('saidaChart'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode($labelsComunsChart) ?>,
                    datasets: [{
                        label: 'Faturamento Mensal (Últimos 6 Meses)',
                        data: <?= json_encode($dataSaidasChart) ?>,
                        backgroundColor: 'rgba(76, 201, 240, 0.7)',
                        borderRadius: 6,
                        borderWidth: 0,
                        hoverBackgroundColor: 'rgba(76, 201, 240, 0.9)'
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif",
                                    size: 11
                                },
                                callback: function (value) {
                                    return 'R$ ' + value / 1000 + 'k';
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: "'Poppins', sans-serif",
                                    size: 11
                                }
                            }
                        }
                    }
                }
            });

            // Gráfico de Estoque (rosquinha)
            new Chart(document.getElementById('estoqueChart'), {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($labelsEstoqueChart) ?>,
                    datasets: [{
                        label: 'Valor em Estoque por Categoria (Top 5)',
                        data: <?= json_encode($dataEstoqueChart) ?>,
                         backgroundColor: [
                            '#ff6b6b', // vermelho vibrante
                            '#feca57', // amarelo alaranjado
                            '#54a0ff', // azul claro
                            '#5f27cd', // roxo vibrante
                            '#1dd1a1', // verde água
                            '#ff9ff3', // rosa claro
                            '#00d2d3', // verde azulado
                            '#ff8c00', // laranja vibrante
                            '#ff4757', // vermelho intenso
                            '#2ed573'  // verde limão
                        ],

                        borderWidth: 2,
                        borderColor: '#ffffff',
                        hoverOffset: 6
                    }]
                },
                options: {
                    ...chartOptions,
                    cutout: '65%',
                    plugins: {
                        ...chartOptions.plugins,
                        legend: {
                            ...chartOptions.plugins.legend,
                            position: 'right',
                            align: 'center'
                        }
                    }
                }
            });

            // Atualização automática a cada 5 minutos
            setInterval(() => {
                location.reload();
            }, 300000);
        });


        const dataAtual = new Date();
        const dia = String(dataAtual.getDate()).padStart(2, '0');
        const mes = String(dataAtual.getMonth() + 1).padStart(2, '0'); // Janeiro é 0
        const ano = dataAtual.getFullYear();

        document.getElementById('data-atual').textContent = ` ${dia}/${mes}/${ano}`;

    </script>
</body>

</html>