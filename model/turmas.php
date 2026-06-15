<?php
// ============================================================
// ARQUIVO: model/turmas.php
// Camada de Modelo: Cursos, Disciplinas e Turmas
// ============================================================

require_once __DIR__ . '/../persistencia/persistencia.php';
require_once __DIR__ . '/usuarios.php';

// ============================================================
// CURSOS
// ============================================================

function listarCursos($apenasAtivos = true) {
    $where = $apenasAtivos ? "WHERE ativo = 1" : "";
    $res = consultarSQL("SELECT * FROM Cursos $where ORDER BY nome");
    return obterTodos($res);
}

function buscarCursoPorId($id) {
    $res = consultarSQL("SELECT * FROM Cursos WHERE idCurso = ?", "i", [$id]);
    return obterLinha($res);
}

function criarCurso($nome, $codigo, $descricao, $cargaHoraria, $duracao) {
    if (trim($nome) == '') return "Informe o nome do curso.";
    executarSQL(
        "INSERT INTO Cursos (nome, codigo, descricao, cargaHorariaTotal, duracao) VALUES (?,?,?,?,?)",
        "sssii", [$nome, $codigo, $descricao, $cargaHoraria, $duracao]
    );
    return SUCESSO;
}

// ============================================================
// DISCIPLINAS
// ============================================================

function listarDisciplinas($idCurso = null) {
    if ($idCurso) {
        $res = consultarSQL(
            "SELECT d.*, c.nome as nomeCurso FROM Disciplinas d
             JOIN Cursos c ON d.idCurso = c.idCurso
             WHERE d.idCurso = ? AND d.ativo = 1 ORDER BY d.periodo, d.nome",
            "i", [$idCurso]
        );
    } else {
        $res = consultarSQL(
            "SELECT d.*, c.nome as nomeCurso FROM Disciplinas d
             JOIN Cursos c ON d.idCurso = c.idCurso
             WHERE d.ativo = 1 ORDER BY c.nome, d.periodo, d.nome"
        );
    }
    return obterTodos($res);
}

function criarDisciplina($idCurso, $nome, $codigo, $cargaHoraria, $ementa, $periodo) {
    if (trim($nome) == '') return "Informe o nome da disciplina.";
    executarSQL(
        "INSERT INTO Disciplinas (idCurso, nome, codigo, cargaHoraria, ementa, periodo)
         VALUES (?,?,?,?,?,?)",
        "issisi", [$idCurso, $nome, $codigo, $cargaHoraria, $ementa, $periodo]
    );
    return SUCESSO;
}

// ============================================================
// TURMAS
// ============================================================

function listarTurmasProfessor($idProfessor, $ano = null, $semestre = null) {
    $params = [$idProfessor];
    $tipos = "i";
    $where = "WHERE t.idProfessor = ? AND t.ativo = 1";

    if ($ano) {
        $where .= " AND t.ano = ?";
        $params[] = $ano;
        $tipos .= "i";
    }
    if ($semestre) {
        $where .= " AND t.semestre = ?";
        $params[] = $semestre;
        $tipos .= "i";
    }

    $res = consultarSQL(
        "SELECT t.*, d.nome as nomeDisciplina, d.cargaHoraria, d.codigo as codigoDisciplina,
                c.nome as nomeCurso,
                (SELECT COUNT(*) FROM Matriculas m WHERE m.idTurma = t.idTurma AND m.status = 'ativa') as totalAlunos
         FROM Turmas t
         JOIN Disciplinas d ON t.idDisciplina = d.idDisciplina
         JOIN Cursos c ON d.idCurso = c.idCurso
         $where ORDER BY t.ano DESC, t.semestre DESC, d.nome",
        $tipos, $params
    );
    return obterTodos($res);
}

