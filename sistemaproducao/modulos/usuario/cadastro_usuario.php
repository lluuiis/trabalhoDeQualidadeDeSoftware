<?php


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador') {
    die('Acesso negado.');
}

require_once __DIR__ . '/../../config/database.php';
include __DIR__ . '/../../includes/header.php';

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $senha = $_POST['senha'];
    $confirmar_senha = $_POST['confirmar_senha'];
    $tipo_usuario = $_POST['tipo_usuario'] ?? '';

    // As validações continuam as mesmas...
    if (empty($usuario) || empty($senha) || empty($confirmar_senha) || empty($tipo_usuario)) {
        $erro = "Todos os campos são obrigatórios.";
    } elseif ($senha !== $confirmar_senha) {
        $erro = "As senhas não conferem.";
    } else {
        $stmt_check = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
        $stmt_check->execute([$usuario]);
        if ($stmt_check->fetch()) {
            $erro = "Este nome de usuário já está em uso.";
        } else {
            $senha_para_salvar = $senha;
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO usuarios (usuario, senha, tipo_usuario) VALUES (?, ?, ?)"
                );
                $stmt->execute([$usuario, $senha_para_salvar, $tipo_usuario]);

                $sucesso = "Usuário '" . htmlspecialchars($usuario) . "' cadastrado com sucesso.";
            } catch (PDOException $e) {
                $erro = "Erro ao cadastrar usuário: " . $e->getMessage();
            }
        }
    }
}
?>

<div class="container">
    <h3>Cadastro de Novo Usuário</h3>
    <hr>

    <?php if (!empty($erro)): ?>
        <div class="alert alert-danger"><?= $erro ?></div>
    <?php endif; ?>

    <?php if (!empty($sucesso)): ?>
        <div class="alert alert-success"><?= $sucesso ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="mb-3">
            <label for="usuario" class="form-label">Nome de Usuário</label>
            <input type="text" class="form-control" id="usuario" name="usuario" required>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required>
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label">Tipo de Usuário</label>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_usuario" id="tipo_funcionario" value="funcionario" checked>
                <label class="form-check-label" for="tipo_funcionario">Funcionário</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="tipo_usuario" id="tipo_administrador" value="administrador">
                <label class="form-check-label" for="tipo_administrador">Administrador</label>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Cadastrar Usuário</button>
    </form>
</div>

<?php
include __DIR__ . '/../../includes/footer.php';
?>