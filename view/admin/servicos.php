<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/servicos.php';

$busca     = filter_input(INPUT_GET, 'busca',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$categoria = filter_input(INPUT_GET, 'categoria', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$pagina    = filter_input(INPUT_GET, 'pagina',    FILTER_VALIDATE_INT) ?: 1;

$servicos = listarServicos($busca, $categoria, $pagina);
$total    = contarServicos($busca, $categoria);
$totalPag = ceil($total / 20);

$pageTitle  = 'Serviços Acadêmicos';
$currentNav = 'servicos';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 style="font-size:1.125rem;font-weight:700;">🛠️ Serviços Acadêmicos</h2>
        <p class="text-muted"><?= $total ?> serviço(s) cadastrado(s)</p>
    </div>
    <a href="formServico.php" class="btn btn-primary">➕ Novo Serviço</a>
</div>

<!-- FILTROS -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center" style="flex-wrap:wrap;">
            <input type="text" name="busca" class="form-control" placeholder="Buscar serviço..." value="<?= htmlspecialchars($busca) ?>" style="max-width:300px;">
            <select name="categoria" class="form-control" style="max-width:200px;">
                <option value="">Todas as categorias</option>
                <option value="monitoria" <?= $categoria === 'monitoria' ? 'selected' : '' ?>>📖 Monitoria</option>
                <option value="biblioteca" <?= $categoria === 'biblioteca' ? 'selected' : '' ?>>📚 Biblioteca</option>
                <option value="laboratorio" <?= $categoria === 'laboratorio' ? 'selected' : '' ?>>🔬 Laboratório</option>
                <option value="secretaria" <?= $categoria === 'secretaria' ? 'selected' : '' ?>>🏛️ Secretaria</option>
                <option value="orientacao" <?= $categoria === 'orientacao' ? 'selected' : '' ?>>🧭 Orientação</option>
                <option value="outro" <?= $categoria === 'outro' ? 'selected' : '' ?>>📋 Outro</option>
            </select>
            <button type="submit" class="btn btn-outline">🔍 Filtrar</button>
        </form>
    </div>
</div>

<!-- CARDS DE SERVIÇOS -->
<div class="stats-grid">
    <?php if (empty($servicos)): ?>
        <p class="text-muted text-center" style="padding:2rem;grid-column:1/-1;">Nenhum serviço encontrado.</p>
    <?php else: ?>
    <?php foreach ($servicos as $s): ?>
    <div class="card">
        <div class="card-body">
            <div class="flex justify-between items-center mb-2">
                <span class="badge badge-info"><?= htmlspecialchars(ucfirst($s['categoria'])) ?></span>
                <?php if ($s['disponivel']): ?>
                    <span class="badge badge-success">Ativo</span>
                <?php else: ?>
                    <span class="badge badge-muted">Inativo</span>
                <?php endif; ?>
            </div>
            <h3 style="font-size:1rem;font-weight:600;margin-bottom:.5rem;"><?= htmlspecialchars($s['nome']) ?></h3>
            <p class="text-muted" style="font-size:.8125rem;margin-bottom:.75rem;"><?= htmlspecialchars(substr($s['descricao'], 0, 100)) ?></p>
            <?php if ($s['horarioFunc']): ?>
                <p style="font-size:.8125rem;margin-bottom:.25rem;">🕐 <?= htmlspecialchars($s['horarioFunc']) ?></p>
            <?php endif; ?>
            <?php if ($s['responsavel']): ?>
                <p style="font-size:.8125rem;margin-bottom:.75rem;">👤 <?= htmlspecialchars($s['responsavel']) ?></p>
            <?php endif; ?>
            <?php if ($s['valorEstimado'] > 0): ?>
                <p style="font-size:.875rem;font-weight:600;color:var(--primary);">R$ <?= number_format($s['valorEstimado'], 2, ',', '.') ?></p>
            <?php else: ?>
                <span class="badge badge-success">Gratuito</span>
            <?php endif; ?>
            <div class="flex gap-2 mt-3">
                <a href="formServico.php?id=<?= $s['idServico'] ?>" class="btn btn-sm btn-outline">✏️ Editar</a>
                <a href="../../controller/controlador.php?operacao=desativarServico&id=<?= $s['idServico'] ?>"
                   class="btn btn-sm btn-danger" data-confirm="Remover este serviço?">🗑️</a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

        </main></div></div></body></html>
