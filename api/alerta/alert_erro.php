<?php
// Exibe uma mensagem qualquer (apenas para teste)

/* Define a sessão com os dados do aviso
$_SESSION['aviso'] = [
    'icon' => "success",
    'title' => "Usuário Cadastrado",
    'text' => "Usuário cadastrado com sucesso!"
];
*/
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Inclui o SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    // Verifica se há um aviso para exibir
    if (isset($_SESSION['aviso'])) {
        echo "<script>
            Swal.fire({
                icon: '{$_SESSION['aviso']['icon']}',
                title: '{$_SESSION['aviso']['title']}',
                text: '{$_SESSION['aviso']['text']}',
                confirmButtonText: 'OK'
            });
        </script>";

        // Remove o aviso após exibir
        unset($_SESSION['aviso']);
    }
    ?>
</body>

</html>