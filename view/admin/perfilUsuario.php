<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/usuarios.php';
require_once '../../model/turmas.php';
require_once '../../model/frequencias.php';

$idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idUsuario) { header('Location: usuarios.php?tipo=aluno'); exit; }

$usuario = buscarUsuarioPorId($idUsuario);
if (!$usuario) { header('Location: usuarios.php?tipo=aluno'); exit; }

// Carregar turmas conforme o tipo
$turmas = [];
if ($usuario['tipo'] === 'aluno') {
    $turmas = listarTurmasAlunoV2($idUsuario);
} elseif ($usuario['tipo'] === 'professor') {
    $turmas = listarTurmasProfessor($idUsuario);
}

$pageTitle  = 'Perfil: ' . $usuario['nome'];
$currentNav = $usuario['tipo'] === 'professor' ? 'professores' : 'alunos';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <div class="flex gap-3 items-center">
        <!-- Avatar iniciais -->
        <div style="width:56px;height:56px;border-radius:50%;background:var(--primary);
                    display:flex;align-items:center;justify-content:center;
                    font-size:1.25rem;font-weight:700;color:#fff;flex-shrink:0;">
            <?= mb_strtoupper(mb_substr($usuario['nome'], 0, 1)) . mb_strtoupper(mb_substr(strrchr($usuario['nome'], ' ') ?: $usuario['nome'], 1, 1)) ?>
        </div>
        <div>
            <h2 style="font-size:1.25rem;font-weight:700;"><?= htmlspecialchars($usuario['nome']) ?></h2>
            <p class="text-muted">
                <span class="badge badge-<?= $usuario['tipo']==='admin'?'danger':($usuario['tipo']==='professor'?'success':'info') ?>">
                    <?= ucfirst($usuario['tipo']) ?>
                </span>
                <span style="margin-left:.5rem;"><?= htmlspecialchars($usuario['matricula']) ?></span>
                <?php if (!$usuario['ativo']): ?>
                    <span class="badge badge-danger" style="margin-left:.5rem;">Inativo</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    <div class="flex gap-2">
        <a href="formUsuario.php?id=<?= $idUsuario ?>&tipo=<?= $usuario['tipo'] ?>" class="btn btn-info btn-sm">✏️ Editar</a>
        <?php if ($usuario['ativo']): ?>
            <a href="arquivarUsuario.php?id=<?= $idUsuario ?>" class="btn btn-warning btn-sm">🗃️ Arquivar</a>
        <?php endif; ?>
        <a href="usuarios.php?tipo=<?= $usuario['tipo'] ?>" class="btn btn-outline btn-sm">← Voltar</a>
    </div>
</div>

