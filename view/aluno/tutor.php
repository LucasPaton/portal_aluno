<?php
require_once '../../controller/validar.php';
validarTipo('aluno');
require_once '../../model/turmas.php';
require_once '../../model/questionarios.php';

$idAluno = $_SESSION['idUsuario'];
$turmas  = listarTurmasAlunoV2($idAluno);
$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);

$pageTitle  = 'Tutor IA';
$currentNav = 'tutor';
$depth      = 2;
include '../_layout.php';
?>

<!-- CABEÇALHO DO TUTOR -->
<div class="card mb-4" style="background:linear-gradient(135deg,#4f46e5 0%,#7c3aed 100%);border:none;color:#fff;">
    <div class="card-body" style="display:flex;gap:1.5rem;align-items:center;">
        <div style="font-size:3rem;line-height:1;">🤖</div>
        <div>
            <h2 style="color:#fff;font-size:1.25rem;font-weight:700;margin-bottom:.25rem;">Tutor IA Personalizado</h2>
            <p style="color:rgba(255,255,255,.8);font-size:.875rem;margin:0;">
                Desenvolvido com Google Gemini · Analisa seu desempenho e sugere rotina de estudos personalizada
            </p>
        </div>
    </div>
</div>

<div class="grid-2">
    <!-- ANÁLISE DE DESEMPENHO POR DISCIPLINA -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">📊 Análise de Desempenho</span>
        </div>
        <div class="card-body">
            <div class="form-group">
                <label class="form-label">Selecione a disciplina para analisar:</label>
                <select id="selTurmaAnalise" class="form-control">
                    <option value="">Escolha uma disciplina...</option>
                    <?php foreach ($turmas as $t): ?>
                        <option value="<?= $t['idTurma'] ?>"><?= htmlspecialchars($t['nomeDisciplina']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn-info w-full" onclick="analisarDesempenho()" style="justify-content:center;">
                🤖 Analisar meu desempenho
            </button>
            <div id="analiseResultado" class="ia-box mt-3" style="display:none;">
                <h4>🤖 Análise da IA</h4>
                <div id="analiseTexto" style="white-space:pre-wrap;line-height:1.7;font-size:.875rem;"></div>
            </div>
        </div>
    </div>

    <!-- CHAT COM O TUTOR -->
    <div class="card">
        <div class="card-header">
            <span class="card-title">💬 Chat com o Tutor</span>
        </div>
        <div class="card-body" style="padding:0;">
            <div id="chatMsgs" style="height:320px;overflow-y:auto;padding:1rem;display:flex;flex-direction:column;gap:.75rem;">
                <div class="msg msg-bot">
                    <div class="msg-bubble bot">👋 Olá, <?= htmlspecialchars(explode(' ', $_SESSION['nome'])[0]) ?>! Sou seu tutor virtual. Posso tirar dúvidas sobre qualquer conteúdo, ajudar você a entender erros em questões ou sugerir materiais de estudo. Como posso ajudar?</div>
                </div>
            </div>
            <div style="padding:.875rem;border-top:1px solid var(--border);display:flex;gap:.5rem;">
                <input type="text" id="chatInput" class="form-control" placeholder="Digite sua dúvida..." onkeydown="if(event.key==='Enter')enviarChat()">
                <button class="btn btn-primary" onclick="enviarChat()">Enviar</button>
            </div>
        </div>
    </div>
</div>

<!-- ROTINA DE ESTUDOS -->
<div class="card mt-4">
    <div class="card-header">
        <span class="card-title">📅 Rotina de Estudos Recomendada</span>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">A IA irá analisar suas notas e frequências em todas as disciplinas e criar uma rotina de estudos personalizada para a semana.</p>
        <button class="btn btn-primary" onclick="gerarRotina()">🤖 Gerar rotina semanal personalizada</button>
        <div id="rotinaResultado" class="ia-box mt-3" style="display:none;">
            <h4>📅 Sua Rotina de Estudos</h4>
            <div id="rotinaTexto" style="white-space:pre-wrap;line-height:1.8;font-size:.875rem;"></div>
        </div>
    </div>
</div>

<style>
.msg { display:flex; margin-bottom:.5rem; }
.msg.msg-user { justify-content:flex-end; }
.msg-bubble {
    max-width:80%; padding:.625rem .875rem;
    border-radius:1rem; font-size:.875rem; line-height:1.5;
}
.msg-bubble.bot  { background:var(--bg); border:1px solid var(--border); border-radius:1rem 1rem 1rem 0; }
.msg-bubble.user { background:var(--primary); color:#fff; border-radius:1rem 1rem 0 1rem; }
.typing { display:flex;gap:4px;align-items:center;padding:.5rem; }
.typing span { width:8px;height:8px;border-radius:50%;background:var(--text-muted);animation:bounce .6s infinite; }
.typing span:nth-child(2){animation-delay:.15s}
.typing span:nth-child(3){animation-delay:.3s}
@keyframes bounce{0%,60%,100%{transform:translateY(0)}30%{transform:translateY(-6px)}}
</style>

<script>
const apiKey = 'AIzaSyDeIJ2OmsLfrw0N_R4a4PHfwi7M3Cml-1E';
const chatHistory = [
    { role: 'user', parts: [{ text: 'Você é um tutor virtual educacional amigável chamado TutorIA. Ajude o aluno com dúvidas acadêmicas, explique conteúdos de forma clara e didática. Respostas em português, máximo 200 palavras por resposta.' }] },
    { role: 'model', parts: [{ text: 'Entendido! Estou pronto para ajudar. Pode me fazer perguntas!' }] }
];

function addMsg(texto, tipo) {
    const div = document.createElement('div');
    div.className = 'msg msg-' + tipo;
    div.innerHTML = `<div class="msg-bubble ${tipo}">${texto.replace(/\n/g,'<br>')}</div>`;
    document.getElementById('chatMsgs').appendChild(div);
    document.getElementById('chatMsgs').scrollTop = 9999;
    return div;
}

async function enviarChat() {
    const input = document.getElementById('chatInput');
    const texto = input.value.trim();
    if (!texto) return;
    input.value = '';

    addMsg(texto, 'user');
    chatHistory.push({ role: 'user', parts: [{ text: texto }] });

    const typing = addMsg('<span class="typing"><span></span><span></span><span></span></span>', 'bot');

    try {
        const resp = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${apiKey}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ contents: chatHistory })
        });
        const data = await resp.json();
        const resposta = data?.candidates?.[0]?.content?.parts?.[0]?.text || 'Desculpe, não consegui processar sua pergunta.';
        chatHistory.push({ role: 'model', parts: [{ text: resposta }] });
        typing.querySelector('.msg-bubble').innerHTML = resposta.replace(/\n/g,'<br>');
    } catch(e) {
        typing.querySelector('.msg-bubble').innerHTML = '❌ Erro ao conectar com a IA. Tente novamente.';
    }
    document.getElementById('chatMsgs').scrollTop = 9999;
}

