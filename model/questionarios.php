<?php
// ============================================================
// ARQUIVO: model/questionarios.php
// Camada de Modelo: Questionários, Questões e Respostas
// ============================================================

require_once __DIR__ . '/../persistencia/persistencia.php';
require_once __DIR__ . '/usuarios.php';
require_once __DIR__ . '/frequencias.php';

// ============================================================
// QUESTIONÁRIOS
// ============================================================

function criarQuestionario($idTurma, $idProfessor, $titulo, $descricao,
                            $dataInicio, $dataFim, $tempoLimite,
                            $tentativas, $embaralharQ, $embaralharA) {
    if (trim($titulo) == '') return "Informe o título do questionário.";
    executarSQL(
        "INSERT INTO Questionarios (idTurma, idProfessor, titulo, descricao, dataInicio, dataFim,
         tempoLimite, tentativasPermitidas, embaralharQuestoes, embaralharAlternativas)
         VALUES (?,?,?,?,?,?,?,?,?,?)",
        "iissssiii",
        [$idTurma, $idProfessor, $titulo, $descricao, $dataInicio, $dataFim,
         $tempoLimite, $tentativas, $embaralharQ, $embaralharA]
    );
    return obterUltimoId();
}

function publicarQuestionario($idQuestionario) {
    executarSQL("UPDATE Questionarios SET publicado = 1 WHERE idQuestionario = ?", "i", [$idQuestionario]);
    return SUCESSO;
}

function listarQuestionariosTurma($idTurma, $apenasPublicados = false) {
    $where = $apenasPublicados ? "AND q.publicado = 1" : "";
    $res = consultarSQL(
        "SELECT q.*,
                (SELECT COUNT(*) FROM Questoes WHERE idQuestionario = q.idQuestionario) as totalQuestoes,
                (SELECT COUNT(DISTINCT idAluno) FROM TentativasQuestionario WHERE idQuestionario = q.idQuestionario AND concluida = 1) as totalResponderam
         FROM Questionarios q
         WHERE q.idTurma = ? $where
         ORDER BY q.dataCriacao DESC",
        "i", [$idTurma]
    );
    return obterTodos($res);
}

function buscarQuestionarioPorId($id) {
    $res = consultarSQL("SELECT * FROM Questionarios WHERE idQuestionario = ?", "i", [$id]);
    return obterLinha($res);
}

// ============================================================
// QUESTÕES
// ============================================================

function adicionarQuestao($idQuestionario, $enunciado, $tipo, $pontos, $alternativas = []) {
    executarSQL(
        "INSERT INTO Questoes (idQuestionario, enunciado, tipo, pontos) VALUES (?,?,?,?)",
        "issd", [$idQuestionario, $enunciado, $tipo, $pontos]
    );
    $idQuestao = obterUltimoId();

    foreach ($alternativas as $idx => $alt) {
        executarSQL(
            "INSERT INTO Alternativas (idQuestao, texto, correta, ordemExibicao) VALUES (?,?,?,?)",
            "isii", [$idQuestao, $alt['texto'], $alt['correta'] ? 1 : 0, $idx]
        );
    }
    return $idQuestao;
}

function listarQuestoesQuestionario($idQuestionario, $embaralhar = false) {
    $order = $embaralhar ? "RAND()" : "q.ordemExibicao, q.idQuestao";
    $res = consultarSQL(
        "SELECT * FROM Questoes WHERE idQuestionario = ? ORDER BY $order",
        "i", [$idQuestionario]
    );
    $questoes = obterTodos($res);

    foreach ($questoes as &$q) {
        $orderAlt = $embaralhar ? "RAND()" : "ordemExibicao";
        $resAlt = consultarSQL(
            "SELECT * FROM Alternativas WHERE idQuestao = ? ORDER BY $orderAlt",
            "i", [$q['idQuestao']]
        );
        $q['alternativas'] = obterTodos($resAlt);
    }
    return $questoes;
}

// ============================================================
// RESPOSTAS / TENTATIVAS
// ============================================================

