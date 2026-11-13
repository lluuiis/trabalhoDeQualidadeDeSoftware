<?php
// modulos/materiais/estoque.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message']['type']) . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['message']['text']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']);
}

$materiais_select = $pdo->query("SELECT id, nome FROM materiais ORDER BY nome ASC")->fetchAll();

$movimentacoes = $pdo->query("
    SELECT m.nome, me.tipo_movimentacao, me.quantidade, me.data_movimentacao, me.observacao 
    FROM movimentacao_estoque me
    JOIN materiais m ON me.material_id = m.id
    ORDER BY me.data_movimentacao DESC LIMIT 20
")->fetchAll();
?>

<h3>Controle de Estoque</h3>
<p>Registre entradas por compra ou saídas por ajuste/perda de materiais.</p>
<hr>

<form method="POST" action="modulos/materiais/actions.php" class="col-md-10">
    <input type="hidden" name="action" value="movimentar_estoque">
    <div class="row g-3">
        <div class="col-md-5">
            <label for="material_id" class="form-label">Material</label>
            <select class="form-select" id="material_id" name="material_id" required>
                <option value="">Selecione...</option>
                <?php foreach ($materiais_select as $material): ?>
                    <option value="<?= $material['id'] ?>"><?= htmlspecialchars($material['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
             <label for="quantidade" class="form-label">Quantidade</label>
            <input type="number" step="0.01" class="form-control" id="quantidade" name="quantidade" required>
        </div>
        <div class="col-md-3">
            <label class="form-label">Tipo de Movimentação</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_movimentacao" id="entrada" value="entrada" checked>
                <label class="form-check-label" for="entrada">Entrada</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_movimentacao" id="saida" value="ajuste_negativo">
                <label class="form-check-label" for="saida">Saída (Ajuste)</label>
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-success w-100">Registrar</button>
        </div>
        <div class="col-12">
            <label for="observacao" class="form-label">Observação (Opcional)</label>
            <input type="text" class="form-control" id="observacao" name="observacao" placeholder="Ex: Compra do fornecedor X, ajuste de inventário, etc.">
        </div>
    </div>
</form>

<h3 class="mt-5">Últimas Movimentações de Estoque</h3>
<hr>
<table class="table table-sm table-bordered">
    <thead>
        <tr>
            <th>Data</th>
            <th>Material</th>
            <th>Tipo</th>
            <th>Quantidade</th>
            <th>Observação</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($movimentacoes as $mov): ?>
            <tr class="<?= $mov['tipo_movimentacao'] == 'entrada' ? 'table-success' : 'table-danger' ?>">
                <td><?= date('d/m/Y H:i', strtotime($mov['data_movimentacao'])) ?></td>
                <td><?= htmlspecialchars($mov['nome']) ?></td>
                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $mov['tipo_movimentacao']))) ?></td>
                <td><?= number_format($mov['quantidade'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($mov['observacao']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>