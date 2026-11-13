<?php
// modulos/relatorios/estoque.php
@session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') { die('Acesso negado.'); }

// --- LÓGICA DE FILTRO ---
$where_clauses = [];
$params = [];

$data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
$data_fim = $_GET['data_fim'] ?? date('Y-m-t');
$where_clauses[] = "me.data_movimentacao BETWEEN ? AND ?";
$params[] = $data_inicio . ' 00:00:00';
$params[] = $data_fim . ' 23:59:59';

if (!empty($_GET['material_id'])) {
    $where_clauses[] = "me.material_id = ?";
    $params[] = $_GET['material_id'];
}

$where_sql = implode(' AND ', $where_clauses);

// --- CONSULTA ---
$sql = "SELECT me.data_movimentacao, m.nome as material_nome, me.tipo_movimentacao, me.quantidade, me.observacao
        FROM movimentacao_estoque me
        JOIN materiais m ON me.material_id = m.id
        WHERE $where_sql
        ORDER BY me.data_movimentacao DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$movimentacoes = $stmt->fetchAll();

// Busca materiais para o filtro
$materiais = $pdo->query("SELECT id, nome FROM materiais ORDER BY nome")->fetchAll();
?>

<h3>Relatório de Movimentação de Estoque</h3>

<form method="GET" class="form-filters no-print border p-3 mb-4 rounded">
    <input type="hidden" name="page" value="relatorios/estoque">
    <div class="row g-3">
        <div class="col-md-3"><label>Data Início</label><input type="date" class="form-control" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>"></div>
        <div class="col-md-3"><label>Data Fim</label><input type="date" class="form-control" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>"></div>
        <div class="col-md-4"><label>Material</label><select class="form-select" name="material_id"><option value="">Todos</option>
            <?php foreach($materiais as $material): ?><option value="<?= $material['id'] ?>" <?= (($_GET['material_id'] ?? '') == $material['id']) ? 'selected' : '' ?>><?= htmlspecialchars($material['nome']) ?></option><?php endforeach; ?>
        </select></div>
        <div class="col-md-2 d-flex align-items-end"><button type="submit" class="btn btn-primary w-100">Filtrar</button></div>
    </div>
</form>

<div class="printable-area">
    <h4>Movimentações de <?= date('d/m/Y', strtotime($data_inicio)) ?> a <?= date('d/m/Y', strtotime($data_fim)) ?></h4>
    <table class="table table-bordered table-sm">
        <thead><tr><th>Data/Hora</th><th>Material</th><th>Tipo</th><th>Quantidade</th><th>Observação</th></tr></thead>
        <tbody>
            <?php foreach($movimentacoes as $mov): ?>
            <tr class="<?= str_contains($mov['tipo_movimentacao'], 'entrada') ? 'table-success' : 'table-danger' ?>">
                <td><?= date('d/m/Y H:i', strtotime($mov['data_movimentacao'])) ?></td>
                <td><?= htmlspecialchars($mov['material_nome']) ?></td>
                <td><?= htmlspecialchars(ucfirst(str_replace('_', ' ', $mov['tipo_movimentacao']))) ?></td>
                <td><?= number_format($mov['quantidade'], 2, ',', '.') ?></td>
                <td><?= htmlspecialchars($mov['observacao']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>