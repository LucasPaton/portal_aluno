<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/forum.php';

$avisos = listarAvisosGerais();

$pageTitle  = 'Avisos Gerais';
$currentNav = 'avisos';
$depth      = 2;
include '../_layout.php';
?>

<div class="grid-2">
    <!-- NOVO AVISO -->
    <div class="card">
        <div class="card-header"><span class="card-title">📢 Publicar Aviso Geral</span></div>
        <div class="card-body">
            <form method="post" action="../../controller/controlador.php">
                <input type="hidden" name="operacao" value="criarPost">
                <input type="hidden" name="tipo" value="aviso">
                <!-- idTurma NULL = aviso geral -->
                <div class="form-group">
                    <label class="form-label">Título *</label>
                    <input type="text" name="titulo" class="form-control" required placeholder="Título do aviso...">
                </div>
                <div class="form-group">
                    <label class="form-label">Conteúdo *</label>
                    <textarea name="conteudo" class="form-control" rows="5" required placeholder="Escreva o aviso para todos os usuários..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Fixar no topo?</label>
                    <select name="fixado" class="form-control">
                        <option value="0">Não</option>
                        <option value="1">Sim — fixar</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">📤 Publicar aviso</button>
            </form>
        </div>
    </div>

    <!-- AVISOS EXISTENTES -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Avisos publicados (<?= count($avisos) ?>)</span>
        </div>
        <div class="card-body" style="padding:0;max-height:500px;overflow-y:auto;">
            <?php if (empty($avisos)): ?>
                <p class="text-muted text-center" style="padding:2rem;">Nenhum aviso publicado.</p>
            <?php else: ?>
            <?php foreach ($avisos as $a): ?>
            <div style="padding:.875rem 1.25rem;border-bottom:1px solid var(--border);">
                <div class="flex justify-between items-center mb-1">
                    <strong style="font-size:.9rem;"><?= $a['fixado'] ? '📌 ' : '' ?><?= htmlspecialchars($a['titulo'] ?: 'Aviso') ?></strong>
                </div>
                <p class="text-muted" style="font-size:.8125rem;line-height:1.5;margin-bottom:.375rem;">
                    <?= htmlspecialchars(substr($a['conteudo'], 0, 150)) ?>
                    <?= strlen($a['conteudo']) > 150 ? '...' : '' ?>
                </p>
                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($a['dataPostagem'])) ?> · <?= htmlspecialchars($a['nomeAutor']) ?></small>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

        </main></div></div></body></html>
