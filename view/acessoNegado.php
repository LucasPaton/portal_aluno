<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$pageTitle = 'Acesso Negado';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado — Portal do Aluno</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;background:var(--bg);">
    <div style="text-align:center;padding:3rem;">
        <div style="font-size:4rem;margin-bottom:1rem;">🔒</div>
        <h1 style="font-size:1.5rem;font-weight:700;margin-bottom:.5rem;">Acesso Negado</h1>
        <p class="text-muted" style="margin-bottom:1.5rem;">Você não tem permissão para acessar esta página.</p>
        <?php if (isset($_SESSION['tipo'])): ?>
        <a href="<?= match($_SESSION['tipo']) {
            'admin'     => 'admin/dashboard.php',
            'professor' => 'professor/dashboard.php',
            default     => 'aluno/dashboard.php'
        } ?>" class="btn btn-primary">← Voltar ao início</a>
        <?php else: ?>
        <a href="formlogin.php" class="btn btn-primary">← Fazer login</a>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
