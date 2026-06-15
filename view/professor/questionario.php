<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/questionarios.php';
require_once '../../model/turmas.php';

$idQuiz  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
$quiz    = $idQuiz ? buscarQuestionarioPorId($idQuiz) : null;
$questoes= $idQuiz ? listarQuestoesQuestionario($idQuiz) : [];
if ($quiz) $idTurma = $quiz['idTurma'];

$pageTitle  = $quiz ? 'Editar: '.$quiz['titulo'] : 'Novo Questionário';
$currentNav = 'questionarios';
$depth      = 2;
include '../_layout.php';
?>

<?php if (!$quiz): ?>
<!-- FORMULÁRIO DE CRIAÇÃO -->
<div class="card mb-4">
    <div class="card-header">
        <span class="card-title">➕ Criar Questionário</span>
        <a href="questionarios.php?idTurma=<?= $idTurma ?>" class="btn btn-outline btn-sm">← Voltar</a>
    </div>
    <div class="card-body">
        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao" value="criarQuestionario">
            <input type="hidden" name="idTurma" value="<?= $idTurma ?>">
            <div class="form-group">
                <label class="form-label">Título *</label>
                <input type="text" name="titulo" class="form-control" required placeholder="Ex: Questionário 1 — Fundamentos PHP">
            </div>
            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" class="form-control" rows="2" placeholder="Instruções para os alunos..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Data/hora início</label>
                    <input type="datetime-local" name="dataInicio" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Data/hora fim</label>
                    <input type="datetime-local" name="dataFim" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Tempo limite (min, 0=sem limite)</label>
                    <input type="number" name="tempoLimite" class="form-control" value="0" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Tentativas permitidas</label>
                    <input type="number" name="tentativas" class="form-control" value="1" min="1">
                </div>
            </div>
            <div class="flex gap-4 mb-4">
                <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;">
                    <input type="checkbox" name="embaralharQuestoes" value="1"> Embaralhar questões
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;">
                    <input type="checkbox" name="embaralharAlternativas" value="1"> Embaralhar alternativas
                </label>
            </div>
            <button type="submit" class="btn btn-primary">Criar questionário →</button>
        </form>
    </div>
</div>

<?php else: ?>

<!-- CABEÇALHO DO QUIZ EXISTENTE -->
<div class="card mb-4">
    <div class="card-header">
        <div>
            <span class="card-title"><?= htmlspecialchars($quiz['titulo']) ?></span>
            <span class="badge badge-<?= $quiz['publicado'] ? 'success' : 'warning' ?>" style="margin-left:.5rem;">
                <?= $quiz['publicado'] ? '✅ Publicado' : '📝 Rascunho' ?>
            </span>
        </div>
        <div class="flex gap-2">
            <a href="estatQuestionario.php?id=<?= $idQuiz ?>" class="btn btn-sm btn-info">📊 Ver estatísticas</a>
            <a href="questionarios.php?idTurma=<?= $idTurma ?>" class="btn btn-sm btn-outline">← Voltar</a>
        </div>
    </div>
    <div class="card-body">
        <div class="flex gap-4" style="flex-wrap:wrap;">
            <span class="text-muted">❓ <?= count($questoes) ?> questões</span>
            <?php if ($quiz['tempoLimite'] > 0): ?><span class="text-muted">⏱️ <?= $quiz['tempoLimite'] ?> min</span><?php endif; ?>
            <?php if ($quiz['dataFim']): ?><span class="text-muted">📅 Até <?= date('d/m/Y H:i', strtotime($quiz['dataFim'])) ?></span><?php endif; ?>
        </div>
    </div>
</div>

<!-- GERAR COM IA -->
<?php if (!$quiz['publicado']): ?>
<div class="card mb-4">
    <div class="card-header"><span class="card-title">🤖 Gerar Questões com IA (Google Gemini)</span></div>
    <div class="card-body">
        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao" value="gerarIAQuestionario">
            <input type="hidden" name="idQuestionario" value="<?= $idQuiz ?>">
            <div class="form-row">
                <div class="form-group" style="grid-column:span 2">
                    <label class="form-label">Tema / conteúdo para gerar questões *</label>
                    <input type="text" name="tema" class="form-control" required placeholder="Ex: Comandos de seleção SQL, Funções recursivas em PHP...">
                </div>
                <div class="form-group">
                    <label class="form-label">Quantidade de questões</label>
                    <input type="number" name="quantidade" class="form-control" value="5" min="1" max="20">
                </div>
                <div class="form-group">
                    <label class="form-label">Nível de dificuldade</label>
                    <select name="nivel" class="form-control">
                        <option value="básico">Básico</option>
                        <option value="intermediário" selected>Intermediário</option>
                        <option value="avançado">Avançado</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-info">🤖 Gerar com IA</button>
        </form>
    </div>
