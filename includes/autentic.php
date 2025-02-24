<?php
 session_start();
 
define("URL", "estoque/");

 if (isset($_SESSION['logado']) && $_SESSION['logado'] === true) {
    header( "location:  ");
    exit;
}

if (isset($_GET["sair"])) {
    session_destroy();
    header("Location: " . URL . "index.php");
    exit();
  }
?>
