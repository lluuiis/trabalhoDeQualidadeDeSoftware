<?php
// modulos/contas_pagar/actions.php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            if (empty($_POST['descricao']) || empty($_POST['valor']) || empty($_POST['data_vencimento'])) {
                throw new Exception("Todos os campos são obrigatórios.");
            }
            $stmt = $pdo->prepare("INSERT INTO contas_a_pagar (descricao, valor, data_vencimento, status) VALUES (?, ?, ?, 'pendente')");
            $stmt->execute([$_POST['descricao'], $_POST['valor'], $_POST['data_vencimento']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Conta a pagar cadastrada com sucesso!'];
            break;

        case 'update':
            $stmt = $pdo->prepare("UPDATE contas_a_pagar SET descricao = ?, valor = ?, data_vencimento = ? WHERE id = ?");
            $stmt->execute([$_POST['descricao'], $_POST['valor'], $_POST['data_vencimento'], $_POST['id']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Conta atualizada com sucesso!'];
            break;

        case 'marcar_paga':
            $stmt = $pdo->prepare("UPDATE contas_a_pagar SET status = 'paga' WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Conta marcada como paga!'];
            break;

        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM contas_a_pagar WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Conta excluída com sucesso!'];
            break;

        default:
            throw new Exception("Ação desconhecida.");
    }
} catch (Exception $e) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro: ' . $e->getMessage()];
}

header('Location: ../../index.php?page=contas_pagar');
exit();