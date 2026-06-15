<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/retencao.php';
require_once '../../model/usuarios.php';

$idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$idUsuario) { header('Location: inativos.php'); exit; }

$snapshot = buscarSnapshotUsuario($idUsuario);
$usuario  = buscarUsuarioPorId($idUsuario);

$pageTitle  = 'Histórico Arquivado: ' . ($usuario['nome'] ?? '');
$currentNav = 'inativos';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <div>
        <h2 style="font-size:1.25rem;font-weight:700;"><?= htmlspecialchars($usuario['nome'] ?? '—') ?></h2>
        <p class="text-muted"><?= htmlspecialchars($usuario['matricula'] ?? '') ?> · <?= htmlspecialchars($usuario['email'] ?? '') ?></p>
    </div>
    <a href="inativos.php" class="btn btn-outline btn-sm">← Voltar</a>
</div>

<?php if (!$snapshot): ?>
    <div class="alert alert-warning">Nenhum snapshot encontrado para este usuário. Ele pode ter sido desativado sem arquivamento formal.</div>
<?php else: ?>

<!-- CABEÇALHO DO ARQUIVAMENTO -->
<div class="card mb-4">
    <div class="card-header"><span class="card-title">📋 Informações do Arquivamento</span></div>
    <div class="card-body">
        <div class="form-row">
            <div>
                <span class="form-label">Tipo de evento</span>
                <span class="badge badge-primary" style="font-size:.9rem;"><?= ucfirst($snapshot['tipoEvento']) ?></span>
            </div>
            <div>
                <span class="form-label">Data do evento</span>
                <strong><?= date('d/m/Y', strtotime($snapshot['dataEvento'])) ?></strong>
            </div>
            <div>
                <span class="form-label">Arquivado em</span>
                <strong><?= date('d/m/Y H:i', strtotime($snapshot['dataArquivamento'])) ?></strong>
            </div>
            <div>
                <span class="form-label">Expira em</span>
                <?php
                    $dias = (strtotime($usuario['dataExpiracao']) - time()) / 86400;
                    $cor  = $dias < 0 ? 'danger' : ($dias < 180 ? 'warning' : 'success');
                ?>
                <span class="badge badge-<?= $cor ?>">
                    <?= date('d/m/Y', strtotime($usuario['dataExpiracao'])) ?>
                    (<?= $dias < 0 ? 'Expirado' : round($dias / 365, 1) . ' anos restantes' ?>)
                </span>
            </div>
        </div>
        <?php if ($snapshot['observacoes']): ?>
            <p class="mt-3 text-muted"><strong>Observações:</strong> <?= htmlspecialchars($snapshot['observacoes']) ?></p>
        <?php endif; ?>
    </div>
</div>

<?php
    $dados    = $snapshot['dadosSnapshot']  ?? [];
    $historico= $snapshot['historicoDados'] ?? [];
    $matriculas   = $historico['matriculas']    ?? [];
    $notas        = $historico['notas']         ?? [];
    $frequencias  = $historico['frequencias']   ?? [];
    $questionarios= $historico['questionarios'] ?? [];
?>

<!-- DADOS PESSOAIS -->
<div class="card mb-4">
    <div class="card-header"><span class="card-title">👤 Dados Pessoais (snapshot)</span></div>
    <div class="card-body">
        <div class="form-row">
            <div><span class="form-label">Nome</span><p><?= htmlspecialchars($dados['nome'] ?? '—') ?></p></div>
            <div><span class="form-label">CPF</span><p><?= htmlspecialchars($dados['cpf'] ?? '—') ?></p></div>
            <div><span class="form-label">RG</span><p><?= htmlspecialchars($dados['rg'] ?? '—') ?></p></div>
            <div><span class="form-label">Nascimento</span><p><?= $dados['dataNasc'] ? date('d/m/Y', strtotime($dados['dataNasc'])) : '—' ?></p></div>
        </div>
        <div class="form-row">
            <div><span class="form-label">E-mail</span><p><?= htmlspecialchars($dados['email'] ?? '—') ?></p></div>
            <div><span class="form-label">Telefone</span><p><?= htmlspecialchars($dados['telefone'] ?? '—') ?></p></div>
            <div><span class="form-label">Celular</span><p><?= htmlspecialchars($dados['celular'] ?? '—') ?></p></div>
        </div>
        <div class="form-row">
            <div style="grid-column:span 2">
                <span class="form-label">Endereço</span>
                <p><?= htmlspecialchars(implode(', ', array_filter([
                    $dados['logradouro'] ?? '', $dados['numero'] ?? '',
                    $dados['bairro'] ?? '', $dados['cidade'] ?? '',
                    $dados['estado'] ?? '', $dados['cep'] ?? ''
                ]))) ?: '—' ?></p>
            </div>
        </div>
    </div>
