<?php
// É importante colocar este código no início absoluto do arquivo, antes de qualquer saída
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    include "../../../../databases/conexao.php";
    try {
        $sql = "SELECT id_fornecedor FROM fornecedores ORDER BY id_fornecedor DESC LIMIT 1;";
        $stmt = $pdo->query($sql);
        $ultimoCodigo = $stmt->fetchColumn();

        $proximoCodigo = $ultimoCodigo
            ? str_pad((int) $ultimoCodigo + 1, 6, '0', STR_PAD_LEFT)
            : '000001';

        header('Content-Type: application/json');
        echo json_encode(['proximoCodigo' => $proximoCodigo]);
        exit; // Importante para encerrar o script aqui
    } catch (PDOException $e) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Erro ao gerar código: ' . $e->getMessage()]);
        exit;
    }
}

// Continua com o restante do código apenas para requisições normais
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";

$moduloNecessario = "fornecedor";
verificarAcessoPagina($moduloNecessario);

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Cadastro de Fornecedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/pagina_cadFornecedor.css">
</head>

<body>
    <!-- Restante do seu HTML permanece igual -->
    <div class="main-container">
        <div class="page-header">
            <h1 class="page-title">
                <span>Cadastro de Fornecedor</span>
            </h1>
        </div>

        <div class="progress">
            <div class="progress-bar o" role="progressbar" style="width: 33%; color: #2c3e50;" aria-valuenow="33"
                aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        <div class="step-indicator">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-connector"></div>
                <div class="step-title">Informações Básicas</div>
            </div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-connector"></div>
                <div class="step-title">Contato</div>
            </div>
            <div class="step">
                <div class="step-number">3</div>
                <div class="step-connector"></div>
                <div class="step-title">Endereço</div>
            </div>
        </div>

        <form id="formFornecedor" action="config/config_fornecedor.php" method="POST">
            <div class="tab-content">
                <!-- PASSO 1: Informações Básicas -->
                <div class="tab-pane fade show active" id="step1">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-building fa-fw"></i> Informações do Fornecedor
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="form-label required-field">Código</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" name="codigoFornecedor"
                                            id="codigoFornecedor" required placeholder="Código do fornecedor" readonly>

                                        <i class="fa-solid fa-hashtag"></i>
                                    </div>
                                    <div class="form-text">Código único de identificação do fornecedor.</div>
                                </div>
                                <div class="col-md-9 mb-3">
                                    <label class="form-label required-field">Razão Social</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" name="razao_social" required
                                            placeholder="Informe a razão social">
                                        <i class="fa-solid fa-building-circle-check"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field">Nome Fantasia</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" name="nome_fantasia" required
                                            placeholder="Informe o nome fantasia">
                                        <i class="fa-solid fa-tag"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field">CNPJ</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" name="cnpj" required
                                            placeholder="00.000.000/0000-00" id="cnpj">
                                        <i class="fa-solid fa-id-card"></i>
                                    </div>
                                    <div class="form-text">Digite apenas os números. A formatação será aplicada
                                        automaticamente.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Inscrição Estadual</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" name="inscricao_estadual"
                                            placeholder="Inscrição Estadual (opcional)">
                                        <i class="fa-solid fa-file-invoice"></i>
                                    </div>
                                    <div class="form-text">Para isentos, digite "ISENTO".</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PASSO 2: Contato -->
                <div class="tab-pane fade" id="step2">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-address-book fa-fw"></i> Contato
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field">Telefone Principal</label>
                                    <div class="input-with-icon">
                                        <input type="tel" class="form-control" name="telefone" required
                                            placeholder="(00) 0000-0000" id="telefone">
                                        <i class="fa-solid fa-phone"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Telefone Secundário</label>
                                    <div class="input-with-icon">
                                        <input type="tel" class="form-control" name="telefone_secundario"
                                            placeholder="(00) 0000-0000" id="telefone_secundario">
                                        <i class="fa-solid fa-mobile-screen"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email</label>
                                    <div class="input-with-icon">
                                        <input type="email" class="form-control" name="email"
                                            placeholder="email@exemplo.com">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Site</label>
                                    <div class="input-with-icon">
                                        <input type="url" class="form-control" name="site"
                                            placeholder="https://www.exemplo.com.br">
                                        <i class="fa-solid fa-globe"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nome do Contato</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" name="nome_contato"
                                            placeholder="Nome do responsável">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Cargo</label>
                                    <div class="input-with-icon">
                                        <input type="text" class="form-control" name="cargo_contato"
                                            placeholder="Cargo do responsável">
                                        <i class="fa-solid fa-briefcase"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PASSO 3: Endereço -->
                <div class="tab-pane fade" id="step3">
                    <div class="card">
                        <div class="card-header">
                            <i class="fa-solid fa-location-dot fa-fw"></i> Endereço
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required-field">CEP</label>
                                    <div class="input-group">
                                        <div class="input-with-icon w-100">
                                            <input type="text" class="form-control" name="cep" id="cep" required
                                                placeholder="00000-000" onblur="pesquisacep(this.value);">
                                            <i class="fa-solid fa-map-pin"></i>
                                        </div>
                                        <span class="loading-cep" id="loading-cep">
                                            <i class="fa-solid fa-spinner fa-spin"></i> Consultando...
                                        </span>
                                    </div>
                                    <div class="form-text">Digite o CEP para preenchimento automático dos campos de
                                        endereço.</div>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label required-field">Rua</label>
                                    <input type="text" class="form-control" id="rua" name="rua" required
                                        placeholder="Nome da rua">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label required-field">Número</label>
                                    <input type="text" class="form-control" name="numero" required placeholder="Nº">
                                </div>
                                <div class="col-md-5 mb-3">
                                    <label class="form-label">Complemento</label>
                                    <input type="text" class="form-control" name="complemento"
                                        placeholder="Sala, Andar, etc.">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label required-field">Bairro</label>
                                    <input type="text" class="form-control" id="bairro" name="bairro" required
                                        placeholder="Nome do bairro">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field">Cidade</label>
                                    <input type="text" class="form-control" id="cidade" name="cidade" required
                                        placeholder="Nome da cidade">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required-field">Estado</label>
                                    <select class="form-select" id="uf" name="estado" required>
                                        <option value="">Selecione o estado</option>
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
                                <div class="col-md-6 mb-3 d-none">
                                    <label class="form-label">IBGE</label>
                                    <input type="text" class="form-control" id="ibge" name="ibge" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="buttons">
                <button type="button" id="prevBtn" class="btn btn-secondary" style="display:none;">
                    <i class="fa-solid fa-arrow-left"></i> Anterior
                </button>
                <button type="button" id="nextBtn" class="btn btn-primary">
                    Próximo <i class="fa-solid fa-arrow-right"></i>
                </button>
                <button type="submit" id="submitBtn" class="btn btn-success" style="display:none;">
                    <i class="fa-solid fa-floppy-disk"></i> Salvar Fornecedor
                </button>
                <button type="reset" class="btn btn-outline-secondary">
                    <i class="fa-solid fa-broom"></i> Limpar
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            carregarProximoCodigo();
        });

        async function carregarProximoCodigo() {
            try {
                const response = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });

                if (!response.ok) {
                    throw new Error(`Erro na requisição: ${response.status}`);
                }

                const data = await response.json();

                if (data.error) {
                    console.error(data.error);
                    return;
                }

                document.getElementById('codigoFornecedor').value = data.proximoCodigo;
            } catch (error) {
                console.error('Erro ao carregar código:', error);
            }
        }

        // Funções para consulta de CEP via ViaCEP
        function limpa_formulario_cep() {
            document.getElementById('rua').value = "";
            document.getElementById('bairro').value = "";
            document.getElementById('cidade').value = "";
            document.getElementById('uf').value = "";
            document.getElementById('ibge').value = "";
        }

        function meu_callback(conteudo) {
            document.getElementById('loading-cep').style.display = 'none';

            if (!("erro" in conteudo)) {
                document.getElementById('rua').value = conteudo.logradouro;
                document.getElementById('bairro').value = conteudo.bairro;
                document.getElementById('cidade').value = conteudo.localidade;
                document.getElementById('uf').value = conteudo.uf;
                document.getElementById('ibge').value = conteudo.ibge;
            } else {
                limpa_formulario_cep();
                Swal.fire({
                    icon: "error",
                    title: "CEP inválido",
                    text: "CEP não encontrado!",
                    confirmButtonText: 'OK'
                });
            }
        }

        function pesquisacep(valor) {
            var cep = valor.replace(/\D/g, '');

            if (cep !== "") {
                var validacep = /^[0-9]{8}$/;

                if (validacep.test(cep)) {
                    document.getElementById('loading-cep').style.display = 'inline';

                    document.getElementById('rua').value = "...";
                    document.getElementById('bairro').value = "...";
                    document.getElementById('cidade').value = "...";
                    document.getElementById('uf').value = "...";
                    document.getElementById('ibge').value = "...";

                    var script = document.createElement('script');
                    script.src = 'https://viacep.com.br/ws/' + cep + '/json/?callback=meu_callback';
                    document.body.appendChild(script);
                } else {
                    limpa_formulario_cep();
                    Swal.fire({
                        icon: "error",
                        title: "CEP inválido",
                        text: "CEP não encontrado!",
                        confirmButtonText: 'OK'
                    });
                }
            } else {
                limpa_formulario_cep();
            }
        }

        $(document).ready(function () {
            // Máscara para campos
            $('#cnpj').mask('00.000.000/0000-00');
            $('#telefone, #telefone_secundario').mask('(00) 0000-0000');
            $('#cep').mask('00000-000');

            // Navegação entre etapas
            let currentStep = 1;
            const totalSteps = 3;

            function showStep(step) {
                $('.tab-pane').removeClass('show active');
                $(`#step${step}`).addClass('show active');

                // Atualizar indicadores de etapa
                $('.step').removeClass('active');
                $(`.step:nth-child(-n+${step})`).addClass('active');

                // Atualizar barra de progresso
                const progress = (step / totalSteps) * 100;
                $('.progress-bar').css('width', `${progress}%`).attr('aria-valuenow', progress);

                // Mostrar/ocultar botões
                if (step === 1) {
                    $('#prevBtn').hide();
                } else {
                    $('#prevBtn').show();
                }

                if (step === totalSteps) {
                    $('#nextBtn').hide();
                    $('#submitBtn').show();
                } else {
                    $('#nextBtn').show();
                    $('#submitBtn').hide();
                }
            }

            $('#nextBtn').click(function () {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            });

            $('#prevBtn').click(function () {
                if (currentStep > 1) {
                    currentStep--;
                    showStep(currentStep);
                }
            });

            // Validação do formulário antes do envio
            $('#formFornecedor').on('submit', function (e) {
                e.preventDefault();

                // Verificar campos obrigatórios
                let formValido = true;
                $(this).find('[required]').each(function () {
                    if (!$(this).val()) {
                        formValido = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!formValido) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campos obrigatórios',
                        text: 'Por favor, preencha todos os campos obrigatórios.',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }

                // Se tudo estiver ok, enviar o formulário
                Swal.fire({
                    title: 'Salvando fornecedor',
                    text: 'Os dados do fornecedor estão sendo salvos...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar formulário
                this.submit();
            });
        });
    </script>
</body>