function iniciarTentativa($idQuestionario, $idAluno) {
    // Verificar número de tentativas
    $res = consultarSQL(
        "SELECT COUNT(*) as total FROM TentativasQuestionario WHERE idQuestionario = ? AND idAluno = ?",
        "ii", [$idQuestionario, $idAluno]
    );
    $row = obterLinha($res);

    $quiz = buscarQuestionarioPorId($idQuestionario);
    if ($quiz['tentativasPermitidas'] > 0 && $row['total'] >= $quiz['tentativasPermitidas']) {
        return "Número máximo de tentativas atingido.";
    }

    executarSQL(
        "INSERT INTO TentativasQuestionario (idQuestionario, idAluno, numerTentativa) VALUES (?,?,?)",
        "iii", [$idQuestionario, $idAluno, $row['total'] + 1]
    );
    return obterUltimoId();
}

function salvarResposta($idQuestionario, $idAluno, $idQuestao, $idAlternativa, $respostaTexto = '') {
    // Verificar se a alternativa está correta
    $correta = 0;
    $pontosObtidos = 0;

    if ($idAlternativa) {
        $resAlt = consultarSQL("SELECT correta FROM Alternativas WHERE idAlternativa = ?", "i", [$idAlternativa]);
        $alt = obterLinha($resAlt);
        $correta = $alt ? $alt['correta'] : 0;
    }

    if ($correta) {
        $resQ = consultarSQL("SELECT pontos FROM Questoes WHERE idQuestao = ?", "i", [$idQuestao]);
        $q = obterLinha($resQ);
        $pontosObtidos = $q ? $q['pontos'] : 0;
    }

    // Verificar se já respondeu essa questão nessa tentativa
    $resExist = consultarSQL(
        "SELECT idResposta FROM RespostasAluno WHERE idQuestionario = ? AND idAluno = ? AND idQuestao = ?",
        "iii", [$idQuestionario, $idAluno, $idQuestao]
    );

    if (obterNumLinhas($resExist) > 0) {
        executarSQL(
            "UPDATE RespostasAluno SET idAlternativa = ?, respostaTexto = ?, correta = ?, pontosObtidos = ?
             WHERE idQuestionario = ? AND idAluno = ? AND idQuestao = ?",
            "isiidiii",
            [$idAlternativa, $respostaTexto, $correta, $pontosObtidos, $idQuestionario, $idAluno, $idQuestao]
        );
    } else {
        executarSQL(
            "INSERT INTO RespostasAluno (idQuestionario, idAluno, idQuestao, idAlternativa, respostaTexto, correta, pontosObtidos)
             VALUES (?,?,?,?,?,?,?)",
            "iiiisid",
            [$idQuestionario, $idAluno, $idQuestao, $idAlternativa, $respostaTexto, $correta, $pontosObtidos]
        );
    }
    return SUCESSO;
}

function finalizarTentativa($idTentativa, $idQuestionario, $idAluno) {
    // Calcular nota total
    $res = consultarSQL(
        "SELECT SUM(pontosObtidos) as total FROM RespostasAluno
         WHERE idQuestionario = ? AND idAluno = ?",
        "ii", [$idQuestionario, $idAluno]
    );
    $row = obterLinha($res);
    $notaObtida = $row['total'] ?? 0;

    $resMax = consultarSQL(
        "SELECT SUM(pontos) as total FROM Questoes WHERE idQuestionario = ?",
        "i", [$idQuestionario]
    );
    $rowMax = obterLinha($resMax);
    $notaMaxima = $rowMax['total'] ?? 0;

    executarSQL(
        "UPDATE TentativasQuestionario SET notaObtida = ?, notaMaxima = ?, finalizouEm = NOW(), concluida = 1
         WHERE idTentativa = ?",
        "ddi", [$notaObtida, $notaMaxima, $idTentativa]
    );

    return ['notaObtida' => $notaObtida, 'notaMaxima' => $notaMaxima];
}

// ============================================================
// ESTATÍSTICAS PARA O PROFESSOR
// ============================================================