</div>

<!-- HISTÓRICO ACADÊMICO -->
<div class="card mb-4">
    <div class="card-header"><span class="card-title">📚 Histórico de Matrículas</span></div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($matriculas)): ?>
            <p class="text-muted text-center" style="padding:1.5rem;">Sem registros.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Disciplina</th><th>Curso</th><th>Código</th><th>Ano/Sem</th><th>Professor</th><th>Situação</th><th>Média</th></tr></thead>
            <tbody>
            <?php foreach ($matriculas as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['nomeDisciplina']) ?></td>
                <td><?= htmlspecialchars($m['nomeCurso']) ?></td>
                <td><span class="badge badge-muted"><?= htmlspecialchars($m['codigoDisciplina']) ?></span></td>
                <td><?= $m['ano'] ?>/<?= $m['semestre'] ?>º</td>
                <td><?= htmlspecialchars($m['nomeProfessor']) ?></td>
                <td><span class="badge badge-<?= $m['situacao']==='aprovado'?'success':($m['situacao']==='reprovado'?'danger':'info') ?>"><?= ucfirst($m['situacao']) ?></span></td>
                <td><?= $m['mediaFinal'] !== null ? number_format($m['mediaFinal'], 1) : '—' ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- NOTAS -->
<div class="card mb-4">
    <div class="card-header"><span class="card-title">📊 Histórico de Notas</span></div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($notas)): ?>
            <p class="text-muted text-center" style="padding:1.5rem;">Sem registros.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Disciplina</th><th>Ano/Sem</th><th>Tipo</th><th>Descrição</th><th>Nota</th><th>Peso</th></tr></thead>
            <tbody>
            <?php foreach ($notas as $n): ?>
            <tr>
                <td><?= htmlspecialchars($n['nomeDisciplina']) ?></td>
                <td><?= $n['ano'] ?>/<?= $n['semestre'] ?>º</td>
                <td><span class="badge badge-muted"><?= ucfirst($n['tipo']) ?></span></td>
                <td><?= htmlspecialchars($n['descricao'] ?: '—') ?></td>
                <td><span class="badge badge-<?= $n['nota'] >= 7 ? 'success' : ($n['nota'] >= 5 ? 'warning' : 'danger') ?>"><?= number_format($n['nota'], 1) ?>/<?= number_format($n['notaMaxima'], 0) ?></span></td>
                <td><?= $n['peso'] ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<!-- FREQUÊNCIAS RESUMIDAS -->
<div class="card mb-4">
    <div class="card-header"><span class="card-title">📋 Frequência por Disciplina</span></div>
    <div class="card-body" style="padding:0;">
        <?php if (empty($frequencias)): ?>
            <p class="text-muted text-center" style="padding:1.5rem;">Sem registros.</p>
        <?php else: ?>
        <table>
            <thead><tr><th>Disciplina</th><th>Ano/Sem</th><th>Total Aulas</th><th>Presenças</th><th>Faltas</th><th>Frequência</th></tr></thead>
            <tbody>
            <?php foreach ($frequencias as $f):
                $pct = $f['totalAulas'] > 0 ? round(($f['presencas'] / $f['totalAulas']) * 100) : 0;
            ?>
            <tr>
                <td><?= htmlspecialchars($f['nomeDisciplina']) ?></td>
                <td><?= $f['ano'] ?>/<?= $f['semestre'] ?>º</td>
                <td><?= $f['totalAulas'] ?></td>
                <td><?= $f['presencas'] ?></td>
                <td><?= $f['faltas'] ?></td>
                <td>
                    <div class="flex items-center gap-2">
                        <div class="progress-bar" style="width:80px;">
                            <div class="progress-fill <?= $pct >= 75 ? 'green' : ($pct >= 60 ? 'yellow' : 'red') ?>" style="width:<?= $pct ?>%"></div>
                        </div>
                        <strong><?= $pct ?>%</strong>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>

        </main></div></div></body></html>
