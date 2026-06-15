<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/turmas.php';
require_once '../../model/usuarios.php';

$ano     = filter_input(INPUT_GET, 'ano',     FILTER_VALIDATE_INT) ?: (int)date('Y');
$idCurso = filter_input(INPUT_GET, 'idCurso', FILTER_VALIDATE_INT) ?: 0;

$turmas     = listarTurmasCompletas($ano, $idCurso ?: null);
$cursos     = listarCursos(true);
$professores= listarUsuarios('professor', '', 1, 100);
$anos       = range((int)date('Y'), (int)date('Y') - 3);

$pageTitle  = 'Turmas';
$currentNav = 'turmas';
$depth      = 2;
include '../_layout.php';
?>

<!-- FILTROS -->
<div class="card mb-4">
  <div class="card-body">
    <form method="get" class="flex gap-3 items-center" style="flex-wrap:wrap;">
      <select name="ano" class="form-control" style="width:100px;" onchange="this.form.submit()">
        <?php foreach($anos as $a): ?>
          <option value="<?=$a?>" <?=$a==$ano?'selected':''?>><?=$a?></option>
        <?php endforeach; ?>
      </select>
      <select name="idCurso" class="form-control" style="width:220px;" onchange="this.form.submit()">
        <option value="0">Todos os cursos</option>
        <?php foreach($cursos as $c): ?>
          <option value="<?=$c['idCurso']?>" <?=$c['idCurso']==$idCurso?'selected':''?>>
            <?=htmlspecialchars($c['nome'])?>
          </option>
        <?php endforeach; ?>
      </select>
      <a href="turmas.php?ano=<?=$ano?>" class="btn btn-outline btn-sm">✕ Limpar</a>
      <button type="button" class="btn btn-primary" onclick="document.getElementById('modalNovaTurma').style.display='flex'">
        ➕ Nova Turma
      </button>
    </form>
  </div>
</div>

<!-- LISTA DE TURMAS -->
<div class="card">
  <div class="card-header">
    <span class="card-title">🏫 Turmas <?=$ano?></span>
    <span class="text-muted"><?=count($turmas)?> turma(s)</span>
  </div>
  <div class="card-body" style="padding:0;">
    <?php if(empty($turmas)): ?>
      <p class="text-muted text-center" style="padding:2rem;">
        Nenhuma turma cadastrada. Clique em "Nova Turma" para começar.
      </p>
    <?php else: ?>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>Código</th>
            <th>Curso</th>
            <th>Módulo</th>
            <th>Turno</th>
            <th>Ano/Sem</th>
            <th>Professor Resp.</th>
            <th>Alunos</th>
            <th>Sala</th>
            <th>Ações</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($turmas as $t):
          $turnoLabel = ['M'=>'☀️ Matutino','T'=>'🌤️ Tarde','N'=>'🌙 Noturno','I'=>'🕐 Integral'];
        ?>
        <tr>
          <td><strong><?=htmlspecialchars($t['codigo'])?></strong></td>
          <td><?=htmlspecialchars($t['nomeCurso'] ?? '—')?></td>
          <td><span class="badge badge-info">Módulo <?=$t['modulo'] ?? '—'?></span></td>
          <td><?=$turnoLabel[$t['turno'] ?? 'M'] ?? '—'?></td>
          <td><?=$t['ano']?>/<?=$t['semestre']?>º</td>
          <td><?=htmlspecialchars($t['nomeProfessor'] ?? '—')?></td>
          <td><span class="badge badge-<?=$t['totalAlunos']>0?'success':'muted'?>"><?=$t['totalAlunos']?></span></td>
          <td><?=htmlspecialchars($t['sala'] ?? '—')?></td>
          <td>
            <a href="turmaDetalhe.php?id=<?=$t['idTurma']?>" class="btn btn-sm btn-primary">Gerenciar</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- MODAL NOVA TURMA -->
