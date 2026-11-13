<?php
// modulos/relatorios/lucro.php
@session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') { die('Acesso negado.'); }

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');

// --- CONSULTA PRINCIPAL ---
// Agrupa por venda para calcular o total vendido e o custo total de cada uma
$sql = "SELECT 
            v.id, v.data_venda, c.nome as cliente_nome,
            SUM(vp.quantidade * vp.preco_unitario_venda) as total_vendido,
            SUM(vp.quantidade * vp.custo_unitario_producao) as custo_total
        FROM vendas v
        JOIN venda_produtos vp ON v.id = vp.venda_id
        JOIN clientes c ON v.cliente_id = c.id
        WHERE v.status_venda = 'ativa' AND v.data_venda BETWEEN ? AND ?
        GROUP BY v.id, v.data_venda, c.nome
        ORDER BY v.data_venda DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$data_inicio . ' 00:00:00', $data_fim . ' 23:59:59']);
$vendas = $stmt->fetchAll();
?>

<h3>Relatório de Lucratividade por Venda</h3>

<form method="GET" class="form-filters no-print border p-3 mb-4 rounded">
    <input type="hidden" name="page" value="relatorios/lucro">
    <div class="row g-3">
        <div class="col-md-4">
            <label>Data Início</label>
            <input type="date" class="form-control" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
        </div>
        <div class="col-md-4">
            <label>Data Fim</label>
            <input type="date" class="form-control" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>">
        </div>
        <div class="col-md-4 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </div>
</form>

<div class="printable-area">
    <h4>Análise do Período (<?= date('d/m/Y', strtotime($data_inicio)) ?> a <?= date('d/m/Y', strtotime($data_fim)) ?>)</h4>
    
    <table class="table table-bordered">
        <thead>
            <tr><th>Venda</th><th>Data</th><th>Cliente</th><th>Total Venda</th><th>Custo Total</th><th>Lucro (R$)</th><th>Margem (%)</th></tr>
        </thead>
        <tbody>
            <?php 
            $faturamento_periodo = 0;
            $custo_periodo = 0;
            foreach($vendas as $venda): 
                $lucro = $venda['total_vendido'] - $venda['custo_total'];
                $margem = ($venda['total_vendido'] > 0) ? ($lucro / $venda['total_vendido']) * 100 : 0;
                $faturamento_periodo += $venda['total_vendido'];
                $custo_periodo += $venda['custo_total'];
            ?>
            <tr>
                <td>#<?= $venda['id'] ?></td>
                <td><?= date('d/m/Y', strtotime($venda['data_venda'])) ?></td>
                <td><?= htmlspecialchars($venda['cliente_nome']) ?></td>
                <td>R$ <?= number_format($venda['total_vendido'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format($venda['custo_total'], 2, ',', '.') ?></td>
                <td>R$ <?= number_format($lucro, 2, ',', '.') ?></td>
                <td><?= number_format($margem, 1, ',', '.') ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <?php
            $lucro_periodo = $faturamento_periodo - $custo_periodo;
            $margem_periodo = ($faturamento_periodo > 0) ? ($lucro_periodo / $faturamento_periodo) * 100 : 0;
            ?>
            <tr class="table-dark">
                <th colspan="3">TOTAIS DO PERÍODO</th>
                <th>R$ <?= number_format($faturamento_periodo, 2, ',', '.') ?></th>
                <th>R$ <?= number_format($custo_periodo, 2, ',', '.') ?></th>
                <th>R$ <?= number_format($lucro_periodo, 2, ',', '.') ?></th>
                <th><?= number_format($margem_periodo, 1, ',', '.') ?>%</th>
            </tr>
        </tfoot>
    </table>
</div>