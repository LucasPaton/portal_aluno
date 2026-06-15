<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/turmas.php';
require_once '../../model/frequencias.php';
require_once '../../model/questionarios.php';
require_once '../../model/forum.php';

$idAluno = $_SESSION['idUsuario'];
$idTurma = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idTurma) { header('Location: disciplinas.php'); exit; }

$turma     = buscarTurmaPorId($idTurma);
$freq      = obterFrequenciaAluno($idAluno, $idTurma);
$notas     = listarNotasAluno($idAluno, $idTurma);
$media     = calcularMediaAluno($idAluno, $idTurma);
$quizzes   = listarQuestionariosTurma($idTurma, true);
$trabalhos = listarTrabalhosTurma($idTurma, true);
$posts     = listarPostsTurma($idTurma);

$pctFalta = $turma['limiteHorasFalta'] > 0
    ? min(100, round(($freq['totalFaltas'] / $turma['limiteHorasFalta']) * 100))
    : 0;
$corFreq = $pctFalta >= 100 ? 'red' : ($pctFalta >= 75 ? 'yellow' : 'green');
$corNota = $media >= 7 ? 'success' : ($media >= 5 ? 'warning' : 'danger');

$pageTitle  = $turma['nomeDisciplina'] ?? 'Disciplina';
$currentNav = 'disciplinas';
$depth      = 2;
include '../_layout.php';
?>

