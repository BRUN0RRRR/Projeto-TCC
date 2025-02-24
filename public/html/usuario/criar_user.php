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
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<style>
    .card {
        background-color: rgb(228, 228, 228);
        /* Branco suave */
        color: #333;
        /* Texto escuro para contraste */
        border-radius: 12px;
        /* Cantos arredondados */
        padding: 20px;
        /* Espaçamento interno */
        box-shadow: 0px 2px 10px rgba(255, 255, 255, 0.1);
        /* Sombra sutil */
    }

    #formUsuario {
        display: none;
        /* Começa oculto */
        opacity: 0;
        transform: translateY(-10px);
        transition: opacity 0.4s ease-in-out, transform 0.4s ease-in-out;
    }

    #formUsuario.mostrar {
        display: block;
        opacity: 1;
        transform: translateY(0);
    }

    #btnCriarUsuario {
        left: 100%;
        position: sticky;
    }

    .form-control {
        border: 2px solid black;
    }

    .custom-table {
        background-color: #ffffff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: -3px 0px 6px rgb(165 165 165);
        ;
    }

    .custom-table thead {
        background-color: #2c3e50;
        /* Azul escuro */
        color: #ffffff;
    }

    .custom-table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.9rem;
        padding: 12px;
        background: #c5c5c5;
    }

    .custom-table tbody tr {
        transition: background-color 0.3s ease;
    }

    .custom-table tbody tr:hover {
        background-color: #f8f9fa;
        /* Cinza claro ao passar o mouse */
    }

    .custom-table td {
        padding: 12px;
        vertical-align: middle;
        border-color: #e9ecef;
        /* Cor das bordas */
    }

    .custom-table tbody tr:nth-child(even) {
        background-color: #f8f9fa;
        /* Zebrado: linhas pares com fundo cinza claro */
    }

    .btn-edit {
        background-color: rgba(17, 17, 17, 0.86);
        /* Azul */
        color: #ffffff;
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        transition: background-color 0.3s ease;
    }

    .btn-edit:hover {
        background-color: rgb(94, 94, 94);
        /* Azul mais escuro ao passar o mouse */
    }

    .text-center {
        text-align: center;
    }
</style>



<body>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let btn = document.getElementById("btnCriarUsuario");
            let form = document.getElementById("formUsuario");

            btn.addEventListener("click", function () {
                if (form.classList.contains("mostrar")) {
                    form.style.opacity = "0";
                    setTimeout(() => {
                        form.classList.remove("mostrar");
                        form.style.display = "none";
                    }, 400); // Tempo igual ao do CSS
                } else {
                    form.style.display = "block";
                    setTimeout(() => {
                        form.classList.add("mostrar");
                        form.style.opacity = "1";
                    }, 10);
                }
            });
        });
    </script>


    <main>
        <div class="container mt-5">
            <button id="btnCriarUsuario" class="btn btn-success">Criar Usuário</button>

            <div id="formUsuario" class="card p-4 shadow-sm mt-3" style="display: none;">
                <h3 class="mb-3"><i class="bi bi-person-add"></i> Criar Usuário</h3>
                <form action="config/config_user.php" method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" name="nome">
                        </div>


                        <div class="col-md-6">
                            <label class="form-label">Login</label>
                            <input type="text" class="form-control" name="login">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Setor</label>
                            <input type="text" class="form-control" name="setor">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Senha</label>
                            <input type="password" class="form-control" name="senha">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirmar Senha</label>
                            <input type="password" class="form-control" name="senhaCon">
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-md-6">
                            <label class="form-label">Grupo</label>
                            <select class="form-select" name="grupo">
                                <option selected>Operador</option>
                                <option>Administrador</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Perfil</label>
                            <select class="form-select" name="perfil">
                                <option selected>Operador</option>
                                <option>Super Usuário</option>
                                <option>Gerente</option>
                                <option>Analista</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-4">Salvar alterações</button>
                </form>
            </div>


            <div class="card mt-4 p-4 shadow-sm">
                <h4 class="mb-3">Usuários cadastrados</h4>
                <table class="table table-bordered table-striped table-hover custom-table">
                    <thead>
                        <tr>
                            <th>LOGIN</th>
                            <th>NOME COMPLETO</th>
                            <th>EMAIL</th>
                            <th>SETOR</th>
                            <th>GRUPO</th>
                            <th>PERFIL</th>
                            <th>AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($resultados) {
                            foreach ($resultados as $result) {
                                echo "
                        <tr>
                            <td>" . htmlspecialchars($result['login']) . "</td>
                            <td>" . htmlspecialchars($result['nome']) . "</td>
                            <td>" . htmlspecialchars($result['email']) . "</td>
                            <td>" . htmlspecialchars($result['setor']) . "</td>
                            <td>" . htmlspecialchars($result['grupo']) . "</td>
                            <td>" . htmlspecialchars($result['perfil']) . "</td>
                            <td>
                                <button type='submit' class='btn btn-edit'>Editar</button>
                            </td>
                        </tr>";
                            }
                        } else {
                            echo "
                    <tr>
                        <td colspan='6' class='text-center'>Nenhum usuário encontrado.</td>
                    </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </main>
</body>

</html>