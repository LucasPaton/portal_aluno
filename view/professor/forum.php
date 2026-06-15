<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/turmas.php';
require_once '../../model/forum.php';

$idProf  = $_SESSION['idUsuario'];
$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
$idPost  = filter_input(INPUT_GET, 'idPost',  FILTER_VALIDATE_INT);
$turmas  = listarTurmasProfessor($idProf, date('Y'));

$posts   = $idTurma ? listarPostsTurma($idTurma) : [];
$postAtual = $idPost ? buscarPostPorId($idPost) : null;
$respostas = $idPost ? listarRespostasPost($idPost) : [];

$pageTitle  = 'Fórum';
$currentNav = 'forum';
$depth      = 2;
include '../_layout.php';
?>

<!-- SELETOR + NOVO POST -->
<div class="card mb-4">
    <div class="card-body flex gap-3 items-center" style="flex-wrap:wrap;">
        <form method="get" class="flex gap-3 items-center" style="flex:1;">
            <select name="idTurma" class="form-control" onchange="this.form.submit()" style="max-width:320px;">
                <option value="">Selecione uma turma</option>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?= $t['idTurma'] ?>" <?= $idTurma == $t['idTurma'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nomeDisciplina']) ?> — <?= $t['codigo'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php if ($idTurma): ?>
        <button class="btn btn-primary" onclick="document.getElementById('formNovoPost').style.display=document.getElementById('formNovoPost').style.display==='none'?'block':'none'">
            ➕ Novo post
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- FORM NOVO POST -->
<?php if ($idTurma): ?>
<div id="formNovoPost" style="display:none;" class="card mb-4">
    <div class="card-header"><span class="card-title">✍️ Criar Post</span></div>
    <div class="card-body">
        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao" value="criarPost">
            <input type="hidden" name="idTurma" value="<?= $idTurma ?>">
            <div class="form-row">
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" placeholder="Título do post...">
                </div>
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-control">
                        <option value="aviso">Aviso</option>
                        <option value="material">Material</option>
                        <option value="discussao">Discussão</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Fixar no topo?</label>
                    <select name="fixado" class="form-control">
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Conteúdo *</label>
                <textarea name="conteudo" class="form-control" rows="4" required placeholder="Escreva o conteúdo do post..."></textarea>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">📤 Publicar</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('formNovoPost').style.display='none'">Cancelar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($idTurma): ?>
<div class="grid-2">
    <!-- LISTA DE POSTS -->
    <div class="card">
        <div class="card-header"><span class="card-title">💬 Posts (<?= count($posts) ?>)</span></div>
        <div class="card-body" style="padding:0;max-height:500px;overflow-y:auto;">
            <?php if (empty($posts)): ?>
                <p class="text-muted text-center" style="padding:2rem;">Nenhum post ainda.</p>
            <?php else: ?>
            <?php foreach ($posts as $p): $tipoBadge=['aviso'=>'warning','discussao'=>'info','duvida'=>'primary','material'=>'success'][$p['tipo']] ?? 'muted'; ?>
            <a href="?idTurma=<?= $idTurma ?>&idPost=<?= $p['idPost'] ?>"
               style="display:block;padding:.875rem 1.25rem;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;<?= $idPost == $p['idPost'] ? 'background:var(--bg);' : '' ?>">
                <div class="flex justify-between items-center mb-1">
                    <strong style="font-size:.9rem;"><?= $p['fixado'] ? '📌 ' : '' ?><?= htmlspecialchars($p['titulo'] ?: 'Sem título') ?></strong>
                    <span class="badge badge-<?= $tipoBadge ?>"><?= ucfirst($p['tipo']) ?></span>
                </div>
                <p class="text-muted" style="font-size:.8125rem;margin:0;"><?= htmlspecialchars(substr($p['conteudo'],0,80)) ?>...</p>
                <div class="flex gap-2 mt-1" style="font-size:.75rem;color:var(--text-muted);">
                    <span><?= htmlspecialchars($p['nomeAutor']) ?></span>
                    <span>·</span>
                    <span><?= date('d/m H:i', strtotime($p['dataPostagem'])) ?></span>
                    <span>·</span>
                    <span>💬 <?= $p['totalRespostas'] ?></span>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- DETALHE DO POST -->
    <div class="card">
        <?php if ($postAtual): ?>
        <div class="card-header"><span class="card-title"><?= htmlspecialchars($postAtual['titulo'] ?: 'Post') ?></span></div>
        <div class="card-body">
            <p style="font-size:.875rem;line-height:1.7;margin-bottom:1rem;"><?= nl2br(htmlspecialchars($postAtual['conteudo'])) ?></p>
            <small class="text-muted">Por <?= htmlspecialchars($postAtual['nomeAutor']) ?> · <?= date('d/m/Y H:i', strtotime($postAtual['dataPostagem'])) ?></small>
        </div>
        <?php if (!empty($respostas)): ?>
        <div style="border-top:1px solid var(--border);padding:.875rem 1.25rem;max-height:200px;overflow-y:auto;">
            <?php foreach ($respostas as $r): ?>
            <div style="padding:.625rem;background:var(--bg);border-radius:var(--radius-sm);margin-bottom:.5rem;">
                <p style="font-size:.875rem;margin:0;"><?= nl2br(htmlspecialchars($r['conteudo'])) ?></p>
                <small class="text-muted"><?= htmlspecialchars($r['nomeAutor']) ?> · <?= date('d/m H:i', strtotime($r['dataPostagem'])) ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <!-- RESPONDER -->
        <div style="padding:.875rem 1.25rem;border-top:1px solid var(--border);">
            <form method="post" action="../../controller/controlador.php" class="flex gap-2">
                <input type="hidden" name="operacao" value="criarPost">
                <input type="hidden" name="idTurma" value="<?= $idTurma ?>">
                <input type="hidden" name="idPostPai" value="<?= $postAtual['idPost'] ?>">
                <input type="hidden" name="tipo" value="discussao">
                <input type="text" name="conteudo" class="form-control" placeholder="Responder..." required>
                <button type="submit" class="btn btn-primary">↩</button>
            </form>
        </div>
        <?php else: ?>
        <div class="card-body text-center text-muted" style="padding:3rem;">Selecione um post para visualizar.</div>
        <?php endif; ?>
    </div>
</div>
<?php else: ?>
<div class="card"><div class="card-body text-center text-muted" style="padding:3rem;">Selecione uma turma.</div></div>
<?php endif; ?>

        </main></div></div></body></html>
