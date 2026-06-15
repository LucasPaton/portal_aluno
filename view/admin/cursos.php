<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/turmas.php';

$aba    = filter_input(INPUT_GET, 'aba', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'cursos';
$cursos = listarCursos(false);

// Para edição
$editCurso = null;
$editDisc  = null;
if (isset($_GET['editarCurso'])) {
    $id = filter_input(INPUT_GET, 'editarCurso', FILTER_VALIDATE_INT);
    $r  = consultarSQL("SELECT * FROM Cursos WHERE idCurso=?","i",[$id]);
    $editCurso = obterLinha($r);
    $aba = 'cursos';
}
if (isset($_GET['editarDisc'])) {
    $id = filter_input(INPUT_GET, 'editarDisc', FILTER_VALIDATE_INT);
    $r  = consultarSQL("SELECT * FROM Disciplinas WHERE idDisciplina=?","i",[$id]);
    $editDisc = obterLinha($r);
    $aba = 'disciplinas';
}

$pageTitle  = 'Cursos e Disciplinas';
$currentNav = 'cursos';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex gap-2 mb-4">
  <a href="?aba=cursos"      class="btn <?=$aba==='cursos'?'btn-primary':'btn-outline'?>">📚 Cursos</a>
  <a href="?aba=disciplinas" class="btn <?=$aba==='disciplinas'?'btn-primary':'btn-outline'?>">📖 Disciplinas</a>
</div>

<?php if($aba==='cursos'): ?>
<!-- ===================== CURSOS ===================== -->
<div class="grid-2 mb-4">
  <div class="card">
    <div class="card-header">
      <span class="card-title"><?=$editCurso ? '✏️ Editar Curso' : '➕ Novo Curso'?></span>
    </div>
    <div class="card-body">
      <form method="post" action="../../controller/controlador.php">
        <input type="hidden" name="operacao" value="<?=$editCurso?'editarCurso':'criarCurso'?>">
        <?php if($editCurso): ?>
          <input type="hidden" name="idCurso" value="<?=$editCurso['idCurso']?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Nome *</label>
          <input type="text" name="nome" class="form-control" required
                 value="<?=htmlspecialchars($editCurso['nome'] ?? '')?>">
        </div>
        <?php if(!$editCurso): ?>
        <div class="form-group">
          <label class="form-label">Código *</label>
          <input type="text" name="codigo" class="form-control" required placeholder="Ex: TI-001">
        </div>
        <?php endif; ?>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Duração (semestres)</label>
            <input type="number" name="duracao" class="form-control" min="1"
                   value="<?=$editCurso['duracao'] ?? 4?>">
          </div>
          <div class="form-group">
            <label class="form-label">Carga horária total (h)</label>
            <input type="number" name="cargaHoraria" class="form-control" min="1"
                   value="<?=$editCurso['cargaHorariaTotal'] ?? 1200?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Descrição</label>
          <textarea name="descricao" class="form-control" rows="2"><?=htmlspecialchars($editCurso['descricao'] ?? '')?></textarea>
        </div>
        <div class="flex gap-2">
          <button type="submit" class="btn btn-primary">💾 Salvar</button>
          <?php if($editCurso): ?>
            <a href="?aba=cursos" class="btn btn-outline">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header"><span class="card-title">📚 Cursos Cadastrados</span></div>
    <div class="card-body" style="padding:0;">
      <?php if(empty($cursos)): ?>
        <p class="text-muted text-center" style="padding:2rem;">Nenhum curso.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>Nome</th><th>Cód.</th><th>Módulos</th><th>C.H.</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach($cursos as $c): ?>
        <tr>
          <td><strong><?=htmlspecialchars($c['nome'])?></strong></td>
          <td><span class="badge badge-primary"><?=htmlspecialchars($c['codigo'])?></span></td>
          <td><?=$c['duracao']?></td>
          <td><?=$c['cargaHorariaTotal']?>h</td>
          <td><span class="badge badge-<?=$c['ativo']?'success':'muted'?>"><?=$c['ativo']?'Ativo':'Inativo'?></span></td>
          <td>
            <a href="?aba=cursos&editarCurso=<?=$c['idCurso']?>" class="btn btn-sm btn-info">✏️</a>
            <a href="?aba=disciplinas&idCursoFiltro=<?=$c['idCurso']?>" class="btn btn-sm btn-outline">📖 Disciplinas</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php else: /* DISCIPLINAS */ ?>
<!-- ===================== DISCIPLINAS ===================== -->
<?php
$idCursoFiltro = filter_input(INPUT_GET, 'idCursoFiltro', FILTER_VALIDATE_INT) ?: 0;
$disciplinas   = listarDisciplinas($idCursoFiltro ?: null);
?>

<div class="grid-2 mb-4">
  <div class="card">
    <div class="card-header">
      <span class="card-title"><?=$editDisc ? '✏️ Editar Disciplina' : '➕ Nova Disciplina'?></span>
    </div>
    <div class="card-body">
      <form method="post" action="../../controller/controlador.php">
        <input type="hidden" name="operacao" value="<?=$editDisc?'editarDisciplina':'criarDisciplina'?>">
        <?php if($editDisc): ?>
          <input type="hidden" name="idDisciplina" value="<?=$editDisc['idDisciplina']?>">
        <?php endif; ?>

        <?php if(!$editDisc): ?>
        <div class="form-group">
          <label class="form-label">Curso *</label>
          <select name="idCurso" class="form-control" required>
            <option value="">Selecione...</option>
            <?php foreach($cursos as $c): ?>
              <option value="<?=$c['idCurso']?>" <?=$c['idCurso']==$idCursoFiltro?'selected':''?>>
                <?=htmlspecialchars($c['nome'])?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Código *</label>
          <input type="text" name="codigo" class="form-control" required placeholder="Ex: WEB-01">
        </div>
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label">Nome *</label>
          <input type="text" name="nome" class="form-control" required
                 value="<?=htmlspecialchars($editDisc['nome'] ?? '')?>">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Módulo (período)</label>
            <select name="periodo" class="form-control">
              <?php for($m=1;$m<=6;$m++): ?>
                <option value="<?=$m?>" <?=($editDisc['periodo']??1)==$m?'selected':''?>>Módulo <?=$m?></option>
              <?php endfor; ?>
            </select>
            <span class="form-hint">Módulo do curso onde essa disciplina é ofertada</span>
          </div>
          <div class="form-group">
            <label class="form-label">Carga horária (h)</label>
            <input type="number" name="cargaHoraria" class="form-control" min="1"
                   value="<?=$editDisc['cargaHoraria'] ?? 80?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Ementa</label>
          <textarea name="ementa" class="form-control" rows="2"><?=htmlspecialchars($editDisc['ementa'] ?? '')?></textarea>
        </div>
        <div class="flex gap-2">
          <button type="submit" class="btn btn-primary">💾 Salvar</button>
          <?php if($editDisc): ?>
            <a href="?aba=disciplinas" class="btn btn-outline">Cancelar</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <span class="card-title">📖 Disciplinas</span>
      <?php if($idCursoFiltro): ?>
        <a href="?aba=disciplinas" class="btn btn-sm btn-outline">Ver todas</a>
      <?php endif; ?>
    </div>
    <div class="card-body" style="padding:0;">
      <?php if(empty($disciplinas)): ?>
        <p class="text-muted text-center" style="padding:2rem;">Nenhuma disciplina.</p>
      <?php else: ?>
      <table>
        <thead><tr><th>Nome</th><th>Curso</th><th>Módulo</th><th>C.H.</th><th>Ações</th></tr></thead>
        <tbody>
        <?php foreach($disciplinas as $d): ?>
        <tr>
          <td><strong><?=htmlspecialchars($d['nome'])?></strong></td>
          <td><small class="text-muted"><?=htmlspecialchars($d['nomeCurso'])?></small></td>
          <td><span class="badge badge-info">M<?=$d['periodo']?></span></td>
          <td><?=$d['cargaHoraria']?>h</td>
          <td>
            <a href="?aba=disciplinas&editarDisc=<?=$d['idDisciplina']?>" class="btn btn-sm btn-info">✏️</a>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

        </main></div></div></body></html>
