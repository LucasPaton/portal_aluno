<?php
require_once '../../controller/validar.php';
validarTipo('admin');
require_once '../../model/materiais.php';
require_once '../../model/turmas.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$material = $id ? buscarMaterialPorId($id) : null;
$cursos = listarCursos();
$editando = $material !== null;

$pageTitle  = $editando ? 'Editar Material' : 'Novo Material';
$currentNav = 'materiais';
$depth      = 2;
include '../_layout.php';
?>

<div class="card" style="max-width:700px;">
    <div class="card-header">
        <span class="card-title"><?= $editando ? '✏️ Editar Material' : '➕ Novo Material Didático' ?></span>
    </div>
    <div class="card-body">
        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao" value="<?= $editando ? 'editarMaterial' : 'criarMaterial' ?>">
            <?php if ($editando): ?>
                <input type="hidden" name="idMaterial" value="<?= $material['idMaterial'] ?>">
            <?php endif; ?>

            <div class="form-group">
                <label class="form-label">Nome do Material *</label>
                <input type="text" name="nome" class="form-control" required
                       value="<?= htmlspecialchars($material['nome'] ?? '') ?>"
                       placeholder="Ex: Apostila de HTML5 e CSS3">
            </div>

            <div class="form-group">
                <label class="form-label">Descrição</label>
                <textarea name="descricao" class="form-control" rows="3"
                          placeholder="Descrição detalhada do material..."><?= htmlspecialchars($material['descricao'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-control">
                        <?php
                        $tipos = ['apostila'=>'📄 Apostila','video'=>'🎬 Vídeo','software'=>'💻 Software','equipamento'=>'🔧 Equipamento','livro'=>'📚 Livro','outro'=>'📋 Outro'];
                        foreach ($tipos as $k => $v):
                        ?>
                        <option value="<?= $k ?>" <?= ($material['tipo'] ?? '') === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Preço (R$)</label>
                    <input type="number" name="preco" class="form-control" step="0.01" min="0"
                           value="<?= $material['preco'] ?? '0.00' ?>"
                           placeholder="0.00">
                    <div class="form-hint">0 = gratuito</div>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Curso Relacionado</label>
                <select name="idCurso" class="form-control">
                    <option value="">— Nenhum (geral) —</option>
                    <?php foreach ($cursos as $c): ?>
                        <option value="<?= $c['idCurso'] ?>" <?= ($material['idCurso'] ?? '') == $c['idCurso'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">URL da Imagem</label>
                <input type="url" name="imagem" class="form-control"
                       value="<?= htmlspecialchars($material['imagem'] ?? '') ?>"
                       placeholder="https://exemplo.com/imagem.jpg">
            </div>

            <div class="flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary">💾 <?= $editando ? 'Atualizar' : 'Cadastrar' ?></button>
                <a href="materiais.php" class="btn btn-outline">← Voltar</a>
            </div>
        </form>
    </div>
</div>

        </main></div></div></body></html>
