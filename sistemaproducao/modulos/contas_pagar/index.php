<?php
// modulos/contas_pagar/index.php
@session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') {
    die('Acesso negado. Esta página é restrita a administradores.');
}

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message']['type']) . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['message']['text']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']);
}

$contas = $pdo->query("SELECT * FROM contas_a_pagar ORDER BY status ASC, data_vencimento ASC")->fetchAll();
?>

<h3>Controle de Contas a Pagar</h3>
<hr>

<form method="POST" action="modulos/contas_pagar/actions.php">
    <input type="hidden" name="action" value="create">
    <div class="row g-3 align-items-end">
        <div class="col-md-5"><label for="descricao" class="form-label">Descrição da Conta</label><input type="text" class="form-control" id="descricao" name="descricao" required placeholder="Ex: Aluguel, Conta de Luz, Fornecedor..."></div>
        <div class="col-md-3"><label for="valor" class="form-label">Valor (R$)</label><input type="number" step="0.01" class="form-control" id="valor" name="valor" required></div>
        <div class="col-md-2"><label for="data_vencimento" class="form-label">Data de Vencimento</label><input type="date" class="form-control" id="data_vencimento" name="data_vencimento" required></div>
        <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Adicionar Conta</button></div>
    </div>
</form>

<h3 class="mt-5">Contas Registradas</h3>
<hr>
<table class="table table-hover">
    <thead>
        <tr>
            <th>Descrição</th>
            <th>Valor</th>
            <th>Vencimento</th>
            <th class="text-center">Status</th>
            <th class="text-center">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($contas as $conta): 
            $is_overdue = (new DateTime($conta['data_vencimento']) < new DateTime('today')) && $conta['status'] == 'pendente';
            $row_class = $conta['status'] == 'paga' ? 'table-light text-muted' : ($is_overdue ? 'table-danger' : '');
        ?>
        <tr class="<?= $row_class ?>">
            <td><?= htmlspecialchars($conta['descricao']) ?></td>
            <td>R$ <?= number_format($conta['valor'], 2, ',', '.') ?></td>
            <td><?= date('d/m/Y', strtotime($conta['data_vencimento'])) ?></td>
            <td class="text-center">
                <span class="badge <?= $conta['status'] == 'pendente' ? ($is_overdue ? 'bg-danger' : 'bg-warning text-dark') : 'bg-success' ?>">
                    <?= $is_overdue ? 'Vencida' : ucfirst($conta['status']) ?>
                </span>
            </td>
            <td class="text-center">
                <?php if ($conta['status'] == 'pendente'): ?>
                    <form method="POST" action="modulos/contas_pagar/actions.php" style="display:inline;">
                        <input type="hidden" name="action" value="marcar_paga">
                        <input type="hidden" name="id" value="<?= $conta['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-success">Marcar como Paga</button>
                    </form>
                    <a href="index.php?page=contas_pagar/edit&id=<?= $conta['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                    <a href="index.php?page=contas_pagar/delete&id=<?= $conta['id'] ?>" class="btn btn-sm btn-outline-danger">Excluir</a>
                <?php else: ?>
                    <span class="text-muted fst-italic">N/A</span>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>