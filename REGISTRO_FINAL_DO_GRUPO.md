# 📋 Registro Final do Grupo (Ficha de Entrega • Trio)
### Disciplina: Qualidade de Software • Trabalho Prático
**Projeto Prático DevOps e QAOps — Pipeline de Qualidade, Automação e Evidências**  
**Instituição:** Universidade Federal de Santa Maria (UFSM) • Sistemas de Informação

---

| Campo | Registro do Grupo (Trio) |
|---|---|
| **Integrante 1** | Felipe |
| **Integrante 2** | Luis |
| **Integrante 3** | Gabrielle |
| **Repositório** | https://github.com/lluuiis/trabalhoDeQualidadeDeSoftware |
| **Contribuição do integrante 1 (Felipe)** | Arquitetura técnica da esteira de CI/CD (GitHub Actions) nas 6 etapas, configuração de dependências declarativas (`composer.json`), provisionamento do container MySQL e automação dos scripts de build e empacotamento de release. |
| **Contribuição do integrante 2 (Luis)** | Desenvolvimento da suíte de testes automatizados de comportamento e riscos críticos (`RiskBehaviorTestSuite.php` e `ci_test.php`), codificação do motor dos Quality Gates com lógica de veto (`quality_gate.php`) e execução do experimento prático de injeção de falhas. |
| **Contribuição do integrante 3 (Gabrielle)** | Redação integral, estruturação editorial e formatação textual técnica do Relatório Técnico de Engenharia de Software nas 9 seções normativas, diagramação visual das tabelas e fichas dos Quality Gates, e padronização editorial do repositório e roteiro de defesa. |
| **Limitação mais importante** | Ausência de testes de interface ponta a ponta (E2E no navegador) e necessidade de refatoração do armazenamento de senhas do sistema legado para hash moderno (Argon2id/Bcrypt). |
| **Confirmação** | [X] Todos os integrantes do trio revisaram o repositório, as evidências e o relatório técnico. |

---

### 📦 Itens Entregues e Validados no Repositório:
1. **Repositório Executável:** Código versionado, README completo, dependências declaradas (`composer.json`) e ausência de segredos.
2. **Pipeline de CI/CD:** Arquivo `.github/workflows/ci.yml` cobrindo o fluxo mínimo das 6 etapas obrigatórias.
3. **Testes Automatizados:** Testes de integração com banco MySQL (`ci_test.php`) e testes de riscos e comportamentos críticos (`RiskBehaviorTestSuite.php`).
4. **Quality Gates:** Implementação no script `quality_gate.php` avaliando 4 critérios inegociáveis (`QG-01` a `QG-04`) com bloqueio em falhas (`exit 1`).
5. **Experimento Obrigatório:** Documentado em `EXPERIMENTO_QUALITY_GATES.md` com comparação empírica de cenário aprovado vs reprovado por mutação controlada.
6. **Relatório Técnico:** Documentado em `RELATORIO_FINAL_QUALIDADE_DE_SOFTWARE.md` estruturado nas 9 seções normativas.
7. **Roteiro da Demonstração:** Documentado em `ROTEIRO_DEMONSTRACAO_DEFESA.md` com cronograma minuto a minuto da apresentação (10 a 12 minutos).
