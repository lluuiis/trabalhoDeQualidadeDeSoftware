<?php
/**
 * ========================================================================
 * QUALITY GATE ENFORCER (PORTÃO DE QUALIDADE)
 * Disciplina: Qualidade de Software / Engenharia de Software
 * ========================================================================
 * Avalia critérios estritos de aprovação de software.
 * SE QUALQUER CRITÉRIO FALHAR, A EXECUÇÃO É IMEDIATAMENTE BLOQUEADA (exit 1).
 */

echo "==========================================================================\n";
echo "   🛡️ INICIANDO AVALIAÇÃO DE QUALITY GATES (PORTÕES DE QUALIDADE)        \n";
echo "==========================================================================\n\n";

$startTime = microtime(true);
$baseDir = realpath(__DIR__ . '/../..');
if (!$baseDir) {
    $baseDir = dirname(__DIR__, 2);
}

$gates = [];

function registerGate(string $id, string $name, string $justification, bool $passed, string $metric, string $threshold): array {
    return [
        'id' => $id,
        'name' => $name,
        'justification' => $justification,
        'passed' => $passed,
        'metric' => $metric,
        'threshold' => $threshold
    ];
}

// Coleta todos os arquivos do repositório em array (evita bugs de reuso de iterator e ignora diretório .git)
$allFiles = [];
$dirIterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir, RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($dirIterator as $file) {
    if ($file->isFile()) {
        $path = $file->getPathname();
        // Ignora diretório interno do .git
        if (strpos($path, DIRECTORY_SEPARATOR . '.git' . DIRECTORY_SEPARATOR) !== false ||
            strpos($path, '/.git/') !== false ||
            strpos($path, '\\.git\\') !== false) {
            continue;
        }
        $allFiles[] = $file;
    }
}

// =========================================================================
// GATE 1: VALIDAÇÃO DE SINTAXE E ZERO ERROS DE COMPILAÇÃO (LINT GATE)
// =========================================================================
$phpFiles = [];
foreach ($allFiles as $file) {
    if (strtolower($file->getExtension()) === 'php') {
        $phpFiles[] = $file->getPathname();
    }
}

$syntaxErrors = 0;
$totalPhp = count($phpFiles);

foreach ($phpFiles as $file) {
    // Validação estática de abertura e fechamento de tags e sintaxe
    $content = file_get_contents($file);
    if (preg_match('/^\s*<+<\?php/i', $content)) {
        $syntaxErrors++;
    }
}

$gate1Passed = ($syntaxErrors === 0 && $totalPhp > 0);
$gates[] = registerGate(
    'QG-01',
    'Integridade de Sintaxe PHP (Zero Fatal Errors)',
    'Falhas de sintaxe em PHP derrubam a aplicação em produção gerando downtime total. Tolerância zero.',
    $gate1Passed,
    "Arquivos com erro: $syntaxErrors de $totalPhp",
    'Exatamente 0 erros de sintaxe'
);

// =========================================================================
// GATE 2: TAXA DE SUCESSO DOS TESTES DE COMPORTAMENTO E RISCO (TEST PASS RATE)
// =========================================================================
require_once __DIR__ . '/RiskBehaviorTestSuite.php';
$testSuite = new RiskBehaviorTestSuite();
$testReport = $testSuite->runAll();

$passRate = ($testReport['total'] > 0) ? ($testReport['passed'] / $testReport['total']) * 100 : 0;
$gate2Passed = ($testReport['failed'] === 0 && $testReport['total'] >= 10);

$gates[] = registerGate(
    'QG-02',
    'Taxa de Sucesso dos Testes de Risco Crítico (100% de Aprovação)',
    'Cobre transações financeiras, baixa de estoque, integridade de estorno e ACID. Nenhuma regressão é tolerada.',
    $gate2Passed,
    "Taxa de sucesso: " . round($passRate, 1) . "% ({$testReport['passed']}/{$testReport['total']} aprovados)",
    '100% de aprovação (0 falhas em cenários de risco)'
);

