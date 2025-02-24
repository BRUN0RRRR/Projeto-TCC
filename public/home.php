<?php
session_start();
if ($_SESSION['logado'] != true) {
    header("location: ../index.php");
    exit;
}

define("URL", "/estoque/");


include "../includes/sidebar.php";



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - Home</title>
</head>

<body>
    <main>

    </main>

</body>

</html>