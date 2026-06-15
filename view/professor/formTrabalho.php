<?php
require_once '../../controller/validar.php';
validarTipo(['admin','professor']);
require_once '../../model/turmas.php';
require_once '../../model/forum.php';

$idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
$idTrabalho = filter_input(INPUT_GET, 'idTrabalho', FILTER_VALIDATE_INT);

$pageTitle  = 'Criar Trabalho';
$currentNav = 'turmas';
$depth      = 2;
include '../_layout.php';
?>

<div class="card">
    <div class="card-header">
        <span class="card-title">📁 Novo Trabalho</span>
        <a href="turmaDetalhe.php?id=<?= $idTurma ?>" class="btn btn-outline btn-sm">← Voltar</a>
    </div>
    <div class="card-body">
        <form method="post" action="../../controller/controlador.php">
            <input type="hidden" name="operacao" value="criarTrabalho">
            <input type="hidden" name="idTurma" value="<?= $idTurma ?>">
            <div class="form-group">
                <label class="form-label">Título *</label>
                <input type="text" name="titulo" class="form-control" required placeholder="Ex: Trabalho Final — Projeto de Sistema">
            </div>
            <div class="form-group">
                <label class="form-label">Descrição / instruções</label>
                <textarea name="descricao" class="form-control" rows="5" placeholder="Descreva os requisitos, critérios de avaliação, formato de entrega..."></textarea>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Data e hora de entrega</label>
                    <input type="datetime-local" name="dataEntrega" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Permitir entrega em atraso?</label>
                    <select name="permiteAtraso" class="form-control">
                        <option value="0">Não</option>
                        <option value="1">Sim</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 mt-2">
                <button type="submit" class="btn btn-primary">💾 Criar trabalho</button>
                <a href="turmaDetalhe.php?id=<?= $idTurma ?>" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </div>
</div>

        </main></div></div></body></html>
