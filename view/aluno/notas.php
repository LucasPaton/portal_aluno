<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/turmas.php';
require_once '../../model/frequencias.php';

$idAluno = $_SESSION['idUsuario'];
$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
$turmas  = listarTurmasAlunoV2($idAluno);

$pageTitle  = 'Minhas Notas';
$currentNav = 'notas';
$depth      = 2;
include '../_layout.php';
?>

<!-- SELETOR -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center">
            <label class="form-label" style="margin:0;white-space:nowrap;">Notas de:</label>
            <select name="idTurma" class="form-control" onchange="this.form.submit()" style="max-width:320px;">
                <option value="">Selecione uma disciplina</option>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?= $t['idTurma'] ?>" <?= $idTurma == $t['idTurma'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nomeDisciplina']) ?> — <?= $t['ano'] ?>/<?= $t['semestre'] ?>º
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if ($idTurma):
    $notas = listarNotasAluno($idAluno, $idTurma);
    $media = calcularMediaAluno($idAluno, $idTurma);
    $turma = null;
    foreach ($turmas as $t) { if ($t['idTurma'] == $idTurma) { $turma = $t; break; } }
    $corMedia = $media >= 7 ? 'success' : ($media >= 5 ? 'warning' : 'danger');
?>

<div class="grid-2 mb-4">
    <!-- MÉDIA E SITUAÇÃO -->
    <div class="card">
        <div class="card-header"><span class="card-title">📊 Situação em <?= htmlspecialchars($turma['nomeDisciplina'] ?? '') ?></span></div>
        <div class="card-body text-center">
            <div style="font-size:4rem;font-weight:700;color:var(--<?= $corMedia ?>);line-height:1;">
                <?= number_format($media, 1) ?>
            </div>
            <div class="text-muted" style="margin:.5rem 0;">média atual · mínimo 7,0</div>
            <span class="badge badge-<?= $turma['situacao']==='aprovado'?'success':($turma['situacao']==='reprovado'?'danger':'info') ?>" style="font-size:.9rem;padding:.4rem .875rem;">
                <?= ucfirst($turma['situacao'] ?? 'cursando') ?>
            </span>
            <?php if ($media < 7 && $media >= 5): ?>
            <div class="alert alert-warning mt-3" style="margin-bottom:0;text-align:left;">
                Você precisa de pelo menos <strong><?= number_format(7 - $media, 1) ?></strong> pontos para atingir a média mínima.
            </div>
            <?php elseif ($media < 5): ?>
            <div class="alert alert-danger mt-3" style="margin-bottom:0;text-align:left;">
                Sua média está abaixo do mínimo. Busque apoio do professor ou monitoria.
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- GRÁFICO -->
    <div class="card">
        <div class="card-header"><span class="card-title">📈 Evolução das Notas</span></div>
        <div class="card-body">
            <?php if (empty($notas)): ?>
                <p class="text-muted text-center">Nenhuma nota lançada.</p>
            <?php else: ?>
            <div class="chart-container" style="height:220px;">
                <canvas id="graficoNotas"></canvas>
            </div>
            <script>
            new Chart(document.getElementById('graficoNotas'), {
                type: 'bar',
                data: {
                    labels: <?= json_encode(array_map(fn($n) => substr($n['descricao'] ?: $n['tipo'], 0, 12), $notas)) ?>,
                    datasets: [{
                        label: 'Nota',
                        data: <?= json_encode(array_map(fn($n) => round($n['nota'], 2), $notas)) ?>,
                        backgroundColor: <?= json_encode(array_map(fn($n) =>
                            $n['nota'] >= 7 ? 'rgba(16,185,129,.7)' : ($n['nota'] >= 5 ? 'rgba(245,158,11,.7)' : 'rgba(239,68,68,.7)'),
                        $notas)) ?>,
                        borderRadius: 6,
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true, max: 10,
                             afterDataLimits: (axis) => { axis.max = 10; } },
                        x: { ticks: { font: { size: 11 } } }
                    }
                }
            });
            </script>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- LISTA DETALHADA DE NOTAS -->
<div class="card">
    <div class="card-header"><span class="card-title">📝 Detalhamento das Avaliações</span></div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($notas)): ?>
            <p class="text-muted text-center" style="padding:2rem;">Nenhuma nota lançada ainda.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>Tipo</th><th>Descrição</th><th>Nota</th><th>Nota máx.</th><th>Peso</th><th>Contribuição</th><th>Data</th></tr>
            </thead>
            <tbody>
            <?php foreach ($notas as $n):
                $contribuicao = $n['notaMaxima'] > 0 ? ($n['nota'] / $n['notaMaxima']) * 10 : 0;
            ?>
            <tr>
                <td><span class="badge badge-muted"><?= ucfirst($n['tipo']) ?></span></td>
                <td><?= htmlspecialchars($n['descricao'] ?: '—') ?></td>
                <td>
                    <span class="badge badge-<?= $n['nota'] >= $n['notaMaxima']*0.7 ? 'success' : ($n['nota'] >= $n['notaMaxima']*0.5 ? 'warning' : 'danger') ?>" style="font-size:.875rem;">
                        <?= number_format($n['nota'], 1) ?>
                    </span>
                </td>
                <td><?= number_format($n['notaMaxima'], 1) ?></td>
                <td><?= $n['peso'] ?></td>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="progress-bar" style="width:70px;">
                            <div class="progress-fill <?= $contribuicao >= 7 ? 'green' : ($contribuicao >= 5 ? 'yellow' : 'red') ?>"
                                 style="width:<?= min(100, $contribuicao*10) ?>%"></div>
                        </div>
                        <span style="font-size:.8125rem;"><?= number_format($contribuicao, 1) ?>/10</span>
                    </div>
                </td>
                <td class="text-muted" style="font-size:.8125rem;"><?= date('d/m/Y', strtotime($n['dataLancamento'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php else: ?>
<div class="card">
    <div class="card-body text-center text-muted" style="padding:3rem;">
        Selecione uma disciplina para ver as notas.
    </div>
</div>
<?php endif; ?>

        </main></div></div></body></html>
