<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/turmas.php';

$idProf = $_SESSION['idUsuario'];
$ano    = date('Y');
$turmas = listarTurmasProfessorV2($idProf, $ano);

$pageTitle  = 'Dashboard — Professor';
$currentNav = 'dashboard';
$depth      = 2;
include '../_layout.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">🏫</div>
        <div>
            <div class="stat-value"><?= count($turmas) ?></div>
            <div class="stat-label">Turmas em <?= $ano ?></div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">👨‍🎓</div>
        <div>
            <div class="stat-value"><?= array_sum(array_column($turmas, 'totalAlunos')) ?></div>
            <div class="stat-label">Total de alunos</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">🏫 Minhas Turmas — <?= $ano ?></span>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($turmas)): ?>
            <p class="text-muted text-center" style="padding:2rem;">Nenhuma turma atribuída.</p>
        <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr><th>Disciplina</th><th>Código</th><th>Semestre</th><th>Horário</th><th>Alunos</th><th>Ações</th></tr>
                </thead>
                <tbody>
                <?php foreach ($turmas as $t): ?>
                <tr>
                    <td>
                        <strong><?= htmlspecialchars($t['nomeDisciplina']) ?></strong><br>
                        <small class="text-muted"><?= htmlspecialchars($t['nomeCurso']) ?></small>
                    </td>
                    <td><span class="badge badge-primary"><?= htmlspecialchars($t['codigo']) ?></span></td>
                    <td><?= $t['semestre'] ?>º sem</td>
                    <td><?= htmlspecialchars($t['horario'] ?: '—') ?></td>
                    <td><span class="badge badge-info"><?= $t['totalAlunos'] ?></span></td>
                    <td>
                        <a href="turmaDetalhe.php?id=<?= $t['idTurma'] ?>" class="btn btn-sm btn-primary">Acessar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

        </main>
    </div>
</div>
</body>
</html>
