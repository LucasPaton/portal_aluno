<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/usuarios.php';

$tipo   = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'aluno';
$busca  = filter_input(INPUT_GET, 'busca', FILTER_SANITIZE_SPECIAL_CHARS) ?: '';
$pagina = max(1, filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT) ?: 1);

$usuarios = listarUsuarios($tipo, $busca, $pagina);
$total    = contarUsuarios($tipo, $busca);
$totalPags = ceil($total / 20);

$labelTipo = ['aluno' => 'Alunos', 'professor' => 'Professores', 'admin' => 'Administradores'];

$pageTitle  = $labelTipo[$tipo] ?? 'Usuários';
$currentNav = $tipo === 'professor' ? 'professores' : 'alunos';
$depth      = 2;
include '../_layout.php';
?>

<div class="flex justify-between items-center mb-4">
    <div class="flex gap-2">
        <a href="?tipo=aluno"     class="btn <?= $tipo==='aluno'?'btn-primary':'btn-outline' ?>">👨‍🎓 Alunos</a>
        <a href="?tipo=professor" class="btn <?= $tipo==='professor'?'btn-primary':'btn-outline' ?>">👨‍🏫 Professores</a>
    </div>
    <a href="formUsuario.php?tipo=<?= $tipo ?>" class="btn btn-success">➕ Novo <?= ucfirst($tipo) ?></a>
</div>

<!-- PESQUISA -->
<div class="card mb-4">
    <div class="card-body">
        <form method="get" class="flex gap-3 items-center">
            <input type="hidden" name="tipo" value="<?= $tipo ?>">
            <input type="text" name="busca" class="form-control" placeholder="Buscar por nome, e-mail, matrícula ou CPF..."
                   value="<?= htmlspecialchars($busca) ?>" style="max-width:400px;">
            <button type="submit" class="btn btn-primary">🔍 Buscar</button>
            <?php if ($busca): ?>
                <a href="?tipo=<?= $tipo ?>" class="btn btn-outline">✕ Limpar</a>
            <?php endif; ?>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <span class="card-title"><?= $pageTitle ?></span>
        <span class="text-muted"><?= $total ?> encontrado(s)</span>
    </div>
    <div class="card-body" style="padding:0;">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>CPF</th>
                        <th>Cidade / UF</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($usuarios)): ?>
                    <tr><td colspan="7" class="text-center text-muted" style="padding:2rem;">Nenhum usuário encontrado.</td></tr>
                <?php else: ?>
                    <?php foreach ($usuarios as $u): ?>
                    <tr>
                        <td><span class="badge badge-primary"><?= htmlspecialchars($u['matricula']) ?></span></td>
                        <td><strong><?= htmlspecialchars($u['nome']) ?></strong></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td><?= htmlspecialchars($u['cpf'] ?: '—') ?></td>
                        <td><?= htmlspecialchars(($u['cidade'] ? $u['cidade'] . '/' . $u['estado'] : '—')) ?></td>
                        <td>
                            <?php if ($u['ativo']): ?>
                                <span class="badge badge-success">Ativo</span>
                            <?php else: ?>
                                <span class="badge badge-danger">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="perfilUsuario.php?id=<?= $u['idUsuario'] ?>" class="btn btn-sm btn-outline">👁 Ver</a>
                            <a href="formUsuario.php?id=<?= $u['idUsuario'] ?>&tipo=<?= $tipo ?>" class="btn btn-sm btn-info">✏️</a>
                            <?php if ($tipo === 'aluno'): ?>
                            <a href="arquivarUsuario.php?id=<?= $u['idUsuario'] ?>"
                               class="btn btn-sm btn-warning" title="Arquivar (retenção 10 anos)">🗃️</a>
                            <?php else: ?>
                            <a href="../../controller/controlador.php?operacao=desativarUsuario&id=<?= $u['idUsuario'] ?>&tipo=<?= $tipo ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Desativar este usuário?')">🗑</a>
                            <?php endif; ?>
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
                <a href="?tipo=<?= $tipo ?>&busca=<?= urlencode($busca) ?>&pagina=<?= $i ?>"
                   class="btn btn-sm <?= $i == $pagina ? 'btn-primary' : 'btn-outline' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

        </main>
    </div>
</div>
</body>
</html>
