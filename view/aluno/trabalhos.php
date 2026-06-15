<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/turmas.php';
require_once '../../model/forum.php';

$idAluno = $_SESSION['idUsuario'];
$turmas  = listarTurmasAlunoV2($idAluno);

// Juntar todos os trabalhos
$todosTrabalhos = [];
foreach ($turmas as $t) {
    $trabs = listarTrabalhosTurma($t['idTurma'], true);
    foreach ($trabs as $tr) {
        $tr['nomeDisciplina'] = $t['nomeDisciplina'];
        $tr['idTurma']        = $t['idTurma'];
        $todosTrabalhos[]     = $tr;
    }
}

$pageTitle  = 'Trabalhos';
$currentNav = 'trabalhos';
$depth      = 2;
include '../_layout.php';
?>

<?php if (empty($todosTrabalhos)): ?>
<div class="card"><div class="card-body text-center text-muted" style="padding:3rem;">Nenhum trabalho disponível.</div></div>
<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:1rem;">
<?php foreach ($todosTrabalhos as $tr):
    // Verificar se já entregou
    $resEntrega = consultarSQL(
        "SELECT * FROM EntregasTrabalho WHERE idTrabalho = ? AND idAluno = ?",
        "ii", [$tr['idTrabalho'], $idAluno]
    );
    $entrega    = obterLinha($resEntrega);
    $atrasado   = $tr['dataEntrega'] && strtotime($tr['dataEntrega']) < time();
    $podeEntregar = !$atrasado || $tr['permiteAtraso'];
?>
<div class="card">
    <div class="card-header">
        <div>
            <span class="card-title"><?= htmlspecialchars($tr['titulo']) ?></span>
        </div>
        <span class="badge badge-muted"><?= htmlspecialchars($tr['nomeDisciplina']) ?></span>
    </div>
    <div class="card-body">
        <?php if ($tr['descricao']): ?>
            <p class="text-muted" style="margin-bottom:.75rem;font-size:.875rem;"><?= htmlspecialchars(substr($tr['descricao'],0,150)) ?></p>
        <?php endif; ?>

        <div class="flex gap-3 mb-3">
            <?php if ($tr['dataEntrega']): ?>
                <span class="text-muted" style="font-size:.8125rem;">
                    📅 <?= date('d/m/Y H:i', strtotime($tr['dataEntrega'])) ?>
                    <?= $atrasado ? '<span class="badge badge-danger" style="margin-left:4px;">Prazo encerrado</span>' : '<span class="badge badge-success" style="margin-left:4px;">Em prazo</span>' ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if ($entrega): ?>
            <div class="alert alert-<?= $entrega['status']==='corrigido'?'success':'info' ?>" style="margin-bottom:.75rem;">
                <?php if ($entrega['status'] === 'corrigido'): ?>
                    ✅ Corrigido · Nota: <strong><?= $entrega['nota'] !== null ? number_format($entrega['nota'],1) : 'Pendente' ?></strong>
                    <?php if ($entrega['feedback']): ?><br><small><?= htmlspecialchars($entrega['feedback']) ?></small><?php endif; ?>
                <?php else: ?>
                    📤 Entregue em <?= date('d/m/Y H:i', strtotime($entrega['dataEnvio'])) ?>
                    <br><small class="text-muted">Arquivo: <?= htmlspecialchars($entrega['arquivoNome'] ?: 'Sem arquivo') ?></small>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($podeEntregar): ?>
        <button class="btn btn-primary w-full" style="justify-content:center;"
                onclick="abrirModal(<?= $tr['idTrabalho'] ?>, <?= $tr['idTurma'] ?>, '<?= htmlspecialchars(addslashes($tr['titulo'])) ?>')">
            <?= $entrega ? '🔄 Reenviar trabalho' : '📤 Entregar trabalho' ?>
        </button>
        <?php elseif (!$entrega): ?>
        <div class="alert alert-danger" style="margin-bottom:0;">Prazo encerrado. Não é possível entregar.</div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- MODAL DE ENTREGA -->
<div id="modalEntrega" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;display:none;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.5rem;width:100%;max-width:480px;box-shadow:var(--shadow-lg);">
        <h3 style="margin-bottom:1rem;" id="modalTitulo">Entregar Trabalho</h3>
        <form method="post" action="../../controller/controlador.php" enctype="multipart/form-data">
            <input type="hidden" name="operacao" value="entregarTrabalho">
            <input type="hidden" name="idTrabalho" id="modalIdTrabalho">
            <input type="hidden" name="idTurma" id="modalIdTurma">

            <div class="form-group">
                <label class="form-label">Arquivo (PDF, DOC, ZIP...)</label>
                <input type="file" name="arquivo" class="form-control" accept=".pdf,.doc,.docx,.zip,.rar,.txt">
            </div>
            <div class="form-group">
                <label class="form-label">Comentário (opcional)</label>
                <textarea name="comentario" class="form-control" rows="3" placeholder="Observações sobre o trabalho..."></textarea>
            </div>
            <div class="flex gap-3">
                <button type="submit" class="btn btn-primary">📤 Enviar</button>
                <button type="button" class="btn btn-outline" onclick="fecharModal()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function abrirModal(idTrabalho, idTurma, titulo) {
    document.getElementById('modalIdTrabalho').value = idTrabalho;
    document.getElementById('modalIdTurma').value    = idTurma;
    document.getElementById('modalTitulo').textContent = 'Entregar: ' + titulo;
    document.getElementById('modalEntrega').style.display = 'flex';
}
function fecharModal() {
    document.getElementById('modalEntrega').style.display = 'none';
}
</script>

        </main></div></div></body></html>
