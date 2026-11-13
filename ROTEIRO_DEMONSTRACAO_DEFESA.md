# 🎙️ ROTEIRO DE DEMONSTRAÇÃO E DEFESA TÉCNICA (10 A 12 MINUTOS)
## Apresentação Funcional da Pipeline de CI/CD, Testes de Risco e Quality Gates

---

**Disciplina:** Qualidade de Software / Engenharia de Software  
**Projeto:** Sistema Integrado de Gestão de Produção e Vendas  
**Tempo Total de Apresentação:** 10 a 12 minutos  
**Formato:** Apresentação Funcional com Demonstração Prática (Slides/GitHub/Terminal)  

---

## ⏱️ CRONOGRAMA MINUTO A MINUTO

```text
┌───────────────┬────────────────────────────────────────────────────────┬──────────┐
│ Bloco de Tempo│ Etapa da Apresentação                                  │ Duração  │
├───────────────┼────────────────────────────────────────────────────────┼──────────┤
│ 00:00 - 01:30 │ 1. Introdução, Contexto do Negócio e Problema          │ 1m 30s   │
│ 01:30 - 03:30 │ 2. Arquitetura da Esteira de CI/CD (GitHub Actions)    │ 2m 00s   │
│ 03:30 - 05:30 │ 3. Suíte de Testes Automatizados e Matriz de Riscos    │ 2m 00s   │
│ 05:30 - 08:00 │ 4. Demonstração Prática do Experimento e Quality Gates │ 2m 30s   │
│ 08:00 - 09:30 │ 5. Métricas, Resultados e Débito Técnico Identificado  │ 1m 30s   │
│ 09:30 - 11:00 │ 6. Conclusão e Lições Aprendidas                       │ 1m 30s   │
│ 11:00 - 12:00 │ 7. Margem de Contingência / Abertura para Perguntas    │ 1m 00s   │
└───────────────┴────────────────────────────────────────────────────────┴──────────┘
```

---

## 🎬 ROTEIRO DETALHADO: O QUE FALAR E O QUE MOSTRAR NA TELA

---

### BLOCO 1: INTRODUÇÃO, CONTEXTO E O PROBLEMA (00:00 - 01:30)

#### 🖥️ O que mostrar na tela:
- Slide inicial ou a página principal do repositório no GitHub com o [`README.md`](README.md).
- Destacar os badges de **PHP**, **MySQL**, **Bootstrap** e **CI/CD: GitHub Actions**.

#### 🗣️ Roteiro de fala:
> *"Boa noite, professor e colegas. Hoje apresento o trabalho final da disciplina de Qualidade de Software, desenvolvido sobre o Sistema Integrado de Gestão de Produção e Vendas.*
>
> *O domínio desta aplicação é a manufatura e confecção, englobando controle de insumos, ficha técnica de produtos, ordens de fabricação, vendas e contas a pagar e receber.*
>
> *Em sistemas industriais e de varejo, a qualidade de software não é apenas estética: um defeito lógico gera impactos imediatos no mundo físico e financeiro. Por exemplo, permitir que uma produção inicie sem insumos em estoque gera o chamado 'estoque negativo' e paralisa a fábrica. Da mesma forma, registrar uma venda cujo valor total difere das formas de pagamento gera um rombo contábil no caixa.*
>
> *O objetivo central do nosso projeto foi construir uma esteira automatizada de CI/CD e implementar Quality Gates inegociáveis que impedem que qualquer código com defeito chegue aos usuários finais."*

---

### BLOCO 2: ARQUITETURA DA PIPELINE DE CI/CD (01:30 - 03:30)

#### 🖥️ O que mostrar na tela:
- A aba **"Actions"** do GitHub (ou o arquivo [`.github/workflows/ci.yml`](.github/workflows/ci.yml)).
- Mostrar o diagrama de fluxo com os 5 jobs da esteira.

