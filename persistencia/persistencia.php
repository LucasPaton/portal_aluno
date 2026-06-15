<?php
// ============================================================
// ARQUIVO: persistencia.php
// Camada de persistência - MySQL via MySQLi
// ============================================================

require_once __DIR__ . '/conexao.php';

$conexaoGlobal = null;

// ------------------------------------------------------------
// Conectar ao banco
// ------------------------------------------------------------
function conectarSGBD($host = DB_HOST, $login = DB_USER, $senha = DB_PASS, $banco = DB_NAME) {
    global $conexaoGlobal;
    if ($conexaoGlobal && !$conexaoGlobal->connect_error) {
        return $conexaoGlobal;
    }
    $conexaoGlobal = new mysqli($host, $login, $senha, $banco);
    if ($conexaoGlobal->connect_error) {
        // Mostrar erro detalhado para facilitar diagnóstico no desenvolvimento
        $msg = "Falha na conexão com o banco de dados.<br>"
             . "Host: $host | Usuário: $login | Banco: $banco<br>"
             . "Erro MySQL: " . $conexaoGlobal->connect_error . "<br><br>"
             . "<b>Verifique:</b><ul>"
             . "<li>O XAMPP está rodando com MySQL ativo?</li>"
             . "<li>O banco <b>PortalAlunoBD</b> foi criado?</li>"
             . "<li>Senha correta em <b>persistencia/conexao.php</b> (XAMPP = vazia)</li>"
             . "</ul>";
        die($msg);
    }
    $conexaoGlobal->set_charset(DB_CHARSET);
    return $conexaoGlobal;
}

// ------------------------------------------------------------
// Desconectar
// ------------------------------------------------------------
function desconectarSGBD() {
    global $conexaoGlobal;
    if ($conexaoGlobal) {
        $conexaoGlobal->close();
        $conexaoGlobal = null;
    }
}

// ------------------------------------------------------------
// INSERT / UPDATE / DELETE com Prepared Statement
// ------------------------------------------------------------
function executarSQL($sql, $tipos = "", $valores = []) {
    global $conexaoGlobal;
    conectarSGBD();
    $stmt = $conexaoGlobal->prepare($sql);
    if (!$stmt) {
        die("Erro ao preparar SQL: " . $conexaoGlobal->error . "<br>SQL: <code>$sql</code>");
    }
    if (!empty($valores)) {
        $stmt->bind_param($tipos, ...$valores);
    }
    $stmt->execute();
    return $stmt;
}

// ------------------------------------------------------------
// SELECT com Prepared Statement
// ------------------------------------------------------------
function consultarSQL($sql, $tipos = "", $valores = []) {
    global $conexaoGlobal;
    conectarSGBD();
    $stmt = $conexaoGlobal->prepare($sql);
    if (!$stmt) {
        die("Erro ao preparar SQL: " . $conexaoGlobal->error . "<br>SQL: <code>$sql</code>");
    }
    if (!empty($valores)) {
        $stmt->bind_param($tipos, ...$valores);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// ------------------------------------------------------------
// Obter uma linha como array associativo
// ------------------------------------------------------------
function obterLinha($resultado) {
    if (!$resultado) return null;
    return $resultado->fetch_assoc();
}

// ------------------------------------------------------------
// Obter todas as linhas como array de arrays
// ------------------------------------------------------------
function obterTodos($resultado) {
    $dados = [];
    if (!$resultado) return $dados;
    while ($linha = $resultado->fetch_assoc()) {
        $dados[] = $linha;
    }
    return $dados;
}

// ------------------------------------------------------------
// Número de linhas do resultado
// ------------------------------------------------------------
function obterNumLinhas($resultado) {
    if (!$resultado) return 0;
    return $resultado->num_rows;
}

// ------------------------------------------------------------
// Último ID inserido
// ------------------------------------------------------------
function obterUltimoId() {
    global $conexaoGlobal;
    return $conexaoGlobal->insert_id;
}
