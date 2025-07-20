<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";


function excluirRegistro($pdo, $id)
{
    try {
        $stmt = $pdo->prepare("DELETE FROM cadastro_user WHERE cod_usuario = :cod_usuario");
        $stmt->bindParam(':cod_usuario', $id);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        return false;
    }
}

// Verifica se clicou em EXCLUIR
if (isset($_POST['excluir'])) {
    $id = $_POST['idUsuario'];
    if (excluirRegistro($pdo, $id)) {
        echo "<div class='alert alert-success'>Usuário excluído com sucesso!</div>";
    } else {
        echo "<div class='alert alert-danger'>Erro ao excluir o usuário!</div>";
    }
}

try {
    // Consulta para buscar usuários e grupos
    $usuarios_select = "SELECT * FROM cadastro_user;";
    // Executa as consultas
    $stmt_usuarios = $pdo->query($usuarios_select);

    // Obtém os resultados
    $resultados_usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erro ao executar a consulta: " . $e->getMessage());
}

if (session_status() == PHP_SESSION_NONE) {
    // Garantir que a sessão esteja iniciada, caso este arquivo seja incluído
    // em um contexto onde o script principal ainda não iniciou a sessão.
    session_start();
}



$moduloNecessario = "usuarios";

verificarAcessoPagina($moduloNecessario);
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Gerenciamento de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/pagina_user.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>

    </style>
</head>

