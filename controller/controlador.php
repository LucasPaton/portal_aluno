<?php
// ============================================================
// ARQUIVO: controller/controlador.php
// Ponto central do sistema - VERSÃO CORRIGIDA
// ============================================================

// Iniciar sessão ANTES de qualquer output ou include
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/usuarios.php';
require_once __DIR__ . '/../model/turmas.php';
require_once __DIR__ . '/../model/frequencias.php';
require_once __DIR__ . '/../model/questionarios.php';
require_once __DIR__ . '/../model/forum.php';
require_once __DIR__ . '/../model/retencao.php';

// Capturar operação (POST tem prioridade sobre GET)
$operacao = '';
if (!empty($_POST['operacao'])) {
    $operacao = htmlspecialchars(trim($_POST['operacao']));
} elseif (!empty($_GET['operacao'])) {
    $operacao = htmlspecialchars(trim($_GET['operacao']));
}

// Operações que não precisam de sessão
$operacoesPublicas = ['login'];

// Verificar sessão para operações protegidas
if (!in_array($operacao, $operacoesPublicas) && $operacao !== 'logout') {
    if (empty($_SESSION['idUsuario'])) {
        header("Location: ../view/formlogin.php?erro=Sessão expirada. Faça login para continuar.");
        exit;
    }
}

// ============================================================
// LOGIN / LOGOUT
// ============================================================

if ($operacao === 'login') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $senha = $_POST['senha'] ?? '';
    $resultado = autenticarUsuario($email, $senha);
    if ($resultado === ERRO_USUARIO_NAO_ENCONTRADO || $resultado === ERRO_SENHA) {
        header("Location: ../view/formlogin.php?erro=E-mail ou senha inválidos.");
        exit;
    }
    $_SESSION['idUsuario'] = $resultado['idUsuario'];
    $_SESSION['nome']      = $resultado['nome'];
    $_SESSION['tipo']      = $resultado['tipo'];
    $_SESSION['matricula'] = $resultado['matricula'];
    $_SESSION['email']     = $resultado['email'];
    $destino = match($resultado['tipo']) {
        'admin'     => '../view/admin/dashboard.php',
        'professor' => '../view/professor/dashboard.php',
        'aluno'     => '../view/aluno/dashboard.php',
        default     => '../view/formlogin.php'
    };
    header("Location: $destino");
    exit;
}

if ($operacao === 'logout') {
    session_unset();
    session_destroy();
    header("Location: ../view/formlogin.php?msg=Você saiu do sistema.");
    exit;
}

// ============================================================
// USUÁRIOS
// ============================================================

