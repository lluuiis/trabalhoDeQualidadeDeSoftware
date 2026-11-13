<?php
// modulos/materiais/delete.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

$material_id = $_GET['id'] ?? 0;
if (!$material_id) { die('ID do material não fornecido.'); }

$stmt = $pdo->prepare("SELECT nome FROM materiais WHERE id = ?");
$stmt->execute([$material_id]);
$material_nome = $stmt->fetchColumn();

if (!$material_nome) { die('Material não encontrado.'); }
?>

<h3>Confirmar Exclusão de Material</h3>
<hr>

<div class="alert alert-danger">
    <h4>Atenção!</h4>
    <p>Você tem certeza que deseja excluir o material "<strong><?= htmlspecialchars($material_nome) ?></strong>"?</p>
    <p><b>Aviso:</b> Excluir um material pode causar problemas se ele estiver vinculado a produtos já cadastrados.</p>
</div>

<form method="POST" action="modulos/materiais/actions.php">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" value="<?= $material_id ?>">
    
    <button type="submit" class="btn btn-danger">Sim, desejo excluir</button>
    <a href="index.php?page=materiais" class="btn btn-secondary">Cancelar</a>
</form>