<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/turmas.php';
require_once '../../model/questionarios.php';

$idAluno = $_SESSION['idUsuario'];
$turmas  = listarTurmasAlunoV2($idAluno);

// Juntar todos os questionários disponíveis
$todosQuizzes = [];
foreach ($turmas as $t) {
    $quizzes = listarQuestionariosTurma($t['idTurma'], true);
    foreach ($quizzes as $q) {
        $q['nomeDisciplina'] = $t['nomeDisciplina'];
        $q['idTurma']        = $t['idTurma'];
        $todosQuizzes[]      = $q;
    }
}

$pageTitle  = 'Questionários';
$currentNav = 'questionarios';
$depth      = 2;
include '../_layout.php';
?>

<?php if (empty($todosQuizzes)): ?>
<div class="card">
    <div class="card-body text-center text-muted" style="padding:3rem;">
        Nenhum questionário disponível no momento.
    </div>
</div>
<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:1rem;">
<?php foreach ($todosQuizzes as $q):
    // Verificar se já respondeu
    $tentRes = consultarSQL(
        "SELECT notaObtida, notaMaxima, concluida, finalizouEm
         FROM TentativasQuestionario
         WHERE idQuestionario = ? AND idAluno = ? AND concluida = 1
         ORDER BY finalizouEm DESC LIMIT 1",
        "ii", [$q['idQuestionario'], $idAluno]
    );
    $tentativa = obterLinha($tentRes);

    $disponivel = true;
    $msgStatus  = '';
    $now = time();

    if ($q['dataInicio'] && strtotime($q['dataInicio']) > $now) {
        $disponivel = false;
        $msgStatus  = 'Disponível a partir de ' . date('d/m H:i', strtotime($q['dataInicio']));
    } elseif ($q['dataFim'] && strtotime($q['dataFim']) < $now) {
        $disponivel = false;
        $msgStatus  = 'Encerrado em ' . date('d/m/Y', strtotime($q['dataFim']));
    }
?>
<div class="card">
    <div class="card-header">
        <div>
            <span class="card-title"><?= htmlspecialchars($q['titulo']) ?></span>
            <?php if ($q['geradoPorIA']): ?><span class="badge badge-info" style="margin-left:.4rem;">🤖 IA</span><?php endif; ?>
        </div>
        <span class="badge badge-muted"><?= htmlspecialchars($q['nomeDisciplina']) ?></span>
    </div>
    <div class="card-body">
        <?php if ($q['descricao']): ?>
            <p class="text-muted" style="margin-bottom:.75rem;font-size:.875rem;"><?= htmlspecialchars(substr($q['descricao'],0,120)) ?></p>
        <?php endif; ?>

        <div class="flex gap-3 mb-3" style="flex-wrap:wrap;">
            <span class="text-muted" style="font-size:.8125rem;">❓ <?= $q['totalQuestoes'] ?> questões</span>
            <?php if ($q['tempoLimite'] > 0): ?>
                <span class="text-muted" style="font-size:.8125rem;">⏱️ <?= $q['tempoLimite'] ?> min</span>
            <?php endif; ?>
            <?php if ($q['dataFim']): ?>
                <span class="text-muted" style="font-size:.8125rem;">📅 Até <?= date('d/m/Y H:i', strtotime($q['dataFim'])) ?></span>
            <?php endif; ?>
        </div>

        <?php if ($tentativa): ?>
            <?php $nota10 = $tentativa['notaMaxima'] > 0 ? round($tentativa['notaObtida'] / $tentativa['notaMaxima'] * 10, 1) : 0; ?>
            <div class="alert alert-<?= $nota10 >= 7 ? 'success' : ($nota10 >= 5 ? 'warning' : 'danger') ?>" style="margin-bottom:.75rem;">
                ✅ Concluído · Nota: <strong><?= $nota10 ?>/10</strong>
                <span class="text-muted" style="font-size:.8125rem;"> — <?= date('d/m/Y H:i', strtotime($tentativa['finalizouEm'])) ?></span>
            </div>
            <?php if ($q['tentativasPermitidas'] > 1): ?>
                <a href="responderQuiz.php?id=<?= $q['idQuestionario'] ?>" class="btn btn-outline btn-sm">Tentar novamente</a>
            <?php endif; ?>
        <?php elseif (!$disponivel): ?>
            <div class="alert alert-warning" style="margin-bottom:.75rem;"><?= $msgStatus ?></div>
        <?php else: ?>
            <a href="responderQuiz.php?id=<?= $q['idQuestionario'] ?>" class="btn btn-primary w-full" style="justify-content:center;">
                ▶️ Iniciar questionário
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

        </main></div></div></body></html>
