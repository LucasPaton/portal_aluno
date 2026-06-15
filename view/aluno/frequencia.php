<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/turmas.php';
require_once '../../model/frequencias.php';

$idAluno = $_SESSION['idUsuario'];
$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
$turmas  = listarTurmasAlunoV2($idAluno);

$pageTitle  = 'Frequência';
$currentNav = 'frequencia';
$depth      = 2;
include '../_layout.php';
?>

<!-- SELETOR DE TURMA -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center">
            <label class="form-label" style="margin:0;white-space:nowrap;">Ver frequência de:</label>
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

<?php if ($idTurma): ?>
<?php
    $turma    = null;
    foreach ($turmas as $t) { if ($t['idTurma'] == $idTurma) { $turma = $t; break; } }
    $freq     = obterFrequenciaAluno($idAluno, $idTurma);
    $historico= historicoFrequenciaAluno($idAluno, $idTurma);

    $pctFalta = $turma['limiteHorasFalta'] > 0
        ? min(100, round(($freq['totalFaltas'] / $turma['limiteHorasFalta']) * 100))
        : 0;
?>

<!-- RESUMO GERAL -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon blue">📅</div>
        <div><div class="stat-value"><?= $freq['totalAulas'] ?></div><div class="stat-label">Aulas registradas</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div><div class="stat-value"><?= $freq['totalPresencas'] ?></div><div class="stat-label">Presenças</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= $freq['reprovadoFalta'] ? 'red' : ($freq['emRisco'] ? 'yellow' : 'green') ?>">❌</div>
        <div>
            <div class="stat-value"><?= $freq['totalFaltas'] ?></div>
            <div class="stat-label">Faltas (máx. <?= $turma['limiteHorasFalta'] ?>h)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= $freq['porcentagemFaltas'] > 25 ? 'red' : 'green' ?>">📊</div>
        <div><div class="stat-value"><?= $freq['porcentagemFaltas'] ?>%</div><div class="stat-label">% de faltas</div></div>
    </div>
</div>

<!-- BARRA DE PROGRESSO DE FALTAS -->
<div class="card mb-4">
    <div class="card-header">
        <span class="card-title">⚠️ Situação de Frequência — <?= htmlspecialchars($turma['nomeDisciplina']) ?></span>
    </div>
    <div class="card-body">
        <div class="flex justify-between items-center mb-2">
            <span style="font-size:.875rem;">Faltas utilizadas: <strong><?= $freq['totalFaltas'] ?>h</strong> de <strong><?= $turma['limiteHorasFalta'] ?>h</strong> permitidas</span>
            <span class="freq-badge freq-<?= $freq['reprovadoFalta'] ? 'critico' : ($freq['emRisco'] ? 'risco' : 'ok') ?>">
                <?php if ($freq['reprovadoFalta']): ?>🔴 REPROVADO POR FALTA
                <?php elseif ($freq['emRisco']): ?>🟡 EM RISCO
                <?php else: ?>🟢 SITUAÇÃO REGULAR
                <?php endif; ?>
            </span>
        </div>
        <div class="progress-bar" style="height:.875rem;border-radius:8px;">
            <div class="progress-fill <?= $pctFalta >= 100 ? 'red' : ($pctFalta >= 75 ? 'yellow' : 'green') ?>"
                 style="width:<?= $pctFalta ?>%;border-radius:8px;"></div>
        </div>
        <div class="flex justify-between mt-1">
            <span class="text-muted" style="font-size:.75rem;">0h</span>
            <span class="text-muted" style="font-size:.75rem;">75% — zona de risco</span>
            <span class="text-muted" style="font-size:.75rem;"><?= $turma['limiteHorasFalta'] ?>h — limite</span>
        </div>

        <?php
            $faltasRestantes = max(0, $turma['limiteHorasFalta'] - $freq['totalFaltas']);
        ?>
        <?php if (!$freq['reprovadoFalta']): ?>
        <div class="alert alert-info mt-3" style="margin-bottom:0;">
            Você ainda pode faltar <strong><?= $faltasRestantes ?>h</strong> sem ser reprovado por falta.
        </div>
        <?php else: ?>
        <div class="alert alert-danger mt-3" style="margin-bottom:0;">
            ⚠️ Você ultrapassou o limite. Procure a coordenação do curso imediatamente.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- HISTÓRICO AULA A AULA -->
<div class="card">
    <div class="card-header">
        <span class="card-title">📋 Histórico Aula a Aula</span>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($historico)): ?>
            <p class="text-muted text-center" style="padding:2rem;">Nenhuma aula registrada ainda.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr><th>Data</th><th>Horário</th><th>Conteúdo</th><th>Situação</th></tr>
            </thead>
            <tbody>
            <?php foreach ($historico as $h): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($h['dataAula'])) ?></td>
                <td><?= $h['horaInicio'] ? substr($h['horaInicio'],0,5).' - '.substr($h['horaFim'],0,5) : '—' ?></td>
                <td><?= htmlspecialchars($h['conteudo'] ?: '—') ?></td>
                <td>
                    <?php if ($h['presente'] == 1): ?>
                        <span class="badge badge-success">✅ Presente</span>
                    <?php elseif ($h['presente'] == 0): ?>
                        <span class="badge badge-danger">❌ Falta</span>
                        <?php if ($h['justificativa']): ?>
                            <small class="text-muted"> — <?= htmlspecialchars($h['justificativa']) ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <span class="badge badge-muted">— Não registrada</span>
                    <?php endif; ?>
                </td>
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
        Selecione uma disciplina acima para ver o histórico de frequência.
    </div>
</div>
<?php endif; ?>

        </main></div></div></body></html>
