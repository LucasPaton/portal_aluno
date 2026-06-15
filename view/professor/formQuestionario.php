<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/turmas.php';

$idProf  = $_SESSION['idUsuario'];
$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
if (!$idTurma) { header('Location: turmas.php'); exit; }

$turma   = buscarTurmaPorId($idTurma);

$pageTitle  = 'Novo Questionário — ' . ($turma['nomeDisciplina'] ?? '');
$currentNav = 'questionarios';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 style="font-size:1.125rem;font-weight:700;">Novo Questionário</h2>
        <p class="text-muted"><?= htmlspecialchars($turma['nomeDisciplina'] ?? '') ?> · <?= htmlspecialchars($turma['codigo'] ?? '') ?></p>
    </div>
    <a href="turmaDetalhe.php?id=<?= $idTurma ?>" class="btn btn-outline btn-sm">← Voltar</a>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header"><span class="card-title">📋 Configurações do Questionário</span></div>
    <div class="card-body">
        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao" value="criarQuestionario">
            <input type="hidden" name="idTurma"  value="<?= $idTurma ?>">

            <div class="form-group">
                <label class="form-label">Título *</label>
                <input type="text" name="titulo" class="form-control" required
                       placeholder="Ex: Avaliação 1 — Fundamentos de PHP">
            </div>

            <div class="form-group">
                <label class="form-label">Descrição / Instruções</label>
                <textarea name="descricao" class="form-control" rows="2"
                          placeholder="Instruções para os alunos..."></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Data/hora de início</label>
                    <input type="datetime-local" name="dataInicio" class="form-control">
                    <span class="form-hint">Deixe em branco para liberar imediatamente ao publicar.</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Data/hora de encerramento</label>
                    <input type="datetime-local" name="dataFim" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Tempo limite (minutos)</label>
                    <input type="number" name="tempoLimite" class="form-control" value="0" min="0">
                    <span class="form-hint">0 = sem limite de tempo.</span>
                </div>
                <div class="form-group">
                    <label class="form-label">Tentativas permitidas</label>
                    <input type="number" name="tentativas" class="form-control" value="1" min="1">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Embaralhar questões?</label>
                    <select name="embaralharQuestoes" class="form-control">
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Embaralhar alternativas?</label>
                    <select name="embaralharAlternativas" class="form-control">
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary btn-lg">
                    ✅ Criar e adicionar questões
                </button>
                <a href="turmaDetalhe.php?id=<?= $idTurma ?>" class="btn btn-outline btn-lg">Cancelar</a>
            </div>
        </form>
    </div>
</div>

        </main></div></div></body></html>
