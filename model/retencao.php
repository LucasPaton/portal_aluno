<?php
// ============================================================
// ARQUIVO: model/retencao.php
// Gerenciamento de retenção de dados por 10 anos
// Conformidade com políticas de instituições de ensino
// ============================================================

require_once __DIR__ . '/../persistencia/persistencia.php';
require_once __DIR__ . '/usuarios.php';

// ------------------------------------------------------------
// Arquivar/desativar usuário com snapshot completo dos dados
// Seta dataExpiracao = dataEvento + 10 anos
// ------------------------------------------------------------
function arquivarUsuario($idUsuario, $tipoEvento, $dataEvento, $arquivadoPor, $observacoes = '') {
    conectarSGBD();

    // 1. Buscar dados completos do usuário
    $resUser = consultarSQL("SELECT * FROM Usuarios WHERE idUsuario = ?", "i", [$idUsuario]);
    $usuario = obterLinha($resUser);
    if (!$usuario) return "Usuário não encontrado.";

    // 2. Buscar histórico de matrículas e notas
    $resMat = consultarSQL(
        "SELECT m.*, t.codigo as codigoTurma, d.nome as nomeDisciplina,
                d.codigo as codigoDisciplina, d.cargaHoraria,
                c.nome as nomeCurso, t.ano, t.semestre,
                u.nome as nomeProfessor
         FROM Matriculas m
         JOIN Turmas t ON m.idTurma = t.idTurma
         JOIN Disciplinas d ON t.idDisciplina = d.idDisciplina
         JOIN Cursos c ON d.idCurso = c.idCurso
         JOIN Usuarios u ON t.idProfessor = u.idUsuario
         WHERE m.idAluno = ?",
        "i", [$idUsuario]
    );
    $matriculas = obterTodos($resMat);

    // 3. Buscar todas as notas
    $resNotas = consultarSQL(
        "SELECT n.*, d.nome as nomeDisciplina, t.ano, t.semestre
         FROM Notas n
         JOIN Turmas t ON n.idTurma = t.idTurma
         JOIN Disciplinas d ON t.idDisciplina = d.idDisciplina
         WHERE n.idAluno = ?",
        "i", [$idUsuario]
    );
    $notas = obterTodos($resNotas);

    // 4. Buscar frequências
    $resFreq = consultarSQL(
        "SELECT COUNT(*) as totalAulas,
                SUM(CASE WHEN f.presente = 1 THEN 1 ELSE 0 END) as presencas,
                SUM(CASE WHEN f.presente = 0 THEN 1 ELSE 0 END) as faltas,
                d.nome as nomeDisciplina, t.ano, t.semestre
         FROM Frequencias f
         JOIN Aulas a ON f.idAula = a.idAula
         JOIN Turmas t ON a.idTurma = t.idTurma
         JOIN Disciplinas d ON t.idDisciplina = d.idDisciplina
         WHERE f.idAluno = ?
         GROUP BY t.idTurma",
        "i", [$idUsuario]
    );
    $frequencias = obterTodos($resFreq);

    // 5. Buscar resultados de questionários
    $resQuiz = consultarSQL(
        "SELECT tq.notaObtida, tq.notaMaxima, tq.finalizouEm,
                q.titulo as nomeQuestionario, d.nome as nomeDisciplina
         FROM TentativasQuestionario tq
         JOIN Questionarios q ON tq.idQuestionario = q.idQuestionario
         JOIN Turmas t ON q.idTurma = t.idTurma
         JOIN Disciplinas d ON t.idDisciplina = d.idDisciplina
         WHERE tq.idAluno = ? AND tq.concluida = 1",
        "i", [$idUsuario]
    );
    $questionarios = obterTodos($resQuiz);

    // Remover senha do snapshot por segurança
    unset($usuario['senha']);

    $snapshot = json_encode($usuario, JSON_UNESCAPED_UNICODE);
    $historico = json_encode([
        'matriculas'   => $matriculas,
        'notas'        => $notas,
        'frequencias'  => $frequencias,
        'questionarios'=> $questionarios
    ], JSON_UNESCAPED_UNICODE);

    $dataExp = date('Y-m-d', strtotime($dataEvento . ' +10 years'));

    // 6. Salvar snapshot
    executarSQL(
        "INSERT INTO RegistrosArquivados
         (idUsuario, tipoEvento, dadosSnapshot, historicoDados, dataEvento, dataExpiracao, arquivadoPor, observacoes)
         VALUES (?,?,?,?,?,?,?,?)",
        "isssssss",
        [$idUsuario, $tipoEvento, $snapshot, $historico, $dataEvento, $dataExp, $arquivadoPor, $observacoes]
    );

    // 7. Registrar no histórico de status
    executarSQL(
        "INSERT INTO HistoricoStatus (idUsuario, statusAnterior, statusNovo, motivo, alteradoPor)
         VALUES (?,1,0,?,?)",
        "isi", [$idUsuario, "Arquivamento: $tipoEvento", $arquivadoPor]
    );

    // 8. Desativar e setar datas no usuário
    executarSQL(
        "UPDATE Usuarios SET ativo = 0, dataFormacao = ?, dataExpiracao = ? WHERE idUsuario = ?",
        "ssi", [$dataEvento, $dataExp, $idUsuario]
    );

    return SUCESSO;
}

