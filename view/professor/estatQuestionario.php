<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/questionarios.php';

$idQuiz = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idQuiz) { header('Location: dashboard.php'); exit; }

$quiz  = buscarQuestionarioPorId($idQuiz);
$stats = estatisticasQuestionario($idQuiz);

$pageTitle  = 'Estatísticas: ' . ($quiz['titulo'] ?? '');
$currentNav = 'questionarios';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <h2 style="font-size:1.125rem;font-weight:700;"><?= htmlspecialchars($quiz['titulo']) ?></h2>
    <a href="turmaDetalhe.php?id=<?= $quiz['idTurma'] ?>" class="btn btn-outline btn-sm">← Voltar</a>
</div>

<!-- STATS GERAIS -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon blue">👨‍🎓</div>
        <div>
            <div class="stat-value"><?= $stats['totalAlunos'] ?></div>
            <div class="stat-label">Alunos responderam</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= $stats['mediaGeral'] >= 7 ? 'green' : ($stats['mediaGeral'] >= 5 ? 'yellow' : 'red') ?>">📊</div>
        <div>
            <div class="stat-value"><?= number_format($stats['mediaGeral'], 1) ?></div>
            <div class="stat-label">Média da turma</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon purple">❓</div>
        <div>
            <div class="stat-value"><?= count($stats['questoes']) ?></div>
            <div class="stat-label">Total de questões</div>
        </div>
    </div>
</div>

