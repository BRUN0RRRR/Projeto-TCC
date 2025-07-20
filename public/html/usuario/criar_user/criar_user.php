<?php
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";


$moduloNecessario = "usuarios";

verificarAcessoPagina($moduloNecessario);

try {
    // Consulta para buscar usuários e grupos
    $usuarios_select = "SELECT * FROM cadastro_user ORDER BY nome ASC;";
    // Executa as consultas
    $stmt_usuarios = $pdo->query($usuarios_select);
  
    // Obtém os resultados
    $resultados_usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao executar a consulta: " . $e->getMessage());
}

ob_end_flush();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Gerenciamento de Usuários</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/pagina_criar_user.css">
    <!-- Adicionar fonte do Google para melhorar a aparência -->
</head>

<body>
    <main>
        <div class="container mt-4">
            <div class="header-actions">
                <h5 class="text-primary"><i class="bi bi-person-plus-fill me-2"></i>Cadastrar Novo Usuário</h5>
                
            </div>

            <!-- Formulário de criação de usuário -->
            <div class="card p-4 shadow-sm mt-3 mb-4 fade-in">
                <form id="formCriarUsuario" action="config/config_user.php" method="POST"
                    class="needs-validation" novalidate>
                    <!-- Barra de progresso -->
                    <div class="progress mb-4" style="height: 6px;">
                        <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0"
                            aria-valuemin="0" aria-valuemax="100" id="formProgress"></div>
                    </div>

                    <!-- Campos do formulário -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person-fill me-1"></i>Nome Completo</label>
                            <input type="text" class="form-control" name="nome" id="nome" required>
                            <div class="invalid-feedback">Por favor, informe o nome completo.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-person-badge-fill me-1"></i>Login</label>
                            <input type="text" class="form-control" name="login" id="login" required>
                            <div class="invalid-feedback">Por favor, informe o login.</div>
                            <div class="form-text">O login deve ser único no sistema.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-envelope-fill me-1"></i>Email</label>
                            <input type="email" class="form-control" name="email" id="email" required>
                            <div class="invalid-feedback">Por favor, informe um email válido.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-building-fill me-1"></i>Setor</label>
                            <input type="text" class="form-control" name="setor" id="setor" required>
                            <div class="invalid-feedback">Por favor, informe o setor.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-key-fill me-1"></i>Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="senha" id="senha" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleSenha">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                            <div class="password-feedback" id="passwordFeedback"></div>
                            <div class="invalid-feedback">Por favor, informe uma senha.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-key-fill me-1"></i>Confirmar Senha</label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="senhaCon" id="senhaCon" required>
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfSenha">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback" id="senhaMismatch">As senhas não coincidem.</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><i class="bi bi-shield-fill me-1"></i>Perfil</label>
                            <select class="form-select" name="perfil" id="perfil" required>
                                <option value="">Selecione um perfil</option>
                                <option value="operador">Operador</option>
                                <option value="almoxarifado">Almoxarifado</option>
                                <option value="administrador">Administrador</option>
                            </select>
                            <div class="invalid-feedback">Por favor, selecione um perfil.</div>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <button type="button" id="btnCancelarCriar" class="btn btn-secondary">
                            <i class="bi bi-x-circle me-1"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Salvar
                        </button>
                    </div>
                </form>
            </div>

            <!-- Notificações -->
            <div class="toast-container position-fixed bottom-0 end-0 p-3">
                <div id="toastSuccess" class="toast align-items-center text-white bg-success border-0" role="alert"
                    aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-check-circle me-2"></i><span id="toastSuccessMessage">Operação realizada com
                                sucesso!</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>

                <div id="toastError" class="toast align-items-center text-white bg-danger border-0" role="alert"
                    aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="bi bi-exclamation-circle me-2"></i><span id="toastErrorMessage">Ocorreu um
                                erro!</span>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Validação do formulário
            const form = document.getElementById('formCriarUsuario');
            const passwordInput = document.getElementById('senha');
            const confirmPasswordInput = document.getElementById('senhaCon');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordFeedback = document.getElementById('passwordFeedback');
            const formProgress = document.getElementById('formProgress');

            // Função para mostrar mensagem de sucesso ou erro
            function showToast(type, message) {
                const toast = document.getElementById(type === 'success' ? 'toastSuccess' : 'toastError');
                const messageElement = document.getElementById(type === 'success' ? 'toastSuccessMessage' : 'toastErrorMessage');

                messageElement.textContent = message;
                const bsToast = new bootstrap.Toast(toast);
                bsToast.show();
            }

            // Atualizar barra de progresso do formulário
            function updateFormProgress() {
                const requiredFields = form.querySelectorAll('[required]');
                let filledFields = 0;

                requiredFields.forEach(field => {
                    if (field.value.trim() !== '') {
                        filledFields++;
                    }
                });

                const progressPercentage = Math.floor((filledFields / requiredFields.length) * 100);
                formProgress.style.width = progressPercentage + '%';
                formProgress.setAttribute('aria-valuenow', progressPercentage);

                // Mudar cor com base no progresso
                if (progressPercentage < 30) {
                    formProgress.className = 'progress-bar bg-danger';
                } else if (progressPercentage < 70) {
                    formProgress.className = 'progress-bar bg-warning';
                } else {
                    formProgress.className = 'progress-bar bg-success';
                }
            }

            // Avaliar força da senha
            function checkPasswordStrength(password) {
                let strength = 0;

                if (password.length >= 8) strength += 1;
                if (password.match(/[a-z]+/)) strength += 1;
                if (password.match(/[A-Z]+/)) strength += 1;
                if (password.match(/[0-9]+/)) strength += 1;
                if (password.match(/[^a-zA-Z0-9]+/)) strength += 1;

                switch (strength) {
                    case 0:
                    case 1:
                        passwordStrength.style.width = '20%';
                        passwordStrength.style.backgroundColor = '#dc3545';
                        passwordFeedback.style.color = '#dc3545';
                        passwordFeedback.textContent = 'Muito fraca';
                        break;
                    case 2:
                        passwordStrength.style.width = '40%';
                        passwordStrength.style.backgroundColor = '#ffc107';
                        passwordFeedback.style.color = '#ffc107';
                        passwordFeedback.textContent = 'Fraca';
                        break;
                    case 3:
                        passwordStrength.style.width = '60%';
                        passwordStrength.style.backgroundColor = '#fd7e14';
                        passwordFeedback.style.color = '#fd7e14';
                        passwordFeedback.textContent = 'Média';
                        break;
                    case 4:
                        passwordStrength.style.width = '80%';
                        passwordStrength.style.backgroundColor = '#20c997';
                        passwordFeedback.style.color = '#20c997';
                        passwordFeedback.textContent = 'Boa';
                        break;
                    case 5:
                        passwordStrength.style.width = '100%';
                        passwordStrength.style.backgroundColor = '#198754';
                        passwordFeedback.style.color = '#198754';
                        passwordFeedback.textContent = 'Forte';
                        break;
                }
            }

            // Eventos para atualizar progresso do formulário
            form.querySelectorAll('[required]').forEach(field => {
                field.addEventListener('input', updateFormProgress);
            });

            // Verificar força da senha
            passwordInput.addEventListener('input', function () {
                checkPasswordStrength(this.value);

                // Verificar se as senhas coincidem
                if (confirmPasswordInput.value !== '') {
                    if (this.value !== confirmPasswordInput.value) {
                        confirmPasswordInput.classList.add('is-invalid');
                    } else {
                        confirmPasswordInput.classList.remove('is-invalid');
                    }
                }
            });

            // Verificar se as senhas coincidem
            confirmPasswordInput.addEventListener('input', function () {
                if (this.value !== passwordInput.value) {
                    this.classList.add('is-invalid');
                } else {
                    this.classList.remove('is-invalid');
                }
            });

            // Alternar visibilidade da senha
            document.getElementById('toggleSenha').addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });

            document.getElementById('toggleConfSenha').addEventListener('click', function () {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);
                this.querySelector('i').classList.toggle('bi-eye');
                this.querySelector('i').classList.toggle('bi-eye-slash');
            });

            // Validação do formulário antes de enviar
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    showToast('error', 'Por favor, preencha todos os campos obrigatórios.');
                } else if (passwordInput.value !== confirmPasswordInput.value) {
                    event.preventDefault();
                    event.stopPropagation();
                    confirmPasswordInput.classList.add('is-invalid');
                    showToast('error', 'As senhas não coincidem.');
                }

                form.classList.add('was-validated');
            });

            // Botão cancelar
            document.getElementById('btnCancelarCriar').addEventListener('click', function () {
                if (confirm('Tem certeza que deseja cancelar? Os dados não salvos serão perdidos.')) {
                    form.reset();
                    form.classList.remove('was-validated');
                    passwordStrength.style.width = '0%';
                    passwordFeedback.textContent = '';
                    formProgress.style.width = '0%';
                    window.location.href = 'index.php'; // Redirecionar para a página principal
                }
            });

            // Inicializar progresso
            updateFormProgress();
        });
    </script>
</body>

</html>