// ------------------------------------------------------------
// Reativar usuário (ex: aluno que voltou)
// ------------------------------------------------------------
function reativarUsuario($idUsuario, $reativadoPor, $motivo = '') {
    executarSQL(
        "INSERT INTO HistoricoStatus (idUsuario, statusAnterior, statusNovo, motivo, alteradoPor)
         VALUES (?,0,1,?,?)",
        "isi", [$idUsuario, "Reativação: $motivo", $reativadoPor]
    );
    executarSQL(
        "UPDATE Usuarios SET ativo = 1, dataFormacao = NULL, dataExpiracao = NULL WHERE idUsuario = ?",
        "i", [$idUsuario]
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// Listar usuários inativos com status de retenção
// ------------------------------------------------------------
function listarInativos($busca = '', $tipoEvento = '', $pagina = 1, $porPagina = 20) {
    $offset = ($pagina - 1) * $porPagina;
    $where = "WHERE u.ativo = 0";
    $params = [];
    $tipos_bind = "";

    if ($busca) {
        $where .= " AND (u.nome LIKE ? OR u.email LIKE ? OR u.matricula LIKE ? OR u.cpf LIKE ?)";
        $buscaLike = "%$busca%";
        $params = array_merge($params, [$buscaLike, $buscaLike, $buscaLike, $buscaLike]);
        $tipos_bind .= "ssss";
    }
    if ($tipoEvento) {
        $where .= " AND ra.tipoEvento = ?";
        $params[] = $tipoEvento;
        $tipos_bind .= "s";
    }

    $sql = "SELECT u.idUsuario, u.matricula, u.nome, u.email, u.cpf, u.tipo,
                   u.dataFormacao, u.dataExpiracao,
                   DATEDIFF(u.dataExpiracao, CURDATE()) as diasRestantes,
                   ra.tipoEvento, ra.dataEvento, ra.dataArquivamento,
                   CASE
                       WHEN u.dataExpiracao < CURDATE() THEN 'expirado'
                       WHEN DATEDIFF(u.dataExpiracao, CURDATE()) <= 180 THEN 'expirando'
                       ELSE 'retido'
                   END as statusRetencao
            FROM Usuarios u
            LEFT JOIN RegistrosArquivados ra ON ra.idUsuario = u.idUsuario
            $where
            ORDER BY u.dataExpiracao ASC
            LIMIT ? OFFSET ?";

    $params[] = $porPagina;
    $params[] = $offset;
    $tipos_bind .= "ii";

    $res = consultarSQL($sql, $tipos_bind, $params);
    return obterTodos($res);
}

function contarInativos($busca = '', $tipoEvento = '') {
    $where = "WHERE u.ativo = 0";
    $params = [];
    $tipos_bind = "";

    if ($busca) {
        $where .= " AND (u.nome LIKE ? OR u.email LIKE ? OR u.matricula LIKE ?)";
        $b = "%$busca%";
        $params = [$b, $b, $b];
        $tipos_bind = "sss";
    }
    if ($tipoEvento) {
        $where .= " AND ra.tipoEvento = ?";
        $params[] = $tipoEvento;
        $tipos_bind .= "s";
    }

    $res = consultarSQL(
        "SELECT COUNT(*) as total FROM Usuarios u
         LEFT JOIN RegistrosArquivados ra ON ra.idUsuario = u.idUsuario $where",
        $tipos_bind, $params
    );
    $row = obterLinha($res);
    return $row['total'];
}

// ------------------------------------------------------------
// Buscar snapshot completo de um usuário arquivado
// ------------------------------------------------------------
function buscarSnapshotUsuario($idUsuario) {
    $res = consultarSQL(
        "SELECT ra.*, u.nome, u.matricula, u.email, u.tipo
         FROM RegistrosArquivados ra
         JOIN Usuarios u ON ra.idUsuario = u.idUsuario
         WHERE ra.idUsuario = ?
         ORDER BY ra.dataArquivamento DESC
         LIMIT 1",
        "i", [$idUsuario]
    );
    $row = obterLinha($res);
    if (!$row) return null;

    $row['dadosSnapshot']  = json_decode($row['dadosSnapshot'], true);
    $row['historicoDados'] = json_decode($row['historicoDados'], true);
    return $row;
}

// ------------------------------------------------------------
// Purgar dados expirados (apenas dados pessoais, mantém anonimizado)
// Chamado manualmente ou via cron — nunca automático
// ------------------------------------------------------------
function purgarDadosExpirados($purgarPor) {
    // Buscar expirados
    $res = consultarSQL(
        "SELECT idUsuario FROM Usuarios
         WHERE ativo = 0 AND dataExpiracao < CURDATE() AND dataExpiracao IS NOT NULL",
        "", []
    );
    $purged = 0;
    while ($row = obterLinha($res)) {
        $id = $row['idUsuario'];
        // Anonimizar dados pessoais, mantendo estrutura acadêmica
        executarSQL(
            "UPDATE Usuarios SET
                nome = CONCAT('Aluno-', matricula),
                email = CONCAT('expirado-', idUsuario, '@anonimo.edu'),
                cpf = NULL, rg = NULL, dataNasc = NULL, sexo = NULL,
                telefone = NULL, celular = NULL, logradouro = NULL,
                numero = NULL, complemento = NULL, bairro = NULL,
                cidade = NULL, estado = NULL, cep = NULL, foto = NULL
             WHERE idUsuario = ?",
            "i", [$id]
        );
        // Marcar arquivo como expirado
        executarSQL(
            "UPDATE RegistrosArquivados SET expirado = 1 WHERE idUsuario = ?",
            "i", [$id]
        );
        // Log
        executarSQL(
            "INSERT INTO HistoricoStatus (idUsuario, statusAnterior, statusNovo, motivo, alteradoPor)
             VALUES (?,0,0,'Dados pessoais purgados por expiração de 10 anos',?)",
            "ii", [$id, $purgarPor]
        );
        $purged++;
    }
    return $purged;
}

// ------------------------------------------------------------
// Estatísticas de retenção para o dashboard admin
// ------------------------------------------------------------
function estatisticasRetencao() {
    $res = consultarSQL(
        "SELECT
            SUM(CASE WHEN ativo = 1 THEN 1 ELSE 0 END) as ativos,
            SUM(CASE WHEN ativo = 0 AND (dataExpiracao IS NULL OR dataExpiracao > CURDATE()) THEN 1 ELSE 0 END) as inativos,
            SUM(CASE WHEN ativo = 0 AND dataExpiracao IS NOT NULL AND DATEDIFF(dataExpiracao, CURDATE()) <= 180 AND dataExpiracao > CURDATE() THEN 1 ELSE 0 END) as expirandoEmBreve,
            SUM(CASE WHEN ativo = 0 AND dataExpiracao < CURDATE() THEN 1 ELSE 0 END) as expirados
         FROM Usuarios WHERE tipo = 'aluno'"
    );
    return obterLinha($res);
}
