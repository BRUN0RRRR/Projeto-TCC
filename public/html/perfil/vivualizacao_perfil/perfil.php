<?php
// Iniciando a sessão para manter o usuário logado
ob_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include "../../../../databases/conexao.php";
include "../../../../includes/sidebar.php";
include "../../../../api/alerta/alert_erro.php";

$moduloNecessario = "configuracoes";
verificarAcessoPagina($moduloNecessario);


// ID do usuário (normalmente viria da sessão após login)
$cod_usuario = isset($_SESSION['codi_usuario']) ? $_SESSION['codi_usuario'] : 0;

// Processamento do formulário quando enviado
$mensagem = '';
$tipo_mensagem = '';

// Processamento do formulário de perfil

// Processamento do formulário de senha
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['acao']) && $_POST['acao'] == 'atualizar_senha') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Verificar se a nova senha e a confirmação são iguais
    if ($nova_senha !== $confirmar_senha) {
        $mensagem = "A nova senha e a confirmação não correspondem.";
        $tipo_mensagem = "erro";
    } else {
        try {
            // Verificar se a senha atual está correta
            $stmt = $pdo->prepare("SELECT senha FROM cadastro_user WHERE cod_usuario = :cod_usuario");
            $stmt->bindParam(':cod_usuario', $cod_usuario);
            $stmt->execute();
            $usuario_senha = $stmt->fetch();

            // Aqui você deve usar a função de verificação de senha adequada ao seu sistema
            // Por exemplo, password_verify() se estiver usando hash de senha do PHP
            if (password_verify($senha_atual, $usuario_senha['senha'])) {
                // Senha atual correta, atualizar para a nova senha
                $hash_nova_senha = password_hash($nova_senha, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("UPDATE cadastro_user SET senha = :senha WHERE cod_usuario = :cod_usuario");
                $stmt->bindParam(':senha', $hash_nova_senha);
                $stmt->bindParam(':cod_usuario', $cod_usuario);

                if ($stmt->execute()) {
                    $mensagem = "Senha atualizada com sucesso!";
                    $tipo_mensagem = "sucesso";
                } else {
                    $mensagem = "Erro ao atualizar a senha.";
                    $tipo_mensagem = "erro";
                }
            } else {
                $mensagem = "Senha atual incorreta.";
                $tipo_mensagem = "erro";
            }
        } catch (Exception $e) {
            $mensagem = "Erro: " . $e->getMessage();
            $tipo_mensagem = "erro";
        }
    }
}

// Buscar dados atuais do usuário
try {
    $stmt = $pdo->prepare("SELECT * FROM cadastro_user WHERE cod_usuario = :cod_usuario");
    $stmt->bindParam(':cod_usuario', $cod_usuario);
    $stmt->execute();
    $usuario = $stmt->fetch();

    if (!$usuario) {
        die("Usuário não encontrado.");
    }
} catch (PDOException $e) {
    die("Erro ao buscar dados do usuário: " . $e->getMessage());
}

ob_end_flush();

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Perfil do Usuário</title>
    <link rel="stylesheet" href="css/pagina_perfil.css">
</head>

<body>
    <div class="container">
        <?php if ($mensagem): ?>
            <div class="mensagem <?php echo $tipo_mensagem == 'sucesso' ? 'mensagem-sucesso' : 'mensagem-erro'; ?>">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <div class="perfil-header">
            <div class="user-info">
                <form action="config/config_perfil.php" method="POST" enctype="multipart/form-data">
                    <label for="foto-perfil">
                        <img src="<?php echo !empty($usuario['fotos']) ? htmlspecialchars($usuario['fotos']) : '../../../../public/assets/img/avatar/avatar_robin.jpg'; ?>"
                            alt="Foto de perfil" class="foto-perfil" style="cursor:pointer;">
                    </label>
                    <input type="file" name="nova_foto" id="foto-perfil" style="display:none;"
                        onchange="this.form.submit()">
                </form>
                <div>
                    <h1><?php echo htmlspecialchars($usuario['nome']); ?></h1>
                    <p class="header-email"><?php echo htmlspecialchars($usuario['email']); ?></p>
                </div>
            </div>
        </div>


        <div class="perfil-content">
            <!-- Seção de Informações Pessoais -->
            <div class="perfil-card">
                <div id="info-display">
                    <div class="form-group">
                        <div class="form-label">Nome Completo</div>
                        <div><?php echo htmlspecialchars($usuario['nome']); ?></div>
                    </div>

                    <div class="form-group">
                        <div class="form-label">Email</div>
                        <div><?php echo htmlspecialchars($usuario['email']); ?></div>
                    </div>
                    <div class="form-group">
                        <div class="form-label">Perfil</div>
                        <div><?php echo htmlspecialchars($usuario['perfil']); ?></div>
                    </div>
                    <div class="form-group">
                        <div class="form-label">Setor</div>
                        <div><?php echo htmlspecialchars($usuario['setor']); ?></div>
                    </div>

                </div>

                <form id="info-form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST"
                    enctype="multipart/form-data" style="display: none;">
                    <input type="hidden" name="acao" value="atualizar_perfil">

                    <div class="form-group">
                        <label for="foto" class="form-label">Foto de Perfil</label>
                        <input type="file" id="foto" name="foto" class="form-control" accept="image/*">
                    </div>

                    <div class="form-group">
                        <label for="nome" class="form-label">Nome Completo</label>
                        <input type="text" id="nome" name="nome" class="form-control"
                            value="<?php echo htmlspecialchars($usuario['nome']); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="<?php echo htmlspecialchars($usuario['setor']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" class="form-control"
                            value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                    </div>

                    <div class="botoes">
                        <button type="button" class="btn btn-secundario"
                            onclick="toggleEdit('info-form')">Cancelar</button>
                        <button type="submit" class="btn btn-primario">Salvar Alterações</button>
                    </div>
                </form>
            </div>

            <!-- Seção de Segurança -->
            <div class="perfil-card">
                <h2>Segurança</h2>

                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <input type="hidden" name="acao" value="atualizar_senha">

                    <div class="form-group">
                        <label for="senha_atual" class="form-label">Senha Atual</label>
                        <div class="password-field">
                            <input type="password" id="senha_atual" name="senha_atual" class="form-control" required>
                            <button type="button" class="password-toggle"
                                onclick="togglePassword('senha_atual')">👁️</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="nova_senha" class="form-label">Nova Senha</label>
                        <div class="password-field">
                            <input type="password" id="nova_senha" name="nova_senha" class="form-control" required>
                            <button type="button" class="password-toggle"
                                onclick="togglePassword('nova_senha')">👁️</button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                        <div class="password-field">
                            <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control"
                                required>
                            <button type="button" class="password-toggle"
                                onclick="togglePassword('confirmar_senha')">👁️</button>
                        </div>
                    </div>

                    <div class="botoes">
                        <button type="submit" class="btn btn-primario">Atualizar Senha</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function toggleEdit(formId) {
            const form = document.getElementById(formId);
            const display = document.getElementById('info-display');

            if (form.style.display === 'none') {
                form.style.display = 'block';
                display.style.display = 'none';
            } else {
                form.style.display = 'none';
                display.style.display = 'block';
            }
        }

        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>

</html>