<?php
// ============================================================
// ARQUIVO: model/servicos.php
// Camada de Modelo: CRUD de Serviços Acadêmicos
// ============================================================

require_once __DIR__ . '/../persistencia/persistencia.php';

// ------------------------------------------------------------
// Listar serviços (com filtro e paginação)
// ------------------------------------------------------------
function listarServicos($busca = '', $categoria = '', $pagina = 1, $porPagina = 20) {
    $offset = ($pagina - 1) * $porPagina;
    $where = "WHERE ativo = 1";
    $params = [];
    $tipos_bind = "";

    if ($busca) {
        $where .= " AND (nome LIKE ? OR descricao LIKE ?)";
        $buscaLike = "%$busca%";
        $params[] = $buscaLike;
        $params[] = $buscaLike;
        $tipos_bind .= "ss";
    }
    if ($categoria) {
        $where .= " AND categoria = ?";
        $params[] = $categoria;
        $tipos_bind .= "s";
    }

    $sql = "SELECT * FROM ServicosAcademicos $where ORDER BY nome LIMIT ? OFFSET ?";
    $params[] = $porPagina;
    $params[] = $offset;
    $tipos_bind .= "ii";

    $res = consultarSQL($sql, $tipos_bind, $params);
    return obterTodos($res);
}

// ------------------------------------------------------------
// Contar serviços (para paginação)
// ------------------------------------------------------------
function contarServicos($busca = '', $categoria = '') {
    $where = "WHERE ativo = 1";
    $params = [];
    $tipos_bind = "";

    if ($busca) {
        $where .= " AND (nome LIKE ? OR descricao LIKE ?)";
        $b = "%$busca%";
        $params = [$b, $b];
        $tipos_bind = "ss";
    }
    if ($categoria) {
        $where .= " AND categoria = ?";
        $params[] = $categoria;
        $tipos_bind .= "s";
    }

    $res = consultarSQL("SELECT COUNT(*) as total FROM ServicosAcademicos $where", $tipos_bind, $params);
    $row = obterLinha($res);
    return $row['total'];
}

// ------------------------------------------------------------
// Buscar por ID
// ------------------------------------------------------------
function buscarServicoPorId($id) {
    $res = consultarSQL("SELECT * FROM ServicosAcademicos WHERE idServico = ?", "i", [$id]);
    return obterLinha($res);
}

// ------------------------------------------------------------
// Criar serviço
// ------------------------------------------------------------
function criarServico($dados) {
    if (trim($dados['nome'] ?? '') === '') return "Informe o nome do serviço.";

    executarSQL(
        "INSERT INTO ServicosAcademicos (nome, descricao, categoria, valorEstimado, imagem, horarioFunc, responsavel)
         VALUES (?,?,?,?,?,?,?)",
        "sssdsss",
        [
            $dados['nome'],
            $dados['descricao'] ?? '',
            $dados['categoria'] ?? 'outro',
            $dados['valorEstimado'] ?? 0,
            $dados['imagem'] ?? '',
            $dados['horarioFunc'] ?? '',
            $dados['responsavel'] ?? ''
        ]
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// Atualizar serviço
// ------------------------------------------------------------
function atualizarServico($id, $dados) {
    if (trim($dados['nome'] ?? '') === '') return "Informe o nome do serviço.";

    executarSQL(
        "UPDATE ServicosAcademicos SET nome=?, descricao=?, categoria=?, valorEstimado=?,
         imagem=?, horarioFunc=?, responsavel=? WHERE idServico=?",
        "sssdsssi",
        [
            $dados['nome'],
            $dados['descricao'] ?? '',
            $dados['categoria'] ?? 'outro',
            $dados['valorEstimado'] ?? 0,
            $dados['imagem'] ?? '',
            $dados['horarioFunc'] ?? '',
            $dados['responsavel'] ?? '',
            $id
        ]
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// Desativar serviço (soft delete)
// ------------------------------------------------------------
function desativarServico($id) {
    executarSQL("UPDATE ServicosAcademicos SET ativo = 0 WHERE idServico = ?", "i", [$id]);
    return SUCESSO;
}