function listarTurmasAluno($idAluno) {
    $res = consultarSQL(
        "SELECT t.*, d.nome as nomeDisciplina, d.cargaHoraria, d.codigo as codigoDisciplina,
                c.nome as nomeCurso,
                u.nome as nomeProfessor,
                m.status as statusMatricula, m.situacao, m.mediaFinal, m.idMatricula,
                t.limiteHorasFalta, t.cargaHorariaCalc,
                (SELECT COUNT(*) FROM Frequencias f
                 JOIN Aulas a ON f.idAula = a.idAula
                 WHERE f.idAluno = ? AND f.idTurma = t.idTurma AND f.presente = 0) as totalFaltas,
                (SELECT COUNT(*) FROM Aulas a2 WHERE a2.idTurma = t.idTurma) as totalAulas
         FROM Matriculas m
         JOIN Turmas t ON m.idTurma = t.idTurma
         JOIN Disciplinas d ON t.idDisciplina = d.idDisciplina
         JOIN Cursos c ON d.idCurso = c.idCurso
         JOIN Usuarios u ON t.idProfessor = u.idUsuario
         WHERE m.idAluno = ? AND m.status = 'ativa'
         ORDER BY d.nome",
        "ii", [$idAluno, $idAluno]
    );
    return obterTodos($res);
}

function buscarTurmaPorId($idTurma) {
    $res = consultarSQL(
        "SELECT t.*, d.nome as nomeDisciplina, d.cargaHoraria, d.codigo as codigoDisciplina,
                c.nome as nomeCurso, u.nome as nomeProfessor
         FROM Turmas t
         JOIN Disciplinas d ON t.idDisciplina = d.idDisciplina
         JOIN Cursos c ON d.idCurso = c.idCurso
         JOIN Usuarios u ON t.idProfessor = u.idUsuario
         WHERE t.idTurma = ?",
        "i", [$idTurma]
    );
    return obterLinha($res);
}

function criarTurma($idDisciplina, $idProfessor, $codigo, $ano, $semestre, $horario, $sala, $limite) {
    // Buscar carga horária da disciplina para calcular limite de faltas
    $res = consultarSQL("SELECT cargaHoraria FROM Disciplinas WHERE idDisciplina = ?", "i", [$idDisciplina]);
    $disc = obterLinha($res);
    $cargaHoraria = $disc ? $disc['cargaHoraria'] : 0;
    $limiteFaltas = (int)round($cargaHoraria * 0.25);

    executarSQL(
        "INSERT INTO Turmas (idDisciplina, idProfessor, codigo, ano, semestre, horario, sala, limiteAlunos, cargaHorariaCalc, limiteHorasFalta)
         VALUES (?,?,?,?,?,?,?,?,?,?)",
        "iissisiiiii",
        [$idDisciplina, $idProfessor, $codigo, $ano, $semestre, $horario, $sala, $limite, $cargaHoraria, $limiteFaltas]
    );
    return SUCESSO;
}

// ============================================================
// MATRÍCULAS
// ============================================================

function matricularAluno($idAluno, $idTurma) {
    // Verificar se já está matriculado
    $res = consultarSQL(
        "SELECT idMatricula FROM Matriculas WHERE idAluno = ? AND idTurma = ?",
        "ii", [$idAluno, $idTurma]
    );
    if (obterNumLinhas($res) > 0) return "Aluno já está matriculado nesta turma.";

    executarSQL(
        "INSERT INTO Matriculas (idAluno, idTurma, dataMatricula) VALUES (?,?,CURDATE())",
        "ii", [$idAluno, $idTurma]
    );
    return SUCESSO;
}

function listarAlunosTurma($idTurma) {
    $res = consultarSQL(
        "SELECT u.idUsuario, u.matricula, u.nome, u.email,
                m.idMatricula, m.status, m.situacao, m.mediaFinal,
                (SELECT COUNT(*) FROM Frequencias f
                 JOIN Aulas a ON f.idAula = a.idAula
                 WHERE f.idAluno = u.idUsuario AND a.idTurma = ? AND f.presente = 0) as totalFaltas
         FROM Matriculas m
         JOIN Usuarios u ON m.idAluno = u.idUsuario
         WHERE m.idTurma = ? AND m.status = 'ativa'
         ORDER BY u.nome",
        "ii", [$idTurma, $idTurma]
    );
    return obterTodos($res);
}

