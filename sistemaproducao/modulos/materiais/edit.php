<?php
// modulos/materiais/edit.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

$material_id = $_GET['id'] ?? 0;

if (!$material_id) {
    die('ID do material não fornecido.');
}

$stmt = $pdo->prepare("SELECT * FROM materiais WHERE id = ?");
$stmt->execute([$material_id]);
$material = $stmt->fetch();

if (!$material) {
    die('Material não encontrado.');
}
?>

<h3>Editar Material: <?= htmlspecialchars($material['nome']) ?></h3>
<hr>

<form method="POST" action="modulos/materiais/actions.php">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= $material['id'] ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label for="nome" class="form-label">Nome do Material</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($material['nome']) ?>" required>
        </div>
        <div class="col-md-3">
            <label for="unidade_medida" class="form-label">Unidade (Ex: kg, L, un, m)</label>
            <input type="text" class="form-control" id="unidade_medida" name="unidade_medida" value="<?= htmlspecialchars($material['unidade_medida']) ?>" required>
        </div>
        <div class="col-md-3">
            <label for="preco_custo" class="form-label">Preço de Custo (R$)</label>
            <input type="number" step="0.01" class="form-control" id="preco_custo" name="preco_custo" value="<?= htmlspecialchars($material['preco_custo']) ?>" required>
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Salvar Alterações</button>
    <a href="index.php?page=materiais" class="btn btn-secondary mt-3">Cancelar</a>
</form>