</div>

<!-- ADICIONAR QUESTÃO MANUAL -->
<div class="card mb-4">
    <div class="card-header"><span class="card-title">✏️ Adicionar Questão Manual</span></div>
    <div class="card-body">
        <form method="post" action="../../controller/controlador.php" id="formQuestao">
            <input type="hidden" name="operacao" value="adicionarQuestao">
            <input type="hidden" name="idQuestionario" value="<?= $idQuiz ?>">
            <div class="form-group">
                <label class="form-label">Enunciado *</label>
                <textarea name="enunciado" class="form-control" rows="3" required placeholder="Digite o enunciado da questão..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-control" onchange="toggleTipo(this.value)">
                        <option value="multipla_escolha">Múltipla escolha</option>
                        <option value="verdadeiro_falso">Verdadeiro/Falso</option>
                        <option value="dissertativa">Dissertativa</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Pontos</label>
                    <input type="number" name="pontos" class="form-control" value="1" min="0.1" step="0.1">
                </div>
            </div>

            <div id="blocAlt">
                <label class="form-label">Alternativas (marque a correta)</label>
                <?php for ($i = 0; $i < 4; $i++): ?>
                <div class="flex gap-2 items-center mb-2">
                    <input type="checkbox" name="correta[]" value="<?= $i ?>" style="width:18px;height:18px;accent-color:var(--success);">
                    <input type="text" name="alternativa[]" class="form-control" placeholder="Alternativa <?= chr(65+$i) ?>">
                </div>
                <?php endfor; ?>
                <span class="form-hint">Marque a(s) alternativa(s) correta(s).</span>
            </div>

            <button type="submit" class="btn btn-primary mt-3">➕ Adicionar questão</button>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- QUESTÕES EXISTENTES -->
<div class="card">
    <div class="card-header">
        <span class="card-title">❓ Questões (<?= count($questoes) ?>)</span>
        <?php if (!$quiz['publicado'] && count($questoes) > 0): ?>
            <a href="../../controller/controlador.php?operacao=publicarQuestionario&id=<?= $idQuiz ?>&idTurma=<?= $idTurma ?>"
               class="btn btn-success btn-sm"
               onclick="return confirm('Publicar este questionário para os alunos?')">✅ Publicar agora</a>
        <?php endif; ?>
    </div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($questoes)): ?>
            <p class="text-muted text-center" style="padding:2rem;">Nenhuma questão adicionada ainda.</p>
        <?php else: ?>
        <?php foreach ($questoes as $i => $q): ?>
        <div style="padding:1rem 1.25rem;border-bottom:1px solid var(--border);">
            <div class="flex justify-between items-center mb-1">
                <strong><?= $i+1 ?>. <?= htmlspecialchars($q['enunciado']) ?></strong>
                <div class="flex gap-2 items-center">
                    <?php if ($q['geradaPorIA']): ?><span class="badge badge-info">🤖 IA</span><?php endif; ?>
                    <span class="badge badge-muted"><?= $q['pontos'] ?> pt(s)</span>
                    <span class="badge badge-primary"><?= ucfirst(str_replace('_',' ',$q['tipo'])) ?></span>
                </div>
            </div>
            <?php if (!empty($q['alternativas'])): ?>
            <div style="display:flex;flex-direction:column;gap:4px;margin-top:.5rem;">
                <?php foreach ($q['alternativas'] as $alt): ?>
                <div style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;">
                    <span style="color:<?= $alt['correta'] ? 'var(--success)' : 'var(--text-muted)' ?>;">
                        <?= $alt['correta'] ? '✅' : '○' ?>
                    </span>
                    <span><?= htmlspecialchars($alt['texto']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleTipo(tipo) {
    document.getElementById('blocAlt').style.display = tipo === 'dissertativa' ? 'none' : 'block';
    if (tipo === 'verdadeiro_falso') {
        const alts = document.querySelectorAll('input[name="alternativa[]"]');
        alts[0].value = 'Verdadeiro';
        alts[1].value = 'Falso';
        alts[2].value = '';
        alts[3].value = '';
    }
}
</script>
<?php endif; ?>

        </main></div></div></body></html>
