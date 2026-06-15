<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/usuarios.php';

$idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idUsuario) { header('Location: usuarios.php?tipo=aluno'); exit; }
$usuario = buscarUsuarioPorId($idUsuario);
if (!$usuario) { header('Location: usuarios.php?tipo=aluno'); exit; }

$pageTitle  = 'Arquivar: '.$usuario['nome'];
$currentNav = 'alunos';
$depth      = 2;
include '../_layout.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">🗃️ Arquivar Aluno — <?= htmlspecialchars($usuario['nome']) ?></span>
        <a href="usuarios.php?tipo=aluno" class="btn btn-outline btn-sm">← Voltar</a>
    </div>
    <div class="card-body">
        <div class="alert alert-warning mb-4">
            ⚠️ Ao arquivar, o aluno será <strong>desativado</strong> e um <strong>snapshot completo</strong> de todos os seus dados acadêmicos (matrículas, notas, frequências) será salvo e retido por <strong>10 anos</strong>, conforme política institucional.
        </div>

        <div class="card mb-4" style="border:none;background:var(--bg);">
            <div class="card-body">
                <div class="form-row">
                    <div><span class="form-label">Nome</span><p><?= htmlspecialchars($usuario['nome']) ?></p></div>
                    <div><span class="form-label">Matrícula</span><p><?= htmlspecialchars($usuario['matricula']) ?></p></div>
                    <div><span class="form-label">E-mail</span><p><?= htmlspecialchars($usuario['email']) ?></p></div>
                    <div><span class="form-label">CPF</span><p><?= htmlspecialchars($usuario['cpf'] ?: '—') ?></p></div>
                </div>
            </div>
        </div>

        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao"   value="arquivarUsuario">
            <input type="hidden" name="idUsuario"  value="<?= $idUsuario ?>">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Motivo do arquivamento *</label>
                    <select name="tipoEvento" class="form-control" required>
                        <option value="">Selecione</option>
                        <option value="formatura">Formatura / Conclusão de curso</option>
                        <option value="desligamento">Desligamento / Abandono</option>
                        <option value="transferencia">Transferência para outra instituição</option>
                        <option value="falecimento">Falecimento</option>
                        <option value="outros">Outros</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Data do evento *</label>
                    <input type="date" name="dataEvento" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    <span class="form-hint">A data de expiração será 10 anos após esta data.</span>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Observações</label>
                <textarea name="observacoes" class="form-control" rows="3" placeholder="Informações adicionais sobre o arquivamento..."></textarea>
            </div>

            <div class="flex gap-3 mt-2">
                <button type="submit" class="btn btn-warning"
                        onclick="return confirm('Confirma o arquivamento de <?= htmlspecialchars(addslashes($usuario['nome'])) ?>? Esta ação desativará o acesso do aluno.')">
                    🗃️ Arquivar e desativar
                </button>
                <a href="usuarios.php?tipo=aluno" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>

        </main></div></div></body></html>
