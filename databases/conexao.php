<?php
$host = "bancotcc";
$dbname = "estoquetcc";
$username = "tcc";
$password = "2025";


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    $pdo ->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

   //echo "Conexão Feita!";
}catch (PDOException $e) {
    // Capturar erro de conexão e exibir mensagem amigável
    echo "Erro na conexão: " . $e->getMessage();
}


?>