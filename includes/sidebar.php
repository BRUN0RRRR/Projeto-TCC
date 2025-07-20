<?php
ob_start();
// Configurações de erro melhoradas
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
error_reporting(E_ALL);


define("URL", "/estoque/");
//require_once "../config/configig/config.php";  
session_start();

if (isset($_GET["sair"])) {
    session_destroy();
    header("Location: " . URL . "index.php");
    exit();
}

if (isset($_SESSION["perfil"])) {
    $perfil = $_SESSION["perfil"];
} else {
    // Redirecionar ou mostrar erro apropriado
    session_destroy();
    header("Location: " . URL . "index.php");
    exit();
}

$mapaPaginas = [
    'dashboard' => 'dashboard',
    'estoque' => 'estoque',
    'entrada_produtos' => 'entrada_produtos',
    'baixa_produtos' => 'baixa_produtos',
    'produtos' => 'produtos',
    'deposito' => 'deposito',
    'fornecedor' => 'fornecedor',
    'usuarios' => 'usuarios',
    'configuracoes' => 'configuracoes',
    'home' => 'dashboard'
];

// MODIFIED PERMISSIONS ARRAY
$permissoes = [
    "administrador" => [
        "dashboard",
        "estoque",
        "entrada_produtos",
        "baixa_produtos",
        "produtos",
        "deposito",
        "fornecedor",
        "usuarios",
        "configuracoes"
    ],
    "operador" => [
        "dashboard",
        "estoque",
        "entrada_produtos",
        "baixa_produtos",
        "configuracoes"
    ],
    "almoxarifado" => [
        "dashboard",
        "estoque",
        "entrada_produtos",
        "baixa_produtos",
        "produtos",
        "deposito",
        "fornecedor",
        "configuracoes"
    ]
];

function temPermissao($modulo, $perfil, $permissoes)
{
    if (isset($permissoes[$perfil])) {
        return in_array($modulo, $permissoes[$perfil]);
    }
    return false;
}



function verificarAcessoPagina($moduloNecessario)
{
    // Certifique-se de que o perfil da sessão está sendo usado e existe
    if (!isset($_SESSION['perfil'])) {
        // Se não há perfil na sessão, o usuário não está logado ou a sessão é inválida.
        // Redireciona para a página de login.
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Sessão Inválida",
            'text' => "Por favor, faça login novamente para continuar."
        ];
        // A constante URL deve estar definida pelo script que inclui este arquivo (ex: sidebar.php)
        if (defined('URL')) {
            header("Location: " . URL . "index.php");
        } else {
            // Fallback se URL não estiver definida, embora isso indique um problema de configuração.
            header("Location: /estoque/index.php");
        }
        exit();
    }
    $perfilUsuario = $_SESSION['perfil'];

    // Acessa $permissoes do escopo global, que deve ser definido pelo script que inclui este (ex: sidebar.php)
    // Se $permissoes não for global, precisaria ser passada como parâmetro.
    global $permissoes;



    if (!isset($permissoes) || !is_array($permissoes)) {
        // Se $permissoes não está definida ou não é um array, algo está errado com a configuração.
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Erro de Configuração",
            'text' => "As permissões do sistema não foram carregadas corretamente."
        ];
        if (defined('URL')) {
            header("Location: " . URL . "public/home.php");
            exit();

        } else {
            header("Location: " . URL . "public/home.php");

        }
        exit();
    }

    if (!temPermissao($moduloNecessario, $perfilUsuario, $permissoes)) {
        $_SESSION['aviso'] = [
            'icon' => "error",
            'title' => "Acesso Negado",
            'text' => "Você não tem permissão para acessar esta página!"
        ];

        // header("Location: " . URL . "public/home.php");


        /* Redireciona para a página home (Dashboard)*/

        if (defined('URL')) {

            header("Location: " . URL . "public/home.php");
            $_SESSION['aviso'] = [
                'icon' => "error",
                'title' => "Acesso Negado",
                'text' => "Você não tem permissão para acessar esta página!"
            ];
            exit();
        } else {
            header("Location: " . URL . "public/home.php");
        }
        exit();
    }

}

function exibirAviso()
{
    if (isset($_SESSION['aviso']) && is_array($_SESSION['aviso'])) {
        $aviso = $_SESSION['aviso'];
        $icon = isset($aviso['icon']) ? htmlspecialchars($aviso['icon'], ENT_QUOTES, 'UTF-8') : 'info';
        $title = isset($aviso['title']) ? htmlspecialchars($aviso['title'], ENT_QUOTES, 'UTF-8') : 'Aviso';
        $text = isset($aviso['text']) ? htmlspecialchars($aviso['text'], ENT_QUOTES, 'UTF-8') : '';

        echo "<script type='text/javascript'>";
        echo "document.addEventListener('DOMContentLoaded', function() {";
        echo "  if (typeof Swal !== 'undefined') { Swal.fire({ icon: '{$icon}', title: '{$title}', text: '{$text}' }); }";
        echo "  else { alert('{$title}: {$text}'); }"; // Fallback simples
        echo "});";
        echo "</script>";
        unset($_SESSION['aviso']); // Limpa o aviso da sessão após prepará-lo para exibição
    }
}
exibirAviso();



