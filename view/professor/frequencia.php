<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/turmas.php';
require_once '../../model/frequencias.php';

$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
$idAula  = filter_input(INPUT_GET, 'idAula',  FILTER_VALIDATE_INT);
$idProf  = $_SESSION['idUsuario'];

// Se não veio idTurma, listar turmas do professor
$turmas  = listarTurmasProfessorV2($idProf, date('Y'));
$turma   = $idTurma ? buscarTurmaPorId($idTurma) : null;
$alunos  = $idTurma ? listarAlunosTurma($idTurma) : [];
$aulas   = $idTurma ? listarAulasTurma($idTurma)  : [];

$pageTitle  = 'Registrar Frequência';
$currentNav = 'frequencias';
$depth      = 2;
include '../_layout.php';
?>

<!-- SELETOR DE TURMA -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center">
            <label class="form-label" style="margin:0;white-space:nowrap;">Turma:</label>
            <select name="idTurma" class="form-control" onchange="this.form.submit()" style="max-width:360px;">
                <option value="">Selecione uma turma</option>
                <?php foreach ($turmas as $t): ?>
                    <option value="<?= $t['idTurma'] ?>" <?= $idTurma == $t['idTurma'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['nomeDisciplina']) ?> — <?= $t['codigo'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
</div>

<?php if ($idTurma && $turma): ?>

<div class="grid-2 mb-4">
    <!-- REGISTRAR NOVA AULA -->
    <div class="card">
        <div class="card-header"><span class="card-title">➕ Registrar Nova Aula</span></div>
        <div class="card-body">
            <form method="post" action="../../controller/controlador.php">
                <input type="hidden" name="operacao" value="registrarAula">
                <input type="hidden" name="idTurma"  value="<?= $idTurma ?>">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Data da Aula *</label>
                        <input type="date" name="dataAula" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora Início</label>
                        <input type="time" name="horaInicio" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hora Fim</label>
                        <input type="time" name="horaFim" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Conteúdo ministrado</label>
                    <textarea name="conteudo" class="form-control" rows="2" placeholder="Descreva o conteúdo da aula..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary">📋 Registrar e marcar presença</button>
            </form>
        </div>
    </div>

    <!-- MARCAR PRESENÇA EM AULA EXISTENTE -->
    <div class="card">
        <div class="card-header"><span class="card-title">✅ Marcar Presença em Aula Existente</span></div>
        <div class="card-body">
            <?php if (empty($aulas)): ?>
                <p class="text-muted">Nenhuma aula registrada ainda. Registre uma aula ao lado.</p>
            <?php else: ?>
            <form method="post" action="../../controller/controlador.php">
                <input type="hidden" name="operacao" value="registrarFrequencias">
                <input type="hidden" name="idTurma"  value="<?= $idTurma ?>">
                <div class="form-group">
                    <label class="form-label">Selecionar aula</label>
                    <select name="idAula" class="form-control" onchange="this.form.submit()" id="selAula">
                        <option value="">Selecione a aula</option>
                        <?php foreach ($aulas as $a): ?>
                            <option value="<?= $a['idAula'] ?>" <?= $idAula == $a['idAula'] ? 'selected' : '' ?>>
                                <?= date('d/m/Y', strtotime($a['dataAula'])) ?>
                                <?= $a['horaInicio'] ? '('.substr($a['horaInicio'],0,5).')' : '' ?>
                                — <?= htmlspecialchars(substr($a['conteudo'] ?: 'Sem conteúdo', 0, 40)) ?>
                                (P:<?= $a['presentes'] ?> / F:<?= $a['ausentes'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($idAula): ?>
                <div style="max-height:280px;overflow-y:auto;border:1px solid var(--border);border-radius:var(--radius-sm);margin-bottom:1rem;">
                    <table style="width:100%;">
                        <thead><tr><th style="padding:.5rem 1rem;">Aluno</th><th style="padding:.5rem 1rem;">Matrícula</th><th style="padding:.5rem 1rem;text-align:center;">Presente</th></tr></thead>
                        <tbody>
                        <?php foreach ($alunos as $a): ?>
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:.5rem 1rem;"><?= htmlspecialchars($a['nome']) ?></td>
                            <td style="padding:.5rem 1rem;"><span class="badge badge-muted"><?= htmlspecialchars($a['matricula']) ?></span></td>
                            <td style="padding:.5rem 1rem;text-align:center;">
                                <input type="checkbox" name="presentes[]" value="<?= $a['idUsuario'] ?>"
                                       style="width:18px;height:18px;accent-color:var(--success);" checked>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="flex gap-2">
                    <button type="button" class="btn btn-outline btn-sm" onclick="toggleTodos(true)">✅ Todos</button>
                    <button type="button" class="btn btn-outline btn-sm" onclick="toggleTodos(false)">❌ Nenhum</button>
                    <button type="submit" class="btn btn-success">💾 Salvar frequência</button>
                </div>
                <?php endif; ?>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- HISTÓRICO DE AULAS -->
<div class="card">
    <div class="card-header">
        <span class="card-title">📅 Histórico de Aulas — <?= htmlspecialchars($turma['nomeDisciplina']) ?></span>
        <span class="text-muted"><?= count($aulas) ?> aula(s) registrada(s)</span>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($aulas)): ?>
            <p class="text-muted text-center" style="padding:2rem;">Nenhuma aula registrada.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Data</th><th>Horário</th><th>Conteúdo</th><th>Presenças</th><th>Faltas</th></tr></thead>
            <tbody>
            <?php foreach ($aulas as $a): ?>
            <tr>
                <td><?= date('d/m/Y', strtotime($a['dataAula'])) ?></td>
                <td><?= $a['horaInicio'] ? substr($a['horaInicio'],0,5).' - '.substr($a['horaFim'],0,5) : '—' ?></td>
                <td><?= htmlspecialchars($a['conteudo'] ?: '—') ?></td>
                <td><span class="badge badge-success"><?= $a['presentes'] ?></span></td>
                <td><span class="badge badge-<?= $a['ausentes'] > 0 ? 'danger' : 'muted' ?>"><?= $a['ausentes'] ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleTodos(val) {
    document.querySelectorAll('input[name="presentes[]"]').forEach(cb => cb.checked = val);
}
</script>

<?php else: ?>
<div class="card"><div class="card-body text-center text-muted" style="padding:3rem;">Selecione uma turma para registrar frequência.</div></div>
<?php endif; ?>

        </main></div></div></body></html>
