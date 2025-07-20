<?php
// Verifica se a constante já foi definida
if (!defined('URL')) {
    define('URL', '/estoque/');
    define('BASE_PATH', __DIR__ . '/estoque/');
    define('ASSETS_URL', URL . 'assets/');
}
?>