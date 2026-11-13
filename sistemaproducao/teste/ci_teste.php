<?php
/**
 * Teste Automatizado de Integração para CI/CD
 * Disciplina de Qualidade de Software
 * 
 * Executa asserções na conexão PDO, integridade das tabelas,
 * carga de dados iniciais e regras de cálculo de custo.
 */

echo "========================================\n";
echo " INICIANDO TESTES AUTOMATIZADOS DE CI\n";
echo "========================================\n\n";

$databasePaths = [
    __DIR__ . '/../config/database.php',
    __DIR__ . '/config/database.php',
    dirname(__DIR__) . '/config/database.php',
    dirname(__DIR__, 2) . '/config/database.php',
    dirname(__DIR__, 2) . '/sistemaproducao/config/database.php',
    dirname(__DIR__, 3) . '/sistemaproducao/config/database.php'
];

$loaded = false;
foreach ($databasePaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $loaded = true;
        break;
    }
}

if (!$loaded) {
    fwrite(STDERR, "❌ Erro: Arquivo config/database.php não foi localizado.\n");
    exit(1);
}

$errors = 0;
$totalTests = 0;

function assertTest($description, $condition) {
    global $errors, $totalTests;
    $totalTests++;
    if ($condition) {
        echo "  ✅ PASSOU: $description\n";
    } else {
        echo "  ❌ FALHOU: $description\n";
        $errors++;
    }
}

// 1. Teste de Conexão PDO
assertTest("Conexão com o banco de dados via PDO ativa", isset($pdo) && $pdo instanceof PDO);

if (!isset($pdo)) {
    fwrite(STDERR, "Falha crítica: Sem conexão PDO para prosseguir com os testes.\n");
    exit(1);
}

// 2. Teste de Estrutura de Tabelas Essenciais
$expectedTables = [
    'clientes',
    'contas_a_pagar',
    'materiais',
    'movimentacao_estoque',
    'produto_materiais',
    'produtos',
    'usuarios',
    'venda_pagamentos',
    'venda_produtos',
    'vendas'
];

$stmt = $pdo->query("SHOW TABLES");
$existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);

foreach ($expectedTables as $table) {
    assertTest("Tabela '$table' criada no banco", in_array($table, $existingTables));
}

// 3. Teste de Registros Iniciais (Massa de Dados)
$stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
$userCount = (int)$stmt->fetchColumn();
assertTest("Tabela 'usuarios' populada com dados iniciais ($userCount usuários encontrados)", $userCount > 0);

$stmt = $pdo->query("SELECT COUNT(*) FROM materiais");
$matCount = (int)$stmt->fetchColumn();
assertTest("Tabela 'materiais' populada ($matCount materiais encontrados)", $matCount > 0);

$stmt = $pdo->query("SELECT COUNT(*) FROM produtos");
$prodCount = (int)$stmt->fetchColumn();
assertTest("Tabela 'produtos' populada ($prodCount produtos encontrados)", $prodCount > 0);

// 4. Teste da Função de Negócio: calcularCustoProduto()
assertTest("Função calcularCustoProduto está declarada", function_exists('calcularCustoProduto'));

if (function_exists('calcularCustoProduto')) {
    // Produto 1 tem material 1 (quantidade 1.00 * preco_custo 5.00) = custo 5.00
    $custo = calcularCustoProduto($pdo, 1);
    assertTest("Cálculo de custo de produção do Produto #1 retorna valor válido (R$ $custo)", is_numeric($custo) && $custo > 0);
}

// 5. Teste de Transação Atômica (ACID)
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("INSERT INTO clientes (nome, email) VALUES ('Teste CI Temporário', 'ci@teste.com')");
    $stmt->execute();
    $insertId = $pdo->lastInsertId();
    $pdo->rollBack();

    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM clientes WHERE id = ?");
    $stmtCheck->execute([$insertId]);
    $exists = (int)$stmtCheck->fetchColumn();
    assertTest("Rollback em transação atômica funciona corretamente (registro temporário revertido)", $exists === 0);
} catch (Exception $e) {
    assertTest("Execução de rollback de transação", false);
}

echo "\n----------------------------------------\n";
echo " RESUMO DA EXECUÇÃO:\n";
echo " Total de Testes: $totalTests\n";
echo " Falhas: $errors\n";
echo "----------------------------------------\n";

if ($errors > 0) {
    echo "❌ Pipeline CI: Falha na validação de testes.\n";
    exit(1);
} else {
    echo "🎉 Todos os testes passaram com sucesso!\n";
    exit(0);
}
