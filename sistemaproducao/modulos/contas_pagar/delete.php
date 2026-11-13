<?php
// modulos/contas_pagar/delete.php
@session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') {
    die('Acesso negado.');
}

$conta_id = $_GET['id'] ?? 0;
if (!$conta_id) {
    die('ID da conta não fornecido.');
}

$stmt = $pdo->prepare("SELECT descricao FROM contas_a_pagar WHERE id = ?");
$stmt->execute([$conta_id]);
$conta_descricao = $stmt->fetchColumn();

if (!$conta_descricao) {
    die('Conta não encontrada.');
}
?>

<h3>Confirmar Exclusão</h3>
<hr>

<div class="alert alert-danger">
    <h4>Atenção!</h4>
    <p>Você tem certeza que deseja excluir a conta "<strong><?= htmlspecialchars($conta_descricao) ?></strong>"?</p>
    <p>Esta ação não pode ser desfeita.</p>
</div>

<form method="POST" action="modulos/contas_pagar/actions.php">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= $conta_id ?>">
    
    <button type="submit" class="btn btn-danger">Sim, desejo excluir</button>
    <a href="index.php?page=contas_pagar" class="btn btn-secondary">Cancelar</a>
</form>