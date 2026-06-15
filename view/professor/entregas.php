<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/forum.php';
require_once '../../model/turmas.php';

$idTrabalho = filter_input(INPUT_GET, 'idTrabalho', FILTER_VALIDATE_INT);
$idTurma    = filter_input(INPUT_GET, 'idTurma',    FILTER_VALIDATE_INT);
if (!$idTrabalho) { header('Location: dashboard.php'); exit; }

$entregas = listarEntregasTurma($idTrabalho);

// Buscar dados do trabalho
require_once '../../persistencia/persistencia.php';
$resT = consultarSQL("SELECT * FROM Trabalhos WHERE idTrabalho = ?", "i", [$idTrabalho]);
$trabalho = obterLinha($resT);

$pageTitle  = 'Entregas: '.($trabalho['titulo'] ?? '');
$currentNav = 'turmas';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 style="font-size:1.125rem;font-weight:700;"><?= htmlspecialchars($trabalho['titulo'] ?? '') ?></h2>
        <p class="text-muted">
            <?= count($entregas) ?> entrega(s) recebida(s)
            <?= $trabalho['dataEntrega'] ? ' · Prazo: '.date('d/m/Y H:i', strtotime($trabalho['dataEntrega'])) : '' ?>
        </p>
    </div>
    <a href="turmaDetalhe.php?id=<?= $idTurma ?>" class="btn btn-outline btn-sm">← Voltar</a>
</div>

<?php if (empty($entregas)): ?>
<div class="card"><div class="card-body text-center text-muted" style="padding:3rem;">Nenhuma entrega recebida ainda.</div></div>
<?php else: ?>

<!-- MODAL DE CORREÇÃO -->
<div id="modalCorrecao" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.5rem;width:100%;max-width:480px;box-shadow:var(--shadow-lg);">
        <h3 style="margin-bottom:1rem;" id="modalNomeAluno">Corrigir entrega</h3>
        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao" value="corrigirEntrega">
            <input type="hidden" name="idTurma"  value="<?= $idTurma ?>">
            <input type="hidden" name="idEntrega" id="modalIdEntrega">
            <div class="form-group">
                <label class="form-label">Nota (0 a 10)</label>
                <input type="number" name="nota" class="form-control" step="0.1" min="0" max="10" required>
            </div>
            <div class="form-group">
                <label class="form-label">Feedback para o aluno</label>
                <textarea name="feedback" class="form-control" rows="3" placeholder="Comentários sobre o trabalho..."></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-success">✅ Salvar correção</button>
                <button type="button" class="btn btn-outline" onclick="fecharModalCorr()">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body" style="padding:0;">
        <table>
            <thead>
                <tr><th>Aluno</th><th>Matrícula</th><th>Arquivo</th><th>Enviado em</th><th>Status</th><th>Nota</th><th>Ação</th></tr>
            </thead>
            <tbody>
            <?php foreach ($entregas as $e): ?>
            <tr>
                <td><strong><?= htmlspecialchars($e['nomeAluno']) ?></strong></td>
                <td><span class="badge badge-muted"><?= htmlspecialchars($e['matricula']) ?></span></td>
                <td>
                    <?php if ($e['arquivoCaminho']): ?>
                        <a href="../../<?= htmlspecialchars($e['arquivoCaminho']) ?>" target="_blank" class="btn btn-sm btn-outline">📄 <?= htmlspecialchars($e['arquivoNome'] ?: 'Baixar') ?></a>
                    <?php else: ?>
                        <span class="text-muted">Sem arquivo</span>
                    <?php endif; ?>
                </td>
                <td class="text-muted" style="font-size:.8125rem;"><?= date('d/m/Y H:i', strtotime($e['dataEnvio'])) ?></td>
                <td><span class="badge badge-<?= $e['status']==='corrigido' ? 'success' : 'warning' ?>"><?= ucfirst($e['status']) ?></span></td>
                <td><?= $e['nota'] !== null ? '<strong>'.number_format($e['nota'],1).'</strong>' : '<span class="text-muted">—</span>' ?></td>
                <td>
                    <button class="btn btn-sm btn-primary"
                            onclick="abrirCorrecao(<?= $e['idEntrega'] ?>, '<?= htmlspecialchars(addslashes($e['nomeAluno'])) ?>')">
                        ✏️ Corrigir
                    </button>
                </td>
            </tr>
            <?php if ($e['comentario'] || $e['feedback']): ?>
            <tr style="background:var(--bg);">
                <td colspan="7" style="padding:.5rem 1.25rem;font-size:.8125rem;color:var(--text-muted);">
                    <?php if ($e['comentario']): ?><strong>Aluno:</strong> <?= htmlspecialchars($e['comentario']) ?>&nbsp;&nbsp;<?php endif; ?>
                    <?php if ($e['feedback']): ?><strong>Feedback:</strong> <?= htmlspecialchars($e['feedback']) ?><?php endif; ?>
                </td>
            </tr>
            <?php endif; ?>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<script>
function abrirCorrecao(idEntrega, nome) {
    document.getElementById('modalIdEntrega').value = idEntrega;
    document.getElementById('modalNomeAluno').textContent = 'Corrigir: ' + nome;
    document.getElementById('modalCorrecao').style.display = 'flex';
}
function fecharModalCorr() {
    document.getElementById('modalCorrecao').style.display = 'none';
}
</script>

        </main></div></div></body></html>