#### 🗣️ Roteiro de fala:
> *"Nossa esteira foi construída no GitHub Actions e é acionada automaticamente a cada push ou pull request nas branches principais.*
>
> *A pipeline foi dividida em cinco estágios estratégicos:*
> 1. *O primeiro job é o **php-lint**, que testa a sintaxe em quatro versões ativas do PHP: 8.0, 8.1, 8.2 e 8.3, garantindo retrocompatibilidade e tolerância zero a erros de compilação.*
> 2. *O segundo job é o **database-integration**, que provisiona um container de serviço oficial com MySQL 8.0, importa o script DDL e DML do banco `banco.sql` e valida as conexões PDO.*
> 3. *O terceiro job é o **security-audit**, uma varredura estática que assegura que nenhum arquivo de certificado ou chave privada (como `.pem` ou `.key`) seja comitado no código.*
> 4. *O quarto job é o coração da nossa garantia de qualidade: o **quality-gate**, um guardião mandatório que avalia quatro critérios rígidos.*
> 5. *E por fim, o job de **build-and-package**, que só é executado se todos os Quality Gates forem aprovados, gerando o pacote ZIP de distribuição para deploy."*

---

### BLOCO 3: SUÍTE DE TESTES E MATRIZ DE RISCOS (03:30 - 05:30)

#### 🖥️ O que mostrar na tela:
- O arquivo [`sistemaproducao/tests/RiskBehaviorTestSuite.php`](sistemaproducao/tests/RiskBehaviorTestSuite.php) e a tabela de matriz de riscos no [`RELATORIO_FINAL_QUALIDADE_DE_SOFTWARE.md`](RELATORIO_FINAL_QUALIDADE_DE_SOFTWARE.md).

#### 🗣️ Roteiro de fala:
> *"Para testar o sistema, não criamos apenas testes triviais. Mapeamos os dez maiores riscos operacionais do negócio e construímos uma suíte em PHP puro, desacoplada e independente de frameworks pesados, rodando sobre banco em memória com tempo de execução de apenas 14 milissegundos.*
>
> *Destaco quatro testes críticos da nossa suíte:*
> - *O **CT-RISK-01**, que valida a ordem de produção: se um produto exige 20 botões e o almoxarifado só tem 5, o sistema aborta a operação e impede que o saldo fique negativo.*
> - *O **CT-RISK-03**, que valida a paridade financeira: se o total dos produtos é R$ 120,00 e o pagamento informado é R$ 100,00, a venda é estritamente rejeitada por divergência de caixa.*
> - *O **CT-RISK-07**, que valida as propriedades ACID do banco: forçamos uma falha no meio do processo e confirmamos que o comando `rollBack()` desfaz 100% das alterações, sem deixar dados corrompidos.*
> - *E o **CT-RISK-10**, que submete um payload de SQL Injection no login e comprova que os Prepared Statements tratam a entrada como texto puro, neutralizando o ataque."*

---

### BLOCO 4: DEMONSTRAÇÃO PRÁTICA DO EXPERIMENTO E QUALITY GATES (05:30 - 08:00)
*(⭐ Este é o momento de maior impacto da apresentação!)*

#### 🖥️ O que mostrar na tela:
- Terminal rodando o script [`sistemaproducao/tests/run_experiment.php`](sistemaproducao/tests/run_experiment.php) (ou exibir a seção 3 do [`EXPERIMENTO_QUALITY_GATES.md`](EXPERIMENTO_QUALITY_GATES.md)).
- Mostrar o scorecard dos Quality Gates e a mensagem de bloqueio.

#### 🗣️ Roteiro de fala:
> *"Agora apresento a validação empírica da nossa solução: realizamos um experimento comparando dois cenários controlados:*
>
> *No **Cenário A (Linha de Base)**, executamos o sistema estável. Todos os 10 testes passam, a taxa de sucesso é de 100%, os 4 Quality Gates são aprovados com código de saída 0 e a pipeline libera o pacote de deploy.*
>
> *No **Cenário B**, realizamos um teste de mutação com injeção deliberada de falhas: removemos intencionalmente a trava de verificação monetária no arquivo `vendas/actions.php`, simulando um descuido onde o sistema aceitaria vender R$ 120,00 recebendo apenas R$ 100,00.*
>
> *Vejam o que acontece no terminal e na esteira:*
> - *O teste **CT-RISK-03 acusa a falha imediatamente**.*
> - *A taxa de sucesso cai para 90%, violando o Quality Gate QG-02.*
> - *O script do Quality Gate emite o código **exit(1)**.*
> - *E o GitHub Actions **interrompe imediatamente a execução**, impedindo o deploy e cancelando o job de release.*
>
> *Isso comprova cientificamente que nossos Quality Gates não são apenas relatórios decorativos: eles têm poder de veto real e blindam a produção."*

---

### BLOCO 5: RESULTADOS E DÉBITO TÉCNICO IDENTIFICADO (08:00 - 09:30)