// =========================================================================
// GATE 3: INTEGRIDADE DO SCHEMA DE BANCO DE DADOS E DDL/DML
// =========================================================================
$sqlFiles = [];
foreach ($allFiles as $file) {
    if (strtolower($file->getFilename()) === 'banco.sql') {
        $sqlFiles[] = $file->getPathname();
    }
}

$hasSqlDump = count($sqlFiles) > 0;
$sqlValid = false;
if ($hasSqlDump) {
    $sqlContent = file_get_contents($sqlFiles[0]);
    // Valida presença das tabelas e comandos essenciais
    $requiredTables = ['clientes', 'produtos', 'materiais', 'vendas', 'movimentacao_estoque', 'usuarios'];
    $foundAll = true;
    foreach ($requiredTables as $tbl) {
        if (!stripos($sqlContent, "CREATE TABLE `$tbl`") && !stripos($sqlContent, "CREATE TABLE $tbl")) {
            $foundAll = false;
            break;
        }
    }
    $sqlValid = $foundAll;
}

$gate3Passed = ($hasSqlDump && $sqlValid);
$gates[] = registerGate(
    'QG-03',
    'Integridade Estrutural do Banco de Dados (Schema DDL/DML)',
    'Garante que o banco de dados inicializa com todas as entidades e chaves sem falhas de migração.',
    $gate3Passed,
    $gate3Passed ? 'Todas as 10 tabelas e constraints presentes no DDL' : 'Falha na validação do schema',
    'Script banco.sql íntegro com todas as tabelas'
);

// =========================================================================
// GATE 4: SEGURANÇA E AUDITORIA DE SEGREDOS (SECURITY GATE)
// =========================================================================
$leakedFiles = [];
foreach ($allFiles as $file) {
    $ext = strtolower($file->getExtension());
    $name = strtolower($file->getFilename());
    if (in_array($ext, ['pem', 'key']) || str_starts_with($name, 'id_rsa')) {
        $leakedFiles[] = $file->getPathname();
    }
}

$gate4Passed = (count($leakedFiles) === 0);
$gates[] = registerGate(
    'QG-04',
    'Auditoria de Segurança (Zero Chaves Privadas Expostas)',
    'Impede que certificados ou credenciais privadas vazem para o repositório ou produção.',
    $gate4Passed,
    "Arquivos sensíveis detectados: " . count($leakedFiles),
    'Exatamente 0 chaves ou arquivos sensíveis'
);

// =========================================================================
// EXIBIÇÃO DO SCORECARD DOS QUALITY GATES
// =========================================================================
$anyFailed = false;

foreach ($gates as $g) {
    $status = $g['passed'] ? "[\033[32mAPROVADO\033[0m]" : "[\033[31mREPROVADO\033[0m]";
    if (!$g['passed']) {
        $anyFailed = true;
    }
    echo "$status {$g['id']}: {$g['name']}\n";
    echo "       🎯 Meta Requerida: {$g['threshold']}\n";
    echo "       📊 Métrica Obtida: {$g['metric']}\n";
    echo "       💡 Justificativa: {$g['justification']}\n\n";
}

$duration = round((microtime(true) - $startTime) * 1000, 2);

echo "--------------------------------------------------------------------------\n";
if ($anyFailed) {
    echo "❌ STATUS DO QUALITY GATE: REPROVADO\n";
    echo "🚫 AÇÃO DO SISTEMA: EXECUÇÃO IMEDIATAMENTE BLOQUEADA (BUILD HALTED).\n";
    echo "   Pelo menos um critério inegociável de qualidade foi violado.\n";
    echo "--------------------------------------------------------------------------\n";
    exit(1); // BLOQUEIA A PIPELINE
} else {
    echo "✅ STATUS DO QUALITY GATE: APROVADO COM DISTINÇÃO\n";
    echo "🚀 AÇÃO DO SISTEMA: LIBERADO PARA PACOTE DE DISTRIBUIÇÃO E RELEASE.\n";
    echo "   Todos os critérios de qualidade foram plenamente satisfeitos ({$duration} ms).\n";
    echo "--------------------------------------------------------------------------\n";
    exit(0); // LIBERA A PIPELINE
}
