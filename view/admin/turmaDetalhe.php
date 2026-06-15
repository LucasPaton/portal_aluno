<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/turmas.php';
require_once '../../model/usuarios.php';
require_once '../../model/frequencias.php';

$idTurma = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idTurma) { header('Location: turmas.php'); exit; }

// Buscar turma com metadata
$res = consultarSQL(
    "SELECT t.*, tm.idCurso, tm.modulo, tm.turno,
            c.nome as nomeCurso, c.codigo as codigoCurso,
            u.nome as nomeProfessor
     FROM Turmas t
     LEFT JOIN TurmaMetadata tm ON tm.idTurma = t.idTurma
     LEFT JOIN Cursos c ON c.idCurso = tm.idCurso
     LEFT JOIN Usuarios u ON u.idUsuario = t.idProfessor
     WHERE t.idTurma = ?", "i", [$idTurma]
);
$turma = obterLinha($res);
if (!$turma) { header('Location: turmas.php'); exit; }

$alunos      = listarAlunosTurma($idTurma);
$disciplinas = listarDisciplinasDaTurma($idTurma);

// Buscar alunos disponíveis para matricular (não matriculados nesta turma)
$resDisponiveis = consultarSQL(
    "SELECT idUsuario, matricula, nome FROM Usuarios
     WHERE tipo='aluno' AND ativo=1
       AND idUsuario NOT IN (
           SELECT idAluno FROM Matriculas WHERE idTurma=? AND status='ativa'
       )
     ORDER BY nome",
    "i", [$idTurma]
);
$disponiveis = obterTodos($resDisponiveis);

// Buscar turmas do mesmo curso para promoção
$resTurmasPromocao = consultarSQL(
    "SELECT t.idTurma, t.codigo, tm.modulo FROM Turmas t
     JOIN TurmaMetadata tm ON tm.idTurma = t.idTurma
     WHERE tm.idCurso = ? AND t.idTurma != ? AND t.ativo = 1
     ORDER BY tm.modulo, t.codigo",
    "ii", [$turma['idCurso'] ?? 0, $idTurma]
);
$turmasPromocao = obterTodos($resTurmasPromocao);

$turnoLabel = ['M'=>'Matutino','T'=>'Tarde','N'=>'Noturno','I'=>'Integral'];

$pageTitle  = 'Turma ' . htmlspecialchars($turma['codigo']);
$currentNav = 'turmas';
$depth      = 2;
include '../_layout.php';
?>

<!-- CABEÇALHO DA TURMA -->
<div class="card mb-4">
  <div class="card-body">
    <div class="flex justify-between items-center">
      <div>
        <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:.25rem;">
          Turma <?=htmlspecialchars($turma['codigo'])?>
        </h2>
        <div class="flex gap-2" style="flex-wrap:wrap;">
          <span class="badge badge-primary"><?=htmlspecialchars($turma['nomeCurso'] ?? '—')?></span>
          <span class="badge badge-info">Módulo <?=$turma['modulo'] ?? '—'?></span>
          <span class="badge badge-muted"><?=$turnoLabel[$turma['turno'] ?? 'M']?></span>
          <span class="badge badge-muted"><?=$turma['ano']?>/<?=$turma['semestre']?>º Semestre</span>
          <?php if($turma['sala']): ?>
            <span class="badge badge-muted">Sala: <?=htmlspecialchars($turma['sala'])?></span>
          <?php endif; ?>
        </div>
        <?php if($turma['nomeProfessor']): ?>
          <p class="text-muted mt-2" style="font-size:.875rem;">👨‍🏫 Prof. <?=htmlspecialchars($turma['nomeProfessor'])?></p>
        <?php endif; ?>
      </div>
      <a href="turmas.php" class="btn btn-outline">← Voltar</a>
    </div>
  </div>
</div>

