<?php
// ============================================================
// ARQUIVO: view/_layout.php
// Layout base: sidebar + topbar. Incluído em todas as páginas.
// Variáveis esperadas: $pageTitle, $currentNav, $tipoUsuario
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$tipo = $_SESSION['tipo'] ?? 'aluno';
$nome = $_SESSION['nome'] ?? '';
$mat  = $_SESSION['matricula'] ?? '';

$navAdmin = [
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>', 'label'=>'Dashboard',    'href'=>'dashboard.php',  'key'=>'dashboard'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>', 'label'=>'Alunos',      'href'=>'usuarios.php?tipo=aluno',  'key'=>'alunos'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>', 'label'=>'Professores', 'href'=>'usuarios.php?tipo=professor','key'=>'professores'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>', 'label'=>'Cursos',       'href'=>'cursos.php',     'key'=>'cursos'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>', 'label'=>'Turmas',       'href'=>'turmas.php',     'key'=>'turmas'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>', 'label'=>'Materiais',    'href'=>'materiais.php',  'key'=>'materiais'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>', 'label'=>'Serviços',     'href'=>'servicos.php',   'key'=>'servicos'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path></svg>', 'label'=>'Avisos',       'href'=>'avisos.php',     'key'=>'avisos'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>', 'label'=>'Mensagens',    'href'=>'mensagens.php',  'key'=>'mensagens'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>', 'label'=>'Inativos',     'href'=>'inativos.php',   'key'=>'inativos'],
];

$navProfessor = [
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>', 'label'=>'Dashboard',    'href'=>'dashboard.php',  'key'=>'dashboard'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>', 'label'=>'Minhas Turmas','href'=>'dashboard.php',  'key'=>'turmas'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>', 'label'=>'Frequência',   'href'=>'frequencia.php', 'key'=>'frequencias'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>', 'label'=>'Notas',        'href'=>'notas.php',      'key'=>'notas'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>', 'label'=>'Questionários','href'=>'questionarios.php','key'=>'questionarios'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>', 'label'=>'Fórum',        'href'=>'forum.php',      'key'=>'forum'],
];

$navAluno = [
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>', 'label'=>'Dashboard',    'href'=>'dashboard.php',  'key'=>'dashboard'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>', 'label'=>'Disciplinas',  'href'=>'disciplinas.php','key'=>'disciplinas'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>', 'label'=>'Frequência',   'href'=>'frequencia.php', 'key'=>'frequencia'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>', 'label'=>'Notas',        'href'=>'notas.php',      'key'=>'notas'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>', 'label'=>'Questionários','href'=>'questionarios.php','key'=>'questionarios'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"></path></svg>', 'label'=>'Trabalhos',    'href'=>'trabalhos.php',  'key'=>'trabalhos'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>', 'label'=>'Fórum',        'href'=>'forum.php',      'key'=>'forum'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>', 'label'=>'Tutor IA',     'href'=>'tutor.php',      'key'=>'tutor'],
    ['icon'=>'<svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>', 'label'=>'Fale Conosco', 'href'=>'../contato.php', 'key'=>'contato'],
];

$nav = match($tipo) {
    'admin'     => $navAdmin,
    'professor' => $navProfessor,
    default     => $navAluno
};

$nomeCurto = explode(' ', trim($nome))[0];
$iniciais  = strtoupper(substr($nome, 0, 2));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Portal do Aluno') ?></title>
    <link rel="stylesheet" href="<?= str_repeat('../', ($depth ?? 1)) ?>assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="<?= str_repeat('../', ($depth ?? 1)) ?>assets/js/portal.js" defer></script>
</head>
<body class="animate-fade-in">
<div class="app-container">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <span style="font-size:1.5rem;">🎓</span> Portal Aluno
        </div>

        <div style="padding:1.5rem 1.5rem 0.5rem;">
            <div class="user-profile">
                <div class="avatar"><?= $iniciais ?></div>
                <div class="user-info">
                    <div class="user-name"><?= htmlspecialchars($nomeCurto) ?></div>
                    <div class="user-role"><?= htmlspecialchars($mat) ?> · <?= ucfirst($tipo) ?></div>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php foreach ($nav as $item): ?>
                <a href="<?= $item['href'] ?>" class="nav-item <?= ($currentNav ?? '') === $item['key'] ? 'active' : '' ?>">
                    <span class="icon"><?= $item['icon'] ?></span>
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= str_repeat('../', ($depth ?? 1)) ?><?= $tipo === 'admin' ? 'admin/' : '' ?>alterarSenha.php" class="nav-item" style="padding:0.5rem 0;">
                <span class="icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg></span> Senha
            </a>
            <a href="<?= str_repeat('../', ($depth ?? 1)) ?>controller/controlador.php?operacao=logout" class="nav-item" style="color:var(--danger);padding:0.5rem 0;">
                <span class="icon"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg></span> Sair
            </a>
        </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="main-content">
        <header class="topbar">
            <div class="flex items-center gap-3">
                <button onclick="document.getElementById('sidebar').classList.toggle('open')" class="menu-toggle" id="menuToggle">☰</button>
                <div class="flex items-center gap-2">
                    <span class="text-muted" style="font-size:0.875rem;"><?= ucfirst($tipo) ?> / </span>
                    <span style="font-weight:600;"><?= htmlspecialchars($pageTitle ?? '') ?></span>
                </div>
            </div>
            <div class="flex items-center gap-4">
                <button id="themeToggleTop" class="btn btn-outline btn-sm" style="border-radius:50%;width:36px;height:36px;padding:0;display:flex;align-items:center;justify-content:center;">🌓</button>
                <div class="text-right" style="display:none;">
                    <strong style="font-size:0.875rem;"><?= htmlspecialchars($nomeCurto) ?></strong><br>
                    <span class="text-muted" style="font-size:0.75rem;">Online</span>
                </div>
            </div>
        </header>

        <main class="content-wrapper animate-slide-up">
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <div class="flex items-center gap-2">
                        <span>✅</span> <?= htmlspecialchars($_GET['msg']) ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['erro'])): ?>
                <div class="alert alert-danger">
                    <div class="flex items-center gap-2">
                        <span>⚠️</span> <?= htmlspecialchars($_GET['erro']) ?>
                    </div>
                </div>
            <?php endif; ?>