if (isset($_SESSION['codi_usuario'])) {
    $stmt = $pdo->prepare("SELECT fotos FROM cadastro_user WHERE cod_usuario = ?");
    $stmt->execute([$_SESSION['codi_usuario']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && !empty($usuario["fotos"])) {
        $foto_usuario = $usuario["fotos"];
    }
} else {
    session_destroy();
    // header("Location: " . URL . "index.php");
    exit();
}

ob_end_flush();
?>



<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
        integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="src/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="<?= URL ?>public/assets/css/sidebar.css">
    <style>
        /* Adicionado para remover as bolinhas brancas */
        #sidebar_content>ul {
            list-style-type: none;
            padding-left: 0;
            /* Remove o padding padrão da lista */
            margin-left: 0;
            /* Remove a margem padrão da lista */
        }

        #sidebar_content>ul>li.side-item {
            /* Se necessário, pode adicionar estilos específicos aqui, mas o list-style-type: none; no UL pai deve ser suficiente */
        }
    </style>
</head>

<body>
    <nav id="sidebar">
        <div id="sidebar_content">
            <div id="user">
                <img src="<?= htmlspecialchars($foto_usuario) ?>" id="user_avatar" alt="Avatar">
                <p id="user_infos">
                    <span class="item-description">
                        <label id="nome"><?= htmlspecialchars($_SESSION["nome_com"]) ?></label>
                    </span>
                </p>
            </div>
            <hr>
            <ul> <!-- UL adicionada para envolver os itens principais -->
                <?php if (temPermissao("dashboard", $perfil, $permissoes)): ?>
                    <li class="side-item active">
                        <a href="<?= URL ?>public/home.php">
                            <i class="fa-solid fa-chart-line"></i>
                            <span class="item-description">Dashboard</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (temPermissao("estoque", $perfil, $permissoes)): ?>
                    <!-- Estoque com submenu -->
                    <li class="side-item has-submenu">
                        <a href="#" class="submenu-toggle">
                            <i class="fa-solid fa-boxes-stacked"></i>
                            <span class="item-description">
                                Estoque
                            </span>
                            <!--  <i class="fa-solid fa-chevron-down submenu-arrow"></i>-->
                        </a>
                        <ul class="submenu">
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/estoque/visualizar_estoque/estoque_visualizacao.php">
                                    <i class="fa-solid fa-eye"></i>
                                    <span class="item-description">Visualizar Estoque</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (temPermissao("entrada_produtos", $perfil, $permissoes)): ?>
                    <!-- Entrada de Produtos com submenu -->
                    <li class="side-item has-submenu">
                        <a href="#" class="submenu-toggle">
                            <i class="fa-solid fa-box-open"></i>
                            <span class="item-description">
                                Entrada de Produtos
                            </span>
                            <!--  <i class="fa-solid fa-chevron-down submenu-arrow"></i>-->
                        </a>
                        <ul class="submenu">
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/entrada/entrada_item/entrada_produtos.php">
                                    <i class="fa-solid fa-plus"></i>
                                    <span class="item-description">Nova Entrada</span>
                                </a>
                            </li>
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/entrada/lista_ent_item/lista_ent_item.php">
                                    <i class="fa-solid fa-history"></i>
                                    <span class="item-description">Histórico de Entrada</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (temPermissao("baixa_produtos", $perfil, $permissoes)): ?>
                    <li class="side-item has-submenu">
                        <a href="#" class="submenu-toggle">
                            <i class="fas fa-external-link-alt"></i>
                            <span class="item-description">
                                Baixa de Produtos
                            </span>
                            <!--  <i class="fa-solid fa-chevron-down submenu-arrow"></i>-->
                        </a>
                        <ul class="submenu">
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/saida/saida_item/saida_produtos.php">
                                    <i class="fa-solid fa-plus"></i>
                                    <span class="item-description">Nova Baixa</span>
                                </a>
                            </li>
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/saida/lista_saida_item/lista_saida_item.php">
                                    <i class="fa-solid fa-history"></i>
                                    <span class="item-description">Histórico de Baixa</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (temPermissao("produtos", $perfil, $permissoes)): ?>
                    <!-- Produtos com submenu -->
                    <li class="side-item has-submenu">
                        <a href="#" class="submenu-toggle">
                            <i class="fa-solid fa-box"></i>
                            <span class="item-description">
                                Produtos
                            </span>
                            <!--  <i class="fa-solid fa-chevron-down submenu-arrow"></i>-->
                        </a>
                        <ul class="submenu">
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/itens/criar_item/criar_item.php">
                                    <i class="fa-solid fa-plus"></i>
                                    <span class="item-description">Novo Produto</span>
                                </a>
                            </li>
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/itens/lista_item/lista_item.php">
                                    <i class="fa-solid fa-list"></i>
                                    <span class="item-description">Listar Produtos</span>
                                </a>
                            </li>
                            <li class="side-item">
                                <a href="<?= URL ?>public\html\itens\categoria_item\categoria_item.php">
                                    <i class="fa-solid fa-tags"></i>
                                    <span class="item-description">Categorias</span>
                                </a>
                            </li>
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/centroCusto/centro_custo.php">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <span class="item-description">Centro de Custo</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (temPermissao("deposito", $perfil, $permissoes)): ?>
                    <!-- Produtos com submenu -->
                    <li class="side-item has-submenu">
                        <a href="#" class="submenu-toggle">
                            <i class="fas fa-map-marker-alt"></i>
                            <span class="item-description">
                                Deposito
                            </span>
                            <!--  <i class="fa-solid fa-chevron-down submenu-arrow"></i>-->
                        </a>
                        <ul class="submenu">
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/deposito/cadastrar_deposito/cadastro_deposito.php">
                                    <i class="fas fa-warehouse"></i>
                                    <span class="item-description">Depositos</span>
                                </a>
                            </li>
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/localizacao/criacao_localizacao/cadastro_locatizacao.php">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                    <span class="item-description">Locatização</span>
                                </a>
                            </li>

                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (temPermissao("fornecedor", $perfil, $permissoes)): ?>
                    <li class="side-item has-submenu">
                        <a href="#" class="submenu-toggle">
                            <i class="fa-solid fa-handshake"></i>
                            <span class="item-description">
                                Fornecedor
                            </span>
                            <!--  <i class="fa-solid fa-chevron-down submenu-arrow"></i>-->
                        </a>
                        <ul class="submenu">
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/fornecedor/cadastrar_fornecedor/cadastrar_fornecedor.php">
                                    <i class="fa-solid fa-plus"></i>
                                    <span class="item-description">Cadastrar Fornecedor</span>
                                </a>
                            </li>
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/fornecedor/lista_fornecedor/lista_fornecedor.php">
                                    <i class="fa-solid fa-history"></i>
                                    <span class="item-description">Fornecedor Cadastrado</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (temPermissao("usuarios", $perfil, $permissoes)): ?>
                    <!-- Usuários com submenu -->
                    <li class="side-item has-submenu">
                        <a href="#" class="submenu-toggle">
                            <i class="fa-solid fa-user"></i>
                            <span class="item-description">
                                Usuários
                            </span>
                            <!--  <i class="fa-solid fa-chevron-down submenu-arrow"></i>-->
                        </a>
                        <ul class="submenu">
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/usuario/criar_user/criar_user.php">
                                    <i class="fa-solid fa-user-plus"></i>
                                    <span class="item-description">Novo Usuário</span>
                                </a>
                            </li>
                            <li class="side-item">
                                <a href="<?= URL ?>public/html/usuario/lista_user/users.php">
                                    <i class="fa-solid fa-users"></i>
                                    <span class="item-description">Listar Usuários</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php endif; ?>

                <?php if (temPermissao("configuracoes", $perfil, $permissoes)): ?>
                    <!-- Configurações com submenu -->
                    <li class="side-item has-submenu">
                        <a href="#" class="submenu-toggle">
                            <i class="fa-solid fa-gear"></i>
                            <span class="item-description">
                                Configurações
                            </span>
                            <!--  <i class="fa-solid fa-chevron-down submenu-arrow"></i>-->
                        </a>
                        <ul class="submenu">
                            <li class="side-item">
                                <a href="<?= URL ?>public\html\perfil\vivualizacao_perfil\perfil.php">
                                    <i class="fa-solid fa-id-card"></i>
                                    <span class="item-description">Perfil</span>
                                </a>
                            </li>
                           


                        </ul>
                    </li>
                <?php endif; ?>
            </ul> <!-- Fechamento da UL adicionada -->
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
            const submenuToggles = document.querySelectorAll(".submenu-toggle");

            function openSidebar() {
                sidebar.classList.add("open");
            }

            function closeSidebar() {
                sidebar.classList.remove("open");
                closeAllSubmenus();
            }

            function closeAllSubmenus() {
                document.querySelectorAll(".has-submenu.submenu-open").forEach(item => {
                    item.classList.remove("submenu-open");
                });
            }

            sidebar.addEventListener("mouseenter", openSidebar);

            let sidebarTimeout;
            sidebar.addEventListener("mouseleave", function () {
                sidebarTimeout = setTimeout(function () {
                    closeSidebar();
                }, 100);
            });

            sidebar.addEventListener("mouseenter", function () {
                if (sidebarTimeout) {
                    clearTimeout(sidebarTimeout);
                    sidebarTimeout = null;
                }
            });

            submenuToggles.forEach(toggle => {
                toggle.addEventListener("click", function (event) {
                    event.preventDefault();
                    const parentItem = this.closest(".has-submenu");
                    const wasOpen = parentItem.classList.contains("submenu-open");
                    closeAllSubmenus();
                    if (!wasOpen) {
                        parentItem.classList.add("submenu-open");
                    }
                });
            });
        });
    </script>
</body>

</html>