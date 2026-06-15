<?php
// ============================================================
// ARQUIVO: model/contato.php
// Camada de Modelo: Mensagens de Contato
// ============================================================

require_once __DIR__ . '/../persistencia/persistencia.php';

// ------------------------------------------------------------
// Salvar mensagem de contato
// ------------------------------------------------------------
function salvarMensagemContato($dados) {
    $erros = [];
    if (trim($dados['nome'] ?? '') === '') $erros[] = "Informe seu nome.";
    if (trim($dados['email'] ?? '') === '') $erros[] = "Informe seu e-mail.";
    if (!filter_var($dados['email'] ?? '', FILTER_VALIDATE_EMAIL)) $erros[] = "E-mail inválido.";
    if (trim($dados['mensagem'] ?? '') === '') $erros[] = "Escreva sua mensagem.";
    if (!empty($erros)) return implode('<br>', $erros);

    executarSQL(
        "INSERT INTO MensagensContato (nome, email, telefone, assunto, mensagem, formaContato)
         VALUES (?,?,?,?,?,?)",
        "ssssss",
        [
            $dados['nome'],
            $dados['email'],
            $dados['telefone'] ?? '',
            $dados['assunto'] ?? '',
            $dados['mensagem'],
            $dados['formaContato'] ?? 'email'
        ]
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// Listar mensagens (admin)
// ------------------------------------------------------------
function listarMensagens($busca = '', $apenasNaoRespondidas = false, $pagina = 1, $porPagina = 20) {
    $offset = ($pagina - 1) * $porPagina;
    $where = "WHERE 1=1";
    $params = [];
    $tipos_bind = "";

    if ($busca) {
        $where .= " AND (nome LIKE ? OR email LIKE ? OR assunto LIKE ? OR mensagem LIKE ?)";
        $b = "%$busca%";
        $params = array_merge($params, [$b, $b, $b, $b]);
        $tipos_bind .= "ssss";
    }
    if ($apenasNaoRespondidas) {
        $where .= " AND respondida = 0";
    }

    $sql = "SELECT mc.*, u.nome as nomeResponsavel
            FROM MensagensContato mc
            LEFT JOIN Usuarios u ON mc.respondidaPor = u.idUsuario
            $where ORDER BY mc.criadoEm DESC
            LIMIT ? OFFSET ?";
    $params[] = $porPagina;
    $params[] = $offset;
    $tipos_bind .= "ii";

    $res = consultarSQL($sql, $tipos_bind, $params);
    return obterTodos($res);
}

// ------------------------------------------------------------
// Contar mensagens
// ------------------------------------------------------------
function contarMensagens($busca = '', $apenasNaoRespondidas = false) {
    $where = "WHERE 1=1";
    $params = [];
    $tipos_bind = "";

    if ($busca) {
        $where .= " AND (nome LIKE ? OR email LIKE ? OR assunto LIKE ?)";
        $b = "%$busca%";
        $params = [$b, $b, $b];
        $tipos_bind = "sss";
    }
    if ($apenasNaoRespondidas) {
        $where .= " AND respondida = 0";
    }

    $res = consultarSQL("SELECT COUNT(*) as total FROM MensagensContato $where", $tipos_bind, $params);
    $row = obterLinha($res);
    return $row['total'];
}

// ------------------------------------------------------------
// Buscar por ID
// ------------------------------------------------------------
function buscarMensagemPorId($id) {
    $res = consultarSQL("SELECT * FROM MensagensContato WHERE idMensagem = ?", "i", [$id]);
    return obterLinha($res);
}

// ------------------------------------------------------------
// Marcar como respondida
// ------------------------------------------------------------
function responderMensagem($id, $resposta, $respondidaPor) {
    executarSQL(
        "UPDATE MensagensContato SET respondida = 1, respostaAdmin = ?, respondidaPor = ?, respondidaEm = NOW()
         WHERE idMensagem = ?",
        "sii", [$resposta, $respondidaPor, $id]
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// Contar não respondidas (para badges)
// ------------------------------------------------------------
function contarMensagensNaoRespondidas() {
    $res = consultarSQL("SELECT COUNT(*) as total FROM MensagensContato WHERE respondida = 0");
    $row = obterLinha($res);
    return $row['total'];
}
