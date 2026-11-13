<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: ../../index.php'); exit(); }

if ($_POST['action'] === 'marcar_recebido') {
    $pagamento_id = $_POST['pagamento_id'];
    $stmt = $pdo->prepare("UPDATE venda_pagamentos SET status = 'pago', data_pagamento = NOW() WHERE id = ?");
    $stmt->execute([$pagamento_id]);
    $_SESSION['message'] = ['type' => 'success', 'text' => 'Pagamento recebido com sucesso!'];
}
header('Location: ../../index.php?page=contas_receber');
exit();