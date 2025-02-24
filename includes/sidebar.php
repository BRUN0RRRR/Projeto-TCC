<?php
session_start();

define("URL", "/estoque/");

if (isset($_GET["sair"])) {
    session_destroy();
    header("Location: " . URL . "index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Estoque</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="src/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="<?= URL ?>public/assets/css/sidebar.css">

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const openBtn = document.getElementById("open_btn");

            openBtn.addEventListener("click", function () {
                sidebar.classList.toggle("open");
            });
        });
    </script>
</head>


<body>
    <nav id="sidebar">
        <div id="sidebar_content">
            <div id="user">
                <img src="<?= URL ?>public/assets/img/avatar/avatar_robin.jpg" id="user_avatar" alt="Avatar">
                <p id="user_infos">
                    <span class="item-description">
                        <?php
                        if (isset($_SESSION['nome_com'])) {
                            echo "<label id='nome'>{$_SESSION['nome_com']}</label>";
                        } else {
                            session_destroy();
                            header("Location: " . URL . "index.php");
                            exit();
                        }
                        ?>
                    </span>
                </p>
            </div>

            <ul id="side_items">
                <li class="side-item active">
                    <a href="<?= URL ?>public\home.php">
                        <i class="fa-solid fa-chart-line"></i>
                        <span class="item-description">
                            Dashboard
                        </span>
                    </a>
                </li>



                <li class="side-item">
                    <a href="#">
                        <i class="fa-solid fa-boxes-stacked"></i>
                        <span class="item-description">
                            Estoque
                        </span>
                    </a>
                </li>

                <li class="side-item">
                    <a href="#">
                        <i class="fa-solid fa-box"></i>
                        <span class="item-description">
                            Produtos
                        </span>
                    </a>
                </li>


                <li class="side-item">
                    <a href="<?= URL ?>public/html/usuario/criar_user.php">
                        <i class="fa-solid fa-user"></i>
                        <span class="item-description">
                            Usuários
                        </span>
                    </a>

                </li>
                <li class="side-item">
                    <a href="<?= URL ?>public/html/grupo/criar_grupo.php">
                        <i class="fa-solid fa-user-group"></i>
                        <span class="item-description">
                            Grupos
                        </span>
                    </a>
                </li>
                <li class="side-item">
                    <a href="#">
                        <i class="fa-solid fa-gear"></i>
                        <span class="item-description">
                            Configurações
                        </span>
                    </a>
                </li>
            </ul>

            <!--  <button id="open_btn">
                <i id="open_btn_icon" class="fa-solid fa-chevron-right"></i>
            </button> -->
        </div>

        <div id="logout">
            <a href="?sair" id="logout_btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span class="item-description">
                    Logout
                </span>

            </a>
        </div>
    </nav>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const sidebar = document.getElementById("sidebar");
            const openBtn = document.getElementById("open_btn");

            // Função para abrir a sidebar
            function openSidebar() {
                sidebar.classList.add("open");
            }

            // Função para fechar a sidebar
            function closeSidebar() {
                sidebar.classList.remove("open");
            }

            // Abrir sidebar ao passar o mouse
            sidebar.addEventListener("mouseenter", openSidebar);

            // Fechar sidebar ao retirar o mouse
            sidebar.addEventListener("mouseleave", closeSidebar);

            // Botão manual para abrir/fechar (opcional)
            openBtn.addEventListener("click", function () {
                sidebar.classList.toggle("open");
            });
        });
    </script>
</body>

</html>