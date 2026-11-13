<?php
// config/database.php

$host = 'localhost';
$db_name = 'banco_artes';
$charset = 'utf8mb4';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=$charset");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

function calcularCustoProduto($pdo, $produto_id) {
    $sql = "SELECT SUM(pm.quantidade_utilizada * m.preco_custo) AS custo_total
            FROM produto_materiais pm
            JOIN materiais m ON pm.material_id = m.id
            WHERE pm.produto_id = :produto_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':produto_id' => $produto_id]);
    $result = $stmt->fetch();
    return $result['custo_total'] ?? 0;
}
?>