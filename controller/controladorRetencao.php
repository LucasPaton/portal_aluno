<?php
// ============================================================
// ARQUIVO: controller/controladorRetencao.php
// Operações de arquivamento e retenção de dados
// Incluído pelo controlador principal
// ============================================================

require_once __DIR__ . '/../model/retencao.php';

$operacao = filter_input(INPUT_POST, 'operacao') ?? filter_input(INPUT_GET, 'operacao') ?? '';

if ($operacao === 'arquivarUsuario') {
    $idUsuario   = filter_input(INPUT_POST, 'idUsuario', FILTER_VALIDATE_INT);
    $tipoEvento  = filter_input(INPUT_POST, 'tipoEvento', FILTER_SANITIZE_SPECIAL_CHARS);
    $dataEvento  = filter_input(INPUT_POST, 'dataEvento');
    $observacoes = filter_input(INPUT_POST, 'observacoes', FILTER_SANITIZE_SPECIAL_CHARS);
    $arquivadoPor= $_SESSION['idUsuario'];

    $retorno = arquivarUsuario($idUsuario, $tipoEvento, $dataEvento, $arquivadoPor, $observacoes);

    if ($retorno === SUCESSO) {
        header("Location: ../view/admin/inativos.php?msg=Usuário arquivado com sucesso. Dados retidos por 10 anos.");
    } else {
        header("Location: ../view/admin/usuarios.php?tipo=aluno&msg=" . urlencode($retorno));
    }
    exit;
}

if ($operacao === 'reativarUsuario') {
    $idUsuario  = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $motivo     = filter_input(INPUT_GET, 'motivo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'Reativação manual';
    $reativadoPor = $_SESSION['idUsuario'];

    reativarUsuario($idUsuario, $reativadoPor, $motivo);
    header("Location: ../view/admin/inativos.php?msg=Usuário reativado com sucesso.");
    exit;
}

if ($operacao === 'purgarExpirados') {
    $purgedPor = $_SESSION['idUsuario'];
    $total = purgarDadosExpirados($purgedPor);
    header("Location: ../view/admin/inativos.php?msg=$total registro(s) purgado(s). Dados pessoais anonimizados.");
    exit;
}