<body>
    <main>
        <div class="container mt-4">
            <!-- Cabeçalho com título e botão -->
            <div class="header-actions">
                <h4>Gerenciamento de Usuários</h4>
            </div>
            <!-- Barra de pesquisa -->
            <div class="search-box">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="pesquisaUsuario" class="form-control" placeholder="Pesquisar usuários...">
            </div>
            <!-- Tabela de Usuários -->
            <div class="card mt-4 p-4 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-striped table-hover custom-table" id="tabelaUsuarios">
                        <thead>
                            <tr>
                                <th><i class="bi bi-person-badge me-1"></i>LOGIN</th>
                                <th><i class="bi bi-person me-1"></i>NOME</th>
                                <th><i class="bi bi-envelope me-1"></i>EMAIL</th>
                                <th><i class="bi bi-building me-1"></i>SETOR</th>
                                <th><i class="bi bi-person-badge me-1"></i>PERFIL</th>
                                <th><i class="bi bi-circle-fill me-1"></i>STATUS</th>
                                <th><i class="bi bi-gear-fill me-1"></i>AÇÕES</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultados_usuarios): ?>
                                <?php foreach ($resultados_usuarios as $usuario): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($usuario['login']) ?></td>
                                        <td><?= htmlspecialchars($usuario['nome']) ?></td>
                                        <td><?= htmlspecialchars($usuario['email']) ?></td>
                                        <td><?= htmlspecialchars($usuario['setor']) ?></td>
                                        <td><?= htmlspecialchars($usuario['perfil']) ?></td>
                                        <td>
                                            <span class="status-badge status-<?= strtolower($usuario['status_user']) ?>">
                                                <?= htmlspecialchars($usuario['status_user']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-edit btn-editar-usuario" data-bs-toggle="modal"
                                                data-bs-target="#editarUsuarioModal" data-id="<?= $usuario['cod_usuario'] ?>"
                                                data-nome_completo="<?= htmlspecialchars($usuario['nome']) ?>"
                                                data-email="<?= htmlspecialchars($usuario['email']) ?>"
                                                data-setor="<?= htmlspecialchars($usuario['setor']) ?>"
                                                data-perfil="<?= htmlspecialchars($usuario['perfil']) ?>"
                                                data-status="<?= htmlspecialchars($usuario['status_user']) ?>">
                                                <i class="bi bi-pencil-fill"></i> Editar
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center">Nenhum usuário encontrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal de Edição -->
            <div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header text-white">
                            <h5 class="modal-title" id="editarUsuarioModalLabel">
                                <i class="bi bi-pencil-square me-2"></i>Editar Usuário
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formEditarUsuario" action="config/config_edit.php" method="POST"
                                class="needs-validation" novalidate>
                                <input type="hidden" id="idUsuario" name="idUsuario">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><i class="bi bi-person me-1"></i>Nome
                                            Completo</label>
                                        <input type="text" class="form-control" id="nome_completo" name="nome_model"
                                            required>
                                        <div class="invalid-feedback">Por favor, informe o nome completo.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
                                        <input type="email" class="form-control" id="email" name="email_model" required>
                                        <div class="invalid-feedback">Por favor, informe um email válido.</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><i class="bi bi-building me-1"></i>Setor</label>
                                        <input type="text" class="form-control" id="setor" name="setor_model" required>
                                        <div class="invalid-feedback">Por favor, informe o setor.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><i class="bi bi-circle-fill me-1"></i>Status</label>
                                        <select class="form-select" id="status" name="status_model" required>
                                            <option value="Ativo">Ativo</option>
                                            <option value="Inativo">Inativo</option>
                                            <option value="Bloqueado">Bloqueado</option>
                                        </select>
                                        <div class="invalid-feedback">Por favor, selecione um status.</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><i class="bi bi-person-badge me-1"></i>Perfil</label>
                                        <select class="form-select" id="perfil" name="perfil_model" required>
                                            <option value="operador">Operador</option>
                                            <option value="almoxarifado">Almoxarifado</option>
                                            <option value="administrador">Administrador</option>
                                        </select>
                                        <div class="invalid-feedback">Por favor, selecione um perfil.</div>
                                    </div>

                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <div class="form-check form-switch">
                                            <!-- Campo oculto para garantir o envio do valor 0 quando o checkbox não for marcado -->
                                            <input type="hidden" name="alterarSenhaCheck" value="0">
                                            <input class="form-check-input" type="checkbox" id="alterar_senha"
                                                name="alterarSenhaCheck" value="1">
                                            <label class="form-check-label" for="alterar_senha">
                                                <i class="bi bi-key me-1"></i>Alterar Senha
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mt-3" id="senhaSection" style="display: none;">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><i class="bi bi-key me-1"></i>Nova Senha</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="senha_model"
                                                id="senha_model">
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="toggleSenhaEdit">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback">Por favor, informe uma nova senha.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label"><i class="bi bi-key me-1"></i>Confirmar Nova
                                            Senha</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" name="senhaCon_model"
                                                id="senhaCon_model">
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="toggleConfSenhaEdit">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback" id="senhaMismatchEdit">As senhas não
                                            coincidem.
                                        </div>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        <i class="bi bi-x-circle me-1"></i>Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-circle me-1"></i>Salvar Alterações
                                    </button>
                                    <button type="button" id="btnExcluirUsuario" class="btn btn-danger">
                                        <i class="fas fa-times me-2"></i>Excluir
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            // Variáveis para elementos do DOM
            const formUsuario = document.getElementById('formUsuario');
            const btnCriarUsuario = document.getElementById('btnCriarUsuario');
            const btnCancelarCriar = document.getElementById('btnCancelarCriar');
            const inputPesquisa = document.getElementById('pesquisaUsuario');
            const checkboxSenha = document.getElementById('alterar_senha');
            const senhaSection = document.getElementById('senhaSection');
            const formEditarUsuario = document.getElementById('formEditarUsuario');
            const senha_model = document.getElementById('senha_model');
            const senhaCon_model = document.getElementById('senhaCon_model');
            const status_model = document.getElementById('status_model');


            // Botões para mostrar/ocultar senha
            const senhaFields = [
                { toggle: 'toggleSenhaEdit', field: 'senha_model' },
                { toggle: 'toggleConfSenhaEdit', field: 'senhaCon_model' }
            ];

            senhaFields.forEach(({ toggle, field }) => {
                const toggleButton = document.getElementById(toggle);
                const inputField = document.getElementById(field);
                if (toggleButton && inputField) {
                    toggleButton.addEventListener('click', function () {
                        togglePasswordVisibility(inputField, this);
                    });
                }
            });

            function togglePasswordVisibility(inputField, button) {
                if (inputField.type === "password") {
                    inputField.type = "text";
                    button.innerHTML = '<i class="bi bi-eye-slash"></i>';
                } else {
                    inputField.type = "password";
                    button.innerHTML = '<i class="bi bi-eye"></i>';
                }
            }

            // Pesquisa dinâmica
            inputPesquisa.addEventListener('keyup', function () {
                const termo = this.value.toLowerCase();
                document.querySelectorAll('#tabelaUsuarios tbody tr').forEach(linha => {
                    linha.style.display = linha.textContent.toLowerCase().includes(termo) ? "" : "none";
                });
            });

            // Checkbox de alteração de senha
            checkboxSenha.addEventListener('change', function () {
                senhaSection.style.display = this.checked ? 'flex' : 'none';
                senha_model.required = this.checked;
                senhaCon_model.required = this.checked;
            });

            document.getElementById('alterar_senha').addEventListener('change', function () {
                this.value = this.checked ? 1 : 0;
            });

            // Validação do formulário de edição
            formEditarUsuario.addEventListener('submit', function (event) {
                if (!formEditarUsuario.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                // Verificar se as senhas coincidem quando o checkbox está marcado
                if (checkboxSenha.checked && senha_model.value !== senhaCon_model.value) {
                    event.preventDefault();
                    document.getElementById('senhaMismatchEdit').style.display = 'block';
                    senhaCon_model.classList.add('is-invalid');
                } else {
                    document.getElementById('senhaMismatchEdit').style.display = 'none';
                    senhaCon_model.classList.remove('is-invalid');
                }

                formEditarUsuario.classList.add('was-validated');
            });

            // Preenchimento do modal de edição
            const editButtons = document.querySelectorAll('.btn-editar-usuario');
            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Capturar dados do botão
                    const id = this.getAttribute('data-id');
                    const nome = this.getAttribute('data-nome_completo');
                    const email = this.getAttribute('data-email');
                    const setor = this.getAttribute('data-setor');
                    const grupo = this.getAttribute('data-grupo');
                    const perfil = this.getAttribute('data-perfil');
                    const status = this.getAttribute('data-status');

                    console.log("ID:", id);
                    console.log("Nome:", nome);
                    console.log("Email:", email);
                    console.log("Setor:", setor);
                    console.log("Grupo:", grupo);
                    console.log("Perfil:", perfil);
                    console.log("Status:", status);

                    // Preencher campos do formulário
                    document.getElementById('idUsuario').value = id;
                    document.getElementById('nome_completo').value = nome;
                    document.getElementById('email').value = email;
                    document.getElementById('setor').value = setor;
                    document.getElementById('grupo').value = grupo;
                    document.getElementById('perfil').value = perfil;
                    document.getElementById('status').value = status;

                    // Resetar campos de senha
                    checkboxSenha.checked = false;
                    senhaSection.style.display = 'none';
                    senha_model.value = '';
                    senhaCon_model.value = '';
                    senha_model.required = false;
                    senhaCon_model.required = false;

                    // Limpar validações anteriores
                    formEditarUsuario.classList.remove('was-validated');
                    senhaCon_model.classList.remove('is-invalid');
                    document.getElementById('senhaMismatchEdit').style.display = 'none';
                });
            });

            $(document).ready(function () {
                $('#btnExcluirUsuario').click(function (e) {
                    e.preventDefault();

                    if (!confirm('Tem certeza que deseja excluir este usuário?')) {
                        return; // Cancela exclusão se não confirmar
                    }
                    var idUsuario = $('#idUsuario').val();
                    $.ajax({
                        url: 'config/config_delete.php',
                        method: 'POST',
                        data: { idUsuario: idUsuario },
                        dataType: 'json',
                        success: function (response) {
                            if (response.status === 'success') {
                                alert(response.message);
                                $('#editarUsuarioModal').modal('hide');
                                // Aqui recarrega lista/tabela de usuários, se tiver
                            } else {
                                alert('Erro: ' + response.message);
                            }
                        },
                        error: function (xhr, status, error) {
                            alert('Erro na requisição: ' + error);
                        }
                    });
                });
            });
        });

    </script>
</body>

</html>