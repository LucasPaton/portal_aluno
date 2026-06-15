<?php
// ============================================================
// ARQUIVO: model/materiais.php
// Camada de Modelo: CRUD de Materiais Didáticos
// ============================================================

require_once __DIR__ . '/../persistencia/persistencia.php';

// ------------------------------------------------------------
// Listar materiais (com filtro e paginação)
// ------------------------------------------------------------
function listarMateriais($busca = '', $tipo = '', $pagina = 1, $porPagina = 20) {
    $offset = ($pagina - 1) * $porPagina;
    $where = "WHERE m.ativo = 1";
    $params = [];
    $tipos_bind = "";

    if ($busca) {
        $where .= " AND (m.nome LIKE ? OR m.descricao LIKE ?)";
        $buscaLike = "%$busca%";
        $params[] = $buscaLike;
        $params[] = $buscaLike;
        $tipos_bind .= "ss";
    }
    if ($tipo) {
        $where .= " AND m.tipo = ?";
        $params[] = $tipo;
        $tipos_bind .= "s";
    }

    $sql = "SELECT m.*, c.nome as nomeCurso
            FROM MateriaisDidaticos m
            LEFT JOIN Cursos c ON m.idCurso = c.idCurso
            $where ORDER BY m.nome
            LIMIT ? OFFSET ?";
    $params[] = $porPagina;
    $params[] = $offset;
    $tipos_bind .= "ii";

    $res = consultarSQL($sql, $tipos_bind, $params);
    return obterTodos($res);
}

// ------------------------------------------------------------
// Contar materiais (para paginação)
// ------------------------------------------------------------
function contarMateriais($busca = '', $tipo = '') {
    $where = "WHERE ativo = 1";
    $params = [];
    $tipos_bind = "";

    if ($busca) {
        $where .= " AND (nome LIKE ? OR descricao LIKE ?)";
        $b = "%$busca%";
        $params = [$b, $b];
        $tipos_bind = "ss";
    }
    if ($tipo) {
        $where .= " AND tipo = ?";
        $params[] = $tipo;
        $tipos_bind .= "s";
    }

    $res = consultarSQL("SELECT COUNT(*) as total FROM MateriaisDidaticos $where", $tipos_bind, $params);
    $row = obterLinha($res);
    return $row['total'];
}

// ------------------------------------------------------------
// Buscar por ID
// ------------------------------------------------------------
function buscarMaterialPorId($id) {
    $res = consultarSQL(
        "SELECT m.*, c.nome as nomeCurso
         FROM MateriaisDidaticos m
         LEFT JOIN Cursos c ON m.idCurso = c.idCurso
         WHERE m.idMaterial = ?",
        "i", [$id]
    );
    return obterLinha($res);
}

// ------------------------------------------------------------
// Criar material
// ------------------------------------------------------------
function criarMaterial($dados) {
    if (trim($dados['nome'] ?? '') === '') return "Informe o nome do material.";

    executarSQL(
        "INSERT INTO MateriaisDidaticos (nome, descricao, tipo, preco, idCurso, imagem)
         VALUES (?,?,?,?,?,?)",
        "sssdis",
        [
            $dados['nome'],
            $dados['descricao'] ?? '',
            $dados['tipo'] ?? 'apostila',
            $dados['preco'] ?? 0,
            $dados['idCurso'] ?: null,
            $dados['imagem'] ?? ''
        ]
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// Atualizar material
// ------------------------------------------------------------
function atualizarMaterial($id, $dados) {
    if (trim($dados['nome'] ?? '') === '') return "Informe o nome do material.";

    executarSQL(
        "UPDATE MateriaisDidaticos SET nome=?, descricao=?, tipo=?, preco=?, idCurso=?, imagem=?
         WHERE idMaterial=?",
        "sssdisi",
        [
            $dados['nome'],
            $dados['descricao'] ?? '',
            $dados['tipo'] ?? 'apostila',
            $dados['preco'] ?? 0,
            $dados['idCurso'] ?: null,
            $dados['imagem'] ?? '',
            $id
        ]
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// Desativar material (soft delete)
// ------------------------------------------------------------
function desativarMaterial($id) {
    executarSQL("UPDATE MateriaisDidaticos SET ativo = 0 WHERE idMaterial = ?", "i", [$id]);
    return SUCESSO;
}

// ------------------------------------------------------------
// Reativar material
// ------------------------------------------------------------
function reativarMaterial($id) {
    executarSQL("UPDATE MateriaisDidaticos SET ativo = 1 WHERE idMaterial = ?", "i", [$id]);
    return SUCESSO;
}
