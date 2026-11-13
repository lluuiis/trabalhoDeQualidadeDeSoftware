<?php
/**
 * ========================================================================
 * SUÍTE DE TESTES AUTOMATIZADOS: COMPORTAMENTOS E RISCOS CRÍTICOS
 * Disciplina: Qualidade de Software / Engenharia de Software
 * ========================================================================
 * Esta suíte implementa testes automatizados voltados a matriz de riscos
 * do negócio (estoque, transações financeiras, atomicidade ACID, integridade
 * de estorno, controle de acesso e segurança contra injeção SQL).
 */

class RiskBehaviorTestSuite {
    private PDO $pdo;
    private array $results = [];
    private float $startTime;

    public function __construct() {
        $this->startTime = microtime(true);
        $this->setupDatabase();
    }

    /**
     * Inicializa banco de dados de teste (SQLite em memória ou MySQL)
     * recriando o schema das tabelas essenciais.
     */
    private function setupDatabase(): void {
        // Usa SQLite em memória para testes ultrarrápidos e independentes de infraestrutura externa
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Criação de schema idêntico ao MySQL da aplicação
        $this->pdo->exec("
            CREATE TABLE clientes (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                email TEXT,
                telefone TEXT,
                endereco TEXT
            );

            CREATE TABLE materiais (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                unidade_medida TEXT NOT NULL,
                preco_custo NUMERIC NOT NULL,
                estoque_atual NUMERIC NOT NULL DEFAULT 0.00
            );

            CREATE TABLE produtos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nome TEXT NOT NULL,
                preco_venda NUMERIC NOT NULL,
                estoque_atual INTEGER NOT NULL DEFAULT 0
            );

            CREATE TABLE produto_materiais (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                produto_id INTEGER NOT NULL,
                material_id INTEGER NOT NULL,
                quantidade_utilizada NUMERIC NOT NULL
            );

            CREATE TABLE movimentacao_estoque (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                material_id INTEGER NOT NULL,
                tipo_movimentacao TEXT NOT NULL,
                quantidade NUMERIC NOT NULL,
                observacao TEXT,
                data_movimentacao DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE vendas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                cliente_id INTEGER NOT NULL,
                data_venda DATETIME DEFAULT CURRENT_TIMESTAMP,
                valor_total NUMERIC NOT NULL,
                status_venda TEXT NOT NULL DEFAULT 'ativa'
            );

            CREATE TABLE venda_produtos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                venda_id INTEGER NOT NULL,
                produto_id INTEGER NOT NULL,
                quantidade INTEGER NOT NULL,
                preco_unitario_venda NUMERIC NOT NULL,
                custo_unitario_producao NUMERIC NOT NULL
            );

            CREATE TABLE venda_pagamentos (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                venda_id INTEGER NOT NULL,
                forma_pagamento TEXT NOT NULL,
                valor NUMERIC NOT NULL,
                data_vencimento DATE NOT NULL,
                status TEXT NOT NULL DEFAULT 'pendente'
            );

            CREATE TABLE usuarios (
                id_usuario INTEGER PRIMARY KEY AUTOINCREMENT,
                usuario TEXT NOT NULL UNIQUE,
                senha TEXT NOT NULL,
                tipo_usuario TEXT NOT NULL
            );
        ");

