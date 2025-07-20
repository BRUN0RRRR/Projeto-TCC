<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../databases/conexao.php";
include "../../../includes/sidebar.php";
include "../../../api/alerta/alert_erro.php";

try {
    $usuarios_select = "SELECT * FROM grupos;";

    // Prepara a consulta
    $stmt = $pdo->prepare($usuarios_select);

    // Executa a consulta
    $stmt->execute();

    // Obtém todos os resultados como um array associativo
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Trata erros de execução da consulta
    die("Erro ao executar a consulta: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Infostck - Grupos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 1200px;
            margin-top: 20px;
        }

        h2 {
            color: #333;
            font-size: 24px;
            margin-bottom: 10px;
        }

        p {
            color: #666;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            padding: 8px 16px;
            font-size: 14px;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }

        .modal-content {
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid #dee2e6;
        }

        .modal-title {
            font-size: 18px;
            color: #333;
        }

        .form-label {
            font-size: 14px;
            color: #333;
            margin-bottom: 5px;
        }

        .form-control {
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 8px;
            font-size: 14px;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        h6 {
            font-size: 16px;
            color: #333;
            margin-top: 15px;
            margin-bottom: 10px;
        }

        .form-check {
            margin-right: 20px;
            margin-bottom: 10px;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
            border: 1px solid #ced4da;
            background-color: #fff;
        }

        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }

        .form-check-label {
            font-size: 14px;
            color: #333;
            margin-left: 8px;
        }

        /* Contêiner pai para os cards */
        .gruposContainer {

            display: grid;
            gap: 15px;
            justify-content: center;
            align-content: center;
            grid-auto-flow: column;
            justify-content: start;
            /* Similar ao row-reverse do flex */
            /* Para simular o row-reverse */
            grid-template-areas: repeat(3, 1fr);
        }


        /* Estilo do card individual */
        .card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
            /* Mantém margem inferior para quando houver quebra de linha */
            padding: 15px;
            background-color: #d4d4d4;
            width: 26vw;
            /* Largura fixa para cada card */
            display: flex;
            /* Mantém o flex interno, se precisar organizar conteúdo dentro do card */
            flex-direction: row;
            flex-wrap: wrap;
            align-content: space-around;
            justify-content: center;
            align-items: baseline;
        }

        .card-title {
            font-size: 16px;
            color: #333;
            margin-bottom: 10px;
        }

        .card-text {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        h6.card-subtitle {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .permissoes {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .btn-warning {
            background-color: #ffc107;
            border-color: #ffc107;
            padding: 6px 12px;
            font-size: 14px;
        }

        .btn-warning:hover {
            background-color: #e0a800;
            border-color: #e0a800;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Gerenciamento de Grupos</h2>
        <p>Defina grupos e seus módulos de acesso</p>

        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovoGrupo">
            + Novo Grupo
        </button>

        <!-- Exibição dos grupos cadastrados -->
        <div id="gruposContainer" class="container">
            <div class="row">
                <?php
                $contador = 0;
                foreach ($resultados as $usuario) {
                    if ($contador % 3 == 0 && $contador != 0) {
                        echo '</div><div class="row">'; // Fecha a div anterior e abre uma nova a cada 3 itens
                    }
                    ?>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($usuario['nome_grupo']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($usuario['descricao']); ?></p>
                                <button class="btn btn-warning" onclick="editarGrupo(this)">Editar</button>
                                <button class="btn btn-danger">Excluir</button>
                            </div>
                        </div>
                    </div>
                    <?php
                    $contador++;
                }
                ?>
            </div>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="modalNovoGrupo" tabindex="-1" aria-labelledby="modalNovoGrupoLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalNovoGrupoLabel">Novo Grupo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formNovoGrupo">
                            <div class="mb-3">
                                <label for="nomeGrupo" class="form-label">Nome do Grupo</label>
                                <input type="text" class="form-control" id="nomeGrupo" required>
                            </div>
                            <div class="mb-3">
                                <label for="descricaoGrupo" class="form-label">Descrição</label>
                                <textarea class="form-control" id="descricaoGrupo" rows="3"></textarea>
                            </div>
                            <h6>Módulos do Sistema</h6>
                            <div class="d-flex flex-wrap">
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="usuarios" name="modulos[]"
                                        value="Usuários">
                                    <label class="form-check-label" for="usuarios">Usuários</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="produtos" name="modulos[]"
                                        value="Produtos">
                                    <label class="form-check-label" for="produtos">Produtos</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="vendas" name="modulos[]"
                                        value="Vendas">
                                    <label class="form-check-label" for="vendas">Vendas</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="relatorios" name="modulos[]"
                                        value="Relatórios">
                                    <label class="form-check-label" for="relatorios">Relatórios</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="configuracoes" name="modulos[]"
                                        value="Configurações">
                                    <label class="form-check-label" for="configuracoes">Configurações</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="financeiro" name="modulos[]"
                                        value="Financeiro">
                                    <label class="form-check-label" for="financeiro">Financeiro</label>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                            id="cancelarEdicao">Cancelar</button>
                        <button type="button" class="btn btn-primary" id="salvarGrupo">Salvar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let grupoEditando = null;

        document.getElementById("salvarGrupo").addEventListener("click", function () {
            let nomeGrupo = document.getElementById("nomeGrupo").value.trim();
            let descricaoGrupo = document.getElementById("descricaoGrupo").value.trim();
            let checkboxes = document.querySelectorAll("input[type='checkbox']:checked");
            let permissoes = Array.from(checkboxes).map(cb => cb.value);

            if (!nomeGrupo) {
                alert("Por favor, insira o nome do grupo.");
                return;
            }

            if (grupoEditando) {
                // Atualiza o grupo existente
                grupoEditando.querySelector(".card-title").innerText = nomeGrupo;
                grupoEditando.querySelector(".card-text").innerText = descricaoGrupo || "Sem descrição";
                grupoEditando.querySelector(".permissoes").innerText = permissoes.length > 0 ? permissoes.join(", ") : "Nenhuma permissão atribuída.";
            } else {
                // Cria um novo grupo
                let grupoHTML = `
                    <div class="card mt-3" data-nome="${nomeGrupo}">
                

                        <div class="card-body">
                            <h5 class="card-title">${nomeGrupo}</h5>
                            <p class="card-text">${descricaoGrupo || "Sem descrição"}</p>
                            <h6 class="card-subtitle mb-2">Módulos com Acesso:</h6>
                            <p class="permissoes">${permissoes.length > 0 ? permissoes.join(", ") : "Nenhuma permissão atribuída."}</p>
                            <button class="btn btn-warning btn-sm" onclick="editarGrupo(this)">Editar</button>
                        </div>
                    </div>
                `;
                document.getElementById("gruposContainer").innerHTML += grupoHTML;
            }

            // Limpa os campos e fecha o modal
            document.getElementById("formNovoGrupo").reset();
            var modal = bootstrap.Modal.getInstance(document.getElementById("modalNovoGrupo"));
            modal.hide();

            // Limpa a variável de edição
            grupoEditando = null;
        });

        function editarGrupo(button) {
            grupoEditando = button.closest('.card');
            let nomeGrupo = grupoEditando.querySelector(".card-title").innerText;
            let descricaoGrupo = grupoEditando.querySelector(".card-text").innerText;
            let permissoes = grupoEditando.querySelector(".permissoes").innerText.split(", ").filter(p => p.trim() !== "Nenhuma permissão atribuída.");

            document.getElementById("nomeGrupo").value = nomeGrupo;
            document.getElementById("descricaoGrupo").value = descricaoGrupo === "Sem descrição" ? "" : descricaoGrupo;

            // Desmarca todos os checkboxes
            document.querySelectorAll("input[type='checkbox']").forEach((checkbox) => {
                checkbox.checked = false;
            });

            // Marca as permissões no modal
            permissoes.forEach(perm => {
                document.querySelector(`input[type='checkbox'][value='${perm.trim()}']`).checked = true;
            });

            // Abre o modal para edição
            var modal = new bootstrap.Modal(document.getElementById("modalNovoGrupo"));
            modal.show();
        }
    </script>
</body>

</html>