function estatisticasQuestionario($idQuestionario) {
    // Por questão: porcentagem de acerto
    $res = consultarSQL(
        "SELECT q.idQuestao, q.enunciado, q.pontos,
                COUNT(r.idResposta) as totalRespostas,
                SUM(r.correta) as totalAcertos,
                ROUND(SUM(r.correta) / COUNT(r.idResposta) * 100, 1) as porcentagemAcerto
         FROM Questoes q
         LEFT JOIN RespostasAluno r ON r.idQuestao = q.idQuestao
         WHERE q.idQuestionario = ?
         GROUP BY q.idQuestao
         ORDER BY porcentagemAcerto ASC",
        "i", [$idQuestionario]
    );
    $questoes = obterTodos($res);

    // Notas dos alunos
    $resNotas = consultarSQL(
        "SELECT u.nome, u.matricula, t.notaObtida, t.notaMaxima,
                ROUND(t.notaObtida / t.notaMaxima * 10, 2) as notaDez,
                t.finalizouEm
         FROM TentativasQuestionario t
         JOIN Usuarios u ON t.idAluno = u.idUsuario
         WHERE t.idQuestionario = ? AND t.concluida = 1
         ORDER BY notaDez DESC",
        "i", [$idQuestionario]
    );
    $notas = obterTodos($resNotas);

    $mediaGeral = 0;
    if (!empty($notas)) {
        $mediaGeral = array_sum(array_column($notas, 'notaDez')) / count($notas);
    }

    return [
        'questoes'    => $questoes,
        'notas'       => $notas,
        'mediaGeral'  => round($mediaGeral, 2),
        'totalAlunos' => count($notas)
    ];
}

// ============================================================
// INTEGRAÇÃO COM IA (Google Gemini)
// ============================================================