<div class="grid-2 mb-4">
    <!-- PORCENTAGEM DE ACERTO POR QUESTÃO -->
    <div class="card">
        <div class="card-header"><span class="card-title">📊 Acerto por Questão</span></div>
        <div class="card-body">
            <?php if (empty($stats['questoes'])): ?>
                <p class="text-muted">Sem dados.</p>
            <?php else: ?>
            <div class="chart-container" style="height:280px;">
                <canvas id="graficoQuestoes"></canvas>
            </div>
            <script>
            new Chart(document.getElementById('graficoQuestoes'), {
                type: 'horizontalBar',
                data: {
                    labels: <?= json_encode(array_map(fn($q, $i) => 'Q'.($i+1), $stats['questoes'], array_keys($stats['questoes']))) ?>,
                    datasets: [{
                        label: '% Acerto',
                        data: <?= json_encode(array_column($stats['questoes'], 'porcentagemAcerto')) ?>,
                        backgroundColor: <?= json_encode(array_map(
                            fn($q) => $q['porcentagemAcerto'] >= 70 ? 'rgba(16,185,129,.7)' : ($q['porcentagemAcerto'] >= 40 ? 'rgba(245,158,11,.7)' : 'rgba(239,68,68,.7)'),
                            $stats['questoes']
                        )) ?>,
                        borderRadius: 4,
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { x: { beginAtZero: true, max: 100 } }
                }
            });
            </script>
            <?php endif; ?>
        </div>
    </div>

    <!-- DISTRIBUIÇÃO DE NOTAS -->
    <div class="card">
        <div class="card-header"><span class="card-title">🎯 Distribuição de Notas</span></div>
        <div class="card-body">
            <?php if (empty($stats['notas'])): ?>
                <p class="text-muted">Sem dados.</p>
            <?php else: ?>
            <div class="chart-container" style="height:280px;">
                <canvas id="graficoDistrib"></canvas>
            </div>
            <?php
                $faixas = ['0-3'=>0,'3-5'=>0,'5-7'=>0,'7-9'=>0,'9-10'=>0];
                foreach ($stats['notas'] as $n) {
                    $nota = $n['notaDez'];
                    if ($nota < 3)      $faixas['0-3']++;
                    elseif ($nota < 5)  $faixas['3-5']++;
                    elseif ($nota < 7)  $faixas['5-7']++;
                    elseif ($nota < 9)  $faixas['7-9']++;
                    else                $faixas['9-10']++;
                }
            ?>
            <script>
            new Chart(document.getElementById('graficoDistrib'), {
                type: 'doughnut',
                data: {
                    labels: ['0-3','3-5','5-7','7-9','9-10'],
                    datasets: [{
                        data: <?= json_encode(array_values($faixas)) ?>,
                        backgroundColor: ['#ef4444','#f97316','#f59e0b','#22c55e','#10b981']
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
            </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- TABELA DE QUESTÕES COM DETALHES -->
<div class="card mb-4">
    <div class="card-header"><span class="card-title">❓ Detalhes por Questão (mais difíceis primeiro)</span></div>
    <div class="card-body" style="padding:0;">
        <table>
            <thead><tr><th>#</th><th>Questão</th><th>Pontos</th><th>Acertos</th><th>% Acerto</th></tr></thead>
            <tbody>
            <?php foreach ($stats['questoes'] as $i => $q): ?>
            <tr>
                <td><?= $i+1 ?></td>
                <td style="max-width:400px;"><?= htmlspecialchars(substr($q['enunciado'],0,100)) ?>...</td>
                <td><?= $q['pontos'] ?></td>
                <td><?= $q['totalAcertos'] ?>/<?= $q['totalRespostas'] ?></td>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="progress-bar" style="width:100px;">
                            <div class="progress-fill <?= $q['porcentagemAcerto'] >= 70 ? 'green' : ($q['porcentagemAcerto'] >= 40 ? 'yellow' : 'red') ?>"
                                 style="width:<?= $q['porcentagemAcerto'] ?>%"></div>
                        </div>
                        <strong><?= $q['porcentagemAcerto'] ?>%</strong>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ANÁLISE IA -->
<div class="card mb-4">
    <div class="card-header">
        <span class="card-title">🤖 Análise e Recomendação da IA</span>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">Clique para que a IA analise os resultados e sugira como reforçar os conteúdos com maior dificuldade.</p>
        <button class="btn btn-info" id="btnIA" onclick="analisarIA()">🤖 Gerar Análise com IA</button>
        <div id="iaResultado" class="ia-box mt-4" style="display:none;">
            <h4>🤖 Recomendação Pedagógica</h4>
            <div id="iaTexto" style="white-space:pre-wrap;line-height:1.7;"></div>
        </div>
    </div>
</div>

<!-- NOTAS DOS ALUNOS -->
<div class="card">
    <div class="card-header"><span class="card-title">👨‍🎓 Notas dos Alunos</span></div>
    <div class="card-body" style="padding:0;">
        <table>
            <thead><tr><th>Aluno</th><th>Matrícula</th><th>Nota</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach ($stats['notas'] as $n): ?>
            <tr>
                <td><?= htmlspecialchars($n['nome']) ?></td>
                <td><?= htmlspecialchars($n['matricula']) ?></td>
                <td>
                    <span class="badge badge-<?= $n['notaDez'] >= 7 ? 'success' : ($n['notaDez'] >= 5 ? 'warning' : 'danger') ?>">
                        <?= number_format($n['notaDez'], 1) ?>
                    </span>
                    <small class="text-muted"> (<?= number_format($n['notaObtida'],1) ?>/<?= number_format($n['notaMaxima'],1) ?> pts)</small>
                </td>
                <td><?= $n['finalizouEm'] ? date('d/m/Y H:i', strtotime($n['finalizouEm'])) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function analisarIA() {
    document.getElementById('btnIA').disabled = true;
    document.getElementById('btnIA').textContent = '⏳ Analisando...';
    document.getElementById('iaResultado').style.display = 'none';

    try {
        const resp = await fetch('../../controller/iaAnalise.php?idQuestionario=<?= $idQuiz ?>');
        const data = await resp.json();
        document.getElementById('iaTexto').textContent = data.analise;
        document.getElementById('iaResultado').style.display = 'block';
    } catch(e) {
        alert('Erro ao conectar com a IA.');
    }

    document.getElementById('btnIA').disabled = false;
    document.getElementById('btnIA').textContent = '🤖 Gerar Análise com IA';
}
</script>

        </main>
    </div>
</div>
</body>
</html>
