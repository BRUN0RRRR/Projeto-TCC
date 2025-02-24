<?php

session_start();

include '../../databases/conexao.php';



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
    <style>
        @keyframes fadeInText {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }

            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .tela-branca {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 1;
            z-index: 9999;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif
        }

        .mensagem-bemvindo {
            font-size: 3rem;
            font-weight: bold;
            color: #333;
            opacity: 0;
            animation: fadeInText 1.0s ease-out 1.0s forwards;
        }
    </style>

    <div class="tela-branca" id="telaBranca">
        <span class="mensagem-bemvindo" id="mensagem"></span>
    </div>
    <script>
        window.onload = function () {
            let tela = document.getElementById("telaBranca");
            let mensagem = document.getElementById("mensagem");

            let nomeUsuario = "<?php echo $nomeUsuario = $_SESSION['nome_com']; ?>";
            console.log(nomeUsuario)
            mensagem.innerText = `Seja bem-vindo, ${nomeUsuario}!`;

            // Após 2 segundos, remove a tela e carrega o sistema
            setTimeout(() => {
                tela.style.opacity = "0";
                setTimeout(() => {
                    tela.style.display = "none"; // Oculta a tela
                    window.location.href = "../../public/home.php"; 
                }, 500);
            }, 4000);

        };
    </script>


</body>

</html>