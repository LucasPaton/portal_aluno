<?php
require_once '../controller/validar.php';
// Qualquer usuário logado pode contatar a instituição

$pageTitle  = 'Fale Conosco';
$currentNav = 'contato';
$depth      = 1;
include '_layout.php';

$msg  = filter_input(INPUT_GET, 'msg', FILTER_SANITIZE_SPECIAL_CHARS);
?>

<div class="card" style="max-width:800px;margin:0 auto;">
    <div class="card-header" style="background:linear-gradient(135deg,var(--primary) 0%,var(--primary-dark) 100%);color:#fff;border-radius:var(--radius) var(--radius) 0 0;">
        <div class="flex items-center gap-3">
            <span style="font-size:2rem;">💬</span>
            <div>
                <span class="card-title" style="color:#fff;">Fale com a Instituição</span>
                <p style="font-size:.875rem;color:rgba(255,255,255,.8);margin:0;">Envie suas dúvidas, sugestões ou problemas técnicos.</p>
            </div>
        </div>
    </div>
    <div class="card-body" style="padding:2rem;">
        <?php if ($msg): ?>
            <div class="alert alert-danger" style="margin-bottom:1.5rem;"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <form method="post" action="../controller/controlador.php">
            <input type="hidden" name="operacao" value="enviarContato">
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Seu Nome *</label>
                    <input type="text" name="nome" class="form-control" required value="<?= htmlspecialchars($_SESSION['nome']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Seu E-mail *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Telefone / WhatsApp</label>
                    <input type="text" name="telefone" class="form-control" placeholder="(00) 00000-0000">
                </div>
                <div class="form-group">
                    <label class="form-label">Forma de Retorno Preferida</label>
                    <select name="formaContato" class="form-control">
                        <option value="email">E-mail</option>
                        <option value="whatsapp">WhatsApp</option>
                        <option value="telefone">Telefone</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Assunto</label>
                <input type="text" name="assunto" class="form-control" placeholder="Do que se trata?">
            </div>

            <div class="form-group">
                <label class="form-label">Mensagem *</label>
                <textarea name="mensagem" class="form-control" rows="5" required placeholder="Escreva sua mensagem detalhadamente..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary btn-lg w-full" style="justify-content:center;margin-top:1rem;">✉️ Enviar Mensagem</button>
        </form>
    </div>
</div>

        </main></div></div></body></html>