// ------------------------------------------------------------
// Nova função: criar turma completa (nova estrutura)
// Turma = sala de aula com código, módulo, turno, curso
// As disciplinas são vinculadas pelo módulo do curso
// ------------------------------------------------------------
function criarTurmaCompleta($idCurso, $idProfResp, $codigo, $modulo, $turno, $ano, $semestre, $horario, $sala, $limite) {
    // Verificar código duplicado
    $res = consultarSQL("SELECT idTurma FROM Turmas WHERE codigo = ?", "s", [$codigo]);
    if (obterNumLinhas($res) > 0) return "Código de turma '$codigo' já existe.";

    // Buscar carga horária total das disciplinas do módulo
    $resDisc = consultarSQL(
        "SELECT SUM(cargaHoraria) as totalCH FROM Disciplinas WHERE idCurso = ? AND periodo = ? AND ativo = 1",
        "ii", [$idCurso, $modulo]
    );
    $rowCH = obterLinha($resDisc);
    $cargaTotal = $rowCH['totalCH'] ?? 0;
    $limiteFaltas = $cargaTotal > 0 ? (int)round($cargaTotal * 0.25) : 0;

    executarSQL(
        "INSERT INTO Turmas (idDisciplina, idProfessor, codigo, ano, semestre, horario, sala, limiteAlunos, cargaHorariaCalc, limiteHorasFalta, ativo)
         VALUES (0, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)",
        "issiissiii",
        [$idProfResp ?? 0, $codigo, $ano, $semestre, $horario ?? '', $sala ?? '', $limite, $cargaTotal, $limiteFaltas]
    );
    $idTurma = obterUltimoId();

    // Registrar metadados da turma (curso + módulo + turno)
    executarSQL(
        "INSERT INTO TurmaMetadata (idTurma, idCurso, modulo, turno) VALUES (?,?,?,?)
         ON DUPLICATE KEY UPDATE modulo=VALUES(modulo), turno=VALUES(turno)",
        "iiis", [$idTurma, $idCurso, $modulo, $turno]
    );

    return SUCESSO;
}

// ------------------------------------------------------------
// Remover matrícula
// ------------------------------------------------------------
function removerMatricula($idMatricula) {
    executarSQL("DELETE FROM Matriculas WHERE idMatricula = ?", "i", [$idMatricula]);
    return SUCESSO;
}

// ------------------------------------------------------------
// Promover aluno para próximo módulo (muda de turma)
// ------------------------------------------------------------
function promoverAluno($idAluno, $idTurmaAtual, $idTurmaNova) {
    // Encerrar matrícula atual como concluída
    executarSQL(
        "UPDATE Matriculas SET status='concluida', situacao='aprovado' WHERE idAluno=? AND idTurma=?",
        "ii", [$idAluno, $idTurmaAtual]
    );
    // Criar nova matrícula na turma nova
    return matricularAluno($idAluno, $idTurmaNova);
}

// ------------------------------------------------------------
// Buscar turmas com metadata (curso, módulo, turno)
// ------------------------------------------------------------
function listarTurmasCompletas($ano = null, $idCurso = null) {
    $where = "WHERE t.ativo = 1";
    $params = [];
    $tipos = "";
    if ($ano) {
        $where .= " AND t.ano = ?";
        $params[] = $ano;
        $tipos .= "i";
    }
    if ($idCurso) {
        $where .= " AND tm.idCurso = ?";
        $params[] = $idCurso;
        $tipos .= "i";
    }
    $res = consultarSQL(
        "SELECT t.*, tm.idCurso, tm.modulo, tm.turno,
                c.nome as nomeCurso, c.codigo as codigoCurso,
                u.nome as nomeProfessor,
                (SELECT COUNT(*) FROM Matriculas m WHERE m.idTurma = t.idTurma AND m.status='ativa') as totalAlunos
         FROM Turmas t
         LEFT JOIN TurmaMetadata tm ON tm.idTurma = t.idTurma
         LEFT JOIN Cursos c ON c.idCurso = tm.idCurso
         LEFT JOIN Usuarios u ON u.idUsuario = t.idProfessor
         $where
         ORDER BY t.ano DESC, c.nome, tm.modulo, t.codigo",
        $tipos, $params
    );
    return obterTodos($res);
}

