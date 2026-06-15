<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/questionarios.php';

$idAluno = $_SESSION['idUsuario'];
$idQuiz  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idQuiz) { header('Location: questionarios.php'); exit; }

$quiz    = buscarQuestionarioPorId($idQuiz);
if (!$quiz || !$quiz['publicado']) { header('Location: questionarios.php?msg=Questionário indisponível.'); exit; }

// Iniciar tentativa se ainda não iniciada
$idTentativa = filter_input(INPUT_GET, 'tentativa', FILTER_VALIDATE_INT);
if (!$idTentativa) {
    $resultado = iniciarTentativa($idQuiz, $idAluno);
    if (!is_numeric($resultado)) {
        header('Location: questionarios.php?msg=' . urlencode($resultado)); exit;
    }
    $idTentativa = $resultado;
    header("Location: responderQuiz.php?id=$idQuiz&tentativa=$idTentativa");
    exit;
}

$questoes = listarQuestoesQuestionario($idQuiz, (bool)$quiz['embaralharQuestoes']);

// Se POST → salvar e finalizar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($questoes as $q) {
        $alt   = filter_input(INPUT_POST, 'alt_' . $q['idQuestao'], FILTER_VALIDATE_INT);
        $texto = filter_input(INPUT_POST, 'txt_' . $q['idQuestao'], FILTER_SANITIZE_SPECIAL_CHARS);
        salvarResposta($idQuiz, $idAluno, $q['idQuestao'], $alt, $texto ?? '');
    }
    $resultado = finalizarTentativa($idTentativa, $idQuiz, $idAluno);
    $nota10 = $resultado['notaMaxima'] > 0
        ? round($resultado['notaObtida'] / $resultado['notaMaxima'] * 10, 1)
        : 0;
    header("Location: questionarios.php?msg=Questionário concluído! Sua nota: $nota10/10");
    exit;
}

$pageTitle  = $quiz['titulo'];
$currentNav = 'questionarios';
$depth      = 2;
include '../_layout.php';
?>

<div class="card mb-4">
    <div class="card-header">
        <div>
            <span class="card-title"><?= htmlspecialchars($quiz['titulo']) ?></span>
        </div>
        <div class="flex gap-2 items-center">
            <?php if ($quiz['tempoLimite'] > 0): ?>
                <span class="badge badge-warning" id="cronometro">⏱️ <?= $quiz['tempoLimite'] ?>:00</span>
            <?php endif; ?>
            <span class="text-muted"><?= count($questoes) ?> questões</span>
        </div>
    </div>
    <?php if ($quiz['descricao']): ?>
    <div class="card-body" style="border-bottom:1px solid var(--border);">
        <p class="text-muted"><?= htmlspecialchars($quiz['descricao']) ?></p>
    </div>
    <?php endif; ?>
</div>

<form method="post" id="formQuiz" onsubmit="return confirmarEnvio()">
    <input type="hidden" name="operacao" value="finalizarQuiz">

    <?php foreach ($questoes as $idx => $q): ?>
    <div class="card mb-3" id="questao-<?= $q['idQuestao'] ?>">
        <div class="card-header">
            <span class="card-title" style="font-size:.9375rem;">
                <span class="badge badge-primary" style="margin-right:.5rem;"><?= $idx + 1 ?></span>
                <?= htmlspecialchars($q['enunciado']) ?>
                <?php if ($q['geradaPorIA']): ?><span class="badge badge-info" style="margin-left:.5rem;">🤖</span><?php endif; ?>
            </span>
            <span class="text-muted"><?= $q['pontos'] ?> pt(s)</span>
        </div>
        <div class="card-body">
            <?php if ($q['tipo'] === 'multipla_escolha' || $q['tipo'] === 'verdadeiro_falso'): ?>
                <?php foreach ($q['alternativas'] as $alt): ?>
                <label style="display:flex;align-items:center;gap:.75rem;padding:.625rem .875rem;border:1px solid var(--border);border-radius:var(--radius-sm);margin-bottom:.5rem;cursor:pointer;transition:background .15s;"
                       onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <input type="radio" name="alt_<?= $q['idQuestao'] ?>" value="<?= $alt['idAlternativa'] ?>" style="accent-color:var(--primary);">
                    <span><?= htmlspecialchars($alt['texto']) ?></span>
                </label>
                <?php endforeach; ?>
            <?php else: ?>
                <textarea name="txt_<?= $q['idQuestao'] ?>" class="form-control" rows="4"
                          placeholder="Digite sua resposta..."></textarea>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>

    <div class="card">
        <div class="card-body flex gap-3 justify-between items-center">
            <a href="questionarios.php" class="btn btn-outline" onclick="return confirm('Sair? Suas respostas não serão salvas.')">Cancelar</a>
            <button type="submit" class="btn btn-primary btn-lg">✅ Finalizar e Enviar</button>
        </div>
    </div>
</form>

<?php if ($quiz['tempoLimite'] > 0): ?>
<script>
let segundos = <?= $quiz['tempoLimite'] * 60 ?>;
const el = document.getElementById('cronometro');
const timer = setInterval(() => {
    segundos--;
    const m = Math.floor(segundos / 60);
    const s = segundos % 60;
    el.textContent = '⏱️ ' + m + ':' + String(s).padStart(2,'0');
    if (segundos <= 60) el.style.background = 'var(--danger)';
    if (segundos <= 0) {
        clearInterval(timer);
        alert('Tempo esgotado! O questionário será enviado automaticamente.');
        document.getElementById('formQuiz').submit();
    }
}, 1000);
</script>
<?php endif; ?>

<script>
function confirmarEnvio() {
    const total = <?= count($questoes) ?>;
    const respondidas = document.querySelectorAll('input[type=radio]:checked').length
                      + document.querySelectorAll('textarea').length;
    return confirm(`Deseja enviar o questionário?\n${respondidas} de ${total} questões respondidas.`);
}
</script>

        </main></div></div></body></html>
