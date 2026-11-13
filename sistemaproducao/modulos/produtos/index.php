<?php
// modulos/produtos/index.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }
$is_admin = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'administrador');

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message']['type']) . '">' . htmlspecialchars($_SESSION['message']['text']) . '</div>';
    unset($_SESSION['message']);
}

$materiais = $pdo->query("SELECT * FROM materiais ORDER BY nome ASC")->fetchAll();
$produtos = $pdo->query("SELECT * FROM produtos ORDER BY nome ASC")->fetchAll();
?>

<h3>Cadastro de Produtos</h3>
<p>Cadastre um novo produto e vincule os materiais necessários para a sua produção.</p>
<hr>
<form method="POST" action="modulos/produtos/actions.php">
    <input type="hidden" name="action" value="create">
    <div class="row g-3">
        <div class="col-md-8">
            <label for="nome" class="form-label">Nome do Produto</label>
            <input type="text" class="form-control" id="nome" name="nome" required>
        </div>
        <div class="col-md-4">
            <label for="preco_venda" class="form-label">Preço de Venda (R$)</label>
            <input type="number" step="0.01" class="form-control" id="preco_venda" name="preco_venda" required>
        </div>
    </div>
    
    <h5 class="mt-4">Vincular Materiais (Receita de Produção)</h5>
    <p><small>Marque os materiais e especifique a quantidade para produzir <strong>UMA</strong> unidade deste produto.</small></p>
    
    <div class="row">
        <?php foreach ($materiais as $index => $material): ?>
        <div class="col-md-6 mb-2">
            <div class="input-group">
                <div class="input-group-text">
                    <input class="form-check-input mt-0" type="checkbox" name="materiais[<?= $index ?>][id]" value="<?= $material['id'] ?>">
                </div>
                <span class="input-group-text flex-grow-1"><?= htmlspecialchars($material['nome']) ?></span>
                <span class="input-group-text">Qtd:</span>
                <input type="number" step="0.01" class="form-control" name="materiais[<?= $index ?>][qtd]" placeholder="0.00">
                <span class="input-group-text"><?= htmlspecialchars($material['unidade_medida']) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <button type="submit" name="cadastrar_produto" class="btn btn-primary mt-3">Cadastrar Produto</button>
</form>

<h3 class="mt-5">Produtos Cadastrados</h3>
<hr>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Produto</th>
            <th>Estoque</th>
            <th>Preço Venda</th>
            <?php if ($is_admin):?>
            <th>Custo Produção</th>
            <th>Lucro Bruto (R$)</th>
            <th>Lucro Bruto (%)</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($produtos as $produto): ?>
        <tr>
            <td><?= htmlspecialchars($produto['nome']) ?></td>
            <td><?= $produto['estoque_atual'] ?> un</td>
            <td>R$ <?= number_format($produto['preco_venda'], 2, ',', '.') ?></td>
            
            <?php 
            if ($is_admin):
                $custo_producao = calcularCustoProduto($pdo, $produto['id']);
                $lucro_valor = $produto['preco_venda'] - $custo_producao;
                $lucro_percentual = ($custo_producao > 0) ? ($lucro_valor / $custo_producao) * 100 : 0;
            ?>
            <td>R$ <?= number_format($custo_producao, 2, ',', '.') ?></td>
            <td>R$ <?= number_format($lucro_valor, 2, ',', '.') ?></td>
            <td><?= number_format($lucro_percentual, 1, ',', '.') ?> %</td>
            <?php endif; ?>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>