async function analisarDesempenho() {
    const idTurma = document.getElementById('selTurmaAnalise').value;
    if (!idTurma) { alert('Selecione uma disciplina.'); return; }

    document.getElementById('analiseResultado').style.display = 'none';
    document.getElementById('analiseTexto').textContent = '⏳ Analisando...';
    document.getElementById('analiseResultado').style.display = 'block';

    try {
        const resp = await fetch(`../../controller/iaAluno.php?operacao=analisarDesempenho&idAluno=<?= $idAluno ?>&idTurma=${idTurma}`);
        const data = await resp.json();
        document.getElementById('analiseTexto').textContent = data.analise || 'Sem dados suficientes para análise.';
    } catch(e) {
        document.getElementById('analiseTexto').textContent = '❌ Erro ao conectar com a IA.';
    }
}

async function gerarRotina() {
    document.getElementById('rotinaResultado').style.display = 'none';
    document.getElementById('rotinaTexto').textContent = '⏳ Gerando rotina personalizada...';
    document.getElementById('rotinaResultado').style.display = 'block';

    try {
        const resp = await fetch(`../../controller/iaAluno.php?operacao=gerarRotina&idAluno=<?= $idAluno ?>`);
        const data = await resp.json();
        document.getElementById('rotinaTexto').textContent = data.rotina || 'Sem dados suficientes para gerar rotina.';
    } catch(e) {
        document.getElementById('rotinaTexto').textContent = '❌ Erro ao conectar com a IA.';
    }
}
</script>

        </main></div></div></body></html>
