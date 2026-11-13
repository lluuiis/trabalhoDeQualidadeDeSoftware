<?php
// modulos/contas_receber/index.php 
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

$sql_where_clauses = ["vp.status = 'pendente'"];
$sql_params = [];
$cliente_id_filtrado = $_GET['cliente_id'] ?? null;
if (!empty($cliente_id_filtrado)) {
    $sql_where_clauses[] = "v.cliente_id = ?";
    $sql_params[] = $cliente_id_filtrado;
}
$where_condition = implode(' AND ', $sql_where_clauses);

$sql = "
    SELECT vp.*, v.id as venda_id, c.nome as cliente_nome
    FROM venda_pagamentos vp
    JOIN vendas v ON vp.venda_id = v.id
    JOIN clientes c ON v.cliente_id = c.id
    WHERE $where_condition
    ORDER BY vp.data_vencimento ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($sql_params);
$contas = $stmt->fetchAll();

$clientes_filtro = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome ASC")->fetchAll();
$cliente_filtrado_nome = '';
if($cliente_id_filtrado) {
    $stmt_cliente = $pdo->prepare("SELECT nome FROM clientes WHERE id = ?");
    $stmt_cliente->execute([$cliente_id_filtrado]);
    $cliente_filtrado_nome = $stmt_cliente->fetchColumn();
}

?>

<div class="no-print">
    <h3>Contas a Receber (Crediário / Pendentes)</h3>
    <form method="GET" class="form-filters border p-3 mb-4 rounded bg-light">
        <input type="hidden" name="page" value="contas_receber">
        <div class="row g-3 align-items-end">
            <div class="col-md-6">
                <label for="cliente_id" class="form-label"><strong>Pesquisar por Cliente</strong></label>
                <select class="form-select" id="cliente_id" name="cliente_id">
                    <option value="">-- Todos os Clientes --</option>
                    <?php foreach ($clientes_filtro as $cliente): ?>
                        <option value="<?= $cliente['id'] ?>" <?= ($cliente_id_filtrado == $cliente['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cliente['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
            <div class="col-md-3">
                <a href="index.php?page=contas_receber" class="btn btn-secondary w-100">Limpar Filtro</a>
            </div>
        </div>
    </form>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['message']['text']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>
</div>


<div class="printable-area">
    
    <h3>Relatório de Contas a Receber</h3>
    <?php if($cliente_filtrado_nome): ?>
        <h5>Cliente: <?= htmlspecialchars($cliente_filtrado_nome) ?></h5>
    <?php endif; ?>
    <p>Gerado em: <?= date('d/m/Y H:i:s') ?></p>
    <hr>
    
    <table class="table table-bordered">
        <thead class="table-light">
            <tr>
                <th style="width: 5%;">Venda ID</th>
                <th>Cliente</th>
                <th>Forma Pag.</th>
                <th>Valor</th>
                <th>Vencimento</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($contas) > 0): ?>
                <?php foreach($contas as $conta): ?>
                <tr>
                    <td>#<?= $conta['venda_id'] ?></td>
                    <td><?= htmlspecialchars($conta['cliente_nome']) ?></td>
                    <td><?= htmlspecialchars($conta['forma_pagamento']) ?></td>
                    <td>R$ <?= number_format($conta['valor'], 2, ',', '.') ?></td>
                    <td><?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Nenhuma conta a receber encontrada para o filtro selecionado.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>