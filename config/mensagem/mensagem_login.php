<?php
session_start();
include '../../databases/conexao.php';
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bem-vindo</title>
</head>

<body>
    <style>
        /* Estilo mais moderno e suave */
        @keyframes fadeIn {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes backgroundFade {
            0% {
                opacity: 1;
            }

            100% {
                opacity: 0;
            }
        }

        .welcome-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            font-family: 'Segoe UI', Roboto, sans-serif;
            transition: opacity 0.5s ease-out;
        }

        .welcome-message {
            font-size: 2.5rem;
            font-weight: 600;
            color: #2c3e50;
            text-align: center;
            opacity: 0;
            animation: fadeIn 1s ease-out 0.5s forwards;
            margin-bottom: 1rem;
        }

        .welcome-subtext {
            font-size: 1.2rem;
            color: #34495e;
            opacity: 0;
            animation: fadeIn 1s ease-out 1s forwards;
        }

        .loader {
            width: 40px;
            height: 40px;
            border: 4px solid #3498db;
            border-top: 4px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-top: 2rem;
            opacity: 0;
            animation: fadeIn 1s ease-out 1.5s forwards;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    <div class="welcome-screen" id="welcomeScreen">
        <span class="welcome-message" id="message"></span>
        <span class="welcome-subtext">Estamos preparando tudo para você!</span>
        <div class="loader"></div>
    </div>

    <script>
        window.onload = function () {
            const screen = document.getElementById("welcomeScreen");
            const message = document.getElementById("message");

            // Obtém o nome do usuário da sessão
            let nomeUsuario = "<?php echo isset($_SESSION['nome_com']) ? $_SESSION['nome_com'] : 'Usuário'; ?>";

            // Mensagem mais calorosa e personalizada
            message.innerText = `Olá, ${nomeUsuario}! É ótimo ter você aqui!`;

            // Controle da animação de saída
            setTimeout(() => {
                screen.style.opacity = "0";
                setTimeout(() => {
                    screen.style.display = "none";
                    window.location.href = "../../public/home.php";
                }, 500); // Tempo sincronizado com a transição do CSS
            }, 4000); // 4 segundos de exibição
        };
    </script>
</body>

</html>