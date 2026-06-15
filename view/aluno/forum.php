<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/turmas.php';
require_once '../../model/forum.php';

$idAluno = $_SESSION['idUsuario'];
$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
$idPost  = filter_input(INPUT_GET, 'idPost',  FILTER_VALIDATE_INT);
$turmas  = listarTurmasAlunoV2($idAluno);
$posts   = $idTurma ? listarPostsTurma($idTurma) : listarAvisosGerais();
$postAtual = $idPost ? buscarPostPorId($idPost) : null;
$respostas = $idPost ? listarRespostasPost($idPost) : [];

$pageTitle  = 'Fórum';
$currentNav = 'forum';
$depth      = 2;
include '../_layout.php';
?>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center" style="flex-wrap:wrap;">
            <select name="idTurma" class="form-control" onchange="this.form.submit()" style="max-width:320px;">
                <option value="">📢 Avisos gerais</option>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?= $t['idTurma'] ?>" <?= $idTurma == $t['idTurma'] ? 'selected' : '' ?>>
                        💬 <?= htmlspecialchars($t['nomeDisciplina']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <span class="card-title"><?= $idTurma ? '💬 Fórum da Disciplina' : '📢 Avisos Gerais' ?></span>
        </div>
        <div class="card-body" style="padding:0;max-height:480px;overflow-y:auto;">
            <?php if (empty($posts)): ?>
                <p class="text-muted text-center" style="padding:2rem;">Nenhuma publicação.</p>
            <?php else: ?>
            <?php foreach ($posts as $p): $tipoBadge=['aviso'=>'warning','discussao'=>'info','duvida'=>'primary','material'=>'success'][$p['tipo']] ?? 'muted'; ?>
            <a href="?idTurma=<?= $idTurma ?>&idPost=<?= $p['idPost'] ?>"
               style="display:block;padding:.875rem 1.25rem;border-bottom:1px solid var(--border);text-decoration:none;color:inherit;<?= $idPost == $p['idPost'] ? 'background:var(--bg);' : '' ?>">
                <div class="flex justify-between items-center mb-1">
                    <strong style="font-size:.875rem;"><?= $p['fixado'] ? '📌 ' : '' ?><?= htmlspecialchars($p['titulo'] ?: 'Mensagem') ?></strong>
                    <span class="badge badge-<?= $tipoBadge ?>"><?= ucfirst($p['tipo']) ?></span>
                </div>
                <p class="text-muted" style="font-size:.8125rem;margin:0;"><?= htmlspecialchars(substr($p['conteudo'],0,80)) ?>...</p>
                <div style="font-size:.75rem;color:var(--text-muted);margin-top:.25rem;">
                    <?= htmlspecialchars($p['nomeAutor']) ?> · <?= date('d/m H:i', strtotime($p['dataPostagem'])) ?>
                    <?php if (isset($p['totalRespostas']) && $p['totalRespostas'] > 0): ?> · 💬 <?= $p['totalRespostas'] ?><?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <?php if ($postAtual): ?>
        <div class="card-header"><span class="card-title"><?= htmlspecialchars($postAtual['titulo'] ?: 'Mensagem') ?></span></div>
        <div class="card-body">
            <p style="font-size:.9rem;line-height:1.7;margin-bottom:.75rem;"><?= nl2br(htmlspecialchars($postAtual['conteudo'])) ?></p>
            <small class="text-muted">Por <?= htmlspecialchars($postAtual['nomeAutor']) ?> · <?= date('d/m/Y H:i', strtotime($postAtual['dataPostagem'])) ?></small>
        </div>
        <?php if (!empty($respostas)): ?>
        <div style="border-top:1px solid var(--border);padding:.875rem 1.25rem;max-height:180px;overflow-y:auto;">
            <?php foreach ($respostas as $r): ?>
            <div style="padding:.5rem .75rem;background:var(--bg);border-radius:var(--radius-sm);margin-bottom:.5rem;border-left:3px solid var(--border);">
                <p style="font-size:.875rem;margin:0;"><?= nl2br(htmlspecialchars($r['conteudo'])) ?></p>
                <small class="text-muted"><?= htmlspecialchars($r['nomeAutor']) ?> · <?= date('d/m H:i', strtotime($r['dataPostagem'])) ?></small>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($idTurma): ?>
        <div style="padding:.875rem 1.25rem;border-top:1px solid var(--border);">
            <form method="post" action="../../controller/controlador.php" class="flex gap-2">
                <input type="hidden" name="operacao"  value="criarPost">
                <input type="hidden" name="idTurma"   value="<?= $idTurma ?>">
                <input type="hidden" name="idPostPai" value="<?= $postAtual['idPost'] ?>">
                <input type="hidden" name="tipo"      value="discussao">
                <input type="text" name="conteudo" class="form-control" placeholder="Sua resposta..." required>
                <button type="submit" class="btn btn-primary">↩</button>
            </form>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="card-body text-center text-muted" style="padding:3rem;">Selecione um post para ler.</div>
        <?php endif; ?>
    </div>
</div>

        </main></div></div></body></html>
