<?php
// login.php (VERSÃO INSEGURA - NÃO USE EM PRODUÇÃO)
session_start(); 
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_form = $_POST['usuario'];
    $senha_form = $_POST['senha']; // A senha que o usuário digitou

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
    $stmt->execute([$usuario_form]);
    $user = $stmt->fetch();

    // --- MUDANÇA CRÍTICA AQUI ---
    // Em vez de password_verify, fazemos uma comparação direta e insegura.
    // Isso só funciona se a senha no banco de dados ($user['senha']) estiver em texto puro.
    if ($user && $senha_form === $user['senha']) {
        // Login bem-sucedido!
        $_SESSION['user_id'] = $user['id_usuario'];
        $_SESSION['user_name'] = $user['usuario'];
        $_SESSION['user_type'] = $user['tipo_usuario'];
        
        header("Location: index.php");
        exit();
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de Gestão</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body { height: 100%; }
        body { display: flex; align-items: center; padding-top: 40px; padding-bottom: 40px; background-color: #f5f5f5; }
        .form-signin { width: 100%; max-width: 330px; padding: 15px; margin: auto; }
    </style>
</head>
<body class="text-center">
    <main class="form-signin">
        <form method="POST">
            <h1 class="h3 mb-3 fw-normal">Acessar Sistema</h1>
            
            <?php if (!empty($erro)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($erro) ?></div>
            <?php endif; ?>

            <div class="form-floating">
                <input type="text" class="form-control" id="usuario" name="usuario" placeholder="Usuário" required>
                <label for="usuario">Usuário</label>
            </div>
            <div class="form-floating mt-2">
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Senha" required>
                <label for="senha">Senha</label>
            </div>

            <button class="w-100 btn btn-lg btn-primary mt-3" type="submit">Entrar</button>
            <p class="mt-5 mb-3 text-muted">&copy; 2025</p>
        </form>
    </main>
</body>
</html>