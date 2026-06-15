<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/retencao.php';

$busca      = filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$tipoEvento = filter_input(INPUT_GET, 'tipoEvento', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$pagina     = max(1, filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1);

$inativos   = listarInativos($busca, $tipoEvento, $pagina);
$total      = contarInativos($busca, $tipoEvento);
$totalPags  = ceil($total / 20);
$stats      = estatisticasRetencao();

$pageTitle  = 'Registros Inativos — Retenção de Dados';
$currentNav = 'inativos';
$depth      = 2;
include '../_layout.php';
?>

<!-- STATS RETENÇÃO -->
<div class="stats-grid mb-4">
    <div class="stat-card">
        <div class="stat-icon green">✅</div>
        <div><div class="stat-value"><?= $stats['ativos'] ?></div><div class="stat-label">Alunos ativos</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">🗃️</div>
        <div><div class="stat-value"><?= $stats['inativos'] ?></div><div class="stat-label">Em retenção (10 anos)</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon yellow">⏳</div>
        <div><div class="stat-value"><?= $stats['expirandoEmBreve'] ?></div><div class="stat-label">Expirando em 180 dias</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">🗑️</div>
        <div><div class="stat-value"><?= $stats['expirados'] ?></div><div class="stat-label">Expirados (purgar)</div></div>
    </div>
</div>

<!-- POLÍTICA DE RETENÇÃO -->
<div class="alert alert-info mb-4">
    <strong>Política de Retenção de Dados:</strong> Registros acadêmicos são mantidos por <strong>10 anos</strong> após o desligamento/formatura do aluno, conforme exigência legal para instituições de ensino. Após esse prazo, os dados pessoais são anonimizados, preservando apenas o histórico acadêmico.
</div>

<!-- FILTROS -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center" style="flex-wrap:wrap;">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome, matrícula, CPF..."
                   value="<?= htmlspecialchars($busca) ?>" style="max-width:320px;">
            <select name="tipoEvento" class="form-control" style="max-width:200px;">
                <option value="">Todos os eventos</option>
                <option value="formatura"      <?= $tipoEvento==='formatura'      ?'selected':'' ?>>Formatura</option>
                <option value="desligamento"   <?= $tipoEvento==='desligamento'   ?'selected':'' ?>>Desligamento</option>
                <option value="transferencia"  <?= $tipoEvento==='transferencia'  ?'selected':'' ?>>Transferência</option>
                <option value="falecimento"    <?= $tipoEvento==='falecimento'    ?'selected':'' ?>>Falecimento</option>
                <option value="outros"         <?= $tipoEvento==='outros'         ?'selected':'' ?>>Outros</option>
            </select>
            <button type="submit" class="btn btn-primary">🔍 Buscar</button>
            <?php if ($busca || $tipoEvento): ?>
                <a href="inativos.php" class="btn btn-outline">✕ Limpar</a>
            <?php endif; ?>
            <?php if ($stats['expirados'] > 0): ?>
                <a href="../../controller/controlador.php?operacao=purgarExpirados"
                   class="btn btn-danger"
                   onclick="return confirm('Purgar dados pessoais de <?= $stats['expirados'] ?> registro(s) expirado(s)? Esta ação é irreversível.')">
                    🗑️ Purgar <?= $stats['expirados'] ?> expirado(s)
                </a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title">🗃️ Registros em Retenção</span>
        <span class="text-muted"><?= $total ?> registro(s)</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nome</th>
                        <th>CPF</th>
                        <th>Evento</th>
                        <th>Data Evento</th>
                        <th>Expira em</th>
                        <th>Dias restantes</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($inativos)): ?>
                    <tr><td colspan="9" class="text-center text-muted" style="padding:2rem;">Nenhum registro inativo.</td></tr>
                <?php else: ?>
                    <?php foreach ($inativos as $u):
                        $badgeClass = match($u['statusRetencao'] ?? 'retido') {
                            'expirado'  => 'danger',
                            'expirando' => 'warning',
                            default     => 'info'
                        };
                        $badgeLabel = match($u['statusRetencao'] ?? 'retido') {
                            'expirado'  => '🗑️ Expirado',
                            'expirando' => '⏳ Expirando',
                            default     => '🗃️ Retido'
                        };
                        $diasRestantes = $u['diasRestantes'] ?? null;
                    ?>
                    <tr>
                        <td><span class="badge badge-muted"><?= htmlspecialchars($u['matricula']) ?></span></td>
                        <td><strong><?= htmlspecialchars($u['nome']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($u['email']) ?></small></td>
                        <td><?= htmlspecialchars($u['cpf'] ?: '—') ?></td>
                        <td>
                            <?php if ($u['tipoEvento']): ?>
                                <span class="badge badge-primary"><?= ucfirst($u['tipoEvento']) ?></span>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $u['dataEvento'] ? date('d/m/Y', strtotime($u['dataEvento'])) : '—' ?></td>
                        <td><?= $u['dataExpiracao'] ? date('d/m/Y', strtotime($u['dataExpiracao'])) : '—' ?></td>
                        <td>
                            <?php if ($diasRestantes !== null): ?>
                                <?php if ($diasRestantes < 0): ?>
                                    <span class="badge badge-danger">Expirado</span>
                                <?php elseif ($diasRestantes <= 180): ?>
                                    <span class="badge badge-warning"><?= $diasRestantes ?> dias</span>
                                <?php else: ?>
                                    <span class="text-muted"><?= number_format($diasRestantes / 365, 1) ?> anos</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">—</span>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge badge-<?= $badgeClass ?>"><?= $badgeLabel ?></span></td>
                        <td>
                            <a href="snapshotUsuario.php?id=<?= $u['idUsuario'] ?>" class="btn btn-sm btn-outline">📋 Histórico</a>
                            <a href="../../controller/controlador.php?operacao=reativarUsuario&id=<?= $u['idUsuario'] ?>"
                               class="btn btn-sm btn-success"
                               onclick="return confirm('Reativar este usuário?')">↩ Reativar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- PAGINAÇÃO -->
        <?php if ($totalPags > 1): ?>
        <div class="flex gap-2 items-center" style="padding:1rem;justify-content:center;">
            <?php for ($i = 1; $i <= $totalPags; $i++): ?>
                <a href="?busca=<?= urlencode($busca) ?>&tipoEvento=<?= $tipoEvento ?>&pagina=<?= $i ?>"
                   class="btn btn-sm <?= $i == $pagina ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

        </main></div></div></body></html>
