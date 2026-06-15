<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/turmas.php';
require_once '../../model/questionarios.php';

$idProf  = $_SESSION['idUsuario'];
$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
$turmas  = listarTurmasProfessorV2($idProf, date('Y'));
$quizzes = $idTurma ? listarQuestionariosTurma($idTurma) : [];

$pageTitle  = 'Questionários';
$currentNav = 'questionarios';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <form method="get" class="flex gap-3 items-center">
        <label class="form-label" style="margin:0;">Turma:</label>
        <select name="idTurma" class="form-control" onchange="this.form.submit()" style="max-width:320px;">
            <option value="">Todas as turmas</option>
            <?php foreach ($turmas as $t): ?>
                <option value="<?= $t['idTurma'] ?>" <?= $idTurma == $t['idTurma'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($t['nomeDisciplina']) ?> — <?= $t['codigo'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php if ($idTurma): ?>
        <a href="formQuestionario.php?idTurma=<?= $idTurma ?>" class="btn btn-primary">➕ Novo Questionário</a>
    <?php endif; ?>
</div>

<?php if (empty($quizzes)): ?>
<div class="card"><div class="card-body text-center text-muted" style="padding:3rem;">
    <?= $idTurma ? 'Nenhum questionário criado para esta turma.' : 'Selecione uma turma para ver os questionários.' ?>
</div></div>
<?php else: ?>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;">
<?php foreach ($quizzes as $q): ?>
<div class="card">
    <div class="card-header">
        <span class="card-title"><?= htmlspecialchars($q['titulo']) ?> <?= $q['geradoPorIA'] ? '<span class="badge badge-info">🤖 IA</span>' : '' ?></span>
        <span class="badge badge-<?= $q['publicado'] ? 'success' : 'warning' ?>"><?= $q['publicado'] ? 'Publicado' : 'Rascunho' ?></span>
    </div>
    <div class="card-body">
        <?php if ($q['descricao']): ?>
            <p class="text-muted" style="margin-bottom:.75rem;font-size:.875rem;"><?= htmlspecialchars(substr($q['descricao'],0,100)) ?></p>
        <?php endif; ?>
        <div class="flex gap-3 mb-3" style="flex-wrap:wrap;">
            <span class="text-muted" style="font-size:.8125rem;">❓ <?= $q['totalQuestoes'] ?> questões</span>
            <span class="text-muted" style="font-size:.8125rem;">👥 <?= $q['totalResponderam'] ?> responderam</span>
            <?php if ($q['tempoLimite'] > 0): ?>
                <span class="text-muted" style="font-size:.8125rem;">⏱️ <?= $q['tempoLimite'] ?> min</span>
            <?php endif; ?>
        </div>
        <div class="flex gap-2" style="flex-wrap:wrap;">
            <a href="questionario.php?id=<?= $q['idQuestionario'] ?>" class="btn btn-sm btn-outline">✏️ Editar</a>
            <a href="estatQuestionario.php?id=<?= $q['idQuestionario'] ?>" class="btn btn-sm btn-info">📊 Stats</a>
            <?php if (!$q['publicado']): ?>
                <a href="../../controller/controlador.php?operacao=publicarQuestionario&id=<?= $q['idQuestionario'] ?>&idTurma=<?= $idTurma ?>"
                   class="btn btn-sm btn-success"
                   onclick="return confirm('Publicar este questionário para os alunos?')">✅ Publicar</a>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

        </main></div></div></body></html>
