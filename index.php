<?php
session_start();

include "api/alerta/alert_erro.php";

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfoStock - Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Estilização geral */
        body {
            background: linear-gradient(135deg, #111827, #1f2937);
            color: white;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }

        /* Container do login */
        .login-container {
            background-color: #1f2937;
            padding: 2rem;
            border-radius: 36px 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 483px;
            /* Define um tamanho máximo para evitar telas muito grandes */
        }

        /* Estilização do título */
        .login-container h2 {
            text-align: center;
            font-weight: bold;
            color: #a5b4fc;
        }

        /* Campos de entrada */
        .form-control {
            background-color: #334155;
            color: white;
            border: none;
        }

        /* Placeholder dos inputs */
        .form-control::placeholder {
            color: #94a3b8;
        }

        /* Botão personalizado */
        .btn-custom {
            background-color: #6366f1;
            color: white;
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #4f46e5;
        }

        /* Links personalizados */
        .link-custom {
            color: #a5b4fc;
            text-decoration: none;
        }

        .link-custom:hover {
            text-decoration: underline;
        }

        /* Texto auxiliar */
        .text-muted {
            color: #94a3b8 !important;
        }

        /* 🔹 Responsividade */
        @media (max-width: 1024px) {

            /* Tablets */
            .login-container {
                max-width: 70%;
            }
        }

        @media (max-width: 768px) {

            /* Celulares */
            .login-container {
                max-width: 90%;
                padding: 1.5rem;
            }
        }

        @media (max-width: 480px) {

            /* Telas muito pequenas */
            .login-container {
                padding: 1rem;
                border-radius: 20px 8px;
            }

            .btn-custom {
                font-size: 14px;
                padding: 8px;
            }
        }
    </style>
</head>

<body>

    <div class="login-container">
        <h2>InfoStock</h2>
        <p class="text-center text-muted">Sistema de Gestão de Estoque</p>
        <form action="config/validacao/validacao.php" method="POST">
            <div class="mb-3">
                <label class="form-label">Login</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control" name="login" placeholder="login" required>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Senha</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control" name="senha" placeholder="••••••••" required>
                </div>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <div>
                    <input type="checkbox" id="lembrar">
                    <label for="lembrar" class="text-muted">Lembrar-me</label>
                </div>
                <a href="#" class="link-custom">Esqueceu a senha?</a>
            </div>
            <button type="submit" class="btn btn-custom">Entrar →</button>
        </form>
        <p class="text-center mt-3">Não tem uma conta? <a href="#" class="link-custom">Cadastre-se</a></p>
    </div>

</body>

</html>