<!-- CABEÇALHO DA TURMA -->
<div class="card mb-4">
    <div class="card-body" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;">
        <div>
            <h2 style="font-size:1.25rem;font-weight:700;"><?= htmlspecialchars($turma['nomeDisciplina']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($turma['nomeCurso']) ?> · Prof. <?= htmlspecialchars($turma['nomeProfessor']) ?></p>
            <p class="text-muted" style="font-size:.8125rem;">
                Código: <?= htmlspecialchars($turma['codigo']) ?> ·
                <?= $turma['semestre'] ?>º sem <?= $turma['ano'] ?> ·
                Sala: <?= htmlspecialchars($turma['sala'] ?: '—') ?> ·
                Horário: <?= htmlspecialchars($turma['horario'] ?: '—') ?>
            </p>
        </div>
        <a href="disciplinas.php" class="btn btn-outline btn-sm">← Voltar</a>
    </div>
</div>

<!-- STATS RÁPIDOS -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon <?= $corNota ?>">📊</div>
        <div>
            <div class="stat-value" style="color:var(--<?= $corNota ?>);"><?= number_format($media, 1) ?></div>
            <div class="stat-label">Média atual</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= $pctFalta >= 100 ? 'red' : ($pctFalta >= 75 ? 'yellow' : 'green') ?>">📋</div>
        <div>
            <div class="stat-value"><?= $freq['totalFaltas'] ?><small style="font-size:1rem;">h</small></div>
            <div class="stat-label">Faltas (máx <?= $turma['limiteHorasFalta'] ?>h)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">❓</div>
        <div>
            <div class="stat-value"><?= count($quizzes) ?></div>
            <div class="stat-label">Questionários</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">📁</div>
        <div>
            <div class="stat-value"><?= count($trabalhos) ?></div>
            <div class="stat-label">Trabalhos</div>
        </div>
    </div>
</div>

<?php if ($freq['reprovadoFalta']): ?>
    <div class="alert alert-danger mb-4">⚠️ Você ultrapassou o limite de faltas nesta disciplina.</div>
<?php elseif ($freq['emRisco']): ?>
    <div class="alert alert-warning mb-4">⏳ Atenção: você está próximo do limite de faltas.</div>
<?php endif; ?>

<div class="grid-2 mb-4">
    <!-- NOTAS -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📊 Minhas Notas</span>
            <a href="notas.php?idTurma=<?= $idTurma ?>" class="btn btn-sm btn-outline">Ver tudo</a>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($notas)): ?>
                <p class="text-muted text-center" style="padding:1.5rem;">Nenhuma nota lançada.</p>
            <?php else: ?>
            <table>
                <thead><tr><th>Descrição</th><th>Nota</th><th>Data</th></tr></thead>
                <tbody>
                <?php foreach ($notas as $n): ?>
                <tr>
                    <td><?= htmlspecialchars($n['descricao'] ?: ucfirst($n['tipo'])) ?></td>
                    <td>
                        <span class="badge badge-<?= $n['nota'] >= 7 ? 'success' : ($n['nota'] >= 5 ? 'warning' : 'danger') ?>">
                            <?= number_format($n['nota'], 1) ?>/<?= number_format($n['notaMaxima'], 0) ?>
                        </span>
                    </td>
                    <td class="text-muted" style="font-size:.8125rem;"><?= date('d/m/Y', strtotime($n['dataLancamento'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background:var(--bg);font-weight:600;">
                    <td>Média</td>
                    <td><span class="badge badge-<?= $corNota ?>"><?= number_format($media, 1) ?></span></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- FREQUÊNCIA RESUMO -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📋 Frequência</span>
            <a href="frequencia.php?idTurma=<?= $idTurma ?>" class="btn btn-sm btn-outline">Histórico</a>
        </div>
        <div class="card-body">
            <div class="flex justify-between mb-2">
                <span>Faltas: <strong><?= $freq['totalFaltas'] ?>h</strong> de <strong><?= $turma['limiteHorasFalta'] ?>h</strong></span>
                <span class="freq-badge freq-<?= $pctFalta >= 100 ? 'critico' : ($pctFalta >= 75 ? 'risco' : 'ok') ?>">
                    <?= $pctFalta >= 100 ? '🔴 Reprovado' : ($pctFalta >= 75 ? '🟡 Em risco' : '🟢 Regular') ?>
                </span>
            </div>
            <div class="progress-bar" style="height:.875rem;border-radius:8px;">
                <div class="progress-fill <?= $corFreq ?>" style="width:<?= $pctFalta ?>%;border-radius:8px;"></div>
            </div>
            <div class="flex justify-between mt-1 text-muted" style="font-size:.75rem;">
                <span><?= $freq['totalPresencas'] ?> presenças</span>
                <span><?= $freq['totalAulas'] ?> aulas registradas</span>
            </div>
            <p class="text-muted mt-3" style="font-size:.8125rem;">
                Carga horária: <strong><?= $turma['cargaHorariaCalc'] ?>h</strong> ·
                Limite de faltas: <strong><?= $turma['limiteHorasFalta'] ?>h</strong> (25%)
            </p>
        </div>
    </div>
</div>

<!-- QUESTIONÁRIOS DA TURMA -->
<?php if (!empty($quizzes)): ?>
<div class="card mb-4">
    <div class="card-header">
        <span class="card-title">❓ Questionários disponíveis</span>
        <a href="questionarios.php" class="btn btn-sm btn-outline">Ver todos</a>
    </div>
    <div class="card-body" style="padding:0;">
        <table>
            <thead><tr><th>Título</th><th>Questões</th><th>Prazo</th><th>Ação</th></tr></thead>
            <tbody>
            <?php foreach ($quizzes as $q):
                $tentRes = consultarSQL(
                    "SELECT notaObtida, notaMaxima FROM TentativasQuestionario
                     WHERE idQuestionario=? AND idAluno=? AND concluida=1 ORDER BY finalizouEm DESC LIMIT 1",
                    "ii", [$q['idQuestionario'], $idAluno]
                );
                $tent = obterLinha($tentRes);
            ?>
            <tr>
                <td><?= htmlspecialchars($q['titulo']) ?></td>
                <td><?= $q['totalQuestoes'] ?></td>
                <td class="text-muted" style="font-size:.8125rem;">
                    <?= $q['dataFim'] ? date('d/m/Y H:i', strtotime($q['dataFim'])) : 'Sem prazo' ?>
                </td>
                <td>
                    <?php if ($tent): ?>
                        <span class="badge badge-<?= ($tent['notaMaxima']>0 && ($tent['notaObtida']/$tent['notaMaxima'])>=0.7)?'success':'warning' ?>">
                            Nota: <?= $tent['notaMaxima']>0 ? round($tent['notaObtida']/$tent['notaMaxima']*10,1) : 0 ?>/10
                        </span>
                    <?php else: ?>
                        <a href="responderQuiz.php?id=<?= $q['idQuestionario'] ?>" class="btn btn-sm btn-primary">Responder</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- TRABALHOS DA TURMA -->
<?php if (!empty($trabalhos)): ?>
<div class="card mb-4">
    <div class="card-header">
        <span class="card-title">📁 Trabalhos</span>
        <a href="trabalhos.php" class="btn btn-sm btn-outline">Ver todos</a>
    </div>
    <div class="card-body" style="padding:0;">
        <table>
            <thead><tr><th>Título</th><th>Entrega</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($trabalhos as $tr):
                $entRes = consultarSQL(
                    "SELECT status, nota FROM EntregasTrabalho WHERE idTrabalho=? AND idAluno=?",
                    "ii", [$tr['idTrabalho'], $idAluno]
                );
                $ent = obterLinha($entRes);
                $atrasado = $tr['dataEntrega'] && strtotime($tr['dataEntrega']) < time();
            ?>
            <tr>
                <td><?= htmlspecialchars($tr['titulo']) ?></td>
                <td class="text-muted" style="font-size:.8125rem;">
                    <?= $tr['dataEntrega'] ? date('d/m/Y H:i', strtotime($tr['dataEntrega'])) : '—' ?>
                    <?= $atrasado && !$ent ? '<span class="badge badge-danger">Atrasado</span>' : '' ?>
                </td>
                <td>
                    <?php if ($ent): ?>
                        <span class="badge badge-<?= $ent['status']==='corrigido'?'success':'info' ?>">
                            <?= $ent['status']==='corrigido' ? '✅ Nota: '.number_format($ent['nota'],1) : '📤 Enviado' ?>
                        </span>
                    <?php elseif (!$atrasado || $tr['permiteAtraso']): ?>
                        <a href="trabalhos.php" class="btn btn-sm btn-primary">Entregar</a>
                    <?php else: ?>
                        <span class="badge badge-danger">Não entregue</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- FÓRUM DA TURMA -->
<div class="card">
    <div class="card-header">
        <span class="card-title">💬 Fórum / Avisos da Turma</span>
        <a href="forum.php?idTurma=<?= $idTurma ?>" class="btn btn-sm btn-outline">Ver fórum completo</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($posts)): ?>
            <p class="text-muted text-center" style="padding:1.5rem;">Nenhuma postagem ainda.</p>
        <?php else: ?>
        <?php foreach (array_slice($posts, 0, 5) as $p): ?>
        <div style="padding:.875rem 1.25rem;border-bottom:1px solid var(--border);">
            <div class="flex justify-between items-center">
                <strong style="font-size:.9rem;"><?= htmlspecialchars($p['titulo'] ?: 'Postagem') ?></strong>
                <div class="flex gap-2 items-center">
                    <?php if ($p['fixado']): ?><span class="badge badge-warning">📌</span><?php endif; ?>
                    <span class="badge badge-muted"><?= ucfirst($p['tipo']) ?></span>
                </div>
            </div>
            <p class="text-muted" style="font-size:.8125rem;margin:.25rem 0;">
                <?= htmlspecialchars(mb_substr($p['conteudo'], 0, 120)) ?>...
            </p>
            <small class="text-muted"><?= htmlspecialchars($p['nomeAutor']) ?> · <?= date('d/m/Y H:i', strtotime($p['dataPostagem'])) ?></small>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

        </main></div></div></body></html>
