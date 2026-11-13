<?php
// modulos/vendas/index.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

if (isset($_GET['action']) && $_GET['action'] === 'novo') {
    unset($_SESSION['venda_atual']);
}

if (!isset($_SESSION['venda_atual'])) {
    $_SESSION['venda_atual'] = [
        'cliente_id' => null,
        'produtos'   => [],
        'pagamentos' => []
    ];
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Adicionar um produto ao carrinho
    if (isset($_POST['add_produto'])) {
        $produto_id = $_POST['produto_id_add'];
        $quantidade = (int)($_POST['quantidade_add'] ?? 0);

        if (!empty($produto_id) && $quantidade > 0) {
            $stmt = $pdo->prepare("SELECT nome, preco_venda, estoque_atual FROM produtos WHERE id = ?");
            $stmt->execute([$produto_id]);
            $produto_db = $stmt->fetch();

            if ($produto_db) {
                $qtd_no_carrinho = 0;
                foreach ($_SESSION['venda_atual']['produtos'] as $item) {
                    if ($item['id'] == $produto_id) $qtd_no_carrinho += $item['qtd'];
                }
                if (($quantidade + $qtd_no_carrinho) > $produto_db['estoque_atual']) {
                    $erro = "Estoque insuficiente para '" . htmlspecialchars($produto_db['nome']) . "'";
                } else {
                    $_SESSION['venda_atual']['produtos'][] = ['id' => $produto_id, 'qtd' => $quantidade, 'nome' => $produto_db['nome'], 'preco' => $produto_db['preco_venda']];
                }
            }
        }
    }

    // Remover um produto do carrinho
    if (isset($_POST['remove_produto'])) {
        $index_remover = $_POST['remove_produto'];
        unset($_SESSION['venda_atual']['produtos'][$index_remover]);
        $_SESSION['venda_atual']['produtos'] = array_values($_SESSION['venda_atual']['produtos']); // Reindexa o array
    }

    // Adicionar um pagamento
    if (isset($_POST['add_pagamento'])) {
        $forma = $_POST['forma_pagamento_add'];
        $valor = (float)($_POST['valor_pagamento_add'] ?? 0);
        $vencimento = $_POST['vencimento_pagamento_add'];
        if (!empty($forma) && $valor > 0 && !empty($vencimento)) {
            $_SESSION['venda_atual']['pagamentos'][] = ['forma' => $forma, 'valor' => $valor, 'vencimento' => $vencimento];
        }
    }

    //Remover um pagamento
    if (isset($_POST['remove_pagamento'])) {
        $index_remover = $_POST['remove_pagamento'];
        unset($_SESSION['venda_atual']['pagamentos'][$index_remover]);
        $_SESSION['venda_atual']['pagamentos'] = array_values($_SESSION['venda_atual']['pagamentos']);
    }
    
    // Guarda o ID do cliente na sessão sempre que o formulário é postado
    if (isset($_POST['cliente_id'])) {
        $_SESSION['venda_atual']['cliente_id'] = $_POST['cliente_id'];
    }

    // Finalizar a Venda
    if (isset($_POST['finalizar_venda'])) {
        $venda_data = $_SESSION['venda_atual'];
        try {
            if (empty($venda_data['cliente_id']) || empty($venda_data['produtos']) || empty($venda_data['pagamentos'])) {
                throw new Exception("Dados insuficientes: Cliente, produtos e pagamentos são obrigatórios.");
            }

            $total_produtos = 0;
            foreach ($venda_data['produtos'] as $item) { $total_produtos += $item['preco'] * $item['qtd']; }
            $total_pagamentos = 0;
            foreach ($venda_data['pagamentos'] as $pag) { $total_pagamentos += $pag['valor']; }

            if (abs($total_produtos - $total_pagamentos) > 0.01) { throw new Exception("O total dos pagamentos (R$ $total_pagamentos) não corresponde ao total dos produtos (R$ $total_produtos)."); }

            $pdo->beginTransaction();

            // Inserir a venda principal
            $stmt_venda = $pdo->prepare("INSERT INTO vendas (cliente_id, valor_total, status_venda) VALUES (?, ?, 'ativa')");
            $stmt_venda->execute([$venda_data['cliente_id'], $total_produtos]);
            $venda_id = $pdo->lastInsertId();

            // Inserir produtos da venda e dar baixa no estoque
            $stmt_item = $pdo->prepare("INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario_venda, custo_unitario_producao) VALUES (?, ?, ?, ?, ?)");
            $stmt_baixa = $pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?");
            foreach($venda_data['produtos'] as $item) {
                // Validação final de estoque (segurança extra)
                $stmt_estoque = $pdo->prepare("SELECT estoque_atual FROM produtos WHERE id = ?");
                $stmt_estoque->execute([$item['id']]);
                if($stmt_estoque->fetchColumn() < $item['qtd']) { throw new Exception("Estoque esgotado para o produto " . $item['nome']); }

                $custo_unitario = calcularCustoProduto($pdo, $item['id']); // Função do config/database.php
                $stmt_item->execute([$venda_id, $item['id'], $item['qtd'], $item['preco'], $custo_unitario]);
                $stmt_baixa->execute([$item['qtd'], $item['id']]);
            }
            
            // Inserir os pagamentos
            $stmt_pag = $pdo->prepare("INSERT INTO venda_pagamentos (venda_id, forma_pagamento, valor, data_vencimento, status) VALUES (?, ?, ?, ?, ?)");
            foreach($venda_data['pagamentos'] as $pag) {
                $status = (in_array($pag['forma'], ['Crediário', 'Boleto'])) ? 'pendente' : 'pago';
                $stmt_pag->execute([$venda_id, $pag['forma'], $pag['valor'], $pag['vencimento'], $status]);
            }

            $pdo->commit();
            unset($_SESSION['venda_atual']);
            $_SESSION['message'] = ['type' => 'success', 'text' => 'Venda #' . $venda_id . ' registrada com sucesso!'];
            echo '<script>window.location.href = "index.php?page=vendas/list";</script>';
            exit();

        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $erro = 'Erro ao finalizar venda: ' . $e->getMessage();
        }
    }
}

