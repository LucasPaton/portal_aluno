<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/usuarios.php';
require_once '../../model/turmas.php';

require_once '../../model/retencao.php';

$totalAlunos    = contarUsuarios('aluno');
$totalProfessores = contarUsuarios('professor');
$cursos         = listarCursos();
$totalCursos    = count($cursos);
$statsRetencao  = estatisticasRetencao();

$pageTitle  = 'Dashboard — Administrador';
$currentNav = 'dashboard';
$depth      = 2;
include '../_layout.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue">👨‍🎓</div>
        <div>
            <div class="stat-value"><?= $totalAlunos ?></div>
            <div class="stat-label">Alunos ativos</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">👨‍🏫</div>
        <div>
            <div class="stat-value"><?= $totalProfessores ?></div>
            <div class="stat-label">Professores</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow">📚</div>
        <div>
            <div class="stat-value"><?= $totalCursos ?></div>
            <div class="stat-label">Cursos cadastrados</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon <?= ($statsRetencao['expirandoEmBreve'] ?? 0) > 0 ? 'yellow' : 'blue' ?>">🗃️</div>
        <div>
            <div class="stat-value"><?= $statsRetencao['inativos'] ?? 0 ?></div>
            <div class="stat-label">Em retenção (10 anos)<?= ($statsRetencao['expirandoEmBreve'] ?? 0) > 0 ? ' ⚠️' : '' ?></div>
        </div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <span class="card-title">⚡ Ações Rápidas</span>
        </div>
        <div class="card-body" style="display:flex;flex-direction:column;gap:.75rem;">
            <a href="formUsuario.php?tipo=aluno"     class="btn btn-primary">➕ Novo Aluno</a>
            <a href="formUsuario.php?tipo=professor" class="btn btn-success">➕ Novo Professor</a>
            <a href="turmas.php"                     class="btn btn-info">🏫 Gerenciar Turmas</a>
            <a href="cursos.php"                     class="btn btn-warning">📚 Gerenciar Cursos</a>
            <a href="avisos.php"                     class="btn btn-outline">📢 Publicar Aviso</a>
            <a href="inativos.php"                   class="btn btn-outline">🗃️ Registros Inativos</a>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <span class="card-title">📚 Cursos Cadastrados</span>
            <a href="cursos.php" class="btn btn-sm btn-outline">Ver todos</a>
        </div>
        <div class="card-body">
            <?php if (empty($cursos)): ?>
                <p class="text-muted">Nenhum curso cadastrado ainda.</p>
            <?php else: ?>
                <table>
                    <thead><tr><th>Curso</th><th>Código</th><th>Duração</th></tr></thead>
                    <tbody>
                    <?php foreach ($cursos as $c): ?>
                        <tr>
                            <td><?= htmlspecialchars($c['nome']) ?></td>
                            <td><span class="badge badge-primary"><?= htmlspecialchars($c['codigo']) ?></span></td>
                            <td><?= $c['duracao'] ?> sem.</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

        </main>
    </div>
</div>
</body>
</html>
