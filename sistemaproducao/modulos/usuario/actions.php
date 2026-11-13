<?php
// modulos/usuarios/actions.php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'administrador' || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../index.php');
    exit();
}

$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'update':
            $id = $_POST['id_usuario'];
            $usuario = trim($_POST['usuario']);
            $tipo_usuario = $_POST['tipo_usuario'];
            $nova_senha = $_POST['nova_senha'];

            $params = [$usuario, $tipo_usuario];
            $sql = "UPDATE usuarios SET usuario = ?, tipo_usuario = ?";
            if (!empty($nova_senha)) {
                if(strlen($nova_senha) < 6) {
                    throw new Exception("A nova senha deve ter no mínimo 6 caracteres.");
                }
                $sql .= ", senha = ?";
                $params[] = $nova_senha;
            }

            $sql .= " WHERE id_usuario = ?";
            $params[] = $id;

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $_SESSION['message'] = ['type' => 'success', 'text' => 'Usuário atualizado com sucesso!'];
            break;

        case 'delete':
            $id = $_POST['id_usuario'];
            
            if ($id == $_SESSION['user_id']) {
                throw new Exception("Você não pode excluir sua própria conta de usuário.");
            }

            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$id]);

            $_SESSION['message'] = ['type' => 'success', 'text' => 'Usuário excluído com sucesso!'];
            break;

        default:
            $_SESSION['message'] = ['type' => 'danger', 'text' => 'Ação desconhecida!'];
            break;
    }
} catch (Exception $e) {
    $_SESSION['message'] = ['type' => 'danger', 'text' => 'Erro: ' . $e->getMessage()];
}

header('Location: ../../index.php?page=usuarios');
exit();