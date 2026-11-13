<?php
// modulos/clientes/index.php
@session_start();

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message']['type']) . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['message']['text']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']);
}

$clientes = $pdo->query("SELECT * FROM clientes ORDER BY nome ASC")->fetchAll();
?>

<h3>Cadastro de Clientes</h3>
<hr>

<form method="POST" action="modulos/clientes/actions.php">
    <input type="hidden" name="action" value="create">
    <div class="row g-3">
        <div class="col-md-6"><label for="nome" class="form-label">Nome Completo</label><input type="text" class="form-control" id="nome" name="nome" required></div>
        <div class="col-md-6"><label for="email" class="form-label">Email</label><input type="email" class="form-control" id="email" name="email"></div>
        <div class="col-md-4"><label for="telefone" class="form-label">Telefone</label><input type="text" class="form-control" id="telefone" name="telefone"></div>
        <div class="col-md-8"><label for="endereco" class="form-label">Endereço</label><input type="text" class="form-control" id="endereco" name="endereco"></div>
    </div>
    <button type="submit" class="btn btn-primary mt-3">Cadastrar Cliente</button>
</form>

<h3 class="mt-5">Clientes Cadastrados</h3>
<hr>
<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nome</th>
            <th>Email</th>
            <th>Telefone</th>
            <th class="text-center">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($clientes as $cliente): ?>
        <tr>
            <td><?= htmlspecialchars($cliente['id']) ?></td>
            <td><?= htmlspecialchars($cliente['nome']) ?></td>
            <td><?= htmlspecialchars($cliente['email']) ?></td>
            <td><?= htmlspecialchars($cliente['telefone']) ?></td>
            <td class="text-center">
                <a href="index.php?page=clientes/edit&id=<?= $cliente['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                <a href="index.php?page=clientes/delete&id=<?= $cliente['id'] ?>" class="btn btn-sm btn-danger">Excluir</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>