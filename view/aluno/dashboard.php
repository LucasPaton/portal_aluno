<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/turmas.php';
require_once '../../model/forum.php';

$idAluno = $_SESSION['idUsuario'];
$turmas  = listarTurmasAlunoV2($idAluno);
$avisos  = listarAvisosGerais();

// Calcular situação geral
$totalTurmas   = count($turmas);
$emRisco       = array_filter($turmas, fn($t) => ($t['limiteHorasFalta'] > 0) && ($t['totalFaltas'] / max(1,$t['limiteHorasFalta'])) >= 0.75);
$reprovadoFalta= array_filter($turmas, fn($t) => $t['totalFaltas'] > $t['limiteHorasFalta']);

$pageTitle  = 'Meu Painel';
$currentNav = 'dashboard';
$depth      = 2;
include '../_layout.php';
?>

<!-- BOAS-VINDAS -->
<div class="card mb-4" style="background:linear-gradient(135deg,var(--primary) 0%,var(--primary-dark) 100%);border:none;color:#fff;">
    <div class="card-body" style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h2 style="font-size:1.25rem;font-weight:700;color:#fff;margin-bottom:.25rem;">
                Olá, <?= htmlspecialchars(explode(' ', $_SESSION['nome'])[0]) ?>! 👋
            </h2>
            <p style="color:rgba(255,255,255,.8);font-size:.875rem;">
                Matrícula: <strong style="color:#fff;"><?= htmlspecialchars($_SESSION['matricula']) ?></strong>
            </p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:2rem;font-weight:700;color:#fff;"><?= $totalTurmas ?></div>
            <div style="font-size:.8125rem;color:rgba(255,255,255,.7);">disciplinas ativas</div>
        </div>
    </div>
</div>

<!-- ALERTAS DE FREQUÊNCIA -->
<?php if (!empty($reprovadoFalta)): ?>
<div class="alert alert-danger">
    ⚠️ <strong>Atenção!</strong> Você ultrapassou o limite de faltas em
    <?= count($reprovadoFalta) ?> disciplina(s): <?= implode(', ', array_map(fn($t) => htmlspecialchars($t['nomeDisciplina']), $reprovadoFalta)) ?>.
    Consulte a secretaria.
</div>
<?php elseif (!empty($emRisco)): ?>
<div class="alert alert-warning">
    ⏳ <strong>Cuidado!</strong> Você está próximo do limite de faltas em:
    <?= implode(', ', array_map(fn($t) => htmlspecialchars($t['nomeDisciplina']), $emRisco)) ?>.
</div>
<?php endif; ?>

<div class="grid-2">
    <!-- MINHAS DISCIPLINAS RESUMO -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📚 Minhas Disciplinas</span>
            <a href="disciplinas.php" class="btn btn-sm btn-outline">Ver todas</a>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($turmas)): ?>
                <p class="text-muted text-center" style="padding:2rem;">Nenhuma disciplina matriculada.</p>
            <?php else: ?>
            <?php foreach (array_slice($turmas, 0, 5) as $t):
                $pctFalta = $t['limiteHorasFalta'] > 0 ? min(100, round(($t['totalFaltas'] / $t['limiteHorasFalta']) * 100)) : 0;
                $corFreq  = $pctFalta >= 100 ? 'red' : ($pctFalta >= 75 ? 'yellow' : 'green');
            ?>
            <div style="padding:.875rem 1.25rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;">
                <div>
                    <strong style="font-size:.9375rem;"><?= htmlspecialchars($t['nomeDisciplina']) ?></strong><br>
                    <small class="text-muted"><?= htmlspecialchars($t['nomeProfessor']) ?></small>
                </div>
                <div style="text-align:right;">
                    <div class="freq-badge freq-<?= $pctFalta >= 100 ? 'critico' : ($pctFalta >= 75 ? 'risco' : 'ok') ?>" style="font-size:.75rem;">
                        <?= $t['totalFaltas'] ?>/<?= $t['limiteHorasFalta'] ?>h faltas
                    </div>
                    <div class="progress-bar mt-1" style="width:100px;">
                        <div class="progress-fill <?= $corFreq ?>" style="width:<?= $pctFalta ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- AVISOS -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📢 Avisos Recentes</span>
        </div>
        <div class="card-body" style="padding:0;">
            <?php if (empty($avisos)): ?>
                <p class="text-muted text-center" style="padding:2rem;">Nenhum aviso no momento.</p>
            <?php else: ?>
            <?php foreach (array_slice($avisos, 0, 5) as $a): ?>
            <div style="padding:.875rem 1.25rem;border-bottom:1px solid var(--border);">
                <div class="flex justify-between items-center">
                    <strong style="font-size:.9rem;"><?= htmlspecialchars($a['titulo'] ?: 'Aviso') ?></strong>
                    <?php if ($a['fixado']): ?><span class="badge badge-warning">📌 Fixado</span><?php endif; ?>
                </div>
                <p class="text-muted" style="font-size:.8125rem;margin-top:.25rem;"><?= htmlspecialchars(substr($a['conteudo'], 0, 100)) ?>...</p>
                <small class="text-muted"><?= date('d/m/Y H:i', strtotime($a['dataPostagem'])) ?> · <?= htmlspecialchars($a['nomeAutor']) ?></small>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

        </main></div></div></body></html>
