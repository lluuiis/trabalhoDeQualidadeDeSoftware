<?php
// config/database.php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: 3306;
$db_name = getenv('DB_NAME') ?: 'banco_artes';
$username = getenv('DB_USER') ?: 'teste';
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '1234';
$charset = 'utf8mb4';

    try {
    $dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=$charset";
    $pdo = new PDO($dsn, $username, $password);

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