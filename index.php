<?php
session_start();

include "api/alerta/alert_erro.php";



?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - InfoStock</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Roboto, sans-serif;
        }

        body {
            background: #1a2333;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-container {
            background: #1a2333;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 900px;
            display: flex;
            height: 500px;
        }

        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #1a2333 0%, #2c3e50 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            color: #ffffff;
        }

        .left-panel h1 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #3498db;
        }

        .left-panel p {
            font-size: 1rem;
            opacity: 0.8;
        }

        .left-panel img {
            width: 100%;
            border-radius: 10px;
            margin-top: 20px;
        }

        .right-panel {
            flex: 1;
            background: #1a2333;
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 40px;
            color: #ffffff;
        }

        .right-panel h2 {
            font-size: 1.5rem;
            margin-bottom: 30px;
            color: #ffffff;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 5px;
            background: #2c3e50;
            color: #ffffff;
            font-size: 1rem;
            outline: none;
        }

        .input-group input::placeholder {
            color: #a0b3c5;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            color: #a0b3c5;
        }

        .remember-me input {
            margin-right: 10px;
        }

        .forgot-password {
            color: #3498db;
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 20px;
            display: block;
        }

        .login-button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .login-button:hover {
            background: #2980b9;
        }

        .support-text {
            font-size: 0.8rem;
            color: #a0b3c5;
            text-align: center;
            margin-top: 20px;
        }

        .sun-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #ffd700;
            font-size: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="left-panel">
            <h1>InfoStock</h1>
            <p>Acesso ao sistema de Estoque</p>
            <img src="public/assets/img/logo.jpg" alt="Imagem do escritório">
        </div>
        <div class="right-panel">
            <h2>Acesso ao Sistema</h2>
            <form action="config/validacao/validacao.php" method="post">
                <div class="input-group">
                    <input type="text" placeholder="Seu login" id="login" name="login" required>
                </div>
                <div class="input-group">
                    <input type="password" placeholder="Sua senha" id="senha" name="senha" required>
                </div>
                <div class="remember-me">
                    <input type="checkbox" id="remember">
                    <label for="remember">Lembrar-me</label>
                </div>
                <a href="#" class="forgot-password">Esqueceu a senha?</a>
                <button type="submit" class="login-button" id="botao">Entrar</button>
                <p class="support-text">Precisa de ajuda? Entre em contato com o suporte técnico</p>
            </form>
        </div>
    </div>
</body>

</html>