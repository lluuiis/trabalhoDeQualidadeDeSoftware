<?php
// modulos/clientes/delete.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

$cliente_id = $_GET['id'] ?? 0;

if (!$cliente_id) {
    die('ID do cliente não fornecido.');
}
$stmt = $pdo->prepare("SELECT nome FROM clientes WHERE id = ?");
$stmt->execute([$cliente_id]);
$cliente_nome = $stmt->fetchColumn();

if (!$cliente_nome) {
    die('Cliente não encontrado.');
}
?>

<h3>Confirmar Exclusão</h3>
<hr>

<div class="alert alert-danger">
    <h4>Atenção!</h4>
    <p>Você tem certeza que deseja excluir permanentemente o cliente "<strong><?= htmlspecialchars($cliente_nome) ?></strong>"?</p>
    <p>Esta ação não pode ser desfeita.</p>
</div>

<form method="POST" action="modulos/clientes/actions.php">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= $cliente_id ?>">
    
    <button type="submit" class="btn btn-danger">Sim, excluir permanentemente</button>
    <a href="index.php?page=clientes" class="btn btn-secondary">Não, voltar para a lista</a>
</form>