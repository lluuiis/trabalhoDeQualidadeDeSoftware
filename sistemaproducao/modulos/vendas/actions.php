<?php
// modulos/vendas/actions.php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    die('Acesso negado.');
}

$action = $_REQUEST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $action === 'get_details') {
    $venda_id = $_GET['id'];
    $stmt = $pdo->prepare("
        SELECT vp.quantidade, vp.preco_unitario_venda, p.nome as produto_nome 
        FROM venda_produtos vp
        JOIN produtos p ON vp.produto_id = p.id
        WHERE vp.venda_id = ?
    ");
    $stmt->execute([$venda_id]);
    $detalhes = $stmt->fetchAll();
    
    header('Content-Type: application/json');
    echo json_encode($detalhes);
    exit(); 
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $redirect_page = 'vendas'; 

    try {
        switch ($action) {
                case 'registrar_venda_v2':
                $pdo->beginTransaction();
                
                // Validações
                $cliente_id = $_POST['cliente_id'];
                $produtos_venda = $_POST['produtos'] ?? [];
                $pagamentos = $_POST['pagamentos'] ?? [];
                if (empty($cliente_id) || empty($produtos_venda) || empty($pagamentos)) { throw new Exception("Dados insuficientes para registrar a venda."); }

                // Calcula totais e valida
                $total_produtos = 0;
                foreach ($produtos_venda as $item) {
                    $stmt_prod = $pdo->prepare("SELECT preco_venda FROM produtos WHERE id = ?");
                    $stmt_prod->execute([$item['id']]);
                    $total_produtos += $stmt_prod->fetchColumn() * $item['qtd'];
                }

                $total_pagamentos = 0;
                foreach ($pagamentos as $pag) { $total_pagamentos += $pag['valor']; }

                if (abs($total_produtos - $total_pagamentos) > 0.01) { throw new Exception("O total dos pagamentos (R$ $total_pagamentos) não corresponde ao total dos produtos (R$ $total_produtos)."); }

                // Insere a venda principal
                $stmt_venda = $pdo->prepare("INSERT INTO vendas (cliente_id, valor_total, status_venda) VALUES (?, ?, 'ativa')");
                $stmt_venda->execute([$cliente_id, $total_produtos]);
                $venda_id = $pdo->lastInsertId();

                $stmt_item = $pdo->prepare("INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario_venda, custo_unitario_producao) VALUES (?, ?, ?, ?, ?)");
                $stmt_baixa = $pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?");

                foreach($produtos_venda as $item) {
                    $stmt_preco = $pdo->prepare("SELECT preco_venda FROM produtos WHERE id = ?");
                    $stmt_preco->execute([$item['id']]);
                    $preco_unitario = $stmt_preco->fetchColumn();
                    $custo_unitario = calcularCustoProduto($pdo, $item['id']);

                    $stmt_item->execute([$venda_id, $item['id'], $item['qtd'], $preco_unitario, $custo_unitario]);
                    $stmt_baixa->execute([$item['qtd'], $item['id']]);
                }

                $stmt_pag = $pdo->prepare("INSERT INTO venda_pagamentos (venda_id, forma_pagamento, valor, data_vencimento, status) VALUES (?, ?, ?, ?, ?)");
                foreach($pagamentos as $pag) {
                    // Se a forma de pagamento não for 'Crediário' ou 'Boleto', consideramos 'pago' na hora.
                    $status = (in_array($pag['forma'], ['Crediário', 'Boleto'])) ? 'pendente' : 'pago';
                    $stmt_pag->execute([$venda_id, $pag['forma'], $pag['valor'], $pag['vencimento'], $status]);
                }

                $pdo->commit();
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Venda #' . $venda_id . ' registrada com sucesso!'];
                header('Location: ../../index.php?page=vendas/list');
                exit();
                break;
            case 'estornar_venda':
                                $redirect_page = 'vendas/list';
                $venda_id = $_POST['venda_id'];
                
                $pdo->beginTransaction();

                // 1. Verifica se a venda ainda está ativa
                $stmt_check = $pdo->prepare("SELECT status_venda FROM vendas WHERE id = ? FOR UPDATE");
                $stmt_check->execute([$venda_id]);
                if ($stmt_check->fetchColumn() !== 'ativa') {
                    throw new Exception("Esta venda já foi estornada ou não pode ser alterada.");
                }

                // 2. Busca todos os produtos e quantidades da venda
                $stmt_itens = $pdo->prepare("SELECT produto_id, quantidade FROM venda_produtos WHERE venda_id = ?");
                $stmt_itens->execute([$venda_id]);
                $itens_da_venda = $stmt_itens->fetchAll();

                // 3. Devolve cada produto ao estoque
                $stmt_retorno = $pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?");
                foreach ($itens_da_venda as $item) {
                    $stmt_retorno->execute([$item['quantidade'], $item['produto_id']]);
                }

                // 4. Cancela todos os pagamentos associados a esta venda
                $stmt_cancela_pag = $pdo->prepare("UPDATE venda_pagamentos SET status = 'cancelado' WHERE venda_id = ?");
                $stmt_cancela_pag->execute([$venda_id]);

                // 5. Marca a venda principal como 'estornada'
                $stmt_estorno = $pdo->prepare("UPDATE vendas SET status_venda = 'estornada' WHERE id = ?");
                $stmt_estorno->execute([$venda_id]);

                $pdo->commit();
                $_SESSION['message'] = ['type' => 'success', 'text' => 'Venda #' . $venda_id . ' estornada com sucesso! Estoque e pagamentos foram atualizados.'];
                break;

            default:
                throw new Exception("Ação POST desconhecida.");
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro: ' . $e->getMessage()];
    }
    
    header('Location: ../../index.php?page=' . $redirect_page);
    exit();
}
header('Location: ../../index.php');
exit();