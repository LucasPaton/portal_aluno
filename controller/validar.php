<?php
// ============================================================
// ARQUIVO: controller/validar.php
// Verifica se o usuário está autenticado.
// Deve ser incluído no INÍCIO de todas as páginas protegidas.
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['idUsuario'])) {
    header("Location: " . str_repeat('../', substr_count($_SERVER['PHP_SELF'], '/') - 2) . "view/formlogin.php?erro=Sessão expirada. Faça login para continuar.");
    exit;
}

// ------------------------------------------------------------
// Verificar permissão por tipo (uso: validarTipo('admin') ou validarTipo(['admin','professor']))
// ------------------------------------------------------------
function validarTipo($tipos) {
    if (!is_array($tipos)) $tipos = [$tipos];
    if (!in_array($_SESSION['tipo'], $tipos)) {
        header("Location: ../view/acessoNegado.php");
        exit;
    }
}