<div class="grid-2 mb-4">
  <!-- DISCIPLINAS DO MÓDULO -->
  <div class="card">
    <div class="card-header"><span class="card-title">📖 Disciplinas do Módulo <?=$turma['modulo'] ?? '—'?></span></div>
    <div class="card-body" style="padding:0;">
      <?php if(empty($disciplinas)): ?>
        <p class="text-muted text-center" style="padding:1.5rem;">
          Nenhuma disciplina cadastrada para este módulo.<br>
          <a href="cursos.php?aba=disciplinas" class="btn btn-sm btn-outline mt-2">Cadastrar disciplinas</a>
        </p>
      <?php else: ?>
      <table>
        <thead><tr><th>Disciplina</th><th>Código</th><th>Carga H.</th></tr></thead>
        <tbody>
        <?php foreach($disciplinas as $d): ?>
        <tr>
          <td><?=htmlspecialchars($d['nome'])?></td>
          <td><span class="badge badge-muted"><?=htmlspecialchars($d['codigo'])?></span></td>
          <td><?=$d['cargaHoraria']?>h</td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>

  <!-- STATS -->
  <div class="card">
    <div class="card-header"><span class="card-title">📊 Resumo</span></div>
    <div class="card-body">
      <div class="stats-grid" style="grid-template-columns:1fr 1fr;">
        <div class="stat-card">
          <div class="stat-icon blue">👨‍🎓</div>
          <div><div class="stat-value"><?=count($alunos)?></div><div class="stat-label">Alunos</div></div>
        </div>
        <div class="stat-card">
          <div class="stat-icon green">📖</div>
          <div><div class="stat-value"><?=count($disciplinas)?></div><div class="stat-label">Disciplinas</div></div>
        </div>
      </div>
      <div class="mt-3">
        <button class="btn btn-primary w-full" style="justify-content:center;"
                onclick="document.getElementById('modalMatricular').style.display='flex'">
          ➕ Matricular Aluno
        </button>
      </div>
    </div>
  </div>
</div>

<!-- LISTA DE ALUNOS -->
<div class="card">
  <div class="card-header">
    <span class="card-title">👨‍🎓 Alunos Matriculados (<?=count($alunos)?>)</span>
  </div>
  <div class="card-body" style="padding:0;">
    <?php if(empty($alunos)): ?>
      <p class="text-muted text-center" style="padding:2rem;">Nenhum aluno matriculado.</p>
    <?php else: ?>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Matrícula</th>
            <th>Nome</th>
            <th>Faltas</th>
            <th>Situação</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($alunos as $a):
          $pctFalta = $turma['limiteHorasFalta'] > 0
            ? min(100, round(($a['totalFaltas'] / $turma['limiteHorasFalta']) * 100))
            : 0;
          $corFreq = $pctFalta >= 100 ? 'critico' : ($pctFalta >= 75 ? 'risco' : 'ok');
        ?>
        <tr>
          <td><span class="badge badge-muted"><?=htmlspecialchars($a['matricula'])?></span></td>
          <td>
            <a href="perfilUsuario.php?id=<?=$a['idUsuario']?>" style="font-weight:600;">
              <?=htmlspecialchars($a['nome'])?>
            </a>
          </td>
          <td>
            <span class="freq-badge freq-<?=$corFreq?>">
              <?=$a['totalFaltas']?>/<?=$turma['limiteHorasFalta']?>h
            </span>
          </td>
          <td>
            <span class="badge badge-<?=$a['situacao']==='aprovado'?'success':($a['situacao']==='reprovado'?'danger':'info')?>">
              <?=ucfirst($a['situacao'])?>
            </span>
          </td>
          <td>
            <a href="perfilUsuario.php?id=<?=$a['idUsuario']?>" class="btn btn-sm btn-outline">👁</a>
            <?php if(!empty($turmasPromocao)): ?>
            <button class="btn btn-sm btn-success"
                    onclick="abrirPromocao(<?=$a['idUsuario']?>, '<?=htmlspecialchars(addslashes($a['nome']))?>', <?=$a['idMatricula']?>)">
              ⬆️ Promover
            </button>
            <?php endif; ?>
            <a href="../../controller/controlador.php?operacao=removerMatricula&idMatricula=<?=$a['idMatricula']?>&idTurma=<?=$idTurma?>"
               class="btn btn-sm btn-danger"
               onclick="return confirm('Remover matrícula de <?=htmlspecialchars(addslashes($a['nome']))?>?')">🗑</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL: MATRICULAR ALUNO -->
