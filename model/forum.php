<?php
// ============================================================
// ARQUIVO: model/forum.php
// Camada de Modelo: Fórum, Avisos e Trabalhos
// ============================================================

require_once __DIR__ . '/../persistencia/persistencia.php';
require_once __DIR__ . '/usuarios.php';

// ============================================================
// FÓRUM / AVISOS
// ============================================================

function criarPost($idAutor, $conteudo, $titulo = '', $tipo = 'aviso', $idTurma = null, $idPostPai = null, $fixado = 0) {
    executarSQL(
        "INSERT INTO Forum (idTurma, idAutor, idPostPai, titulo, conteudo, tipo, fixado) VALUES (?,?,?,?,?,?,?)",
        "iiisssi",
        [$idTurma, $idAutor, $idPostPai, $titulo, $conteudo, $tipo, $fixado]
    );
    return obterUltimoId();
}

function listarPostsTurma($idTurma, $tipo = null) {
    $where = "WHERE (f.idTurma = ? OR f.idTurma IS NULL) AND f.idPostPai IS NULL";
    $params = [$idTurma];
    $tipos_bind = "i";

    if ($tipo) {
        $where .= " AND f.tipo = ?";
        $params[] = $tipo;
        $tipos_bind .= "s";
    }

    $res = consultarSQL(
        "SELECT f.*, u.nome as nomeAutor, u.tipo as tipoAutor,
                (SELECT COUNT(*) FROM Forum r WHERE r.idPostPai = f.idPost) as totalRespostas
         FROM Forum f
         JOIN Usuarios u ON f.idAutor = u.idUsuario
         $where ORDER BY f.fixado DESC, f.dataPostagem DESC",
        $tipos_bind, $params
    );
    return obterTodos($res);
}

function listarAvisosGerais() {
    $res = consultarSQL(
        "SELECT f.*, u.nome as nomeAutor
         FROM Forum f JOIN Usuarios u ON f.idAutor = u.idUsuario
         WHERE f.idTurma IS NULL
         ORDER BY f.fixado DESC, f.dataPostagem DESC LIMIT 20"
    );
    return obterTodos($res);
}

function listarRespostasPost($idPost) {
    $res = consultarSQL(
        "SELECT f.*, u.nome as nomeAutor, u.tipo as tipoAutor
         FROM Forum f JOIN Usuarios u ON f.idAutor = u.idUsuario
         WHERE f.idPostPai = ?
         ORDER BY f.dataPostagem ASC",
        "i", [$idPost]
    );
    return obterTodos($res);
}

function buscarPostPorId($idPost) {
    $res = consultarSQL(
        "SELECT f.*, u.nome as nomeAutor, u.tipo as tipoAutor
         FROM Forum f JOIN Usuarios u ON f.idAutor = u.idUsuario
         WHERE f.idPost = ?",
        "i", [$idPost]
    );
    return obterLinha($res);
}

// ============================================================
// TRABALHOS
// ============================================================

function criarTrabalho($idTurma, $idProfessor, $titulo, $descricao, $dataEntrega, $permiteAtraso = 0) {
    executarSQL(
        "INSERT INTO Trabalhos (idTurma, idProfessor, titulo, descricao, dataEntrega, permiteAtraso, publicado)
         VALUES (?,?,?,?,?,?,1)",
        "iisssi",
        [$idTurma, $idProfessor, $titulo, $descricao, $dataEntrega, $permiteAtraso]
    );
    return obterUltimoId();
}

function listarTrabalhosTurma($idTurma, $apenasPublicados = true) {
    $where = $apenasPublicados ? "AND t.publicado = 1" : "";
    $res = consultarSQL(
        "SELECT t.*,
                (SELECT COUNT(*) FROM EntregasTrabalho e WHERE e.idTrabalho = t.idTrabalho) as totalEntregas
         FROM Trabalhos t
         WHERE t.idTurma = ? $where
         ORDER BY t.dataEntrega ASC",
        "i", [$idTurma]
    );
    return obterTodos($res);
}

function entregarTrabalho($idTrabalho, $idAluno, $arquivoNome, $arquivoCaminho, $comentario = '') {
    // Verificar se já entregou
    $res = consultarSQL(
        "SELECT idEntrega FROM EntregasTrabalho WHERE idTrabalho = ? AND idAluno = ?",
        "ii", [$idTrabalho, $idAluno]
    );
    if (obterNumLinhas($res) > 0) {
        // Atualizar entrega existente
        executarSQL(
            "UPDATE EntregasTrabalho SET arquivoNome=?, arquivoCaminho=?, comentario=?, dataEnvio=NOW(), status='enviado'
             WHERE idTrabalho=? AND idAluno=?",
            "sssii", [$arquivoNome, $arquivoCaminho, $comentario, $idTrabalho, $idAluno]
        );
    } else {
        executarSQL(
            "INSERT INTO EntregasTrabalho (idTrabalho, idAluno, arquivoNome, arquivoCaminho, comentario) VALUES (?,?,?,?,?)",
            "iisss", [$idTrabalho, $idAluno, $arquivoNome, $arquivoCaminho, $comentario]
        );
    }
    return SUCESSO;
}

function corrigirEntrega($idEntrega, $nota, $feedback) {
    executarSQL(
        "UPDATE EntregasTrabalho SET nota=?, feedback=?, status='corrigido' WHERE idEntrega=?",
        "dsi", [$nota, $feedback, $idEntrega]
    );
    return SUCESSO;
}

function listarEntregasTurma($idTrabalho) {
    $res = consultarSQL(
        "SELECT e.*, u.nome as nomeAluno, u.matricula
         FROM EntregasTrabalho e
         JOIN Usuarios u ON e.idAluno = u.idUsuario
         WHERE e.idTrabalho = ?
         ORDER BY e.dataEnvio DESC",
        "i", [$idTrabalho]
    );
    return obterTodos($res);
}
