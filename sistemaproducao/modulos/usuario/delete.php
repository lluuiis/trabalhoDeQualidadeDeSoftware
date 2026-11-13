<?php
// modulos/usuario/delete.php
@session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') {
    die('Acesso negado.');
}

$user_id_to_delete = $_GET['id'] ?? 0;

if ($user_id_to_delete == $_SESSION['user_id']) {
    die('Erro: Você não pode excluir sua própria conta de usuário.');
}

$stmt = $pdo->prepare("SELECT usuario FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$user_id_to_delete]);
$user_name = $stmt->fetchColumn();

if (!$user_name) {
    die('Usuário não encontrado.');
}
?>

<h3>Confirmar Exclusão de Usuário</h3>
<hr>

<div class="alert alert-danger">
    <h4>Atenção!</h4>
    <p>Você tem certeza que deseja excluir permanentemente o usuário "<strong><?= htmlspecialchars($user_name) ?></strong>"?</p>
    <p>Esta ação é irreversível.</p>
</div>

<form method="POST" action="modulos/usuario/actions.php">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id_usuario" value="<?= $user_id_to_delete ?>">
    
    <button type="submit" class="btn btn-danger">Sim, tenho certeza e quero excluir</button>
    <a href="index.php?page=usuarios" class="btn btn-secondary">Cancelar</a>
</form>