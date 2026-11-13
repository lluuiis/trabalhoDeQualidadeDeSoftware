<?php
// modulos/materiais/actions.php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'create':
            $stmt = $pdo->prepare("INSERT INTO materiais (nome, unidade_medida, preco_custo, estoque_atual) VALUES (?, ?, ?, 0)");
            $stmt->execute([$_POST['nome'], $_POST['unidade_medida'], $_POST['preco_custo']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Material cadastrado com sucesso!'];
            header('Location: ../../index.php?page=materiais');
            break;

        case 'update':
            $stmt = $pdo->prepare("UPDATE materiais SET nome = ?, unidade_medida = ?, preco_custo = ? WHERE id = ?");
            $stmt->execute([$_POST['nome'], $_POST['unidade_medida'], $_POST['preco_custo'], $_POST['id']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Material atualizado com sucesso!'];
            header('Location: ../../index.php?page=materiais');
            break;
        case 'delete':
            $stmt = $pdo->prepare("DELETE FROM materiais WHERE id = ?");
            $stmt->execute([$_POST['id']]);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Material excluído com sucesso!'];
            break;    

        case 'movimentar_estoque':
            $material_id = $_POST['material_id'];
            $quantidade = (float)$_POST['quantidade'];
            $tipo = $_POST['tipo_movimentacao'];
            $obs = $_POST['observacao'];

            if ($quantidade <= 0) { throw new Exception("A quantidade deve ser maior que zero."); }

            $pdo->beginTransaction();

            if ($tipo == 'entrada') {
                $sql_update = "UPDATE materiais SET estoque_atual = estoque_atual + ? WHERE id = ?";
            } else { 
                $stmt_check = $pdo->prepare("SELECT estoque_atual FROM materiais WHERE id = ? FOR UPDATE");
                $stmt_check->execute([$material_id]);
                $estoque_atual = $stmt_check->fetchColumn();

                if ($estoque_atual < $quantidade) {
                    throw new Exception("Estoque insuficiente para registrar a saída.");
                }
                $sql_update = "UPDATE materiais SET estoque_atual = estoque_atual - ? WHERE id = ?";
            }
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([$quantidade, $material_id]);

            $stmt_insert = $pdo->prepare(
                "INSERT INTO movimentacao_estoque (material_id, tipo_movimentacao, quantidade, observacao) VALUES (?, ?, ?, ?)"
            );
            $stmt_insert->execute([$material_id, $tipo, $quantidade, $obs]);
            $pdo->commit();

            $_SESSION['message'] = ['type' => 'success', 'text' => 'Movimentação de estoque registrada com sucesso!'];
            header('Location: ../../index.php?page=materiais/estoque');
            break;

        default:
            throw new Exception("Ação desconhecida.");
    }
} catch (Exception $e) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro: ' . $e->getMessage()];
    header('Location: ../../index.php?page=' . $redirect_page);
}

exit();