// ------------------------------------------------------------
// Listar disciplinas de uma turma (pelo módulo/curso)
// ------------------------------------------------------------
function listarDisciplinasDaTurma($idTurma) {
    $res = consultarSQL(
        "SELECT d.* FROM Disciplinas d
         JOIN TurmaMetadata tm ON d.idCurso = tm.idCurso AND d.periodo = tm.modulo
         WHERE tm.idTurma = ? AND d.ativo = 1
         ORDER BY d.nome",
        "i", [$idTurma]
    );
    return obterTodos($res);
}

// ------------------------------------------------------------
// Editar curso
// ------------------------------------------------------------
function editarCurso($id, $nome, $descricao, $cargaHoraria, $duracao) {
    executarSQL(
        "UPDATE Cursos SET nome=?, descricao=?, cargaHorariaTotal=?, duracao=? WHERE idCurso=?",
        "ssiii", [$nome, $descricao, $cargaHoraria, $duracao, $id]
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// Editar disciplina
// ------------------------------------------------------------
function editarDisciplina($id, $nome, $cargaHoraria, $ementa, $periodo) {
    executarSQL(
        "UPDATE Disciplinas SET nome=?, cargaHoraria=?, ementa=?, periodo=? WHERE idDisciplina=?",
        "sisii", [$nome, $cargaHoraria, $ementa, $periodo, $id]
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// listarTurmasAluno — versão atualizada (nova estrutura de turmas)
// ------------------------------------------------------------
function listarTurmasAlunoV2($idAluno) {
    $res = consultarSQL(
        "SELECT t.*, tm.modulo, tm.turno,
                c.nome as nomeCurso, c.codigo as codigoCurso,
                u.nome as nomeProfessor,
                m.status as statusMatricula, m.situacao, m.mediaFinal, m.idMatricula,
                t.limiteHorasFalta,
                (SELECT COUNT(*) FROM Frequencias f
                 JOIN Aulas a ON f.idAula = a.idAula
                 WHERE f.idAluno = ? AND f.idTurma = t.idTurma AND f.presente = 0) as totalFaltas,
                (SELECT COUNT(*) FROM Aulas a2 WHERE a2.idTurma = t.idTurma) as totalAulas,
                d.nome as nomeDisciplina,
                d.cargaHoraria as cargaHoraria
         FROM Matriculas m
         JOIN Turmas t ON m.idTurma = t.idTurma
         LEFT JOIN Disciplinas d ON t.idDisciplina = d.idDisciplina
         LEFT JOIN TurmaMetadata tm ON tm.idTurma = t.idTurma
         LEFT JOIN Cursos c ON c.idCurso = tm.idCurso
         LEFT JOIN Usuarios u ON u.idUsuario = t.idProfessor
         WHERE m.idAluno = ? AND m.status = 'ativa'
         ORDER BY d.nome",
        "ii", [$idAluno, $idAluno]
    );
    return obterTodos($res);
}

// ------------------------------------------------------------
// listarTurmasProfessor — versão atualizada
// ------------------------------------------------------------
function listarTurmasProfessorV2($idProfessor, $ano = null) {
    $where  = "WHERE t.idProfessor = ? AND t.ativo = 1";
    $params = [$idProfessor];
    $tipos  = "i";
    if ($ano) {
        $where .= " AND t.ano = ?";
        $params[] = $ano;
        $tipos .= "i";
    }
    $res = consultarSQL(
        "SELECT t.*, tm.modulo, tm.turno,
                c.nome as nomeCurso, c.codigo as codigoCurso,
                (SELECT COUNT(*) FROM Matriculas m WHERE m.idTurma = t.idTurma AND m.status='ativa') as totalAlunos
         FROM Turmas t
         LEFT JOIN TurmaMetadata tm ON tm.idTurma = t.idTurma
         LEFT JOIN Cursos c ON c.idCurso = tm.idCurso
         $where
         ORDER BY t.ano DESC, t.codigo",
        $tipos, $params
    );
    return obterTodos($res);
}
