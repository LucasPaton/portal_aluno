<?php
// ============================================================
// ARQUIVO: view/_layout.php
// Layout base: sidebar + topbar. Incluído em todas as páginas.
// Variáveis esperadas: $pageTitle, $currentNav, $tipoUsuario
// ============================================================

$tipo = $_SESSION['tipo'] ?? 'aluno';
$nome = $_SESSION['nome'] ?? '';
$mat  = $_SESSION['matricula'] ?? '';

$navAdmin = [
    ['icon'=>'🏠','label'=>'Dashboard',    'href'=>'dashboard.php',  'key'=>'dashboard'],
    ['icon'=>'👨‍🎓','label'=>'Alunos',      'href'=>'usuarios.php?tipo=aluno',  'key'=>'alunos'],
    ['icon'=>'👨‍🏫','label'=>'Professores', 'href'=>'usuarios.php?tipo=professor','key'=>'professores'],
    ['icon'=>'📚','label'=>'Cursos',       'href'=>'cursos.php',     'key'=>'cursos'],
    ['icon'=>'🏫','label'=>'Turmas',       'href'=>'turmas.php',     'key'=>'turmas'],
    ['icon'=>'📢','label'=>'Avisos',       'href'=>'avisos.php',     'key'=>'avisos'],
    ['icon'=>'🗃️','label'=>'Inativos',     'href'=>'inativos.php',   'key'=>'inativos'],
];

$navProfessor = [
    ['icon'=>'🏠','label'=>'Dashboard',    'href'=>'dashboard.php',  'key'=>'dashboard'],
    ['icon'=>'🏫','label'=>'Minhas Turmas','href'=>'dashboard.php',  'key'=>'turmas'],
    ['icon'=>'📋','label'=>'Frequência',   'href'=>'frequencia.php', 'key'=>'frequencias'],
    ['icon'=>'📊','label'=>'Notas',        'href'=>'notas.php',      'key'=>'notas'],
    ['icon'=>'❓','label'=>'Questionários','href'=>'questionarios.php','key'=>'questionarios'],
    ['icon'=>'💬','label'=>'Fórum',        'href'=>'forum.php',      'key'=>'forum'],
];

$navAluno = [
    ['icon'=>'🏠','label'=>'Dashboard',    'href'=>'dashboard.php',  'key'=>'dashboard'],
    ['icon'=>'📚','label'=>'Disciplinas',  'href'=>'disciplinas.php','key'=>'disciplinas'],
    ['icon'=>'📋','label'=>'Frequência',   'href'=>'frequencia.php', 'key'=>'frequencia'],
    ['icon'=>'📊','label'=>'Notas',        'href'=>'notas.php',      'key'=>'notas'],
    ['icon'=>'❓','label'=>'Questionários','href'=>'questionarios.php','key'=>'questionarios'],
    ['icon'=>'📁','label'=>'Trabalhos',    'href'=>'trabalhos.php',  'key'=>'trabalhos'],
    ['icon'=>'💬','label'=>'Fórum',        'href'=>'forum.php',      'key'=>'forum'],
    ['icon'=>'🤖','label'=>'Tutor IA',     'href'=>'tutor.php',      'key'=>'tutor'],
];

$nav = match($tipo) {
    'admin'     => $navAdmin,
    'professor' => $navProfessor,
    default     => $navAluno
};
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
<body>
<div class="layout">
    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">📚 <span>Portal</span>Aluno</div>
            <div class="sidebar-user">
                <strong><?= htmlspecialchars($nome) ?></strong>
                <?= htmlspecialchars($mat) ?> · <?= ucfirst($tipo) ?>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-label">Menu</div>
            <?php foreach ($nav as $item): ?>
                <a href="<?= $item['href'] ?>"
                   class="nav-item <?= ($currentNav ?? '') === $item['key'] ? 'active' : '' ?>">
                    <span class="icon"><?= $item['icon'] ?></span>
                    <?= $item['label'] ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-footer">
            <a href="<?= str_repeat('../', ($depth ?? 1)) ?>controller/controlador.php?operacao=logout"
               class="nav-item" style="color:#ef4444;">
                <span class="icon">🚪</span> Sair
            </a>
        </div>
    </aside>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="main-content">
        <header class="topbar">
            <div class="flex items-center gap-3">
                <button onclick="document.getElementById('sidebar').classList.toggle('open')"
                        class="btn btn-outline btn-sm" id="menuToggle">☰</button>
                <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? '') ?></span>
            </div>
            <div class="topbar-actions">
                <span class="text-muted" style="font-size:.8125rem;">Olá, <?= htmlspecialchars(explode(' ', $nome)[0]) ?></span>
            </div>
        </header>

        <main class="page-content">
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success"><?= htmlspecialchars($_GET['msg']) ?></div>
            <?php endif; ?>
