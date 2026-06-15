<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/turmas.php';
require_once '../../model/frequencias.php';

$idAluno = $_SESSION['idUsuario'];
$turmas  = listarTurmasAlunoV2($idAluno);

$pageTitle  = 'Minhas Disciplinas';
$currentNav = 'disciplinas';
$depth      = 2;
include '../_layout.php';
?>

<div class="stats-grid mb-4">
    <?php
        $totMatriculas = count($turmas);
        $totAprovadas  = count(array_filter($turmas, fn($t) => $t['situacao'] === 'aprovado'));
        $totReprovadas = count(array_filter($turmas, fn($t) => $t['situacao'] === 'reprovado'));
        $totCursando   = count(array_filter($turmas, fn($t) => $t['situacao'] === 'cursando'));
    ?>
    <div class="stat-card"><div class="stat-icon blue">📚</div><div><div class="stat-value"><?= $totCursando ?></div><div class="stat-label">Cursando</div></div></div>
    <div class="stat-card"><div class="stat-icon green">✅</div><div><div class="stat-value"><?= $totAprovadas ?></div><div class="stat-label">Aprovadas</div></div></div>
    <div class="stat-card"><div class="stat-icon red">❌</div><div><div class="stat-value"><?= $totReprovadas ?></div><div class="stat-label">Reprovadas</div></div></div>
    <div class="stat-card"><div class="stat-icon yellow">📖</div><div><div class="stat-value"><?= $totMatriculas ?></div><div class="stat-label">Total matrículas</div></div></div>
</div>

<?php foreach ($turmas as $t):
    $freq = obterFrequenciaAluno($idAluno, $t['idTurma']);
    $media= calcularMediaAluno($idAluno, $t['idTurma']);
    $pctFalta = $t['limiteHorasFalta'] > 0 ? min(100, round(($freq['totalFaltas'] / $t['limiteHorasFalta']) * 100)) : 0;
    $corFreq  = $pctFalta >= 100 ? 'red' : ($pctFalta >= 75 ? 'yellow' : 'green');
    $corNota  = $media >= 7 ? 'green' : ($media >= 5 ? 'yellow' : 'red');
?>
<div class="card mb-3">
    <div class="card-header">
        <div>
            <span class="card-title"><?= htmlspecialchars($t['nomeDisciplina']) ?></span>
            <span class="badge badge-muted" style="margin-left:.5rem;"><?= htmlspecialchars($t['codigoDisciplina']) ?></span>
        </div>
        <div class="flex gap-2 items-center">
            <span class="badge badge-<?= $t['situacao']==='aprovado'?'success':($t['situacao']==='reprovado'?'danger':($t['situacao']==='cursando'?'info':'muted')) ?>">
                <?= ucfirst($t['situacao']) ?>
            </span>
            <a href="turmaDetalhe.php?id=<?= $t['idTurma'] ?>" class="btn btn-sm btn-outline">Ver detalhes</a>
        </div>
    </div>
    <div class="card-body">
        <div class="form-row" style="grid-template-columns:1fr 1fr 1fr 1fr;">
            <!-- PROFESSOR / CURSO -->
            <div>
                <p class="text-muted" style="font-size:.75rem;text-transform:uppercase;margin-bottom:.25rem;">Professor</p>
                <p style="font-size:.9rem;"><?= htmlspecialchars($t['nomeProfessor']) ?></p>
                <p class="text-muted" style="font-size:.8125rem;"><?= htmlspecialchars($t['nomeCurso']) ?> · <?= $t['semestre'] ?>º sem · <?= $t['ano'] ?></p>
            </div>

            <!-- FREQUÊNCIA -->
            <div>
                <p class="text-muted" style="font-size:.75rem;text-transform:uppercase;margin-bottom:.25rem;">Frequência</p>
                <p style="font-size:.9rem;">
                    <span class="freq-badge freq-<?= $pctFalta >= 100 ? 'critico' : ($pctFalta >= 75 ? 'risco' : 'ok') ?>">
                        <?= $freq['totalFaltas'] ?> / <?= $t['limiteHorasFalta'] ?>h permitidas
                    </span>
                </p>
                <div class="progress-bar mt-1">
                    <div class="progress-fill <?= $corFreq ?>" style="width:<?= $pctFalta ?>%"></div>
                </div>
                <p class="text-muted" style="font-size:.75rem;margin-top:.25rem;">
                    <?= $freq['totalPresencas'] ?> presenças · <?= $freq['totalAulas'] ?> aulas registradas
                </p>
            </div>

            <!-- MÉDIA -->
            <div>
                <p class="text-muted" style="font-size:.75rem;text-transform:uppercase;margin-bottom:.25rem;">Média atual</p>
                <p style="font-size:1.75rem;font-weight:700;color:var(--<?= $corNota === 'green' ? 'success' : ($corNota === 'yellow' ? 'warning' : 'danger') ?>);">
                    <?= number_format($media, 1) ?>
                </p>
                <p class="text-muted" style="font-size:.75rem;">mínimo: 7,0</p>
            </div>

            <!-- CARGA HORÁRIA -->
            <div>
                <p class="text-muted" style="font-size:.75rem;text-transform:uppercase;margin-bottom:.25rem;">Carga horária</p>
                <p style="font-size:.9rem;"><?= $t['cargaHoraria'] ?>h total</p>
                <p class="text-muted" style="font-size:.8125rem;">Horário: <?= htmlspecialchars($t['horario'] ?: '—') ?></p>
                <p class="text-muted" style="font-size:.8125rem;">Sala: <?= htmlspecialchars($t['sala'] ?: '—') ?></p>
            </div>
        </div>

        <?php if ($freq['reprovadoFalta']): ?>
            <div class="alert alert-danger mt-3" style="margin-bottom:0;">⚠️ Você ultrapassou o limite de faltas nesta disciplina. Situação: Reprovado por falta.</div>
        <?php elseif ($freq['emRisco']): ?>
            <div class="alert alert-warning mt-3" style="margin-bottom:0;">⏳ Atenção! Você está próximo do limite de faltas. Não falte mais!</div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>

<?php if (empty($turmas)): ?>
    <div class="card"><div class="card-body text-center text-muted" style="padding:3rem;">Você não está matriculado em nenhuma disciplina.</div></div>
<?php endif; ?>

        </main></div></div></body></html>
