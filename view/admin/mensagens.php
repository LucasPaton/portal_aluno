<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/contato.php';

$busca  = filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$status = filter_input(INPUT_GET, 'status', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1;

$apenasNaoRespondidas = ($status === 'pendente');

$mensagens = listarMensagens($busca, $apenasNaoRespondidas, $pagina);
$total     = contarMensagens($busca, $apenasNaoRespondidas);
$totalPag  = ceil($total / 20);

$pageTitle  = 'Mensagens de Contato';
$currentNav = 'mensagens';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 style="font-size:1.125rem;font-weight:700;">📩 Mensagens de Contato</h2>
        <p class="text-muted"><?= $total ?> mensagem(ns) encontrada(s)</p>
    </div>
</div>

<!-- FILTROS -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center" style="flex-wrap:wrap;">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome, e-mail ou assunto..." value="<?= htmlspecialchars($busca) ?>" style="max-width:300px;">
            <select name="status" class="form-control" style="max-width:200px;">
                <option value="">Todas</option>
                <option value="pendente" <?= $status === 'pendente' ? 'selected' : '' ?>>⏳ Apenas Pendentes</option>
            </select>
            <button type="submit" class="btn btn-outline">🔍 Filtrar</button>
        </form>
    </div>
</div>

<!-- TABELA -->
<div class="card">
    <div class="card-body" style="padding:0;">
        <?php if (empty($mensagens)): ?>
            <p class="text-muted text-center" style="padding:2rem;">Nenhuma mensagem encontrada.</p>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Data</th>
                        <th>Remetente</th>
                        <th>Assunto</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($mensagens as $m): ?>
                <tr style="<?= !$m['respondida'] ? 'background-color:rgba(var(--primary-rgb),0.05);' : '' ?>">
                    <td>
                        <?php if ($m['respondida']): ?>
                            <span class="badge badge-success">Respondida</span>
                        <?php else: ?>
                            <span class="badge badge-warning">Pendente</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?= date('d/m/Y H:i', strtotime($m['criadoEm'])) ?>
                        <?php if ($m['respondidaEm']): ?>
                            <br><small class="text-muted">Resp: <?= date('d/m/Y', strtotime($m['respondidaEm'])) ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($m['nome']) ?></strong><br>
                        <small class="text-muted">
                            <a href="mailto:<?= htmlspecialchars($m['email']) ?>"><?= htmlspecialchars($m['email']) ?></a>
                            <?= $m['telefone'] ? ' | ' . htmlspecialchars($m['telefone']) : '' ?>
                        </small>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($m['assunto'] ?: 'Sem Assunto') ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars(substr($m['mensagem'], 0, 50)) ?>...</small>
                    </td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline" onclick="abrirModal(<?= htmlspecialchars(json_encode($m)) ?>)">Visualizar</button>
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
            <a href="?pagina=<?= $pagina - 1 ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>" class="btn btn-sm btn-outline">← Anterior</a>
        <?php endif; ?>
        <?php if ($pagina < $totalPag): ?>
            <a href="?pagina=<?= $pagina + 1 ?>&busca=<?= urlencode($busca) ?>&status=<?= urlencode($status) ?>" class="btn btn-sm btn-outline">Próxima →</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- MODAL DE VISUALIZAÇÃO E RESPOSTA -->
<div id="modalMensagem" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
    <div class="card" style="width:100%;max-width:600px;max-height:90vh;overflow-y:auto;">
        <div class="card-header flex justify-between items-center">
            <span class="card-title">Detalhes da Mensagem</span>
            <button onclick="fecharModal()" style="background:none;border:none;font-size:1.5rem;cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <div style="background:var(--bg);padding:1rem;border-radius:var(--radius);margin-bottom:1rem;border:1px solid var(--border);">
                <p><strong>De:</strong> <span id="modNome"></span> &lt;<a id="modEmail" href=""></a>&gt;</p>
                <p><strong>Telefone:</strong> <span id="modTelefone"></span></p>
                <p><strong>Data:</strong> <span id="modData"></span></p>
                <p><strong>Assunto:</strong> <span id="modAssunto"></span></p>
                <hr style="margin:1rem 0;">
                <p style="white-space:pre-wrap;" id="modMensagem"></p>
            </div>

            <div id="areaResposta">
                <form method="post" action="../../controller/controlador.php">
                    <input type="hidden" name="operacao" value="responderMensagem">
                    <input type="hidden" name="idMensagem" id="modIdMensagem">
                    <div class="form-group">
                        <label class="form-label">Registrar Resposta / Ação Tomada</label>
                        <textarea name="resposta" class="form-control" rows="3" required placeholder="Digite os detalhes do atendimento..."></textarea>
                        <div class="form-hint">Isso marcará a mensagem como resolvida. Você deve entrar em contato com o usuário via e-mail ou telefone fora do sistema.</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-full" style="justify-content:center;">✅ Marcar como Respondida</button>
                </form>
            </div>

            <div id="areaRespondida" style="display:none;background:#ecfdf5;border:1px solid #a7f3d0;padding:1rem;border-radius:var(--radius);color:#065f46;">
                <strong>✅ Mensagem Respondida</strong><br>
                <small>Por: <span id="modRespondidaPor"></span> em <span id="modRespondidaEm"></span></small>
                <p style="margin-top:0.5rem;white-space:pre-wrap;" id="modRespostaTexto"></p>
            </div>
        </div>
    </div>
</div>

<script>
function abrirModal(m) {
    document.getElementById('modNome').textContent = m.nome;
    document.getElementById('modEmail').textContent = m.email;
    document.getElementById('modEmail').href = 'mailto:' + m.email;
    document.getElementById('modTelefone').textContent = m.telefone || 'Não informado';
    document.getElementById('modData').textContent = m.criadoEm;
    document.getElementById('modAssunto').textContent = m.assunto || 'Sem assunto';
    document.getElementById('modMensagem').textContent = m.mensagem;
    document.getElementById('modIdMensagem').value = m.idMensagem;

    if (m.respondida == 1) {
        document.getElementById('areaResposta').style.display = 'none';
        document.getElementById('areaRespondida').style.display = 'block';
        document.getElementById('modRespondidaPor').textContent = m.nomeResponsavel || 'Admin';
        document.getElementById('modRespondidaEm').textContent = m.respondidaEm;
        document.getElementById('modRespostaTexto').textContent = m.respostaAdmin;
    } else {
        document.getElementById('areaResposta').style.display = 'block';
        document.getElementById('areaRespondida').style.display = 'none';
    }

    document.getElementById('modalMensagem').style.display = 'flex';
}

function fecharModal() {
    document.getElementById('modalMensagem').style.display = 'none';
}

// Fechar ao clicar fora
document.getElementById('modalMensagem').addEventListener('click', function(e) {
    if (e.target === this) fecharModal();
});
</script>

        </main></div></div></body></html>
