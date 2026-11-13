<?php
// modulos/usuario/edit.php
@session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') {
    die('Acesso negado.');
}

$user_id_to_edit = $_GET['id'] ?? 0;
if (!$user_id_to_edit) {
    die('ID de usuário não fornecido.');
}

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
$stmt->execute([$user_id_to_edit]);
$usuario = $stmt->fetch();

if (!$usuario) {
    die('Usuário não encontrado.');
}
?>

<h3>Editar Usuário: <?= htmlspecialchars($usuario['usuario']) ?></h3>
<hr>

<form method="POST" action="modulos/usuario/actions.php">
    <input type="hidden" name="action" value="update">
    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">

    <div class="col-md-8">
        <div class="mb-3">
            <label for="edit-usuario" class="form-label">Nome de Usuário</label>
            <input type="text" class="form-control" id="edit-usuario" name="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Tipo de Usuário</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_usuario" id="edit-tipo-funcionario" value="funcionario" <?= ($usuario['tipo_usuario'] == 'funcionario') ? 'checked' : '' ?>>
                <label class="form-check-label" for="edit-tipo-funcionario">Funcionário</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_usuario" id="edit-tipo-administrador" value="administrador" <?= ($usuario['tipo_usuario'] == 'administrador') ? 'checked' : '' ?>>
                <label class="form-check-label" for="edit-tipo-administrador">Administrador</label>
            </div>
        </div>
        
        <hr>
        <p class="text-muted"><small>Para alterar a senha, preencha o campo abaixo. Se deixá-lo em branco, a senha atual será mantida.</small></p>
        <div class="mb-3">
            <label for="edit-senha" class="form-label">Nova Senha (Opcional)</label>
            <input type="password" class="form-control" id="edit-senha" name="nova_senha">
        </div>
    </div>

    <button type="submit" class="btn btn-primary mt-3">Salvar Alterações</button>
    <a href="index.php?page=usuarios" class="btn btn-secondary mt-3">Cancelar</a>
</form>