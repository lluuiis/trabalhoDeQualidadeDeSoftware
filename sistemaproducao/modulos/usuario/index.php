<?php
// modulos/usuario/index.php
@session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') {
    die('Acesso negado. Esta página é restrita a administradores.');
}

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message']['type']) . ' alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['message']['text']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
    unset($_SESSION['message']);
}

$stmt = $pdo->query("SELECT id_usuario, usuario, tipo_usuario FROM usuarios ORDER BY usuario ASC");
$usuarios = $stmt->fetchAll();
?>

<h3>Gerenciamento de Usuários</h3>
<p>Aqui você pode editar e excluir os usuários cadastrados no sistema.</p>
<a href="index.php?page=usuarios/cadastro" class="btn btn-success mb-3">Cadastrar Novo Usuário</a>
<hr>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Usuário</th>
            <th>Tipo</th>
            <th class="text-center">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($usuarios as $usuario): ?>
        <tr>
            <td><?= htmlspecialchars($usuario['id_usuario']) ?></td>
            <td><?= htmlspecialchars($usuario['usuario']) ?></td>
            <td><?= htmlspecialchars(ucfirst($usuario['tipo_usuario'])) ?></td>
            <td class="text-center">
                <a href="index.php?page=usuarios/edit&id=<?= $usuario['id_usuario'] ?>" class="btn btn-sm btn-warning">Editar</a>

                <?php
                if ($usuario['id_usuario'] != $_SESSION['user_id']):
                ?>
                    <a href="index.php?page=usuarios/delete&id=<?= $usuario['id_usuario'] ?>" class="btn btn-sm btn-danger">Excluir</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>