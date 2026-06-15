<?php
// ============================================================
// ARQUIVO: controller/iaAluno.php
// Endpoint AJAX para chamadas de IA do aluno
// ============================================================

require_once __DIR__ . '/validar.php';
validarTipo('aluno');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/questionarios.php';
require_once __DIR__ . '/../model/frequencias.php';
require_once __DIR__ . '/../model/turmas.php';

header('Content-Type: application/json; charset=utf-8');

$operacao = filter_input(INPUT_GET, 'operacao', FILTER_SANITIZE_SPECIAL_CHARS);
$idAluno  = $_SESSION['idUsuario'];

if ($operacao === 'analisarDesempenho') {
    $idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
    if (!$idTurma) { echo json_encode(['analise' => 'Turma inválida.']); exit; }

    $analise = analisarDesempenhoAluno($idAluno, $idTurma);
    echo json_encode(['analise' => $analise], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($operacao === 'gerarRotina') {
    $turmas = listarTurmasAluno($idAluno);
    if (empty($turmas)) { echo json_encode(['rotina' => 'Nenhuma disciplina encontrada.']); exit; }

    $resumo = [];
    foreach ($turmas as $t) {
        $media = calcularMediaAluno($idAluno, $t['idTurma']);
        $freq  = obterFrequenciaAluno($idAluno, $t['idTurma']);
        $resumo[] = "- {$t['nomeDisciplina']}: média {$media}/10, {$freq['totalFaltas']} faltas (limite {$freq['limiteFaltas']}h)";
    }

    $apiKey = GEMINI_API_KEY;
    $url    = GEMINI_API_URL . "?key=$apiKey";

    $aluno = $_SESSION['nome'];
    $prompt = "Você é um tutor educacional. Crie uma rotina de estudos semanal personalizada para o aluno '$aluno'.
    Situação atual por disciplina:
    " . implode("\n", $resumo) . "
    
    Crie uma rotina detalhada de segunda a sábado, indicando quantas horas dedicar a cada disciplina por dia, priorizando as de menor nota. Inclua também dicas de técnicas de estudo. Responda em português, máximo 300 palavras.";

    $payload = json_encode(['contents' => [['parts' => [['text' => $prompt]]]]]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $resp = curl_exec($ch);
    curl_close($ch);

    $dados  = json_decode($resp, true);
    $rotina = $dados['candidates'][0]['content']['parts'][0]['text'] ?? 'Não foi possível gerar a rotina.';

    echo json_encode(['rotina' => $rotina], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(['erro' => 'Operação inválida.']);
