<?php
// index.php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    // Redireciona para a página de login
    header('Location: login.php');
    exit();
}
define('IS_RUNNING', true);

include 'includes/header.php';

$page = $_GET['page'] ?? 'home';

switch ($page) {
    case 'clientes':
        include 'modulos/clientes/index.php';
        break;
    case 'clientes/edit': 
        include 'modulos/clientes/edit.php';
        break;
    case 'clientes/delete': 
        include 'modulos/clientes/delete.php';
        break;    
    case 'materiais':
        include 'modulos/materiais/index.php';
        break;
    case 'materiais/estoque': 
        include 'modulos/materiais/estoque.php';
        break;
    case 'materiais/edit': 
        include 'modulos/materiais/edit.php';
        break;
    case 'materiais/delete': 
        include 'modulos/materiais/delete.php';
        break;    
        case 'produtos':
        include 'modulos/produtos/index.php';
        break;
    case 'produtos/producao':
        include 'modulos/produtos/producao.php';
        break;    
    case 'vendas':
        include 'modulos/vendas/index.php';
        break;
    case 'vendas/list':
        include 'modulos/vendas/list.php';
        break;
    case 'contas_pagar':
        include 'modulos/contas_pagar/index.php';
        break;
    case 'contas_pagar/edit':
        include 'modulos/contas_pagar/edit.php';
        break;
    case 'contas_pagar/delete':
        include 'modulos/contas_pagar/delete.php';
        break;    
    case 'contas_receber':
        include 'modulos/contas_receber/index.php';
        break;    
    case 'relatorios/vendas':
        include 'modulos/relatorios/vendas.php';
        break;
    case 'relatorios/lucro':
        include 'modulos/relatorios/lucro.php';
        break;
    case 'relatorios/estoque':
        include 'modulos/relatorios/estoque.php';
        break;
    case 'usuarios':
        include 'modulos/usuario/index.php';
        break;
    case 'usuarios/cadastro': 
        include 'modulos/usuario/cadastro_usuario.php';
        break;
    case 'usuarios/edit': 
        include 'modulos/usuario/edit.php';
        break;
    case 'usuarios/delete':
        include 'modulos/usuario/delete.php';
        break;    
        default:
        echo '<h1>Bem-vindo ao Sistema de Gestão!</h1><p>Selecione uma opção no menu acima para começar.</p>';
        break;
}
include 'includes/footer.php';
?>