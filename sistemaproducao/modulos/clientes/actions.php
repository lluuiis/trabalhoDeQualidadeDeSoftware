<?php
// modulos/clientes/actions.php

session_start();

require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php?page=clientes');
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $telefone = $_POST['telefone'];
            $endereco = $_POST['endereco'];

            $stmt = $pdo->prepare("INSERT INTO clientes (nome, email, telefone, endereco) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nome, $email, $telefone, $endereco]);
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Cliente cadastrado com sucesso!'
            ];
            break;
        case 'update':
            $id = $_POST['id'];
            $nome = $_POST['nome'];
            $email = $_POST['email'];
            $telefone = $_POST['telefone'];
            $endereco = $_POST['endereco'];

            $stmt = $pdo->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ?, endereco = ? WHERE id = ?");
            $stmt->execute([$nome, $email, $telefone, $endereco, $id]);

            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Cliente atualizado com sucesso!'
            ];
            break;

        case 'delete':
            $id = $_POST['id'];
            
            $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->execute([$id]);

            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Cliente excluído com sucesso!'
            ];
            break;

        default:

            $_SESSION['message'] = [
                'type' => 'danger',
                'text' => 'Ação desconhecida!'
            ];
            break;
    }
} catch (PDOException $e) {

    $_SESSION['message'] = [
        'type' => 'danger',
        'text' => 'Erro ao processar a operação: ' . $e->getMessage()
    ];
}

header('Location: ../../index.php?page=clientes');
exit();