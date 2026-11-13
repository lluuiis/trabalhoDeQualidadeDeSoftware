<?php
// modulos/produtos/producao.php
@session_start();
if (!isset($_SESSION['user_id'])) { die('Acesso negado.'); }

if (isset($_SESSION['message'])) {
    echo '<div class="alert alert-' . htmlspecialchars($_SESSION['message']['type']) . '">' . htmlspecialchars($_SESSION['message']['text']) . '</div>';
    unset($_SESSION['message']);
}
$produtos_select = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome ASC")->fetchAll();
?>

<h3>Registrar Produção de Produtos</h3>
<p>Selecione o produto e a quantidade a ser produzida. O sistema dará baixa automática no estoque de materiais correspondente.</p>
<hr>

<form method="POST" action="modulos/produtos/actions.php" class="col-md-8">
    <input type="hidden" name="action" value="produzir">
    <div class="row g-3 align-items-end">
        <div class="col-md-6">
            <label for="produto_id" class="form-label">Produto a ser Produzido</label>
            <select class="form-select" id="produto_id" name="produto_id" required>
                <option value="">Selecione um produto...</option>
                <?php foreach ($produtos_select as $produto): ?>
                    <option value="<?= $produto['id'] ?>"><?= htmlspecialchars($produto['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="quantidade" class="form-label">Quantidade a Produzir</label>
            <input type="number" class="form-control" id="quantidade" name="quantidade" min="1" required>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-success w-100">Produzir</button>
        </div>
    </div>
</form>