if ($operacao === 'criarUsuario') {
    // Forçar tipo válido: admin só pode criar professor ou aluno
    $tipoPermitido = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS);
    if (!in_array($tipoPermitido, ['aluno', 'professor'])) {
        $tipoPermitido = 'aluno'; // segurança: nunca cria admin por formulário
    }
    $dados = [
        'nome'       => filter_input(INPUT_POST, 'nome',       FILTER_SANITIZE_SPECIAL_CHARS),
        'email'      => filter_input(INPUT_POST, 'email',      FILTER_SANITIZE_EMAIL),
        'tipo'       => $tipoPermitido,
        'senha'      => $_POST['senha'] ?? '123456',
        'cpf'        => filter_input(INPUT_POST, 'cpf',        FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'rg'         => filter_input(INPUT_POST, 'rg',         FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'dataNasc'   => filter_input(INPUT_POST, 'dataNasc') ?? '',
        'sexo'       => filter_input(INPUT_POST, 'sexo',       FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'telefone'   => filter_input(INPUT_POST, 'telefone',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'celular'    => filter_input(INPUT_POST, 'celular',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'logradouro' => filter_input(INPUT_POST, 'logradouro', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'numero'     => filter_input(INPUT_POST, 'numero',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'complemento'=> filter_input(INPUT_POST, 'complemento',FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'bairro'     => filter_input(INPUT_POST, 'bairro',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'cidade'     => filter_input(INPUT_POST, 'cidade',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'estado'     => filter_input(INPUT_POST, 'estado',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'cep'        => filter_input(INPUT_POST, 'cep',        FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    ];
    $retorno = criarUsuario($dados);
    if ($retorno === SUCESSO) {
        header("Location: ../view/admin/usuarios.php?tipo={$dados['tipo']}&msg=Usuário criado com sucesso!");
    } elseif ($retorno === ERRO_USUARIO_EXISTENTE) {
        header("Location: ../view/admin/formUsuario.php?tipo={$dados['tipo']}&msg=E-mail já cadastrado.");
    } else {
        header("Location: ../view/admin/formUsuario.php?tipo={$dados['tipo']}&msg=" . urlencode($retorno));
    }
    exit;
}

if ($operacao === 'atualizarUsuario') {
    $id   = filter_input(INPUT_POST, 'idUsuario', FILTER_VALIDATE_INT);
    $tipo = filter_input(INPUT_POST, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'aluno';
    $dados = [
        'nome'       => filter_input(INPUT_POST, 'nome',       FILTER_SANITIZE_SPECIAL_CHARS),
        'cpf'        => filter_input(INPUT_POST, 'cpf',        FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'rg'         => filter_input(INPUT_POST, 'rg',         FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'dataNasc'   => filter_input(INPUT_POST, 'dataNasc') ?? '',
        'sexo'       => filter_input(INPUT_POST, 'sexo',       FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'telefone'   => filter_input(INPUT_POST, 'telefone',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'celular'    => filter_input(INPUT_POST, 'celular',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'logradouro' => filter_input(INPUT_POST, 'logradouro', FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'numero'     => filter_input(INPUT_POST, 'numero',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'complemento'=> filter_input(INPUT_POST, 'complemento',FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'bairro'     => filter_input(INPUT_POST, 'bairro',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'cidade'     => filter_input(INPUT_POST, 'cidade',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'estado'     => filter_input(INPUT_POST, 'estado',     FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
        'cep'        => filter_input(INPUT_POST, 'cep',        FILTER_SANITIZE_SPECIAL_CHARS) ?? '',
    ];
    atualizarUsuario($id, $dados);
    header("Location: ../view/admin/perfilUsuario.php?id=$id&msg=Dados atualizados!");
    exit;
}

if ($operacao === 'desativarUsuario') {
    $id   = filter_input(INPUT_GET, 'id',   FILTER_VALIDATE_INT);
    $tipo = filter_input(INPUT_GET, 'tipo', FILTER_SANITIZE_SPECIAL_CHARS) ?: 'aluno';
    desativarUsuario($id);
    header("Location: ../view/admin/usuarios.php?tipo=$tipo&msg=Usuário desativado.");
    exit;
}

// ============================================================
// CURSOS E DISCIPLINAS (admin)
// ============================================================

if ($operacao === 'criarCurso') {
    $nome         = filter_input(INPUT_POST, 'nome',         FILTER_SANITIZE_SPECIAL_CHARS);
    $codigo       = filter_input(INPUT_POST, 'codigo',       FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao    = filter_input(INPUT_POST, 'descricao',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $cargaHoraria = filter_input(INPUT_POST, 'cargaHoraria', FILTER_VALIDATE_INT) ?: 0;
    $duracao      = filter_input(INPUT_POST, 'duracao',      FILTER_VALIDATE_INT) ?: 4;
    $retorno = criarCurso($nome, $codigo, $descricao, $cargaHoraria, $duracao);
    if ($retorno === SUCESSO) {
        header("Location: ../view/admin/cursos.php?aba=cursos&msg=Curso criado com sucesso!");
    } else {
        header("Location: ../view/admin/cursos.php?aba=cursos&msg=" . urlencode($retorno));
    }
    exit;
}

if ($operacao === 'editarCurso') {
    $id           = filter_input(INPUT_POST, 'idCurso',      FILTER_VALIDATE_INT);
    $nome         = filter_input(INPUT_POST, 'nome',         FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao    = filter_input(INPUT_POST, 'descricao',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $cargaHoraria = filter_input(INPUT_POST, 'cargaHoraria', FILTER_VALIDATE_INT) ?: 0;
    $duracao      = filter_input(INPUT_POST, 'duracao',      FILTER_VALIDATE_INT) ?: 4;
    editarCurso($id, $nome, $descricao, $cargaHoraria, $duracao);
    header("Location: ../view/admin/cursos.php?aba=cursos&msg=Curso atualizado!");
    exit;
}

if ($operacao === 'criarDisciplina') {
    $idCurso      = filter_input(INPUT_POST, 'idCurso',      FILTER_VALIDATE_INT);
    $nome         = filter_input(INPUT_POST, 'nome',         FILTER_SANITIZE_SPECIAL_CHARS);
    $codigo       = filter_input(INPUT_POST, 'codigo',       FILTER_SANITIZE_SPECIAL_CHARS);
    $cargaHoraria = filter_input(INPUT_POST, 'cargaHoraria', FILTER_VALIDATE_INT) ?: 80;
    $ementa       = filter_input(INPUT_POST, 'ementa',       FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $periodo      = filter_input(INPUT_POST, 'periodo',      FILTER_VALIDATE_INT) ?: 1;
    $retorno = criarDisciplina($idCurso, $nome, $codigo, $cargaHoraria, $ementa, $periodo);
    if ($retorno === SUCESSO) {
        header("Location: ../view/admin/cursos.php?aba=disciplinas&msg=Disciplina criada!");
    } else {
        header("Location: ../view/admin/cursos.php?aba=disciplinas&msg=" . urlencode($retorno));
    }
    exit;
}

if ($operacao === 'editarDisciplina') {
    $id           = filter_input(INPUT_POST, 'idDisciplina', FILTER_VALIDATE_INT);
    $nome         = filter_input(INPUT_POST, 'nome',         FILTER_SANITIZE_SPECIAL_CHARS);
    $cargaHoraria = filter_input(INPUT_POST, 'cargaHoraria', FILTER_VALIDATE_INT) ?: 80;
    $ementa       = filter_input(INPUT_POST, 'ementa',       FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $periodo      = filter_input(INPUT_POST, 'periodo',      FILTER_VALIDATE_INT) ?: 1;
    editarDisciplina($id, $nome, $cargaHoraria, $ementa, $periodo);
    header("Location: ../view/admin/cursos.php?aba=disciplinas&msg=Disciplina atualizada!");
    exit;
}

// ============================================================
// TURMAS (nova estrutura: turma = sala de aula com módulo/curso)
// ============================================================

if ($operacao === 'criarTurma') {
    $idCurso    = filter_input(INPUT_POST, 'idCurso',    FILTER_VALIDATE_INT);
    $idProfResp = filter_input(INPUT_POST, 'idProfResp', FILTER_VALIDATE_INT); // professor responsável opcional
    $codigo     = filter_input(INPUT_POST, 'codigo',     FILTER_SANITIZE_SPECIAL_CHARS);
    $modulo     = filter_input(INPUT_POST, 'modulo',     FILTER_VALIDATE_INT) ?: 1;
    $turno      = filter_input(INPUT_POST, 'turno',      FILTER_SANITIZE_SPECIAL_CHARS) ?: 'M';
    $ano        = filter_input(INPUT_POST, 'ano',        FILTER_VALIDATE_INT) ?: (int)date('Y');
    $semestre   = filter_input(INPUT_POST, 'semestre',   FILTER_VALIDATE_INT) ?: 1;
    $horario    = filter_input(INPUT_POST, 'horario',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $sala       = filter_input(INPUT_POST, 'sala',       FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $limite     = filter_input(INPUT_POST, 'limiteAlunos', FILTER_VALIDATE_INT) ?: 40;
    $retorno = criarTurmaCompleta($idCurso, $idProfResp, $codigo, $modulo, $turno, $ano, $semestre, $horario, $sala, $limite);
    if ($retorno === SUCESSO) {
        header("Location: ../view/admin/turmas.php?msg=Turma criada com sucesso!");
    } else {
        header("Location: ../view/admin/turmas.php?msg=" . urlencode($retorno));
    }
    exit;
}

if ($operacao === 'matricularAluno') {
    $idAluno = filter_input(INPUT_POST, 'idAluno', FILTER_VALIDATE_INT);
    $idTurma = filter_input(INPUT_POST, 'idTurma', FILTER_VALIDATE_INT);
    $retorno = matricularAluno($idAluno, $idTurma);
    if ($retorno === SUCESSO) {
        header("Location: ../view/admin/turmaDetalhe.php?id=$idTurma&msg=Aluno matriculado!");
    } else {
        header("Location: ../view/admin/turmaDetalhe.php?id=$idTurma&msg=" . urlencode($retorno));
    }
    exit;
}

if ($operacao === 'removerMatricula') {
    $idMatricula = filter_input(INPUT_GET, 'idMatricula', FILTER_VALIDATE_INT);
    $idTurma     = filter_input(INPUT_GET, 'idTurma',     FILTER_VALIDATE_INT);
    removerMatricula($idMatricula);
    header("Location: ../view/admin/turmaDetalhe.php?id=$idTurma&msg=Matrícula removida.");
    exit;
}

if ($operacao === 'promoverAluno') {
    // Move aluno de um módulo para o próximo
    $idAluno     = filter_input(INPUT_POST, 'idAluno',     FILTER_VALIDATE_INT);
    $idTurmaAtual= filter_input(INPUT_POST, 'idTurmaAtual',FILTER_VALIDATE_INT);
    $idTurmaNova = filter_input(INPUT_POST, 'idTurmaNova', FILTER_VALIDATE_INT);
    promoverAluno($idAluno, $idTurmaAtual, $idTurmaNova);
    header("Location: ../view/admin/turmaDetalhe.php?id=$idTurmaAtual&msg=Aluno promovido para o próximo módulo!");
    exit;
}

// ============================================================
// FREQUÊNCIA (professor)
// ============================================================

if ($operacao === 'registrarAula') {
    $idTurma    = filter_input(INPUT_POST, 'idTurma',    FILTER_VALIDATE_INT);
    $dataAula   = filter_input(INPUT_POST, 'dataAula');
    $horaInicio = filter_input(INPUT_POST, 'horaInicio') ?? '';
    $horaFim    = filter_input(INPUT_POST, 'horaFim') ?? '';
    $conteudo   = filter_input(INPUT_POST, 'conteudo',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $idAula = registrarAula($idTurma, $dataAula, $horaInicio, $horaFim, $conteudo);
    header("Location: ../view/professor/frequencia.php?idTurma=$idTurma&idAula=$idAula&msg=Aula registrada! Marque as presenças.");
    exit;
}

if ($operacao === 'registrarFrequencias') {
    $idAula  = filter_input(INPUT_POST, 'idAula',  FILTER_VALIDATE_INT);
    $idTurma = filter_input(INPUT_POST, 'idTurma', FILTER_VALIDATE_INT);
    $presentes = $_POST['presentes'] ?? [];
    $presentes = array_map('intval', $presentes);
    $todosAlunos = listarAlunosTurma($idTurma);
    $presencas = [];
    foreach ($todosAlunos as $a) {
        $presencas[] = [
            'idAluno'  => $a['idUsuario'],
            'presente' => in_array($a['idUsuario'], $presentes) ? 1 : 0
        ];
    }
    registrarFrequencias($idAula, $idTurma, $presencas);
    header("Location: ../view/professor/frequencia.php?idTurma=$idTurma&msg=Frequência registrada!");
    exit;
}

// ============================================================
// NOTAS (professor)
// ============================================================

if ($operacao === 'lancarNota') {
    $idMatricula = filter_input(INPUT_POST, 'idMatricula', FILTER_VALIDATE_INT);
    $idTurma     = filter_input(INPUT_POST, 'idTurma',     FILTER_VALIDATE_INT);
    $idAluno     = filter_input(INPUT_POST, 'idAluno',     FILTER_VALIDATE_INT);
    $tipo        = filter_input(INPUT_POST, 'tipo',        FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao   = filter_input(INPUT_POST, 'descricao',   FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $nota        = filter_input(INPUT_POST, 'nota',        FILTER_VALIDATE_FLOAT);
    $notaMax     = filter_input(INPUT_POST, 'notaMaxima',  FILTER_VALIDATE_FLOAT) ?: 10;
    $peso        = filter_input(INPUT_POST, 'peso',        FILTER_VALIDATE_FLOAT) ?: 1;
    lancarNota($idMatricula, $idTurma, $idAluno, $tipo, $descricao, $nota, $notaMax, $peso);
    header("Location: ../view/professor/notas.php?idTurma=$idTurma&msg=Nota lançada!");
    exit;
}

// ============================================================
// QUESTIONÁRIOS (professor)
// ============================================================

if ($operacao === 'criarQuestionario') {
    $idTurma     = filter_input(INPUT_POST, 'idTurma',    FILTER_VALIDATE_INT);
    $idProfessor = $_SESSION['idUsuario'];
    $titulo      = filter_input(INPUT_POST, 'titulo',     FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao   = filter_input(INPUT_POST, 'descricao',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $dataInicio  = filter_input(INPUT_POST, 'dataInicio') ?? null;
    $dataFim     = filter_input(INPUT_POST, 'dataFim') ?? null;
    $tempo       = filter_input(INPUT_POST, 'tempoLimite',FILTER_VALIDATE_INT) ?: 0;
    $tentativas  = filter_input(INPUT_POST, 'tentativas', FILTER_VALIDATE_INT) ?: 1;
    $embQ        = filter_input(INPUT_POST, 'embaralharQuestoes',    FILTER_VALIDATE_INT) ?: 0;
    $embA        = filter_input(INPUT_POST, 'embaralharAlternativas',FILTER_VALIDATE_INT) ?: 0;
    $idQuestionario = criarQuestionario($idTurma, $idProfessor, $titulo, $descricao, $dataInicio, $dataFim, $tempo, $tentativas, $embQ, $embA);
    if (is_numeric($idQuestionario)) {
        header("Location: ../view/professor/questionario.php?id=$idQuestionario&msg=Questionário criado! Adicione questões.");
    } else {
        header("Location: ../view/professor/formQuestionario.php?idTurma=$idTurma&msg=" . urlencode($idQuestionario));
    }
    exit;
}

if ($operacao === 'adicionarQuestao') {
    $idQuestionario = filter_input(INPUT_POST, 'idQuestionario', FILTER_VALIDATE_INT);
    $enunciado      = filter_input(INPUT_POST, 'enunciado', FILTER_SANITIZE_SPECIAL_CHARS);
    $tipo           = filter_input(INPUT_POST, 'tipo',      FILTER_SANITIZE_SPECIAL_CHARS);
    $pontos         = filter_input(INPUT_POST, 'pontos',    FILTER_VALIDATE_FLOAT) ?: 1;
    $textos   = $_POST['alternativa'] ?? [];
    $corretas = $_POST['correta'] ?? [];
    $alternativas = [];
    foreach ($textos as $i => $texto) {
        if (trim($texto)) {
            $alternativas[] = ['texto' => htmlspecialchars($texto), 'correta' => in_array((string)$i, array_map('strval', $corretas))];
        }
    }
    adicionarQuestao($idQuestionario, $enunciado, $tipo, $pontos, $alternativas);
    header("Location: ../view/professor/questionario.php?id=$idQuestionario&msg=Questão adicionada!");
    exit;
}

if ($operacao === 'publicarQuestionario') {
    $id      = filter_input(INPUT_GET, 'id',      FILTER_VALIDATE_INT);
    $idTurma = filter_input(INPUT_GET, 'idTurma', FILTER_VALIDATE_INT);
    publicarQuestionario($id);
    header("Location: ../view/professor/turmaDetalhe.php?id=$idTurma&msg=Questionário publicado!");
    exit;
}

if ($operacao === 'gerarIAQuestionario') {
    $idQuestionario = filter_input(INPUT_POST, 'idQuestionario', FILTER_VALIDATE_INT);
    $tema           = filter_input(INPUT_POST, 'tema',      FILTER_SANITIZE_SPECIAL_CHARS);
    $quantidade     = filter_input(INPUT_POST, 'quantidade',FILTER_VALIDATE_INT) ?: 5;
    $nivel          = filter_input(INPUT_POST, 'nivel',     FILTER_SANITIZE_SPECIAL_CHARS) ?: 'médio';
    $retorno = gerarQuestionarioComIA($tema, $quantidade, $nivel, $idQuestionario);
    if (is_numeric($retorno)) {
        header("Location: ../view/professor/questionario.php?id=$idQuestionario&msg=$retorno questões geradas pela IA!");
    } else {
        header("Location: ../view/professor/questionario.php?id=$idQuestionario&msg=" . urlencode($retorno));
    }
    exit;
}

// ============================================================
// FÓRUM / AVISOS
// ============================================================

if ($operacao === 'criarPost') {
    $idAutor   = $_SESSION['idUsuario'];
    $idTurma   = filter_input(INPUT_POST, 'idTurma',   FILTER_VALIDATE_INT) ?: null;
    $titulo    = filter_input(INPUT_POST, 'titulo',    FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $conteudo  = filter_input(INPUT_POST, 'conteudo',  FILTER_SANITIZE_SPECIAL_CHARS);
    $tipo      = filter_input(INPUT_POST, 'tipo',      FILTER_SANITIZE_SPECIAL_CHARS) ?: 'aviso';
    $idPostPai = filter_input(INPUT_POST, 'idPostPai', FILTER_VALIDATE_INT) ?: null;
    $fixado    = filter_input(INPUT_POST, 'fixado',    FILTER_VALIDATE_INT) ?: 0;
    criarPost($idAutor, $conteudo, $titulo, $tipo, $idTurma, $idPostPai, $fixado);
    if ($idTurma) {
        $perfil = $_SESSION['tipo'];
        header("Location: ../view/$perfil/forum.php?idTurma=$idTurma&msg=Post criado!");
    } else {
        header("Location: ../view/admin/avisos.php?msg=Aviso publicado!");
    }
    exit;
}

// ============================================================
// TRABALHOS
// ============================================================

if ($operacao === 'criarTrabalho') {
    $idTurma     = filter_input(INPUT_POST, 'idTurma',   FILTER_VALIDATE_INT);
    $idProfessor = $_SESSION['idUsuario'];
    $titulo      = filter_input(INPUT_POST, 'titulo',    FILTER_SANITIZE_SPECIAL_CHARS);
    $descricao   = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $dataEntrega = filter_input(INPUT_POST, 'dataEntrega') ?? null;
    $atraso      = filter_input(INPUT_POST, 'permiteAtraso', FILTER_VALIDATE_INT) ?: 0;
    criarTrabalho($idTurma, $idProfessor, $titulo, $descricao, $dataEntrega, $atraso);
    header("Location: ../view/professor/turmaDetalhe.php?id=$idTurma&msg=Trabalho criado!");
    exit;
}

if ($operacao === 'entregarTrabalho') {
    $idTrabalho = filter_input(INPUT_POST, 'idTrabalho', FILTER_VALIDATE_INT);
    $idAluno    = $_SESSION['idUsuario'];
    $idTurma    = filter_input(INPUT_POST, 'idTurma',    FILTER_VALIDATE_INT);
    $comentario = filter_input(INPUT_POST, 'comentario', FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $nomeArquivo = '';
    $caminhoArquivo = '';
    if (isset($_FILES['arquivo']) && $_FILES['arquivo']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['arquivo']['name'], PATHINFO_EXTENSION));
        $permitidos = ['pdf','doc','docx','zip','rar','txt','png','jpg'];
        if (in_array($ext, $permitidos)) {
            $nomeArquivo = basename($_FILES['arquivo']['name']);
            $nomeUnico   = uniqid('trab_') . ".$ext";
            $destino     = __DIR__ . '/../uploads/trabalhos/' . $nomeUnico;
            move_uploaded_file($_FILES['arquivo']['tmp_name'], $destino);
            $caminhoArquivo = 'uploads/trabalhos/' . $nomeUnico;
        }
    }
    entregarTrabalho($idTrabalho, $idAluno, $nomeArquivo, $caminhoArquivo, $comentario);
    header("Location: ../view/aluno/trabalhos.php?msg=Trabalho enviado com sucesso!");
    exit;
}

if ($operacao === 'corrigirEntrega') {
    $idEntrega = filter_input(INPUT_POST, 'idEntrega', FILTER_VALIDATE_INT);
    $nota      = filter_input(INPUT_POST, 'nota',      FILTER_VALIDATE_FLOAT);
    $feedback  = filter_input(INPUT_POST, 'feedback',  FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $idTurma   = filter_input(INPUT_POST, 'idTurma',   FILTER_VALIDATE_INT);
    corrigirEntrega($idEntrega, $nota, $feedback);
    header("Location: ../view/professor/entregas.php?idTurma=$idTurma&msg=Entrega corrigida!");
    exit;
}

// ============================================================
// RETENÇÃO DE DADOS
// ============================================================

if ($operacao === 'arquivarUsuario') {
    $idUsuario   = filter_input(INPUT_POST, 'idUsuario',  FILTER_VALIDATE_INT);
    $tipoEvento  = filter_input(INPUT_POST, 'tipoEvento', FILTER_SANITIZE_SPECIAL_CHARS);
    $dataEvento  = filter_input(INPUT_POST, 'dataEvento');
    $observacoes = filter_input(INPUT_POST, 'observacoes',FILTER_SANITIZE_SPECIAL_CHARS) ?? '';
    $resultado   = arquivarUsuario($idUsuario, $tipoEvento, $dataEvento, $_SESSION['idUsuario'], $observacoes);
    if ($resultado === SUCESSO) {
        header("Location: ../view/admin/inativos.php?msg=Usuário arquivado. Dados retidos por 10 anos.");
    } else {
        header("Location: ../view/admin/arquivarUsuario.php?id=$idUsuario&msg=" . urlencode($resultado));
    }
    exit;
}

if ($operacao === 'reativarUsuario') {
    $idUsuario = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    reativarUsuario($idUsuario, $_SESSION['idUsuario'], 'Reativação manual pelo admin');
    header("Location: ../view/admin/inativos.php?msg=Usuário reativado.");
    exit;
}

if ($operacao === 'purgarExpirados') {
    $total = purgarDadosExpirados($_SESSION['idUsuario']);
    header("Location: ../view/admin/inativos.php?msg=$total registro(s) purgado(s).");
    exit;
}

// ============================================================
// FALLBACK — operação inválida ou não reconhecida
// ============================================================
if (!empty($operacao)) {
    // Operação veio mas não foi tratada - redirecionar para home do perfil
    $tipo = $_SESSION['tipo'] ?? '';
    $home = match($tipo) {
        'admin'     => '../view/admin/dashboard.php',
        'professor' => '../view/professor/dashboard.php',
        'aluno'     => '../view/aluno/dashboard.php',
        default     => '../view/formlogin.php'
    };
    header("Location: $home?msg=Operação não reconhecida: $operacao");
} else {
    header("Location: ../view/formlogin.php");
}
exit;
