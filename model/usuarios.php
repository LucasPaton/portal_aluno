<?php
// ============================================================
// ARQUIVO: model/usuarios.php
// Camada de Modelo: operações de negócio para Usuários
// ============================================================

require_once __DIR__ . '/../persistencia/persistencia.php';

define('SUCESSO', 'sucesso');
define('ERRO_USUARIO_EXISTENTE', 'usuario_ja_existe');
define('ERRO_CAMPOS', 'campos_invalidos');
define('ERRO_USUARIO_NAO_ENCONTRADO', 'usuario_nao_encontrado');
define('ERRO_SENHA', 'senha_incorreta');

// ------------------------------------------------------------
// Gerar matrícula automática
// ------------------------------------------------------------
function gerarMatricula($tipo) {
    $prefixo = match($tipo) {
        'admin'     => 'ADM',
        'professor' => 'PROF',
        'aluno'     => 'ALU',
        default     => 'USR'
    };
    $ano = date('Y');
    $resultado = consultarSQL(
        "SELECT COUNT(*) as total FROM Usuarios WHERE tipo = ? AND YEAR(dataCriacao) = ?",
        "si", [$tipo, $ano]
    );
    $row = obterLinha($resultado);
    $seq = str_pad(($row['total'] + 1), 4, '0', STR_PAD_LEFT);
    return "$prefixo-$ano-$seq";
}

// ------------------------------------------------------------
// Validar campos comuns
// ------------------------------------------------------------
function validarCamposUsuario($nome, $email, $cpf = '', $tipo = 'aluno') {
    $erros = [];
    if (trim($nome) == '') $erros[] = "Informe o NOME.";
    if (trim($email) == '') $erros[] = "Informe o E-MAIL.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $erros[] = "E-MAIL inválido.";
    return implode('<br>', $erros);
}

// ------------------------------------------------------------
// Criar usuário (admin cria professor ou aluno)
// ------------------------------------------------------------
function criarUsuario($dados) {
    $erros = validarCamposUsuario($dados['nome'], $dados['email'], $dados['cpf'] ?? '', $dados['tipo']);
    if ($erros) return $erros;

    // Verificar email duplicado
    $res = consultarSQL("SELECT idUsuario FROM Usuarios WHERE email = ?", "s", [$dados['email']]);
    if (obterNumLinhas($res) > 0) return ERRO_USUARIO_EXISTENTE;

    $matricula = gerarMatricula($dados['tipo']);
    $senhaHash = password_hash($dados['senha'] ?? '123456', PASSWORD_BCRYPT);

    executarSQL(
        "INSERT INTO Usuarios (matricula, email, nome, senha, tipo, cpf, rg, dataNasc, sexo,
         telefone, celular, logradouro, numero, complemento, bairro, cidade, estado, cep)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)",
        "ssssssssssssssssss",
        [
            $matricula,
            $dados['email'],
            $dados['nome'],
            $senhaHash,
            $dados['tipo'],
            $dados['cpf'] ?? '',
            $dados['rg'] ?? '',
            $dados['dataNasc'] ?? null,
            $dados['sexo'] ?? null,
            $dados['telefone'] ?? '',
            $dados['celular'] ?? '',
            $dados['logradouro'] ?? '',
            $dados['numero'] ?? '',
            $dados['complemento'] ?? '',
            $dados['bairro'] ?? '',
            $dados['cidade'] ?? '',
            $dados['estado'] ?? '',
            $dados['cep'] ?? ''
        ]
    );

    return SUCESSO;
}

// ------------------------------------------------------------
// Autenticar usuário (login)
// ------------------------------------------------------------
function autenticarUsuario($email, $senha) {
    $res = consultarSQL(
        "SELECT * FROM Usuarios WHERE email = ? AND ativo = 1",
        "s", [$email]
    );
    $usuario = obterLinha($res);
    if (!$usuario) return ERRO_USUARIO_NAO_ENCONTRADO;
    if (!password_verify($senha, $usuario['senha'])) return ERRO_SENHA;
    return $usuario;
}

