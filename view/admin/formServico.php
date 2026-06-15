<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/servicos.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$servico = $id ? buscarServicoPorId($id) : null;
$editando = $servico !== null;

$pageTitle  = $editando ? 'Editar Serviço' : 'Novo Serviço';
$currentNav = 'servicos';
$depth      = 2;
include '../_layout.php';
?>

<div class="card" style="max-width:700px;">
    <div class="card-header">
        <span class="card-title"><?= $editando ? '✏️ Editar Serviço' : '➕ Novo Serviço Acadêmico' ?></span>
    </div>
    <div class="card-body">
        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao" value="<?= $editando ? 'editarServico' : 'criarServico' ?>">
            <?php if ($editando): ?>
                <input type="hidden" name="idServico" value="<?= $servico['idServico'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Nome do Serviço *</label>
                <input type="text" name="nome" class="form-control" required
                       value="<?= htmlspecialchars($servico['nome'] ?? '') ?>"
                       placeholder="Ex: Monitoria de Programação">
            </div>

            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" class="form-control" rows="3"
                          placeholder="Descrição detalhada do serviço..."><?= htmlspecialchars($servico['descricao'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Categoria</label>
                    <select name="categoria" class="form-control">
                        <?php
                        $categorias = ['monitoria'=>'📖 Monitoria','biblioteca'=>'📚 Biblioteca','laboratorio'=>'🔬 Laboratório','secretaria'=>'🏛️ Secretaria','orientacao'=>'🧭 Orientação','outro'=>'📋 Outro'];
                        foreach ($categorias as $k => $v):
                        ?>
                        <option value="<?= $k ?>" <?= ($servico['categoria'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Valor Estimado (R$)</label>
                    <input type="number" name="valorEstimado" class="form-control" step="0.01" min="0"
                           value="<?= $servico['valorEstimado'] ?? '0.00' ?>">
                    <div class="form-hint">0 = gratuito</div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Horário de Funcionamento</label>
                    <input type="text" name="horarioFunc" class="form-control"
                           value="<?= htmlspecialchars($servico['horarioFunc'] ?? '') ?>"
                           placeholder="Ex: Seg a Sex, 8h-17h">
                </div>
                <div class="form-group">
                    <label class="form-label">Responsável</label>
                    <input type="text" name="responsavel" class="form-control"
                           value="<?= htmlspecialchars($servico['responsavel'] ?? '') ?>"
                           placeholder="Ex: Prof. Silva">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">URL da Imagem</label>
                <input type="url" name="imagem" class="form-control"
                       value="<?= htmlspecialchars($servico['imagem'] ?? '') ?>"
                       placeholder="https://exemplo.com/imagem.jpg">
            </div>

            <div class="flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary">💾 <?= $editando ? 'Atualizar' : 'Cadastrar' ?></button>
                <a href="servicos.php" class="btn btn-outline">← Voltar</a>
            </div>
        </form>
    </div>
</div>

        </main></div></div></body></html>
