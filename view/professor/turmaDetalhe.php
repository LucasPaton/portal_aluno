<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/turmas.php';
require_once '../../model/frequencias.php';
require_once '../../model/questionarios.php';
require_once '../../model/forum.php';

$idTurma = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idTurma) { header('Location: dashboard.php'); exit; }

$turma   = buscarTurmaPorId($idTurma);
$alunos  = listarAlunosTurma($idTurma);
$quizzes = listarQuestionariosTurma($idTurma);
$trabalhos = listarTrabalhosTurma($idTurma, false);
$estatNotas = estatisticasNotas($idTurma);

// Dados para gráfico de notas
$nomesAlunos = array_column($estatNotas, 'nome');
$mediasNotas = array_column($estatNotas, 'media');

$pageTitle  = 'Turma: ' . ($turma['nomeDisciplina'] ?? '');
$currentNav = 'turmas';
$depth      = 2;
include '../_layout.php';
?>

<!-- INFO DA TURMA -->
<div class="card mb-4">
    <div class="card-body">
        <div class="flex justify-between items-center">
            <div>
                <h2 style="font-size:1.25rem;font-weight:700;"><?= htmlspecialchars($turma['nomeDisciplina']) ?></h2>
                <p class="text-muted"><?= htmlspecialchars($turma['nomeCurso']) ?> · <?= $turma['semestre'] ?>º Semestre · <?= $turma['ano'] ?></p>
                <p class="text-muted">Código: <?= htmlspecialchars($turma['codigo']) ?> · Sala: <?= htmlspecialchars($turma['sala'] ?: '—') ?> · <?= $turma['cargaHorariaCalc'] ?>h · Limite faltas: <?= $turma['limiteHorasFalta'] ?>h</p>
            </div>
            <div class="flex gap-2">
                <a href="frequencia.php?idTurma=<?= $idTurma ?>" class="btn btn-primary">📋 Registrar Frequência</a>
                <a href="notas.php?idTurma=<?= $idTurma ?>"      class="btn btn-success">📊 Lançar Notas</a>
            </div>
        </div>
    </div>
</div>

<div class="grid-2 mb-4">
    <!-- LISTA DE ALUNOS -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">👨‍🎓 Alunos (<?= count($alunos) ?>)</span>
        </div>
        <div class="card-body" style="padding:0;max-height:350px;overflow-y:auto;">
            <table>
                <thead><tr><th>Nome</th><th>Matrícula</th><th>Faltas</th><th>Situação</th></tr></thead>
                <tbody>
                <?php foreach ($alunos as $a):
                    $faltasPct = $turma['limiteHorasFalta'] > 0
                        ? round(($a['totalFaltas'] / $turma['limiteHorasFalta']) * 100)
                        : 0;
                    $cor = $faltasPct >= 100 ? 'critico' : ($faltasPct >= 75 ? 'risco' : 'ok');
                ?>
                <tr>
                    <td><?= htmlspecialchars($a['nome']) ?></td>
                    <td><span class="badge badge-muted"><?= htmlspecialchars($a['matricula']) ?></span></td>
                    <td><span class="freq-badge freq-<?= $cor ?>"><?= $a['totalFaltas'] ?>/<?= $turma['limiteHorasFalta'] ?>h</span></td>
                    <td><span class="badge badge-<?= $a['situacao']==='aprovado'?'success':($a['situacao']==='reprovado'?'danger':'info') ?>"><?= ucfirst($a['situacao']) ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- GRÁFICO DE NOTAS -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📊 Desempenho — Médias</span>
        </div>
        <div class="card-body">
            <?php if (empty($estatNotas)): ?>
                <p class="text-muted text-center">Nenhuma nota lançada ainda.</p>
            <?php else: ?>
            <div class="chart-container" style="height:280px;">
                <canvas id="graficoNotas"></canvas>
            </div>
            <script>
            const ctx = document.getElementById('graficoNotas').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_map(fn($n) => explode(' ', $n)[0], $nomesAlunos)) ?>,
                    datasets: [{
                        label: 'Média',
                        data: <?= json_encode($mediasNotas) ?>,
                        backgroundColor: <?= json_encode(array_map(
                            fn($m) => $m >= 7 ? 'rgba(16,185,129,.7)' : ($m >= 5 ? 'rgba(245,158,11,.7)' : 'rgba(239,68,68,.7)'),
                            $mediasNotas
                        )) ?>,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, max: 10 } }
                }
            });
            </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- QUESTIONÁRIOS -->
<div class="card mb-4">
    <div class="card-header">
        <span class="card-title">❓ Questionários</span>
        <a href="formQuestionario.php?idTurma=<?= $idTurma ?>" class="btn btn-primary btn-sm">➕ Novo</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($quizzes)): ?>
            <p class="text-muted text-center" style="padding:1.5rem;">Nenhum questionário criado.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Título</th><th>Questões</th><th>Responderam</th><th>Status</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($quizzes as $q): ?>
            <tr>
                <td><?= htmlspecialchars($q['titulo']) ?> <?= $q['geradoPorIA'] ? '<span class="badge badge-info">🤖 IA</span>' : '' ?></td>
                <td><?= $q['totalQuestoes'] ?></td>
                <td><?= $q['totalResponderam'] ?> alunos</td>
                <td><span class="badge badge-<?= $q['publicado']?'success':'warning' ?>"><?= $q['publicado']?'Publicado':'Rascunho' ?></span></td>
                <td>
                    <a href="questionario.php?id=<?= $q['idQuestionario'] ?>" class="btn btn-sm btn-outline">✏️ Editar</a>
                    <a href="estatQuestionario.php?id=<?= $q['idQuestionario'] ?>" class="btn btn-sm btn-info">📊 Stats</a>
                    <?php if (!$q['publicado']): ?>
                    <a href="../../controller/controlador.php?operacao=publicarQuestionario&id=<?= $q['idQuestionario'] ?>&idTurma=<?= $idTurma ?>"
                       class="btn btn-sm btn-success"
                       onclick="return confirm('Publicar este questionário?')">✅ Publicar</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- TRABALHOS -->
<div class="card mb-4">
    <div class="card-header">
        <span class="card-title">📁 Trabalhos</span>
        <a href="formTrabalho.php?idTurma=<?= $idTurma ?>" class="btn btn-primary btn-sm">➕ Novo</a>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($trabalhos)): ?>
            <p class="text-muted text-center" style="padding:1.5rem;">Nenhum trabalho criado.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Título</th><th>Entrega</th><th>Entregas recebidas</th><th>Ações</th></tr></thead>
            <tbody>
            <?php foreach ($trabalhos as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['titulo']) ?></td>
                <td><?= $t['dataEntrega'] ? date('d/m/Y H:i', strtotime($t['dataEntrega'])) : '—' ?></td>
                <td><?= $t['totalEntregas'] ?></td>
                <td><a href="entregas.php?idTrabalho=<?= $t['idTrabalho'] ?>&idTurma=<?= $idTurma ?>" class="btn btn-sm btn-outline">Ver entregas</a></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

        </main>
    </div>
</div>
</body>
</html>