// ------------------------------------------------------------
// Buscar usuário por ID
// ------------------------------------------------------------
function buscarUsuarioPorId($id) {
    $res = consultarSQL("SELECT * FROM Usuarios WHERE idUsuario = ?", "i", [$id]);
    return obterLinha($res);
}

// ------------------------------------------------------------
// Listar usuários por tipo (com filtro de pesquisa)
// ------------------------------------------------------------
function listarUsuarios($tipo = null, $busca = '', $pagina = 1, $porPagina = 20) {
    $offset = ($pagina - 1) * $porPagina;
    $params = [];
    $tipos_bind = "";
    $where = "WHERE ativo = 1";

    if ($tipo) {
        $where .= " AND tipo = ?";
        $params[] = $tipo;
        $tipos_bind .= "s";
    }
    if ($busca) {
        $where .= " AND (nome LIKE ? OR email LIKE ? OR matricula LIKE ? OR cpf LIKE ?)";
        $buscaLike = "%$busca%";
        $params = array_merge($params, [$buscaLike, $buscaLike, $buscaLike, $buscaLike]);
        $tipos_bind .= "ssss";
    }

    $sql = "SELECT idUsuario, matricula, email, nome, tipo, cpf, telefone, celular, cidade, estado, ativo, dataCriacao
            FROM Usuarios $where ORDER BY nome LIMIT ? OFFSET ?";
    $params[] = $porPagina;
    $params[] = $offset;
    $tipos_bind .= "ii";

    $res = consultarSQL($sql, $tipos_bind, $params);
    return obterTodos($res);
}

// ------------------------------------------------------------
// Contar usuários (para paginação)
// ------------------------------------------------------------
function contarUsuarios($tipo = null, $busca = '') {
    $params = [];
    $tipos_bind = "";
    $where = "WHERE ativo = 1";

    if ($tipo) {
        $where .= " AND tipo = ?";
        $params[] = $tipo;
        $tipos_bind .= "s";
    }
    if ($busca) {
        $where .= " AND (nome LIKE ? OR email LIKE ? OR matricula LIKE ?)";
        $buscaLike = "%$busca%";
        $params = array_merge($params, [$buscaLike, $buscaLike, $buscaLike]);
        $tipos_bind .= "sss";
    }

    $res = consultarSQL("SELECT COUNT(*) as total FROM Usuarios $where", $tipos_bind, $params);
    $row = obterLinha($res);
    return $row['total'];
}

// ------------------------------------------------------------
// Atualizar dados do usuário
// ------------------------------------------------------------
function atualizarUsuario($id, $dados) {
    $campos = [];
    $valores = [];
    $tipos = "";

    $permitidos = ['nome','cpf','rg','dataNasc','sexo','telefone','celular',
                   'logradouro','numero','complemento','bairro','cidade','estado','cep'];

    foreach ($permitidos as $campo) {
        if (isset($dados[$campo])) {
            $campos[] = "$campo = ?";
            $valores[] = $dados[$campo];
            $tipos .= "s";
        }
    }

    if (empty($campos)) return ERRO_CAMPOS;

    $valores[] = $id;
    $tipos .= "i";

    executarSQL(
        "UPDATE Usuarios SET " . implode(', ', $campos) . " WHERE idUsuario = ?",
        $tipos, $valores
    );
    return SUCESSO;
}

// ------------------------------------------------------------
// Alterar senha
// ------------------------------------------------------------
function alterarSenha($id, $senhaAtual, $novaSenha) {
    $usuario = buscarUsuarioPorId($id);
    if (!$usuario) return ERRO_USUARIO_NAO_ENCONTRADO;
    if (!password_verify($senhaAtual, $usuario['senha'])) return ERRO_SENHA;

    $novaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
    executarSQL("UPDATE Usuarios SET senha = ? WHERE idUsuario = ?", "si", [$novaHash, $id]);
    return SUCESSO;
}

// ------------------------------------------------------------
// Desativar usuário (soft delete)
// ------------------------------------------------------------
function desativarUsuario($id) {
    executarSQL("UPDATE Usuarios SET ativo = 0 WHERE idUsuario = ?", "i", [$id]);
    return SUCESSO;
}
