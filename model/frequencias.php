<?php
// ============================================================
// ARQUIVO: model/frequencias.php
// Camada de Modelo: Aulas, Frequências e Notas
// ============================================================

require_once __DIR__ . '/../persistencia/persistencia.php';
require_once __DIR__ . '/turmas.php';

// ============================================================
// AULAS
// ============================================================

function registrarAula($idTurma, $dataAula, $horaInicio, $horaFim, $conteudo) {
    executarSQL(
        "INSERT INTO Aulas (idTurma, dataAula, horaInicio, horaFim, conteudo) VALUES (?,?,?,?,?)",
        "issss", [$idTurma, $dataAula, $horaInicio, $horaFim, $conteudo]
    );
    return obterUltimoId();
}

function listarAulasTurma($idTurma) {
    $res = consultarSQL(
        "SELECT a.*, 
                (SELECT COUNT(*) FROM Frequencias f WHERE f.idAula = a.idAula AND f.presente = 1) as presentes,
                (SELECT COUNT(*) FROM Frequencias f WHERE f.idAula = a.idAula AND f.presente = 0) as ausentes
         FROM Aulas a WHERE a.idTurma = ? ORDER BY a.dataAula DESC",
        "i", [$idTurma]
    );
    return obterTodos($res);
}

// ============================================================
// FREQUÊNCIAS
// ============================================================

function registrarFrequencias($idAula, $idTurma, $presencas) {
    // $presencas = array de ['idAluno' => x, 'presente' => 0/1]
    foreach ($presencas as $p) {
        $res = consultarSQL(
            "SELECT idFrequencia FROM Frequencias WHERE idAula = ? AND idAluno = ?",
            "ii", [$idAula, $p['idAluno']]
        );
        if (obterNumLinhas($res) > 0) {
            executarSQL(
                "UPDATE Frequencias SET presente = ? WHERE idAula = ? AND idAluno = ?",
                "iii", [$p['presente'], $idAula, $p['idAluno']]
            );
        } else {
            executarSQL(
                "INSERT INTO Frequencias (idAula, idAluno, idTurma, presente) VALUES (?,?,?,?)",
                "iiii", [$idAula, $p['idAluno'], $idTurma, $p['presente']]
            );
        }
    }
    return SUCESSO;
}

function obterFrequenciaAluno($idAluno, $idTurma) {
    // Total de aulas registradas
    $resTotal = consultarSQL(
        "SELECT COUNT(*) as total FROM Aulas WHERE idTurma = ?",
        "i", [$idTurma]
    );
    $rowTotal = obterLinha($resTotal);
    $totalAulas = $rowTotal['total'];

    // Faltas do aluno
    $resFaltas = consultarSQL(
        "SELECT COUNT(*) as faltas FROM Frequencias f
         JOIN Aulas a ON f.idAula = a.idAula
         WHERE f.idAluno = ? AND a.idTurma = ? AND f.presente = 0",
        "ii", [$idAluno, $idTurma]
    );
    $rowFaltas = obterLinha($resFaltas);
    $totalFaltas = $rowFaltas['faltas'];

    // Limite de faltas da turma
    $resTurma = consultarSQL("SELECT limiteHorasFalta, cargaHorariaCalc FROM Turmas WHERE idTurma = ?", "i", [$idTurma]);
    $turma = obterLinha($resTurma);
    $limiteFaltas = $turma ? $turma['limiteHorasFalta'] : 0;

    $porcentagemFaltas = $totalAulas > 0 ? round(($totalFaltas / $totalAulas) * 100, 1) : 0;

    return [
        'totalAulas'       => $totalAulas,
        'totalFaltas'      => $totalFaltas,
        'totalPresencas'   => $totalAulas - $totalFaltas,
        'limiteFaltas'     => $limiteFaltas,
        'porcentagemFaltas'=> $porcentagemFaltas,
        'emRisco'          => $totalFaltas >= ($limiteFaltas * 0.75),
        'reprovadoFalta'   => $totalFaltas > $limiteFaltas
    ];
}

function historicoFrequenciaAluno($idAluno, $idTurma) {
    $res = consultarSQL(
        "SELECT a.dataAula, a.conteudo, a.horaInicio, a.horaFim,
                COALESCE(f.presente, -1) as presente,
                f.justificativa
         FROM Aulas a
         LEFT JOIN Frequencias f ON f.idAula = a.idAula AND f.idAluno = ?
         WHERE a.idTurma = ?
         ORDER BY a.dataAula DESC",
        "ii", [$idAluno, $idTurma]
    );
    return obterTodos($res);
}

// ============================================================
// NOTAS
// ============================================================

function lancarNota($idMatricula, $idTurma, $idAluno, $tipo, $descricao, $nota, $notaMaxima, $peso) {
    executarSQL(
        "INSERT INTO Notas (idMatricula, idTurma, idAluno, tipo, descricao, nota, notaMaxima, peso)
         VALUES (?,?,?,?,?,?,?,?)",
        "iiissddd",
        [$idMatricula, $idTurma, $idAluno, $tipo, $descricao, $nota, $notaMaxima, $peso]
    );
    return SUCESSO;
}

function listarNotasAluno($idAluno, $idTurma) {
    $res = consultarSQL(
        "SELECT * FROM Notas WHERE idAluno = ? AND idTurma = ? ORDER BY dataLancamento DESC",
        "ii", [$idAluno, $idTurma]
    );
    return obterTodos($res);
}

function calcularMediaAluno($idAluno, $idTurma) {
    $res = consultarSQL(
        "SELECT SUM(nota * peso) / SUM(peso) as media,
                SUM(peso) as totalPeso,
                COUNT(*) as totalNotas
         FROM Notas WHERE idAluno = ? AND idTurma = ?",
        "ii", [$idAluno, $idTurma]
    );
    $row = obterLinha($res);
    return $row ? round($row['media'] ?? 0, 2) : 0;
}

function estatisticasNotas($idTurma) {
    $res = consultarSQL(
        "SELECT u.nome, u.matricula,
                AVG(n.nota) as media,
                MAX(n.nota) as maior,
                MIN(n.nota) as menor,
                COUNT(n.idNota) as totalAvaliacoes
         FROM Notas n
         JOIN Usuarios u ON n.idAluno = u.idUsuario
         WHERE n.idTurma = ?
         GROUP BY n.idAluno
         ORDER BY media DESC",
        "i", [$idTurma]
    );
    return obterTodos($res);
}
