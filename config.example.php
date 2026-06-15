<?php
// ============================================================
// ARQUIVO: config.example.php
// Exemplo de configuração — copie para config.php e preencha
// ============================================================

// Chave da API Google Gemini (IA)
// Obtenha em: https://aistudio.google.com/apikey
define('GEMINI_API_KEY', 'SUA_CHAVE_GEMINI_AQUI');

// URL base da API Gemini
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');

// Configurações do sistema
define('SISTEMA_NOME', 'Portal do Aluno');
define('SISTEMA_VERSAO', '2.0.0');
define('SISTEMA_ANO', date('Y'));

// Timezone
date_default_timezone_set('America/Sao_Paulo');