function gerarQuestionarioComIA($tema, $quantidade, $nivelDificuldade, $idQuestionario) {
    $apiKey = 'AIzaSyDeIJ2OmsLfrw0N_R4a4PHfwi7M3Cml-1E';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$apiKey";

    $prompt = "Você é um professor especialista. Crie $quantidade questões de múltipla escolha sobre o tema: '$tema'.
    Nível de dificuldade: $nivelDificuldade.
    Cada questão deve ter 4 alternativas (A, B, C, D), com exatamente 1 correta.
    Retorne SOMENTE um JSON válido no seguinte formato:
    {
      \"questoes\": [
        {
          \"enunciado\": \"Texto da questão\",
          \"pontos\": 1.0,
          \"alternativas\": [
            {\"texto\": \"Alternativa A\", \"correta\": true},
            {\"texto\": \"Alternativa B\", \"correta\": false},
            {\"texto\": \"Alternativa C\", \"correta\": false},
            {\"texto\": \"Alternativa D\", \"correta\": false}
          ]
        }
      ]
    }";

    $payload = json_encode([
        'contents' => [['parts' => [['text' => $prompt]]]]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $resposta = curl_exec($ch);
    curl_close($ch);

    if (!$resposta) return "Erro ao conectar com a IA.";

    $dados = json_decode($resposta, true);
    $textoIA = $dados['candidates'][0]['content']['parts'][0]['text'] ?? '';

    // Extrair JSON da resposta
    preg_match('/\{.*\}/s', $textoIA, $matches);
    if (empty($matches)) return "Erro ao processar resposta da IA.";

    $questoesIA = json_decode($matches[0], true);
    if (!$questoesIA || !isset($questoesIA['questoes'])) return "Formato inválido retornado pela IA.";

    // Salvar questões no banco
    foreach ($questoesIA['questoes'] as $q) {
        $idQuestao = adicionarQuestao(
            $idQuestionario,
            $q['enunciado'],
            'multipla_escolha',
            $q['pontos'] ?? 1.0,
            $q['alternativas']
        );
        // Marcar como gerada por IA
        executarSQL("UPDATE Questoes SET geradaPorIA = 1 WHERE idQuestao = ?", "i", [$idQuestao]);
    }

    // Salvar prompt no questionário
    executarSQL(
        "UPDATE Questionarios SET geradoPorIA = 1, promptIA = ? WHERE idQuestionario = ?",
        "si", ["Tema: $tema | Qtd: $quantidade | Nível: $nivelDificuldade", $idQuestionario]
    );

    return count($questoesIA['questoes']);
}

// ------------------------------------------------------------
// IA para recomendar reforço ao professor
// ------------------------------------------------------------
function analisarERecomendarAoProfessor($idQuestionario) {
    $stats = estatisticasQuestionario($idQuestionario);
    $quiz  = buscarQuestionarioPorId($idQuestionario);

    if (empty($stats['questoes'])) return "Ainda não há respostas suficientes para análise.";

    $topicosProblema = array_filter($stats['questoes'], fn($q) => $q['porcentagemAcerto'] < 50);
    if (empty($topicosProblema)) return "Os alunos estão indo bem! Nenhum tópico com menos de 50% de acerto.";

    $enunciados = array_map(fn($q) => "- {$q['enunciado']} ({$q['porcentagemAcerto']}% de acerto)", $topicosProblema);

    $apiKey = 'AIzaSyDeIJ2OmsLfrw0N_R4a4PHfwi7M3Cml-1E';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$apiKey";

    $prompt = "Você é um assistente pedagógico. Analise os seguintes tópicos onde os alunos tiveram dificuldade no questionário '{$quiz['titulo']}':
    " . implode("\n", $enunciados) . "
    Média da turma: {$stats['mediaGeral']}/10 ({$stats['totalAlunos']} alunos responderam).
    
    Em até 200 palavras, sugira ao professor: quais conteúdos reforçar, como abordar novamente esses tópicos e se deve criar um novo questionário focado nessas dificuldades.";

    $payload = json_encode(['contents' => [['parts' => [['text' => $prompt]]]]]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $resposta = curl_exec($ch);
    curl_close($ch);

    $dados = json_decode($resposta, true);
    return $dados['candidates'][0]['content']['parts'][0]['text'] ?? "Não foi possível gerar análise.";
}

// ------------------------------------------------------------
// IA para recomendar rotina de estudos ao aluno
// ------------------------------------------------------------
function analisarDesempenhoAluno($idAluno, $idTurma) {
    // Coletar dados do aluno
    $freq   = obterFrequenciaAluno($idAluno, $idTurma);
    $notas  = listarNotasAluno($idAluno, $idTurma);
    $media  = calcularMediaAluno($idAluno, $idTurma);
    $aluno  = buscarUsuarioPorId($idAluno) ?? [];
    $turma  = buscarTurmaPorId($idTurma) ?? [];

    $apiKey = 'AIzaSyDeIJ2OmsLfrw0N_R4a4PHfwi7M3Cml-1E';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=$apiKey";

    $notasTexto = implode(', ', array_map(fn($n) => "{$n['descricao']}: {$n['nota']}/{$n['notaMaxima']}", $notas));

    $prompt = "Você é um tutor virtual. Analise o desempenho do aluno '{$aluno['nome']}' na disciplina '{$turma['nomeDisciplina']}':
    - Média atual: $media/10
    - Frequência: {$freq['totalPresencas']} presenças, {$freq['totalFaltas']} faltas ({$freq['porcentagemFaltas']}% de faltas)
    - Limite de faltas: {$freq['limiteFaltas']}
    - Avaliações: $notasTexto
    
    Em até 200 palavras: analise o desempenho, aponte pontos de atenção e sugira uma rotina de estudos específica para melhorar.";

    $payload = json_encode(['contents' => [['parts' => [['text' => $prompt]]]]]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $resposta = curl_exec($ch);
    curl_close($ch);

    $dados = json_decode($resposta, true);
    $analise = $dados['candidates'][0]['content']['parts'][0]['text'] ?? "Não foi possível gerar análise.";

    // Salvar no log de IA
    executarSQL(
        "INSERT INTO LogsIA (idAluno, idTurma, tipo, analise, recomendacao) VALUES (?,?,?,?,?)",
        "iisss", [$idAluno, $idTurma, 'desempenho_geral', $analise, '']
    );

    return $analise;
}