#### 🖥️ O que mostrar na tela:
- Tabela comparativa de resultados do relatório e a seção de **Limitações do Sistema**.

#### 🗣️ Roteiro de fala:
> *"Em termos de resultados, nossa pipeline roda em menos de 1 minuto e meio no GitHub Actions, garantindo feedback ultra-rápido para os desenvolvedores.*
>
> *Como engenheiros de software, também tivemos a maturidade técnica de mapear as limitações e o débito técnico da aplicação:*
> 1. *O primeiro débito é a segurança de senhas: no protótipo acadêmico inicial, a verificação era feita em texto puro. No relatório, já detalhamos o plano de migração para o algoritmo Argon2id com hash criptográfico.*
> 2. *O segundo débito é o acoplamento: o sistema atual opera em scripts modulares, mas recomendamos a migração futura para um padrão MVC formal com injeção de dependências.*
> 3. *E o terceiro é a adição futura de testes de interface End-to-End com Playwright."*

---

### BLOCO 6: CONCLUSÃO E ENCERRAMENTO (09:30 - 11:00)

#### 🖥️ O que mostrar na tela:
- O resumo executivo do [`RELATORIO_FINAL_QUALIDADE_DE_SOFTWARE.md`](RELATORIO_FINAL_QUALIDADE_DE_SOFTWARE.md) ou a tela final com os autores e links do repositório.

#### 🗣️ Roteiro de fala:
> *"Para concluir: este projeto demonstrou na prática como conceitos teóricos de Qualidade de Software — como análise de riscos, transações ACID, testes automatizados e Quality Gates — são aplicados para proteger o negócio de uma empresa.*
>
> *Entregamos um repositório completo com documentação técnica de mais de 8 páginas sem anexos, suíte de testes executável, histórico experimental comparativo e esteira de CI/CD 100% funcional no GitHub Actions.*
>
> *Agradeço a atenção de todos e fico à disposição da banca para perguntas."*

---

### BLOCO 7: MARGEM E ABERTURA PARA PERGUNTAS (11:00 - 12:00)
*(Espaço para o professor e avaliadores fazerem considerações).*

---

## 🎯 GUIA DE PERGUNTAS E RESPOSTAS DA BANCA (FAQ DEFESA)

Esteja preparado caso a banca faça estas perguntas clássicas:

#### 1. *"Por que vocês escolheram SQLite em memória nos testes se o sistema usa MySQL?"*
> **Resposta:** *"Excelente pergunta. O SQLite em memória roda exatamente a mesma sintaxe SQL ANSI do MySQL para as tabelas e transações, mas executa em milissegundos sem precisar de rede ou servidor ativo. Para garantir a fidelidade absoluta com o ambiente de produção, nosso pipeline no GitHub Actions também roda em um container MySQL 8.0 real no job `database-integration`."*

#### 2. *"O que acontece exatamente se alguém tentar subir um commit com erro de sintaxe?"*
> **Resposta:** *"O primeiro job da esteira, `php-lint`, roda `php -l` em todos os arquivos em quatro versões do PHP (8.0 a 8.3). Ao encontrar um erro, o job falha imediatamente, a esteira entra em estado FAILED e o Quality Gate impede qualquer possibilidade de release."*

#### 3. *"Qual foi o critério para definir que a tolerância de falhas no Quality Gate QG-02 é zero?"*
> **Resposta:** *"Os testes de risco que modelamos não testam cores ou botões; testam transações bancárias, cancelamento de cobranças e furos de estoque. No ambiente corporativo, aceitar 95% de sucesso significaria tolerar que 5% das vendas causassem rombo de caixa. Por isso, a meta de 100% de aprovação é mandatória."*

---

## 📦 O QUE COMMITAR E EM QUAL PASTA

Para disponibilizar este roteiro no repositório:

| Arquivo | Pasta onde colocar |
|---|---|
| **[`ROTEIRO_DEMONSTRACAO_DEFESA.md`](ROTEIRO_DEMONSTRACAO_DEFESA.md)** | Na **raiz** do projeto *(junto com o `README.md`)* |

### 💻 Comandos para o Git:
```bash
git add ROTEIRO_DEMONSTRACAO_DEFESA.md
git commit -m "docs: adiciona roteiro cronometrado de demonstracao e defesa tecnica (10 a 12 min)"
git push
```