<div id="modalMatricular" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.5rem;width:100%;max-width:500px;max-height:80vh;overflow-y:auto;">
    <div class="flex justify-between items-center mb-3">
      <h3 style="font-weight:700;">➕ Matricular Aluno na Turma <?=htmlspecialchars($turma['codigo'])?></h3>
      <button onclick="document.getElementById('modalMatricular').style.display='none'" class="btn btn-outline btn-sm">✕</button>
    </div>
    <?php if(empty($disponiveis)): ?>
      <p class="text-muted">Todos os alunos ativos já estão matriculados nesta turma.</p>
    <?php else: ?>
    <form method="post" action="../../controller/controlador.php">
      <input type="hidden" name="operacao" value="matricularAluno">
      <input type="hidden" name="idTurma"  value="<?=$idTurma?>">
      <div class="form-group">
        <label class="form-label">Pesquisar e selecionar aluno:</label>
        <input type="text" id="filtroAluno" class="form-control mb-2"
               placeholder="Digite o nome ou matrícula..." oninput="filtrarAlunos()">
        <div id="listaAlunos" style="max-height:260px;overflow-y:auto;border:1px solid var(--border);border-radius:var(--radius-sm);">
          <?php foreach($disponiveis as $al): ?>
          <label style="display:flex;align-items:center;gap:.75rem;padding:.625rem .875rem;cursor:pointer;border-bottom:1px solid var(--border);"
                 class="item-aluno" data-nome="<?=strtolower($al['nome'])?>" data-mat="<?=strtolower($al['matricula'])?>">
            <input type="radio" name="idAluno" value="<?=$al['idUsuario']?>" required style="accent-color:var(--primary);">
            <span>
              <strong><?=htmlspecialchars($al['nome'])?></strong><br>
              <small class="text-muted"><?=htmlspecialchars($al['matricula'])?></small>
            </span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="flex gap-3 mt-3">
        <button type="submit" class="btn btn-primary">✅ Matricular</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalMatricular').style.display='none'">Cancelar</button>
      </div>
    </form>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL: PROMOVER ALUNO -->
<div id="modalPromover" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.5rem;width:100%;max-width:420px;">
    <div class="flex justify-between items-center mb-3">
      <h3 style="font-weight:700;">⬆️ Promover Aluno</h3>
      <button onclick="document.getElementById('modalPromover').style.display='none'" class="btn btn-outline btn-sm">✕</button>
    </div>
    <p class="text-muted mb-3">Aluno: <strong id="nomePromover"></strong></p>
    <form method="post" action="../../controller/controlador.php">
      <input type="hidden" name="operacao"      value="promoverAluno">
      <input type="hidden" name="idAluno"       id="idAlunoPromover">
      <input type="hidden" name="idTurmaAtual"  value="<?=$idTurma?>">
      <div class="form-group">
        <label class="form-label">Mover para a turma:</label>
        <select name="idTurmaNova" class="form-control" required>
          <?php foreach($turmasPromocao as $tp): ?>
          <option value="<?=$tp['idTurma']?>">
            <?=htmlspecialchars($tp['codigo'])?> — Módulo <?=$tp['modulo']?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="alert alert-info" style="font-size:.8125rem;">
        A matrícula atual será marcada como <strong>concluída</strong> e uma nova matrícula será criada na turma de destino.
      </div>
      <div class="flex gap-3 mt-3">
        <button type="submit" class="btn btn-success">⬆️ Confirmar Promoção</button>
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modalPromover').style.display='none'">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
function filtrarAlunos() {
  const q = document.getElementById('filtroAluno').value.toLowerCase();
  document.querySelectorAll('.item-aluno').forEach(el => {
    const nome = el.dataset.nome;
    const mat  = el.dataset.mat;
    el.style.display = (!q || nome.includes(q) || mat.includes(q)) ? '' : 'none';
  });
}
function abrirPromocao(idAluno, nome, idMatricula) {
  document.getElementById('idAlunoPromover').value  = idAluno;
  document.getElementById('nomePromover').textContent = nome;
  document.getElementById('modalPromover').style.display = 'flex';
}
document.getElementById('modalMatricular').addEventListener('click', e => { if(e.target===this||e.target.id==='modalMatricular') e.target.style.display='none'; });
document.getElementById('modalPromover').addEventListener('click', e => { if(e.target.id==='modalPromover') e.target.style.display='none'; });
</script>

        </main></div></div></body></html>
