<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/materiais.php';
require_once '../../model/turmas.php';

$busca = filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$tipo  = filter_input(INPUT_GET, 'tipo',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;

$materiais = listarMateriais($busca, $tipo, $pagina);
$total     = contarMateriais($busca, $tipo);
$totalPag  = ceil($total / 20);

$pageTitle  = 'Materiais Didáticos';
$currentNav = 'materiais';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 style="font-size:1.125rem;font-weight:700;">📦 Materiais Didáticos</h2>
        <p class="text-muted"><?= $total ?> material(is) cadastrado(s)</p>
    </div>
    <a href="formMaterial.php" class="btn btn-primary">➕ Novo Material</a>
</div>

<!-- FILTROS -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center" style="flex-wrap:wrap;">
            <input type="text" name="busca" class="form-control" placeholder="Buscar material..." value="<?= htmlspecialchars($busca) ?>" style="max-width:300px;">
            <select name="tipo" class="form-control" style="max-width:200px;">
                <option value="">Todos os tipos</option>
                <option value="apostila" <?= $tipo === 'apostila' ? 'selected' : '' ?>>📄 Apostila</option>
                <option value="video" <?= $tipo === 'video' ? 'selected' : '' ?>>🎬 Vídeo</option>
                <option value="software" <?= $tipo === 'software' ? 'selected' : '' ?>>💻 Software</option>
                <option value="equipamento" <?= $tipo === 'equipamento' ? 'selected' : '' ?>>🔧 Equipamento</option>
                <option value="livro" <?= $tipo === 'livro' ? 'selected' : '' ?>>📚 Livro</option>
                <option value="outro" <?= $tipo === 'outro' ? 'selected' : '' ?>>📋 Outro</option>
            </select>
            <button type="submit" class="btn btn-outline">🔍 Filtrar</button>
        </form>
    </div>
</div>

<!-- TABELA -->
<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($materiais)): ?>
            <p class="text-muted text-center" style="padding:2rem;">Nenhum material encontrado.</p>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Tipo</th>
                        <th>Curso</th>
                        <th>Preço</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($materiais as $m): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($m['nome']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars(substr($m['descricao'], 0, 60)) ?><?= strlen($m['descricao']) > 60 ? '...' : '' ?></small>
                    </td>
                    <td>
                        <span class="badge badge-primary"><?= htmlspecialchars(ucfirst($m['tipo'])) ?></span>
                    </td>
                    <td><?= htmlspecialchars($m['nomeCurso'] ?? '—') ?></td>
                    <td><?= $m['preco'] > 0 ? 'R$ ' . number_format($m['preco'], 2, ',', '.') : '<span class="badge badge-success">Gratuito</span>' ?></td>
                    <td>
                        <?php if ($m['disponivel']): ?>
                            <span class="badge badge-success">Disponível</span>
                        <?php else: ?>
                            <span class="badge badge-muted">Indisponível</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="formMaterial.php?id=<?= $m['idMaterial'] ?>" class="btn btn-sm btn-outline">✏️ Editar</a>
                        <a href="../../controller/controlador.php?operacao=desativarMaterial&id=<?= $m['idMaterial'] ?>"
                           class="btn btn-sm btn-danger" data-confirm="Remover este material?">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- PAGINAÇÃO -->
<?php if ($totalPag > 1): ?>
<div class="flex justify-between items-center mt-4">
    <span class="text-muted">Página <?= $pagina ?> de <?= $totalPag ?></span>
    <div class="flex gap-2">
        <?php if ($pagina > 1): ?>
            <a href="?pagina=<?= $pagina - 1 ?>&busca=<?= urlencode($busca) ?>&tipo=<?= urlencode($tipo) ?>" class="btn btn-sm btn-outline">← Anterior</a>
        <?php endif; ?>
        <?php if ($pagina < $totalPag): ?>
            <a href="?pagina=<?= $pagina + 1 ?>&busca=<?= urlencode($busca) ?>&tipo=<?= urlencode($tipo) ?>" class="btn btn-sm btn-outline">Próxima →</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

        </main></div></div></body></html>
