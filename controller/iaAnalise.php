<?php
// ============================================================
// ARQUIVO: controller/iaAnalise.php
// Endpoint AJAX para análise IA do professor
// ============================================================

require_once __DIR__ . '/../controller/validar.php';
validarTipo(['admin','professor']);
require_once __DIR__ . '/../model/questionarios.php';

header('Content-Type: application/json; charset=utf-8');

$idQuestionario = filter_input(INPUT_GET, 'idQuestionario', FILTER_VALIDATE_INT);
if (!$idQuestionario) { echo json_encode(['analise' => 'ID inválido.']); exit; }

$analise = analisarERecomendarAoProfessor($idQuestionario);
echo json_encode(['analise' => $analise], JSON_UNESCAPED_UNICODE);
