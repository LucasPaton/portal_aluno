<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$msg  = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
$erro = filter_input(INPUT_GET, 'erro', FILTER_SANITIZE_SPECIAL_CHARS);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Portal do Aluno</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            position: relative;
        }
        [data-theme="dark"] body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        /* Background decorativo */
        .blob {
            position: absolute;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.6;
        }
        .blob-1 { top: -10%; left: -10%; width: 50vw; height: 50vw; background: rgba(79, 70, 229, 0.3); border-radius: 50%; }
        .blob-2 { bottom: -10%; right: -10%; width: 40vw; height: 40vw; background: rgba(59, 130, 246, 0.3); border-radius: 50%; }
        
        .login-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.4);
            border-radius: 24px;
            padding: 3rem;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
            z-index: 10;
            transform: translateY(20px);
            opacity: 0;
            animation: formEnter 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        [data-theme="dark"] .login-card {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        @keyframes formEnter {
            to { transform: translateY(0); opacity: 1; }
        }
        
        .logo-area { text-align: center; margin-bottom: 2.5rem; }
        .logo-icon { font-size: 3rem; margin-bottom: 0.5rem; }
        .logo-text { font-size: 1.5rem; font-weight: 700; color: var(--text); letter-spacing: -0.5px; }
        
        .form-control {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid var(--border);
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border-radius: 12px;
            font-size: 1rem;
        }
        [data-theme="dark"] .form-control { background: rgba(15, 23, 42, 0.6); }
        .form-control:focus { background: var(--surface); box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.15); }
        
        .input-icon-wrapper { position: relative; margin-bottom: 1.25rem; }
        .input-icon {
            position: absolute; left: 1rem; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 1.1rem;
        }
        
        .btn-login {
            background: var(--primary);
            color: white;
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 6px rgba(var(--primary-rgb), 0.2);
            margin-top: 1rem;
        }
        .btn-login:hover {
            background: var(--primary-dark);
            box-shadow: 0 6px 12px rgba(var(--primary-rgb), 0.3);
            transform: translateY(-2px);
        }
        .btn-login:active { transform: translateY(0); }
        
        .theme-toggle {
            position: absolute; top: 1.5rem; right: 1.5rem;
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 50%; width: 40px; height: 40px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 20; transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        .theme-toggle:hover { transform: scale(1.1); }
    </style>
</head>
<body>
    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>
    
    <button class="theme-toggle" id="themeToggle" title="Alternar tema">🌓</button>

    <div class="login-card">
        <div class="logo-area">
            <div class="logo-icon">🎓</div>
            <div class="logo-text">Portal do Aluno</div>
            <p class="text-muted" style="margin-top:0.25rem;">Acesse sua conta para continuar</p>
        </div>

        <?php if ($msg): ?>
            <div class="alert alert-success" style="border-radius:12px;"><?= $msg ?></div>
        <?php endif; ?>
        <?php if ($erro): ?>
            <div class="alert alert-danger" style="border-radius:12px;"><?= $erro ?></div>
        <?php endif; ?>

        <form action="../controller/controlador.php" method="POST">
            <input type="hidden" name="operacao" value="login">
            
            <div class="input-icon-wrapper">
                <span class="input-icon">✉️</span>
                <input type="email" name="email" class="form-control" placeholder="Seu e-mail acadêmico" required autofocus>
            </div>
            
            <div class="input-icon-wrapper">
                <span class="input-icon">🔒</span>
                <input type="password" name="senha" class="form-control" placeholder="Sua senha" required>
            </div>
            
            <button type="submit" class="btn-login">Entrar</button>
        </form>
    </div>

    <script>
        // Lógica de Dark Mode na página de login
        const toggleBtn = document.getElementById('themeToggle');
        const root = document.documentElement;
        
        const currentTheme = localStorage.getItem('theme');
        if (currentTheme === 'dark') root.setAttribute('data-theme', 'dark');

        toggleBtn.addEventListener('click', () => {
            const isDark = root.getAttribute('data-theme') === 'dark';
            if (isDark) {
                root.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
            } else {
                root.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            }
        });
    </script>
</body>
</html>
