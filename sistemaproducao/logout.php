<?php
session_start(); // Inicia a sessão para poder acessá-la.

// Remove todas as variáveis da sessão.
$_SESSION = array();

// Destrói a sessão.
session_destroy();

// Redireciona o usuário para a página de login.
header('Location: login.php');
exit();
?>