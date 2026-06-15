// ============================================================
// ARQUIVO: assets/js/portal.js
// Utilitários compartilhados do Portal do Aluno
// ============================================================

// ------------------------------------------------------------
// Confirmações de ação destrutiva
// ------------------------------------------------------------
document.addEventListener('DOMContentLoaded', function () {

    // Todos os links com data-confirm pedem confirmação antes
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(this.dataset.confirm)) {
                e.preventDefault();
            }
        });
    });

    // Fechar alertas ao clicar no X
    document.querySelectorAll('.alert-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            this.closest('.alert').remove();
        });
    });

    // Auto-fechar alertas de sucesso após 5 segundos
    document.querySelectorAll('.alert-success').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity    = '0';
            setTimeout(function () { el.remove(); }, 500);
        }, 5000);
    });

    // Máscara de CPF
    document.querySelectorAll('input[name="cpf"]').forEach(function (el) {
        el.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 9)      v = v.replace(/(\d{3})(\d{3})(\d{3})(\d{1,2})/, '$1.$2.$3-$4');
            else if (v.length > 6) v = v.replace(/(\d{3})(\d{3})(\d{1,3})/, '$1.$2.$3');
            else if (v.length > 3) v = v.replace(/(\d{3})(\d{1,3})/, '$1.$2');
            this.value = v;
        });
    });

    // Máscara de CEP
    document.querySelectorAll('input[name="cep"]').forEach(function (el) {
        el.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 8);
            if (v.length > 5) v = v.replace(/(\d{5})(\d{1,3})/, '$1-$2');
            this.value = v;
        });
        // Busca automática via ViaCEP
        el.addEventListener('blur', function () {
            const cep = this.value.replace(/\D/g, '');
            if (cep.length !== 8) return;
            fetch('https://viacep.com.br/ws/' + cep + '/json/')
                .then(r => r.json())
                .then(d => {
                    if (d.erro) return;
                    const s = function (n, v) {
                        const i = document.querySelector('input[name="' + n + '"]');
                        if (i && v) i.value = v;
                    };
                    s('logradouro', d.logradouro);
                    s('bairro',     d.bairro);
                    s('cidade',     d.localidade);
                    s('estado',     d.uf);
                })
                .catch(function () {});
        });
    });

    // Máscara de telefone/celular
    document.querySelectorAll('input[name="telefone"], input[name="celular"]').forEach(function (el) {
        el.addEventListener('input', function () {
            let v = this.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 10)     v = v.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
            else if (v.length > 6) v = v.replace(/(\d{2})(\d{4})(\d{1,4})/, '($1) $2-$3');
            else if (v.length > 2) v = v.replace(/(\d{2})(\d{1,5})/, '($1) $2');
            this.value = v;
        });
    });

    // Sidebar toggle no mobile
    const toggleBtn = document.getElementById('menuToggle');
    const sidebar   = document.getElementById('sidebar');
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function () {
            sidebar.classList.toggle('open');
        });
        // Fechar ao clicar fora no mobile
        document.addEventListener('click', function (e) {
            if (window.innerWidth <= 768 &&
                !sidebar.contains(e.target) &&
                e.target !== toggleBtn) {
                sidebar.classList.remove('open');
            }
        });
    }

    // Highlight da linha da tabela ao hover com linha clicável
    document.querySelectorAll('tr[data-href]').forEach(function (tr) {
        tr.style.cursor = 'pointer';
        tr.addEventListener('click', function () {
            window.location = this.dataset.href;
        });
    });

});

// ------------------------------------------------------------
// Utilitários globais
// ------------------------------------------------------------

// Formatar nota com cor
function corNota(nota) {
    if (nota >= 7) return 'var(--success)';
    if (nota >= 5) return 'var(--warning)';
    return 'var(--danger)';
}

// Exibir toast de notificação
function toast(msg, tipo) {
    tipo = tipo || 'info';
    const div = document.createElement('div');
    div.className = 'alert alert-' + tipo;
    div.style.cssText = 'position:fixed;bottom:1.5rem;right:1.5rem;z-index:9999;min-width:280px;box-shadow:0 4px 12px rgba(0,0,0,.15);';
    div.innerHTML = msg;
    document.body.appendChild(div);
    setTimeout(function () {
        div.style.transition = 'opacity .4s';
        div.style.opacity    = '0';
        setTimeout(function () { div.remove(); }, 400);
    }, 3500);
}
