<?php
require_once '../../controller/validar.php';
// Permite qualquer usuário logado (aluno, professor, admin)

$pageTitle  = 'Alterar Senha';
$currentNav = 'perfil';
$depth      = 2;
include '../_layout.php';

$erro = filter_input(INPUT_GET, 'erro', FILTER_SANITIZE_SPECIAL_CHARS);
?>

<div class="card" style="max-width:500px;margin:0 auto;">
    <div class="card-header">
        <span class="card-title">🔐 Alterar Senha</span>
    </div>
    <div class="card-body">
        <?php if ($erro): ?>
            <div class="alert alert-danger" style="margin-bottom:1.5rem;"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao" value="alterarSenha">
            
            <div class="form-group">
                <label class="form-label">Senha Atual</label>
                <input type="password" name="senhaAtual" class="form-control" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nova Senha</label>
                <input type="password" name="novaSenha" class="form-control" required minlength="6">
                <div class="form-hint">Mínimo de 6 caracteres.</div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Confirmar Nova Senha</label>
                <input type="password" name="confirmarSenha" class="form-control" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary w-full" style="justify-content:center;margin-top:1rem;">💾 Salvar Nova Senha</button>
        </form>
    </div>
</div>

        </main></div></div></body></html>