$clientes = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC")->fetchAll();
$produtos = $pdo->query("SELECT * FROM produtos WHERE estoque_atual > 0 ORDER BY nome ASC")->fetchAll();

$total_produtos_carrinho = 0;
foreach ($_SESSION['venda_atual']['produtos'] as $item) { $total_produtos_carrinho += $item['preco'] * $item['qtd']; }
$total_pagamentos_carrinho = 0;
foreach ($_SESSION['venda_atual']['pagamentos'] as $pag) { $total_pagamentos_carrinho += $pag['valor']; }
$saldo_a_pagar = $total_produtos_carrinho - $total_pagamentos_carrinho;

$carrinho_qtds = [];
foreach ($_SESSION['venda_atual']['produtos'] as $item) {
    if (!isset($carrinho_qtds[$item['id']])) {
        $carrinho_qtds[$item['id']] = 0;
    }
    $carrinho_qtds[$item['id']] += $item['qtd'];
}
?>

<h3>Registrar Nova Venda</h3>
<p class="text-muted">Abordagem de processamento via PHP. A página será recarregada a cada ação.</p>
<hr>

<?php if ($erro): ?><div class="alert alert-danger"><?= $erro ?></div><?php endif; ?>

<form method="POST" action="index.php?page=vendas">
    <div class="row">
        <div class="col-lg-8">
            <div class="mb-3"><label for="cliente_id" class="form-label">Cliente</label>
                <select class="form-select" id="cliente_id" name="cliente_id" required onchange="this.form.submit()">
                    <option value="">Selecione...</option>
                    <?php foreach ($clientes as $cliente):?>
                        <option value="<?=$cliente['id']?>" <?= ($_SESSION['venda_atual']['cliente_id'] == $cliente['id'] ? 'selected' : '') ?>>
                            <?=htmlspecialchars($cliente['nome'])?>
                        </option>
                    <?php endforeach;?>
                </select>
            </div>
            
            <fieldset class="border p-2 mb-3"><legend class="float-none w-auto p-2 h6">Adicionar Produtos</legend>
                <div class="row align-items-end">
                    <div class="col-md-6"><label for="produto_id_add" class="form-label">Produto</label>
                        <select name="produto_id_add" id="produto_id_add" class="form-select"><option value="">Selecione...</option>
                        <?php foreach ($produtos as $produto):
                         $qtd_no_carrinho = $carrinho_qtds[$produto['id']] ?? 0;
                                $estoque_disponivel = $produto['estoque_atual'] - $qtd_no_carrinho;
                                if ($estoque_disponivel > 0):
                        ?>
                          <option value="<?=$produto['id']?>">
                            <?=htmlspecialchars($produto['nome'])?> (Estoque: <?= $estoque_disponivel ?>)
                        </option>
                    <?php endif;
                    endforeach;
                    ?>
                        </select>
                    </div>
                    <div class="col-md-3"><label for="quantidade_add" class="form-label">Qtd</label><input type="number" name="quantidade_add" id="quantidade_add" class="form-control" min="1"></div>
                    <div class="col-md-3"><button type="submit" name="add_produto" class="btn btn-secondary w-100">Adicionar</button></div>
                </div>
            </fieldset>
            
            <h5>Itens da Venda</h5>
            <table class="table"><thead class="table-light"><tr><th>Produto</th><th>Qtd</th><th>Preço Un.</th><th>Subtotal</th><th>Ação</th></tr></thead>
                <tbody>
                    <?php if (empty($_SESSION['venda_atual']['produtos'])): ?>
                        <tr><td colspan="5" class="text-center text-muted">Nenhum produto adicionado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($_SESSION['venda_atual']['produtos'] as $index => $item): ?>
                        <tr>
                            <td><?=htmlspecialchars($item['nome'])?></td><td><?=$item['qtd']?></td><td>R$ <?=number_format($item['preco'], 2, ',', '.')?></td><td>R$ <?=number_format($item['preco'] * $item['qtd'], 2, ',', '.')?></td>
                            <td><button type="submit" name="remove_produto" value="<?=$index?>" class="btn btn-danger btn-sm" title="Remover Item">X</button></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="col-lg-4">
            <div class="card bg-light"><div class="card-body">
                <h5 class="card-title">Resumo e Pagamentos</h5>
                <div class="d-flex justify-content-between align-items-center mb-3"><span class="h4">Total Produtos:</span><span class="h4">R$ <?=number_format($total_produtos_carrinho, 2, ',', '.')?></span></div>
                <fieldset class="border p-2 mb-3"><legend class="float-none w-auto p-2 h6">Adicionar Pagamento</legend>
                    <div class="mb-2"><label for="forma-pagamento" class="form-label">Forma</label><select name="forma_pagamento_add" id="forma-pagamento" class="form-select"><option>Dinheiro</option><option>Cartão Débito</option><option>Cartão Crédito</option><option>Pix</option><option>Boleto</option><option>Crediário</option></select></div>
                    <div class="mb-2"><label for="valor-pagamento" class="form-label">Valor</label><input type="number" name="valor_pagamento_add" id="valor-pagamento" step="0.01" class="form-control"></div>
                    <div class="mb-2"><label for="vencimento-pagamento" class="form-label">Vencimento</label><input type="date" name="vencimento_pagamento_add" id="vencimento-pagamento" class="form-control" value="<?=date('Y-m-d')?>"></div>
                    <button type="submit" name="add_pagamento" class="btn btn-info w-100 text-white">Adicionar Pagamento</button>
                </fieldset>
                <h6>Pagamentos Lançados</h6>
                <ul class="list-group mb-3">
                    <?php if (empty($_SESSION['venda_atual']['pagamentos'])): ?>
                        <li class="list-group-item text-muted">Nenhum pagamento adicionado.</li>
                    <?php else: ?>
                        <?php foreach($_SESSION['venda_atual']['pagamentos'] as $index => $pag): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center"><span><?=htmlspecialchars($pag['forma'])?> (<?=date('d/m/y', strtotime($pag['vencimento']))?>)</span><span class="fw-bold">R$ <?=number_format($pag['valor'], 2, ',', '.')?></span><button type="submit" name="remove_pagamento" value="<?=$index?>" class="btn-close" aria-label="Remover Pagamento"></button></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <div class="d-flex justify-content-between align-items-center mb-3 fw-bold"><span>Total Pagamentos:</span><span>R$ <?=number_format($total_pagamentos_carrinho, 2, ',', '.')?></span></div>
                <div class="alert <?= abs($saldo_a_pagar) > 0.01 ? 'alert-danger' : 'alert-success' ?>">SALDO: <strong>R$ <?=number_format($saldo_a_pagar, 2, ',', '.')?></strong></div>
                <button type="submit" name="finalizar_venda" class="btn btn-success btn-lg w-100" <?= (abs($saldo_a_pagar) > 0.01 || $total_produtos_carrinho == 0) ? 'disabled' : '' ?>>Finalizar Venda</button>
            </div></div>
        </div>
    </div>
</form>