<div class="grid-2 mb-4">
    <!-- DADOS PESSOAIS -->
    <div class="card">
        <div class="card-header"><span class="card-title">👤 Dados Pessoais</span></div>
        <div class="card-body">
            <table style="width:100%;font-size:.875rem;">
                <tr><td class="text-muted" style="width:40%;padding:.375rem 0;">E-mail</td><td><?= htmlspecialchars($usuario['email']) ?></td></tr>
                <tr><td class="text-muted" style="padding:.375rem 0;">CPF</td><td><?= htmlspecialchars($usuario['cpf'] ?: '—') ?></td></tr>
                <tr><td class="text-muted" style="padding:.375rem 0;">RG</td><td><?= htmlspecialchars($usuario['rg'] ?: '—') ?></td></tr>
                <tr><td class="text-muted" style="padding:.375rem 0;">Nascimento</td><td><?= $usuario['dataNasc'] ? date('d/m/Y', strtotime($usuario['dataNasc'])) : '—' ?></td></tr>
                <tr><td class="text-muted" style="padding:.375rem 0;">Sexo</td><td><?= htmlspecialchars($usuario['sexo'] ?: '—') ?></td></tr>
                <tr><td class="text-muted" style="padding:.375rem 0;">Telefone</td><td><?= htmlspecialchars($usuario['telefone'] ?: '—') ?></td></tr>
                <tr><td class="text-muted" style="padding:.375rem 0;">Celular</td><td><?= htmlspecialchars($usuario['celular'] ?: '—') ?></td></tr>
                <tr><td class="text-muted" style="padding:.375rem 0;">Cadastro</td><td><?= date('d/m/Y', strtotime($usuario['dataCriacao'])) ?></td></tr>
            </table>
        </div>
    </div>

    <!-- ENDEREÇO -->
    <div class="card">
        <div class="card-header"><span class="card-title">📍 Endereço</span></div>
        <div class="card-body">
            <?php
            $end = array_filter([
                $usuario['logradouro'], $usuario['numero'],
                $usuario['complemento'], $usuario['bairro'],
                $usuario['cidade'], $usuario['estado'], $usuario['cep']
            ]);
            ?>
            <?php if (empty($end)): ?>
                <p class="text-muted">Endereço não cadastrado.</p>
            <?php else: ?>
            <table style="width:100%;font-size:.875rem;">
                <?php if ($usuario['logradouro']): ?>
                <tr><td class="text-muted" style="width:40%;padding:.375rem 0;">Logradouro</td>
                    <td><?= htmlspecialchars($usuario['logradouro']) ?><?= $usuario['numero'] ? ', '.$usuario['numero'] : '' ?></td></tr>
                <?php endif; ?>
                <?php if ($usuario['complemento']): ?>
                <tr><td class="text-muted" style="padding:.375rem 0;">Complemento</td>
                    <td><?= htmlspecialchars($usuario['complemento']) ?></td></tr>
                <?php endif; ?>
                <?php if ($usuario['bairro']): ?>
                <tr><td class="text-muted" style="padding:.375rem 0;">Bairro</td>
                    <td><?= htmlspecialchars($usuario['bairro']) ?></td></tr>
                <?php endif; ?>
                <?php if ($usuario['cidade']): ?>
                <tr><td class="text-muted" style="padding:.375rem 0;">Cidade / UF</td>
                    <td><?= htmlspecialchars($usuario['cidade']) ?><?= $usuario['estado'] ? ' / '.$usuario['estado'] : '' ?></td></tr>
                <?php endif; ?>
                <?php if ($usuario['cep']): ?>
                <tr><td class="text-muted" style="padding:.375rem 0;">CEP</td>
                    <td><?= htmlspecialchars($usuario['cep']) ?></td></tr>
                <?php endif; ?>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- TURMAS / DISCIPLINAS -->
<?php if (!empty($turmas)): ?>
<div class="card">
    <div class="card-header">
        <span class="card-title">
            <?= $usuario['tipo'] === 'professor' ? '🏫 Turmas que leciona' : '📚 Disciplinas matriculadas' ?>
        </span>
    </div>
    <div class="card-body" style="padding:0;">
        <table>
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th>Código</th>
                    <th>Curso</th>
                    <th>Ano/Sem</th>
                    <?php if ($usuario['tipo'] === 'aluno'): ?>
                        <th>Faltas</th>
                        <th>Situação</th>
                    <?php else: ?>
                        <th>Alunos</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($turmas as $t): ?>
            <tr>
                <td><?= htmlspecialchars($t['nomeDisciplina']) ?></td>
                <td><span class="badge badge-muted"><?= htmlspecialchars($t['codigoDisciplina']) ?></span></td>
                <td><?= htmlspecialchars($t['nomeCurso']) ?></td>
                <td><?= $t['ano'] ?>/<?= $t['semestre'] ?>º</td>
                <?php if ($usuario['tipo'] === 'aluno'): ?>
                    <td><?= $t['totalFaltas'] ?>/<?= $t['limiteHorasFalta'] ?>h</td>
                    <td><span class="badge badge-<?= $t['situacao']==='aprovado'?'success':($t['situacao']==='reprovado'?'danger':'info') ?>"><?= ucfirst($t['situacao']) ?></span></td>
                <?php else: ?>
                    <td><?= $t['totalAlunos'] ?></td>
                <?php endif; ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

        </main></div></div></body></html>
