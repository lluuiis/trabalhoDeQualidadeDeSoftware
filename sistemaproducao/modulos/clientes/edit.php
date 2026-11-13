<?php
// modulos/clientes/edit.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

$cliente_id = $_GET['id'] ?? 0;

if (!$cliente_id) {
    die('ID do cliente não fornecido.');
}

$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

if (!$cliente) {
    die('Cliente não encontrado.');
}
?>

<h3>Editar Cliente: <?= htmlspecialchars($cliente['nome']) ?></h3>
<hr>

<form method="POST" action="modulos/clientes/actions.php">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id" value="<?= $cliente['id'] ?>">

    <div class="row g-3">
        <div class="col-md-6">
            <label for="nome" class="form-label">Nome Completo</label>
            <input type="text" class="form-control" id="nome" name="nome" value="<?= htmlspecialchars($cliente['nome']) ?>" required>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($cliente['email']) ?>">
        </div>
        <div class="col-md-4">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" class="form-control" id="telefone" name="telefone" value="<?= htmlspecialchars($cliente['telefone']) ?>">
        </div>
        <div class="col-md-8">
            <label for="endereco" class="form-label">Endereço</label>
            <input type="text" class="form-control" id="endereco" name="endereco" value="<?= htmlspecialchars($cliente['endereco']) ?>">
        </div>
    </div>
    <button type="submit" class="btn btn-primary mt-3">Salvar Alterações</button>
    <a href="index.php?page=clientes" class="btn btn-secondary mt-3">Cancelar</a>
</form>