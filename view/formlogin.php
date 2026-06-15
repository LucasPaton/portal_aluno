<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal do Aluno — Login</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/portal.js" defer></script>
</head>
<body>
<div class="login-page">
    <div class="login-box">
        <div class="login-logo">
            <h1>📚 Portal do Aluno</h1>
            <p>Sistema Educacional Integrado</p>
        </div>

        <?php if (isset($_GET['erro'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
        <?php endif; ?>

        <form method="post" action="../controller/controlador.php">
            <input type="hidden" name="operacao" value="login">

            <div class="form-group">
                <label class="form-label">E-mail</label>
                <input type="email" name="email" class="form-control" placeholder="seu@email.edu.br" required autofocus>
            </div>

            <div class="form-group">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary w-full btn-lg" style="justify-content:center;margin-top:.5rem;">
                Entrar no Portal
            </button>
        </form>

        <p class="text-center text-muted mt-4" style="font-size:.8125rem;">
            Problemas para acessar? Entre em contato com a secretaria.
        </p>
    </div>
</div>
</body>
</html>
