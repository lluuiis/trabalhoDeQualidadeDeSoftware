<?php
// modulos/produtos/actions.php
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
            $pdo->beginTransaction();
            
            // 1. Inserir o produto
            $stmt_prod = $pdo->prepare("INSERT INTO produtos (nome, preco_venda) VALUES (?, ?)");
            $stmt_prod->execute([$_POST['nome'], $_POST['preco_venda']]);
            $produto_id = $pdo->lastInsertId();

            // 2. Associar os materiais
            $stmt_assoc = $pdo->prepare("INSERT INTO produto_materiais (produto_id, material_id, quantidade_utilizada) VALUES (?, ?, ?)");
            foreach ($_POST['materiais'] as $material_data) {
                if (isset($material_data['id']) && !empty($material_data['qtd']) && $material_data['qtd'] > 0) {
                    $stmt_assoc->execute([$produto_id, $material_data['id'], $material_data['qtd']]);
                }
            }
            
            $pdo->commit();
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Produto cadastrado com sucesso!'];
            header('Location: ../../index.php?page=produtos');
            break;

        case 'produzir':
            $produto_id = $_POST['produto_id'];
            $quantidade_a_produzir = (int)$_POST['quantidade'];

            if ($quantidade_a_produzir <= 0) { throw new Exception("A quantidade a produzir deve ser maior que zero."); }

            // Inicia a transação
            $pdo->beginTransaction();

            // 1. Pega a "receita" do produto
            $stmt_receita = $pdo->prepare("SELECT pm.material_id, m.nome, pm.quantidade_utilizada, m.estoque_atual FROM produto_materiais pm JOIN materiais m ON pm.material_id = m.id WHERE pm.produto_id = ?");
            $stmt_receita->execute([$produto_id]);
            $receita = $stmt_receita->fetchAll();

            if (empty($receita)) { throw new Exception("Este produto não tem materiais vinculados (receita)."); }

            // 2. Verifica se há estoque de TODOS os materiais necessários ANTES de qualquer alteração
            foreach ($receita as $item) {
                $necessario = $item['quantidade_utilizada'] * $quantidade_a_produzir;
                if ($item['estoque_atual'] < $necessario) {
                    throw new Exception("Estoque insuficiente de '" . htmlspecialchars($item['nome']) . "'. Necessário: $necessario / Disponível: " . $item['estoque_atual']);
                }
            }
            
            // 3. Se o estoque está OK, dá baixa nos materiais e registra a movimentação
            foreach ($receita as $item) {
                $quantidade_a_baixar = $item['quantidade_utilizada'] * $quantidade_a_produzir;

                // Dá baixa no estoque do material
                $stmt_baixa = $pdo->prepare("UPDATE materiais SET estoque_atual = estoque_atual - ? WHERE id = ?");
                $stmt_baixa->execute([$quantidade_a_baixar, $item['material_id']]);

                // Registra no histórico de movimentação
                $stmt_hist = $pdo->prepare("INSERT INTO movimentacao_estoque (material_id, tipo_movimentacao, quantidade, observacao) VALUES (?, 'saida_producao', ?, ?)");
                $stmt_hist->execute([$item['material_id'], $quantidade_a_baixar, "Produção de $quantidade_a_produzir unidade(s) do produto #$produto_id"]);
            }

            // 4. Incrementa o estoque do produto final
            $stmt_add_prod = $pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?");
            $stmt_add_prod->execute([$quantidade_a_produzir, $produto_id]);

            // Se tudo deu certo, confirma a transação
            $pdo->commit();
            $_SESSION['message'] = ['type' => 'success', 'text' => "$quantidade_a_produzir unidade(s) do produto produzida(s) com sucesso!"];
            header('Location: ../../index.php?page=produtos/producao');
            break;
            
        default:
            throw new Exception("Ação desconhecida.");
           
    }
} catch (Exception $e) {
    // Se qualquer erro ocorreu, desfaz TUDO
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro: ' . $e->getMessage()];
    // Redireciona para a última página de onde a ação pode ter vindo
    $redirect_page = ($action == 'produzir') ? 'produtos/producao' : 'produtos';
    header('Location: ../../index.php?page=' . $redirect_page);
}

exit();