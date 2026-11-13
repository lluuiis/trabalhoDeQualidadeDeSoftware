<?php
// modulos/contas_pagar/edit.php
@session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') {
    die('Acesso negado.');
}

$conta_id = $_GET['id'] ?? 0;
if (!$conta_id) {
    die('ID da conta não fornecido.');
}

$stmt = $pdo->prepare("SELECT * FROM contas_a_pagar WHERE id = ?");
$stmt->execute([$conta_id]);
$conta = $stmt->fetch();

if (!$conta) {
    die('Conta não encontrada.');
}
?>

<h3>Editar Conta a Pagar</h3>
<hr>

<form method="POST" action="modulos/contas_pagar/actions.php">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= $conta['id'] ?>">

    <div class="row g-3 align-items-end">
        <div class="col-md-5">
            <label for="descricao" class="form-label">Descrição da Conta</label>
            <input type="text" class="form-control" id="descricao" name="descricao" value="<?= htmlspecialchars($conta['descricao']) ?>" required>
        </div>
        <div class="col-md-3">
            <label for="valor" class="form-label">Valor (R$)</label>
            <input type="number" step="0.01" class="form-control" id="valor" name="valor" value="<?= htmlspecialchars($conta['valor']) ?>" required>
        </div>
        <div class="col-md-2">
            <label for="data_vencimento" class="form-label">Data de Vencimento</label>
            <input type="date" class="form-control" id="data_vencimento" name="data_vencimento" value="<?= htmlspecialchars($conta['data_vencimento']) ?>" required>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">Salvar Alterações</button>
        </div>
    </div>
    <a href="index.php?page=contas_pagar" class="btn btn-secondary mt-3">Cancelar</a>
</form>