<div id="modalNovaTurma" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;">
  <div style="background:var(--card-bg);border-radius:var(--radius);padding:1.75rem;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:var(--shadow-lg);">
    <div class="flex justify-between items-center mb-4">
      <h3 style="font-size:1.125rem;font-weight:700;">🏫 Nova Turma</h3>
      <button onclick="document.getElementById('modalNovaTurma').style.display='none'" class="btn btn-outline btn-sm">✕</button>
    </div>

    <form method="post" action="../../controller/controlador.php">
      <input type="hidden" name="operacao" value="criarTurma">

      <div class="form-row">
        <div class="form-group" style="grid-column:span 2;">
          <label class="form-label">Curso *</label>
          <select name="idCurso" class="form-control" required id="selectCurso" onchange="atualizarCodigo()">
            <option value="">Selecione o curso...</option>
            <?php foreach($cursos as $c): ?>
              <option value="<?=$c['idCurso']?>" data-codigo="<?=htmlspecialchars($c['codigo'])?>">
                <?=htmlspecialchars($c['nome'])?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Módulo *</label>
          <select name="modulo" class="form-control" required id="selectModulo" onchange="atualizarCodigo()">
            <option value="1">Módulo 1</option>
            <option value="2">Módulo 2</option>
            <option value="3">Módulo 3</option>
            <option value="4">Módulo 4</option>
            <option value="5">Módulo 5</option>
            <option value="6">Módulo 6</option>
          </select>
          <span class="form-hint">Período/semestre do curso</span>
        </div>
        <div class="form-group">
          <label class="form-label">Turno *</label>
          <select name="turno" class="form-control" required id="selectTurno" onchange="atualizarCodigo()">
            <option value="M">☀️ Matutino</option>
            <option value="T">🌤️ Tarde</option>
            <option value="N">🌙 Noturno</option>
            <option value="I">🕐 Integral</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Código da Turma *</label>
        <div class="flex gap-2 items-center">
          <input type="text" name="codigo" id="codigoTurma" class="form-control" required
                 placeholder="Ex: 1TM1" style="font-family:monospace;font-weight:700;font-size:1.125rem;">
          <button type="button" class="btn btn-outline btn-sm" onclick="atualizarCodigo()">🔄 Gerar</button>
        </div>
        <span class="form-hint">
          Formato sugerido: <strong>N°TurmasTurno+Módulo</strong> — ex: <strong>1TM1</strong>
          (1ª Turma, Matutino, Módulo 1) ou <strong>2TN3</strong> (2ª Turma, Noturno, Módulo 3)
        </span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Ano *</label>
          <select name="ano" class="form-control" required>
            <?php foreach($anos as $a): ?>
              <option value="<?=$a?>" <?=$a==(int)date('Y')?'selected':''?>><?=$a?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Semestre *</label>
          <select name="semestre" class="form-control" required>
            <option value="1">1º Semestre</option>
            <option value="2">2º Semestre</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Professor Responsável</label>
          <select name="idProfResp" class="form-control">
            <option value="">Sem professor responsável</option>
            <?php foreach($professores as $p): ?>
              <option value="<?=$p['idUsuario']?>"><?=htmlspecialchars($p['nome'])?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Sala</label>
          <input type="text" name="sala" class="form-control" placeholder="Ex: Sala 101, Lab. 3">
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Horário</label>
          <input type="text" name="horario" class="form-control"
                 placeholder="Ex: Seg/Qua/Sex 07h-11h">
        </div>
        <div class="form-group">
          <label class="form-label">Limite de alunos</label>
          <input type="number" name="limiteAlunos" class="form-control" value="40" min="1">
        </div>
      </div>

      <div class="flex gap-3 mt-4">
        <button type="submit" class="btn btn-primary btn-lg">💾 Criar Turma</button>
        <button type="button" class="btn btn-outline btn-lg"
                onclick="document.getElementById('modalNovaTurma').style.display='none'">Cancelar</button>
      </div>
    </form>
  </div>
</div>

<script>
// Contador de turmas por curso+modulo+turno para gerar código sugerido
async function atualizarCodigo() {
  const curso  = document.getElementById('selectCurso');
  const modulo = document.getElementById('selectModulo').value;
  const turno  = document.getElementById('selectTurno').value;
  if (!curso.value) return;
  // Código sugerido: sequencial + turno + módulo
  // Ex: 1TM1 = 1ª turma, Matutino, Módulo 1
  const seq = 1; // usuário pode editar manualmente
  document.getElementById('codigoTurma').value = seq + 'T' + turno + modulo;
}

// Fechar modal ao clicar fora
document.getElementById('modalNovaTurma').addEventListener('click', function(e) {
  if (e.target === this) this.style.display = 'none';
});
</script>

        </main></div></div></body></html>
