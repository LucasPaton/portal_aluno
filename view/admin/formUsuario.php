<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/usuarios.php';

$tipo    = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'aluno';
// Segurança: só permite criar aluno ou professor pelo formulário
if (!in_array($tipo, ['aluno','professor'])) $tipo = 'aluno';

$idEdit  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$usuario = $idEdit ? buscarUsuarioPorId($idEdit) : null;
// Se editando, usar o tipo real do usuário
if ($usuario) $tipo = $usuario['tipo'];

$labelTipo = ['aluno'=>'Aluno','professor'=>'Professor'];
$isNovo    = !$usuario;

$pageTitle  = ($isNovo ? 'Novo ' : 'Editar ') . ($labelTipo[$tipo] ?? ucfirst($tipo));
$currentNav = $tipo === 'professor' ? 'professores' : 'alunos';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
  <div>
    <h2 style="font-size:1.125rem;font-weight:700;"><?=$pageTitle?></h2>
    <?php if($usuario): ?>
      <p class="text-muted"><?=htmlspecialchars($usuario['matricula'])?> · <?=htmlspecialchars($usuario['email'])?></p>
    <?php endif; ?>
  </div>
  <a href="usuarios.php?tipo=<?=$tipo?>" class="btn btn-outline btn-sm">← Voltar</a>
</div>

<?php if(isset($_GET['msg'])): ?>
  <div class="alert alert-danger mb-4"><?=htmlspecialchars($_GET['msg'])?></div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <span class="card-title">
      <?=$tipo==='professor'?'👨‍🏫':'👨‍🎓'?>
      <?=$isNovo?'Cadastrar novo':'Editar'?> <?=$labelTipo[$tipo]?>
    </span>
  </div>
  <div class="card-body">
    <form method="post" action="../../controller/controlador.php">
      <input type="hidden" name="operacao" value="<?=$isNovo?'criarUsuario':'atualizarUsuario'?>">
      <input type="hidden" name="tipo"     value="<?=$tipo?>">
      <?php if($idEdit): ?>
        <input type="hidden" name="idUsuario" value="<?=$idEdit?>">
      <?php endif; ?>

      <h4 style="margin-bottom:1rem;color:var(--text-muted);font-size:.8125rem;text-transform:uppercase;letter-spacing:.06em;">
        Dados Pessoais
      </h4>
      <div class="form-row">
        <div class="form-group" style="grid-column:span 2;">
          <label class="form-label">Nome completo *</label>
          <input type="text" name="nome" class="form-control" required
                 value="<?=htmlspecialchars($usuario['nome']??'')?>">
        </div>
        <div class="form-group" style="grid-column:span 2;">
          <label class="form-label">E-mail<?=$isNovo?' *':' (não editável)'?></label>
          <?php if($isNovo): ?>
            <input type="email" name="email" class="form-control" required
                   placeholder="email@exemplo.com">
            <span class="form-hint">Será usado como login de acesso.</span>
          <?php else: ?>
            <input type="text" class="form-control" value="<?=htmlspecialchars($usuario['email']??'')?>" disabled
                   style="background:var(--bg);color:var(--text-muted);">
          <?php endif; ?>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">CPF</label>
          <input type="text" name="cpf" class="form-control" placeholder="000.000.000-00"
                 value="<?=htmlspecialchars($usuario['cpf']??'')?>">
        </div>
        <div class="form-group">
          <label class="form-label">RG</label>
          <input type="text" name="rg" class="form-control"
                 value="<?=htmlspecialchars($usuario['rg']??'')?>">
        </div>
        <div class="form-group">
          <label class="form-label">Data de nascimento</label>
          <input type="date" name="dataNasc" class="form-control"
                 value="<?=htmlspecialchars($usuario['dataNasc']??'')?>">
        </div>
        <div class="form-group">
          <label class="form-label">Sexo</label>
          <select name="sexo" class="form-control">
            <option value="">Selecione</option>
            <option value="M"    <?=($usuario['sexo']??'')==='M'    ?'selected':''?>>Masculino</option>
            <option value="F"    <?=($usuario['sexo']??'')==='F'    ?'selected':''?>>Feminino</option>
            <option value="Outro"<?=($usuario['sexo']??'')==='Outro'?'selected':''?>>Outro</option>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Telefone</label>
          <input type="text" name="telefone" class="form-control"
                 value="<?=htmlspecialchars($usuario['telefone']??'')?>">
        </div>
        <div class="form-group">
          <label class="form-label">Celular</label>
          <input type="text" name="celular" class="form-control"
                 value="<?=htmlspecialchars($usuario['celular']??'')?>">
        </div>
      </div>

      <h4 style="margin:1.5rem 0 1rem;color:var(--text-muted);font-size:.8125rem;text-transform:uppercase;letter-spacing:.06em;">
        Endereço
      </h4>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">CEP</label>
          <input type="text" name="cep" class="form-control" placeholder="00000-000"
                 value="<?=htmlspecialchars($usuario['cep']??'')?>">
          <span class="form-hint">Digite o CEP para preenchimento automático.</span>
        </div>
        <div class="form-group" style="grid-column:span 2;">
          <label class="form-label">Logradouro</label>
          <input type="text" name="logradouro" class="form-control"
                 value="<?=htmlspecialchars($usuario['logradouro']??'')?>">
        </div>
        <div class="form-group">
          <label class="form-label">Número</label>
          <input type="text" name="numero" class="form-control"
                 value="<?=htmlspecialchars($usuario['numero']??'')?>">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Complemento</label>
          <input type="text" name="complemento" class="form-control"
                 value="<?=htmlspecialchars($usuario['complemento']??'')?>">
        </div>
        <div class="form-group">
          <label class="form-label">Bairro</label>
          <input type="text" name="bairro" class="form-control"
                 value="<?=htmlspecialchars($usuario['bairro']??'')?>">
        </div>
        <div class="form-group">
          <label class="form-label">Cidade</label>
          <input type="text" name="cidade" class="form-control"
                 value="<?=htmlspecialchars($usuario['cidade']??'')?>">
        </div>
        <div class="form-group">
          <label class="form-label">Estado (UF)</label>
          <input type="text" name="estado" class="form-control" maxlength="2" placeholder="ES"
                 value="<?=htmlspecialchars($usuario['estado']??'')?>">
        </div>
      </div>

      <?php if($isNovo): ?>
      <h4 style="margin:1.5rem 0 1rem;color:var(--text-muted);font-size:.8125rem;text-transform:uppercase;letter-spacing:.06em;">
        Acesso
      </h4>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Senha inicial *</label>
          <input type="password" name="senha" class="form-control" required
                 value="123456" placeholder="Mínimo 6 caracteres">
          <span class="form-hint">O usuário poderá alterar após o primeiro acesso.</span>
        </div>
      </div>
      <?php endif; ?>

      <div class="flex gap-3 mt-4">
        <button type="submit" class="btn btn-primary btn-lg">💾 <?=$isNovo?'Cadastrar':'Salvar alterações'?></button>
        <a href="usuarios.php?tipo=<?=$tipo?>" class="btn btn-outline btn-lg">Cancelar</a>
      </div>
    </form>
  </div>
</div>

        </main></div></div></body></html>