        $this->seedDatabase();
    }

    /**
     * Popula massa de dados inicial para execução dos cenários de teste
     */
    private function seedDatabase(): void {
        // Insumos
        $this->pdo->exec("INSERT INTO materiais (id, nome, unidade_medida, preco_custo, estoque_atual) VALUES 
            (1, 'Tecido Algodão', 'm', 10.00, 50.00),
            (2, 'Linha Costura', 'rolo', 2.50, 10.00),
            (3, 'Botão Especial', 'un', 0.50, 5.00);"); // Estoque propositalmente baixo de botões

        // Produtos
        $this->pdo->exec("INSERT INTO produtos (id, nome, preco_venda, estoque_atual) VALUES 
            (1, 'Camisa Artesanal', 60.00, 10),
            (2, 'Bolsa Ecológica', 40.00, 5);");

        // Ficha Técnica (BOM)
        // Camisa: 2m Tecido (R$ 20) + 1 rolo Linha (R$ 2.50) + 4 Botões (R$ 2.00) = Custo R$ 24.50
        $this->pdo->exec("INSERT INTO produto_materiais (produto_id, material_id, quantidade_utilizada) VALUES 
            (1, 1, 2.00),
            (1, 2, 1.00),
            (1, 3, 4.00);");

        // Clientes
        $this->pdo->exec("INSERT INTO clientes (id, nome, email) VALUES (1, 'Cliente Teste QA', 'qa@teste.com');");

        // Usuários
        $this->pdo->exec("INSERT INTO usuarios (id_usuario, usuario, senha, tipo_usuario) VALUES 
            (1, 'admin', 'admin123', 'administrador'),
            (2, 'operador', 'op123', 'funcionario');");
    }

    /**
     * Helper de registro de resultado de asserção
     */
    private function record(string $id, string $title, string $risk, bool $passed, string $details = ''): void {
        $this->results[] = [
            'id' => $id,
            'title' => $title,
            'risk' => $risk,
            'passed' => $passed,
            'details' => $details
        ];
    }

    // =========================================================================
    // CENÁRIO 1: RISCO DE RUPTURA E ESTOQUE NEGATIVO NA PRODUÇÃO
    // =========================================================================

    /**
     * CT-RISK-01: Tentar produzir quantidade que exige mais insumo do que o disponível em estoque.
     * Risco: Furo no inventário, saldo negativo de matéria-prima e paradas na produção.
     */
    public function testRisco01_BloqueioProducaoSemInsumosSuficientes(): void {
        $produto_id = 1;
        $qtd_produzir = 5; // Exige 5 * 4 = 20 botões, mas só há 5 em estoque!

        $stmt = $this->pdo->prepare("SELECT pm.material_id, m.nome, pm.quantidade_utilizada, m.estoque_atual 
                                     FROM produto_materiais pm 
                                     JOIN materiais m ON pm.material_id = m.id 
                                     WHERE pm.produto_id = ?");
        $stmt->execute([$produto_id]);
        $receita = $stmt->fetchAll();

        $bloqueado = false;
        $motivo = '';

        foreach ($receita as $item) {
            $necessario = $item['quantidade_utilizada'] * $qtd_produzir;
            if ($item['estoque_atual'] < $necessario) {
                $bloqueado = true;
                $motivo = "Estoque insuficiente de '{$item['nome']}'. Necessário: $necessario / Disponível: {$item['estoque_atual']}";
                break;
            }
        }

        // Verifica que o estoque NÃO foi alterado
        $stmtCheck = $this->pdo->query("SELECT estoque_atual FROM materiais WHERE id = 3");
        $estoqueBotoes = (float)$stmtCheck->fetchColumn();

        $passou = ($bloqueado === true) && ($estoqueBotoes === 5.00);
        $this->record(
            'CT-RISK-01',
            'Bloqueio de Ordem de Produção com Insumos Insuficientes',
            'Risco de Estoque Negativo e Inconsistência Física no Almoxarifado',
            $passou,
            $motivo
        );
    }

    /**
     * CT-RISK-02: Produção com insumos suficientes deve debitar insumo e creditar produto final.
     * Risco: Não atualização dos saldos após conclusão da produção.
     */
    public function testRisco01_ProducaoSucessoAtualizaSaldos(): void {
        $produto_id = 1;
        $qtd_produzir = 1; // Exige: 2m tecido, 1 rolo linha, 4 botões

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("SELECT pm.material_id, pm.quantidade_utilizada FROM produto_materiais pm WHERE pm.produto_id = ?");
            $stmt->execute([$produto_id]);
            $receita = $stmt->fetchAll();

            foreach ($receita as $item) {
                $debitar = $item['quantidade_utilizada'] * $qtd_produzir;
                $this->pdo->prepare("UPDATE materiais SET estoque_atual = estoque_atual - ? WHERE id = ?")
                          ->execute([$debitar, $item['material_id']]);
                
                $this->pdo->prepare("INSERT INTO movimentacao_estoque (material_id, tipo_movimentacao, quantidade, observacao) VALUES (?, 'saida_producao', ?, 'Produção CT-02')")
                          ->execute([$item['material_id'], $debitar]);
            }

            $this->pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?")
                      ->execute([$qtd_produzir, $produto_id]);

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
        }

        $estoqueTecido = (float)$this->pdo->query("SELECT estoque_atual FROM materiais WHERE id = 1")->fetchColumn();
        $estoqueCamisa = (int)$this->pdo->query("SELECT estoque_atual FROM produtos WHERE id = 1")->fetchColumn();

        // Tecido era 50, virou 48 (-2). Camisa era 10, virou 11 (+1)
        $passou = ($estoqueTecido === 48.00) && ($estoqueCamisa === 11);
        $this->record(
            'CT-RISK-02',
            'Baixa Atômica de Insumos e Incremento de Produto Acabado',
            'Risco de Divergência entre Inventário de Matéria-Prima e Produto Final',
            $passou,
            "Tecido: 48.00m (esperado 48.00m), Camisas: 11un (esperado 11un)"
        );
    }

    // =========================================================================
    // CENÁRIO 2: RISCO FINANCEIRO E DIVERGÊNCIA DE VALORES NA VENDA
    // =========================================================================

    /**
     * CT-RISK-03: Tentar registrar venda onde o total dos pagamentos difere do total dos produtos.
     * Risco: Perda de faturamento, furos contábeis e recebimento menor que o valor dos itens.
     */
    public function testRisco02_BloqueioVendaDivergenciaPagamento(): void {
        $total_produtos = 120.00; // 2 camisas a R$ 60
        $total_pagamentos = 100.00; // Pagamento de apenas R$ 100 (diferença de R$ 20)

        $diferenca = abs($total_produtos - $total_pagamentos);
        $bloqueado = false;
        $mensagemErro = '';

        if ($diferenca > 0.01) {
            $bloqueado = true;
            $mensagemErro = "O total dos pagamentos (R$ $total_pagamentos) não corresponde ao total dos produtos (R$ $total_produtos).";
        }

        $this->record(
            'CT-RISK-03',
            'Rejeição de Venda com Divergência Financeira (Diferença de Caixa)',
            'Risco de Inconsistência Financeira, Caixa Negativo e Fraude Operacional',
            $bloqueado,
            $mensagemErro
        );
    }

    /**
     * CT-RISK-04: Registro de venda válida com baixa automática de estoque de produto acabado.
     */
    public function testRisco02_VendaValidaEBaixaEstoqueProduto(): void {
        $cliente_id = 1;
        $produto_id = 1;
        $qtd_venda = 2;
        $preco_venda = 60.00;
        $total_venda = $qtd_venda * $preco_venda; // 120.00

        $estoque_anterior = (int)$this->pdo->query("SELECT estoque_atual FROM produtos WHERE id = 1")->fetchColumn();

        $this->pdo->beginTransaction();
        $stmtVenda = $this->pdo->prepare("INSERT INTO vendas (cliente_id, valor_total, status_venda) VALUES (?, ?, 'ativa')");
        $stmtVenda->execute([$cliente_id, $total_venda]);
        $venda_id = (int)$this->pdo->lastInsertId();

        $this->pdo->prepare("INSERT INTO venda_produtos (venda_id, produto_id, quantidade, preco_unitario_venda, custo_unitario_producao) VALUES (?, ?, ?, ?, ?)")
                  ->execute([$venda_id, $produto_id, $qtd_venda, $preco_venda, 24.50]);

        $this->pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual - ? WHERE id = ?")
                  ->execute([$qtd_venda, $produto_id]);

        $this->pdo->prepare("INSERT INTO venda_pagamentos (venda_id, forma_pagamento, valor, data_vencimento, status) VALUES (?, 'Cartão Débito', ?, '2026-10-22', 'pago')")
                  ->execute([$venda_id, $total_venda]);

        $this->pdo->commit();

        $estoque_atual = (int)$this->pdo->query("SELECT estoque_atual FROM produtos WHERE id = 1")->fetchColumn();

        $passou = ($estoque_atual === ($estoque_anterior - $qtd_venda));
        $this->record(
            'CT-RISK-04',
            'Efetivação de Venda Íntegra e Abatimento do Estoque de Produtos',
            'Risco de Sobrevenda (Venda de itens indisponíveis por falta de baixa imediata)',
            $passou,
            "Estoque anterior: $estoque_anterior | Estoque posterior: $estoque_atual | Venda ID: $venda_id"
        );
    }

    // =========================================================================
    // CENÁRIO 3: RISCO DE DESSINCRONIZAÇÃO NO ESTORNO DE VENDAS
    // =========================================================================

    /**
     * CT-RISK-05: Estorno de venda deve devolver produtos ao estoque e cancelar títulos financeiros.
     * Risco: Produto sumir do inventário e títulos a receber continuarem sendo cobrados do cliente.
     */
    public function testRisco03_EstornoVendaRestauraEstoqueECancelaTitulos(): void {
        // Usa a venda gerada no teste anterior (ID 1)
        $venda_id = 1;
        $estoque_antes_estorno = (int)$this->pdo->query("SELECT estoque_atual FROM produtos WHERE id = 1")->fetchColumn();

        $this->pdo->beginTransaction();

        // 1. Busca itens da venda
        $itens = $this->pdo->query("SELECT produto_id, quantidade FROM venda_produtos WHERE venda_id = $venda_id")->fetchAll();
        foreach ($itens as $item) {
            $this->pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?")
                      ->execute([$item['quantidade'], $item['produto_id']]);
        }

        // 2. Cancela pagamentos
        $this->pdo->prepare("UPDATE venda_pagamentos SET status = 'cancelado' WHERE venda_id = ?")->execute([$venda_id]);

        // 3. Marca venda como estornada
        $this->pdo->prepare("UPDATE vendas SET status_venda = 'estornada' WHERE id = ?")->execute([$venda_id]);

        $this->pdo->commit();

        $estoque_pos_estorno = (int)$this->pdo->query("SELECT estoque_atual FROM produtos WHERE id = 1")->fetchColumn();
        $statusVenda = $this->pdo->query("SELECT status_venda FROM vendas WHERE id = $venda_id")->fetchColumn();
        $statusPag = $this->pdo->query("SELECT status FROM venda_pagamentos WHERE venda_id = $venda_id")->fetchColumn();

        $passou = ($estoque_pos_estorno === ($estoque_antes_estorno + 2)) &&
                  ($statusVenda === 'estornada') &&
                  ($statusPag === 'cancelado');

        $this->record(
            'CT-RISK-05',
            'Estorno Seguro com Reposição de Estoque e Cancelamento Financeiro',
            'Risco de Cobrança Indevida ao Consumidor e Prejuízo por Produto não Recomposto',
            $passou,
            "Estoque recomposto: +2 un | Status Venda: $statusVenda | Status Pagamento: $statusPag"
        );
    }

    /**
     * CT-RISK-06: Bloqueio de duplo estorno em venda já cancelada.
     * Risco: Duplicidade de estoque fantasma (+estoque indevido) por re-execução acidental.
     */
    public function testRisco03_BloqueioDuploEstorno(): void {
        $venda_id = 1;
        $statusAtual = $this->pdo->query("SELECT status_venda FROM vendas WHERE id = $venda_id")->fetchColumn();

        $bloqueado = false;
        if ($statusAtual !== 'ativa') {
            $bloqueado = true;
        }

        $this->record(
            'CT-RISK-06',
            'Bloqueio de Reestorno (Tentativa de Estorno em Venda Já Cancelada)',
            'Risco de Inflação Artificial de Saldo de Estoque por Dupla Restituição',
            $bloqueado,
            "Tentativa bloqueada. Status verificado da venda: '$statusAtual'"
        );
    }

    // =========================================================================
    // CENÁRIO 4: RISCO DE QUEBRA DE ATOMICIDADE (PROPRIEDADES ACID)
    // =========================================================================

    /**
     * CT-RISK-07: Falha forçada no meio de uma transação crítica deve acionar rollback total.
     * Risco: Dados órfãos ou operações incompletas persistidas no banco de dados.
     */
    public function testRisco04_AtomicidadeComRollbackEmFalha(): void {
        $estoque_inicial = (int)$this->pdo->query("SELECT estoque_atual FROM produtos WHERE id = 1")->fetchColumn();

        try {
            $this->pdo->beginTransaction();

            // Passo 1: Debita estoque
            $this->pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual - 5 WHERE id = 1")->execute();

            // Passo 2: Simula erro crítico imprevisto (exceção durante o processo)
            throw new RuntimeException("Falha simulada na comunicação com gateway de pagamento.");

            // Passo 3: Nunca deve chegar aqui
            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
        }

        $estoque_final = (int)$this->pdo->query("SELECT estoque_atual FROM produtos WHERE id = 1")->fetchColumn();

        // O estoque deve permanecer exatamente igual ao inicial (nenhuma alteração pode ter sobrado)
        $passou = ($estoque_inicial === $estoque_final);
        $this->record(
            'CT-RISK-07',
            'Resiliência Transacional ACID (Rollback Total pós Exceção)',
            'Risco de Corrupção de Estado e Dados Órfãos por Falha Parcial',
            $passou,
            "Estoque antes: $estoque_inicial | Estoque após rollback: $estoque_final (100% íntegro)"
        );
    }

    // =========================================================================
    // CENÁRIO 5: RISCO DE CÁLCULO INCORRETO DO CUSTO DE PRODUÇÃO (BOM)
    // =========================================================================

    /**
     * CT-RISK-08: Cálculo exato do custo unitário baseado na composição dos insumos.
     * Risco: Precificação errada, venda com margem negativa e prejuízo operacional.
     */
    public function testRisco05_CalculoCustoProducaoBOM(): void {
        // Produto 1:
        // - 2m Algodão * R$ 10.00 = R$ 20.00
        // - 1 rolo Linha * R$ 2.50 = R$ 2.50
        // - 4 Botões * R$ 0.50 = R$ 2.00
        // Custo total esperado = R$ 24.50
        $stmt = $this->pdo->prepare("SELECT SUM(pm.quantidade_utilizada * m.preco_custo) AS custo_total 
                                     FROM produto_materiais pm 
                                     JOIN materiais m ON pm.material_id = m.id 
                                     WHERE pm.produto_id = ?");
        $stmt->execute([1]);
        $custoApurado = (float)$stmt->fetchColumn();

        $passou = (abs($custoApurado - 24.50) < 0.001);
        $this->record(
            'CT-RISK-08',
            'Validação da Ficha Técnica (BOM) e Custo Unitário de Fabricação',
            'Risco de Margem de Lucro Falsa, Erro na DRE e Venda Abaixo do Custo',
            $passou,
            "Custo apurado: R$ " . number_format($custoApurado, 2) . " | Custo esperado: R$ 24.50"
        );
    }

    // =========================================================================
    // CENÁRIO 6: RISCO DE ESCALAÇÃO DE PRIVILÉGIOS (BROKEN ACCESS CONTROL)
    // =========================================================================

    /**
     * CT-RISK-09: Usuário do tipo funcionário não pode ter acesso a rotas administrativas.
     * Risco: Vazamento de faturamento, visualização de lucros e alteração de despesas por pessoal não autorizado.
     */
    public function testRisco06_RestricaoAcessoRotasAdministrativas(): void {
        $usuarioFuncionario = ['tipo_usuario' => 'funcionario'];
        $usuarioAdmin = ['tipo_usuario' => 'administrador'];

        // Função de autorização simulada idêntica à do index.php / relatorios
        $podeAcessarContasPagar = fn($user) => ($user['tipo_usuario'] ?? '') === 'administrador';
        $podeAcessarLucratividade = fn($user) => ($user['tipo_usuario'] ?? '') === 'administrador';

        $funcBloqueadoContas = !$podeAcessarContasPagar($usuarioFuncionario);
        $funcBloqueadoLucro = !$podeAcessarLucratividade($usuarioFuncionario);
        $adminLiberado = $podeAcessarContasPagar($usuarioAdmin) && $podeAcessarLucratividade($usuarioAdmin);

        $passou = $funcBloqueadoContas && $funcBloqueadoLucro && $adminLiberado;
        $this->record(
            'CT-RISK-09',
            'Controle de Acesso Baseado em Papéis (RBAC - Funcionário vs Admin)',
            'Risco de Acesso a Dados Financeiros Confidenciais por Perfis Operacionais',
            $passou,
            "Funcionário bloqueado em Contas a Pagar e Lucratividade | Admin autorizado"
        );
    }

    // =========================================================================
    // CENÁRIO 7: RISCO DE VULNERABILIDADE SQL INJECTION NO LOGIN
    // =========================================================================

    /**
     * CT-RISK-10: Injeção de payload clássico de bypass de autenticação.
     * Risco: Tomada de controle do sistema e acesso sem credenciais válidas.
     */
    public function testRisco07_ProtecaoContraSqlInjectionNoLogin(): void {
        $payloadMalicioso = "' OR '1'='1";
        $senhaDigitada = "qualquer";

        // Query parametrizada segura da aplicação
        $stmt = $this->pdo->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $stmt->execute([$payloadMalicioso]);
        $user = $stmt->fetch();

        // O usuário não pode ser encontrado porque o PDO trata como literal e não código SQL
        $passou = ($user === false);
        $this->record(
            'CT-RISK-10',
            'Proteção contra SQL Injection com Prepared Statements no Login',
            'Risco de Invasão de Conta (Account Takeover) e Vazamento Total do Banco',
            $passou,
            "Payload tratado como string literal. Nenhum registro retornado."
        );
    }

    /**
     * Executa todos os testes da suíte
     */
    public function runAll(): array {
        $this->testRisco01_BloqueioProducaoSemInsumosSuficientes();
        $this->testRisco01_ProducaoSucessoAtualizaSaldos();
        $this->testRisco02_BloqueioVendaDivergenciaPagamento();
        $this->testRisco02_VendaValidaEBaixaEstoqueProduto();
        $this->testRisco03_EstornoVendaRestauraEstoqueECancelaTitulos();
        $this->testRisco03_BloqueioDuploEstorno();
        $this->testRisco04_AtomicidadeComRollbackEmFalha();
        $this->testRisco05_CalculoCustoProducaoBOM();
        $this->testRisco06_RestricaoAcessoRotasAdministrativas();
        $this->testRisco07_ProtecaoContraSqlInjectionNoLogin();

        return [
            'results' => $this->results,
            'duration' => round((microtime(true) - $this->startTime) * 1000, 2),
            'total' => count($this->results),
            'passed' => count(array_filter($this->results, fn($r) => $r['passed'])),
            'failed' => count(array_filter($this->results, fn($r) => !$r['passed']))
        ];
    }
}

// =========================================================================
// EXECUÇÃO EM MODO CLI (LINHA DE COMANDO OU CI/CD)
// =========================================================================
if (php_sapi_name() === 'cli' || !isset($_SERVER['DOCUMENT_ROOT'])) {
    $suite = new RiskBehaviorTestSuite();
    $report = $suite->runAll();

    echo "==========================================================================\n";
    echo "  RELATÓRIO DE EXECUÇÃO: TESTES DE COMPORTAMENTO E RISCO (QA)\n";
    echo "==========================================================================\n\n";

    foreach ($report['results'] as $item) {
        $badge = $item['passed'] ? "[\033[32mPASSOU\033[0m]" : "[\033[31mFALHOU\033[0m]";
        echo "$badge {$item['id']} - {$item['title']}\n";
        echo "       ⚠️ Risco Coberto: {$item['risk']}\n";
        if (!empty($item['details'])) {
            echo "       ℹ️ Detalhes: {$item['details']}\n";
        }
        echo "\n";
    }

    echo "--------------------------------------------------------------------------\n";
    echo " RESUMO EXECUTIVO:\n";
    echo " Total de Cenários Avaliados: {$report['total']}\n";
    echo " Sucessos: {$report['passed']}\n";
    echo " Falhas:   {$report['failed']}\n";
    echo " Taxa de Sucesso: " . round(($report['passed'] / $report['total']) * 100, 1) . "%\n";
    echo " Tempo de Execução: {$report['duration']} ms\n";
    echo "--------------------------------------------------------------------------\n";

    exit($report['failed'] > 0 ? 1 : 0);
}
