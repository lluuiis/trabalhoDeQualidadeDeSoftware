<?php require_once __DIR__ . '/../config/database.php'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestão de Produção</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body { padding-top: 5rem; }
        .main-container { padding: 2rem 1.5rem; }
    </style>
    </head>
<body>

<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand" href="/sistemaproducao/index.php">
            <img src="images/casa-icon.png" alt="Página Inicial" width="30" height="24">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav me-auto mb-2 mb-md-0">
                <li class="nav-item"><a class="nav-link" href="/sistemaproducao/index.php?page=clientes">Clientes</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Materiais</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php?page=materiais">Listar/Cadastrar</a></li>
                        <li><a class="dropdown-item" href="index.php?page=materiais/estoque">Controle de Estoque</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Produtos</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php?page=produtos">Listar/Cadastrar</a></li>
                        <li><a class="dropdown-item" href="index.php?page=produtos/producao">Registrar Produção</a></li>
                    </ul>
                </li>
                 <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Vendas</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php?page=vendas&action=novo">Registrar Nova Venda</a></li>
                        <li><a class="dropdown-item" href="index.php?page=vendas/list">Histórico de Vendas</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="index.php?page=contas_receber">Contas a Receber</a></li>
                <?php
                if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'administrador') {
                    echo '<li class="nav-item"><a class="nav-link" href="index.php?page=contas_pagar">Contas a Pagar</a></li>';
                }
                ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-expanded="false">Relatórios</a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="/sistemaproducao/index.php?page=relatorios/vendas">Vendas</a></li>
                        <li><a class="dropdown-item" href="/sistemaproducao/index.php?page=relatorios/lucro">Lucratividade</a></li>
                        <li><a class="dropdown-item" href="/sistemaproducao/index.php?page=relatorios/estoque">Mov. Estoque</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="/sistemaproducao/index.php?page=usuarios">Usuários</a></li>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sair</a>
                    </li>
                </ul>
            </ul>
        </div>
    </div>
</nav>
</body>
<main class="container main-container"></main>