<?php
// modulos/vendas/list.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message']['type']) . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['message']['text']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']);
}

$vendas = $pdo->query("
    SELECT v.id, v.data_venda, v.valor_total, v.status_venda, c.nome as cliente_nome 
    FROM vendas v 
    JOIN clientes c ON v.cliente_id = c.id 
    ORDER BY v.data_venda DESC
")->fetchAll();
?>

<h3>Histórico de Vendas</h3>
<hr>
<table class="table table-hover">
    <thead>
        <tr>
            <th>ID Venda</th>
            <th>Data</th>
            <th>Cliente</th>
            <th>Valor Total</th>
            <th>Status da Venda</th> <th class="text-center">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($vendas as $venda): ?>
        <tr class="<?= $venda['status_venda'] == 'estornada' ? 'table-light text-muted' : '' ?>">
            <td>#<?= $venda['id'] ?></td>
            <td><?= date('d/m/Y H:i', strtotime($venda['data_venda'])) ?></td>
            <td><?= htmlspecialchars($venda['cliente_nome']) ?></td>
            <td>R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
            <td>
                <span class="badge <?= $venda['status_venda'] == 'ativa' ? 'bg-success' : 'bg-danger' ?>">
                    <?= ucfirst($venda['status_venda']) ?>
                </span>
            </td>
            <td class="text-center">
                
                <?php 
                // O botão de estorno só aparece se a venda estiver 'ativa'
                if ($venda['status_venda'] == 'ativa'): 
                ?>
                <form method="POST" action="modulos/vendas/actions.php" style="display:inline;" onsubmit="return confirm('Tem certeza que deseja estornar esta venda? Esta ação não pode ser desfeita e os produtos retornarão ao estoque.');">
                    <input type="hidden" name="action" value="estornar_venda">
                    <input type="hidden" name="venda_id" value="<?= $venda['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">Estornar</button>
                </form>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<div class="modal fade" id="saleDetailsModal" tabindex="-1" aria-labelledby="saleDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="saleDetailsModalLabel">Detalhes da Venda</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="saleDetailsBody">
                </div>
        </div>
    </div>
</div>