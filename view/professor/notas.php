<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/turmas.php';
require_once '../../model/frequencias.php';

$idProf  = $_SESSION['idUsuario'];
$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
$turmas  = listarTurmasProfessorV2($idProf, date('Y'));
$turma   = $idTurma ? buscarTurmaPorId($idTurma) : null;
$alunos  = $idTurma ? listarAlunosTurma($idTurma) : [];
$estatNotas = $idTurma ? estatisticasNotas($idTurma) : [];

$pageTitle  = 'Lançar Notas';
$currentNav = 'notas';
$depth      = 2;
include '../_layout.php';
?>

<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center">
            <label class="form-label" style="margin:0;">Turma:</label>
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
    <!-- LANÇAR NOTA -->
    <div class="card">
        <div class="card-header"><span class="card-title">➕ Lançar Nova Nota</span></div>
        <div class="card-body">
            <form method="post" action="../../controller/controlador.php">
                <input type="hidden" name="operacao" value="lancarNota">
                <input type="hidden" name="idTurma"  value="<?= $idTurma ?>">
                <div class="form-group">
                    <label class="form-label">Aluno *</label>
                    <select name="idAluno" class="form-control" required id="selAluno" onchange="buscarMatricula(this.value)">
                        <option value="">Selecione o aluno</option>
                        <?php foreach ($alunos as $a): ?>
                            <option value="<?= $a['idUsuario'] ?>" data-mat="<?= $a['idMatricula'] ?>">
                                <?= htmlspecialchars($a['nome']) ?> (<?= htmlspecialchars($a['matricula']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <input type="hidden" name="idMatricula" id="hidMatricula">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tipo *</label>
                        <select name="tipo" class="form-control" required>
                            <option value="prova">Prova</option>
                            <option value="trabalho">Trabalho</option>
                            <option value="questionario">Questionário</option>
                            <option value="participacao">Participação</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Peso</label>
                        <input type="number" name="peso" class="form-control" value="1" min="0.1" max="10" step="0.1">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Descrição (ex: Prova 1, Trabalho Bimestral)</label>
                    <input type="text" name="descricao" class="form-control" placeholder="Ex: Prova do 1º Bimestre">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nota *</label>
                        <input type="number" name="nota" class="form-control" step="0.1" min="0" max="10" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nota máxima</label>
                        <input type="number" name="notaMaxima" class="form-control" value="10" step="0.1" min="0.1">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">💾 Lançar nota</button>
            </form>
        </div>
    </div>

    <!-- MÉDIAS DA TURMA -->
    <div class="card">
        <div class="card-header"><span class="card-title">📊 Médias da Turma</span></div>
        <div class="card-body" style="padding:0;max-height:380px;overflow-y:auto;">
            <?php if (empty($estatNotas)): ?>
                <p class="text-muted text-center" style="padding:2rem;">Nenhuma nota lançada ainda.</p>
            <?php else: ?>
            <table>
                <thead><tr><th>Aluno</th><th>Média</th><th>Avaliações</th><th>Situação</th></tr></thead>
                <tbody>
                <?php foreach ($estatNotas as $e): ?>
                <tr>
                    <td><?= htmlspecialchars($e['nome']) ?></td>
                    <td>
                        <span class="badge badge-<?= $e['media'] >= 7 ? 'success' : ($e['media'] >= 5 ? 'warning' : 'danger') ?>">
                            <?= number_format($e['media'], 1) ?>
                        </span>
                    </td>
                    <td><?= $e['totalAvaliacoes'] ?></td>
                    <td>
                        <?php if ($e['media'] >= 7): ?>
                            <span class="badge badge-success">Aprovado</span>
                        <?php elseif ($e['media'] >= 5): ?>
                            <span class="badge badge-warning">Recuperação</span>
                        <?php else: ?>
                            <span class="badge badge-danger">Reprovado</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- NOTAS DETALHADAS POR ALUNO -->
<div class="card">
    <div class="card-header">
        <span class="card-title">📝 Todas as Notas Lançadas — <?= htmlspecialchars($turma['nomeDisciplina']) ?></span>
    </div>
    <div class="card-body" style="padding:0;">
        <?php
        $todasNotas = [];
        foreach ($alunos as $al) {
            $ns = listarNotasAluno($al['idUsuario'], $idTurma);
            foreach ($ns as $n) { $n['nomeAluno'] = $al['nome']; $todasNotas[] = $n; }
        }
        if (empty($todasNotas)):
        ?>
            <p class="text-muted text-center" style="padding:2rem;">Nenhuma nota lançada nesta turma.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Aluno</th><th>Tipo</th><th>Descrição</th><th>Nota</th><th>Máx.</th><th>Peso</th><th>Data</th></tr></thead>
            <tbody>
            <?php foreach ($todasNotas as $n): ?>
            <tr>
                <td><?= htmlspecialchars($n['nomeAluno']) ?></td>
                <td><span class="badge badge-muted"><?= ucfirst($n['tipo']) ?></span></td>
                <td><?= htmlspecialchars($n['descricao'] ?: '—') ?></td>
                <td><span class="badge badge-<?= $n['nota']/$n['notaMaxima'] >= 0.7 ? 'success' : ($n['nota']/$n['notaMaxima'] >= 0.5 ? 'warning' : 'danger') ?>"><?= number_format($n['nota'],1) ?></span></td>
                <td><?= number_format($n['notaMaxima'],1) ?></td>
                <td><?= $n['peso'] ?></td>
                <td class="text-muted" style="font-size:.8125rem;"><?= date('d/m/Y', strtotime($n['dataLancamento'])) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<script>
function buscarMatricula(idAluno) {
    const sel = document.getElementById('selAluno');
    const opt = sel.querySelector(`option[value="${idAluno}"]`);
    document.getElementById('hidMatricula').value = opt ? opt.dataset.mat : '';
}
</script>

<?php else: ?>
<div class="card"><div class="card-body text-center text-muted" style="padding:3rem;">Selecione uma turma para lançar notas.</div></div>
<?php endif; ?>

        </main></div></div></body></html>
