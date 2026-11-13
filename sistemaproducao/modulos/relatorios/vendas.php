<?php
// modulos/relatorios/vendas.php
@session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') { die('Acesso negado.'); }

// --- LÓGICA DE FILTRO ---
$where_clauses = ["v.status_venda = 'ativa'"];
$params = [];

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');
$where_clauses[] = "v.data_venda BETWEEN ? AND ?";
$params[] = $data_inicio . ' 00:00:00';
$params[] = $data_fim . ' 23:59:59';

if (!empty($_GET['cliente_id'])) {
    $where_clauses[] = "v.cliente_id = ?";
    $params[] = $_GET['cliente_id'];
}

$where_sql = implode(' AND ', $where_clauses);

// --- CONSULTA PRINCIPAL ---
$sql = "SELECT v.id, v.data_venda, v.valor_total, c.nome as cliente_nome
        FROM vendas v
        JOIN clientes c ON v.cliente_id = c.id
        WHERE $where_sql
        ORDER BY v.data_venda DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vendas = $stmt->fetchAll();

// --- CONSULTA PARA TOTAIS ---
$sql_total = "SELECT COUNT(*) as total_vendas, SUM(valor_total) as faturamento_total FROM vendas v WHERE $where_sql";
$stmt_total = $pdo->prepare($sql_total);
$stmt_total->execute($params);
$totais = $stmt_total->fetch();

// Busca clientes para o filtro
$clientes = $pdo->query("SELECT id, nome FROM clientes ORDER BY nome")->fetchAll();
?>

<h3>Relatório de Vendas</h3>

<form method="GET" class="form-filters no-print border p-3 mb-4 rounded">
    <input type="hidden" name="page" value="relatorios/vendas">
    <div class="row g-3">
        <div class="col-md-3">
            <label for="data_inicio" class="form-label">Data Início</label>
            <input type="date" class="form-control" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>">
        </div>
        <div class="col-md-3">
            <label for="data_fim" class="form-label">Data Fim</label>
            <input type="date" class="form-control" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>">
        </div>
        <div class="col-md-4">
            <label for="cliente_id" class="form-label">Cliente (Opcional)</label>
            <select class="form-select" name="cliente_id">
                <option value="">Todos</option>
                <?php foreach($clientes as $cliente): ?>
                    <option value="<?= $cliente['id'] ?>" <?= (($_GET['cliente_id'] ?? '') == $cliente['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cliente['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </div>
</form>

<div class="printable-area">
    <h4>Resumo do Período (<?= date('d/m/Y', strtotime($data_inicio)) ?> a <?= date('d/m/Y', strtotime($data_fim)) ?>)</h4>
    <p>
        <strong>Total de Vendas:</strong> <?= $totais['total_vendas'] ?><br>
        <strong>Faturamento Total:</strong> R$ <?= number_format($totais['faturamento_total'] ?? 0, 2, ',', '.') ?>
    </p>
    <hr>
    
    <table class="table table-bordered">
        <thead>
            <tr><th>ID Venda</th><th>Data</th><th>Cliente</th><th>Valor Total</th></tr>
        </thead>
        <tbody>
            <?php foreach($vendas as $venda): ?>
            <tr>
                <td>#<?= $venda['id'] ?></td>
                <td><?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></td>
                <td><?= htmlspecialchars($venda['cliente_nome']) ?></td>
                <td>R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>