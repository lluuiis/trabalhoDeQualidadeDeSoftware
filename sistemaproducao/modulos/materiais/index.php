<?php
// modulos/materiais/index.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message']['type']) . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['message']['text']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']);
}

$materiais = $pdo->query("SELECT * FROM materiais ORDER BY nome ASC")->fetchAll();
?>

<h3>Cadastro de Materiais</h3>
<p>Cadastre aqui os insumos e matérias-primas utilizados na produção.</p>
<hr>

<form method="POST" action="modulos/materiais/actions.php">
    <input type="hidden" name="action" value="create">
    <div class="row g-3">
        <div class="col-md-6"><label for="nome" class="form-label">Nome do Material</label><input type="text" class="form-control" id="nome" name="nome" required></div>
        <div class="col-md-3"><label for="unidade_medida" class="form-label">Unidade (Ex: kg, L, un, m)</label><input type="text" class="form-control" id="unidade_medida" name="unidade_medida" required></div>
        <div class="col-md-3"><label for="preco_custo" class="form-label">Preço de Custo (R$)</label><input type="number" step="0.01" class="form-control" id="preco_custo" name="preco_custo" required></div>
    </div>
    <button type="submit" class="btn btn-primary mt-3">Cadastrar Material</button>
</form>

<h3 class="mt-5">Materiais Cadastrados</h3>
<hr>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Unidade</th>
            <th>Preço de Custo</th>
            <th>Estoque Atual</th>
            <th class="text-center">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($materiais as $material): ?>
        <tr>
            <td><?= $material['id'] ?></td>
            <td><?= htmlspecialchars($material['nome']) ?></td>
            <td><?= htmlspecialchars($material['unidade_medida']) ?></td>
            <td>R$ <?= number_format($material['preco_custo'], 2, ',', '.') ?></td>
            <td><?= number_format($material['estoque_atual'], 2, ',', '.') ?></td>
            <td class="text-center">
                <a href="index.php?page=materiais/edit&id=<?= $material['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                <a href="index.php?page=materiais/delete&id=<?= $material['id'] ?>" class="btn btn-sm btn-danger">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>