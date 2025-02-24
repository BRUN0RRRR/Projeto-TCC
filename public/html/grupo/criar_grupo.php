<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


include "../../../databases/conexao.php";
include "../../../includes/sidebar.php";
include "../../../api/alerta/alert_erro.php";

try {
    $usuarios_select = "SELECT * FROM cadastro_user;";

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
    <title>Gerenciamento de Grupos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .form-check-input:checked {
            background-color: #007bff;
            border-color: #007bff;
        }

        .form-check-label {
            font-size: 16px;
            margin-left: 8px;
        }

        .form-check-input {
            width: 20px;
            height: 20px;
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
        <div id="gruposContainer" class="mt-4"></div>

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
                                    <input class="form-check-input" type="checkbox" id="usuarios">
                                    <label class="form-check-label" for="usuarios">Usuários</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="produtos">
                                    <label class="form-check-label" for="produtos">Produtos</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="vendas">
                                    <label class="form-check-label" for="vendas">Vendas</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="relatorios">
                                    <label class="form-check-label" for="relatorios">Relatórios</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="configuracoes">
                                    <label class="form-check-label" for="configuracoes">Configurações</label>
                                </div>
                                <div class="form-check m-2">
                                    <input class="form-check-input" type="checkbox" id="financeiro">
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

            if (!nomeGrupo) {
                alert("Por favor, insira o nome do grupo.");
                return;
            }

            let permissoes = [];
            checkboxes.forEach((checkbox) => {
                permissoes.push(checkbox.nextElementSibling.innerText);
            });

            if (grupoEditando) {
                // Atualiza o grupo existente
                grupoEditando.querySelector(".card-title").innerText = nomeGrupo;
                grupoEditando.querySelector(".card-text").innerText = descricaoGrupo;
                grupoEditando.querySelector(".permissoes").innerText = permissoes.length > 0 ? permissoes.join(", ") : "Nenhuma permissão atribuída.";
            } else {
                // Cria um novo grupo
                let grupoHTML = `
                    <div class="card mt-3" data-nome="${nomeGrupo}">
                        <div class="card-body">
                            <h5 class="card-title">${nomeGrupo}</h5>
                            <p class="card-text">${descricaoGrupo}</p>
                            <h6>Permissões:</h6>
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
            let permissoes = grupoEditando.querySelector(".permissoes").innerText.split(", ");

            document.getElementById("nomeGrupo").value = nomeGrupo;
            document.getElementById("descricaoGrupo").value = descricaoGrupo;

            // Marca as permissões no modal
            document.querySelectorAll("input[type='checkbox']").forEach((checkbox) => {
                checkbox.checked = permissoes.includes(checkbox.nextElementSibling.innerText);
            });

            // Abre o modal para edição
            var modal = new bootstrap.Modal(document.getElementById("modalNovoGrupo"));
            modal.show();
        }
    </script>
</body>

</html>