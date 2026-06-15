-- ============================================================
-- BANCO DE DADOS: PortalAlunoBD
-- Portal do Aluno - Sistema Educacional Completo
-- Dados mantidos por 10 anos após formação do indivíduo
-- ============================================================

CREATE DATABASE IF NOT EXISTS PortalAlunoBD CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE PortalAlunoBD;

-- ------------------------------------------------------------
-- TABELA: Usuarios (base para Admin, Professor e Aluno)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Usuarios (
    idUsuario       INT AUTO_INCREMENT PRIMARY KEY,
    matricula       VARCHAR(20) NOT NULL UNIQUE,       -- número de matrícula único
    email           VARCHAR(100) NOT NULL UNIQUE,
    nome            VARCHAR(100) NOT NULL,
    senha           VARCHAR(255) NOT NULL,
    tipo            ENUM('admin','professor','aluno') NOT NULL DEFAULT 'aluno',
    cpf             VARCHAR(14),
    rg              VARCHAR(20),
    dataNasc        DATE,
    sexo            ENUM('M','F','Outro'),
    telefone        VARCHAR(20),
    celular         VARCHAR(20),
    logradouro      VARCHAR(150),
    numero          VARCHAR(10),
    complemento     VARCHAR(50),
    bairro          VARCHAR(80),
    cidade          VARCHAR(80),
    estado          CHAR(2),
    cep             VARCHAR(10),
    foto            VARCHAR(200),
    ativo           TINYINT(1) DEFAULT 1,
    dataCriacao     DATETIME DEFAULT CURRENT_TIMESTAMP,
    dataAtualizacao DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    dataFormacao    DATE,
    dataExpiracao   DATE      -- 10 anos após formação
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Cursos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Cursos (
    idCurso         INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(100) NOT NULL,
    codigo          VARCHAR(20) NOT NULL UNIQUE,
    descricao       TEXT,
    cargaHorariaTotal INT DEFAULT 0,
    duracao         INT COMMENT 'Duração em semestres/anos',
    ativo           TINYINT(1) DEFAULT 1,
    dataCriacao     DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Disciplinas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Disciplinas (
    idDisciplina    INT AUTO_INCREMENT PRIMARY KEY,
    idCurso         INT NOT NULL,
    nome            VARCHAR(100) NOT NULL,
    codigo          VARCHAR(20) NOT NULL UNIQUE,
    cargaHoraria    INT NOT NULL COMMENT 'Em horas',
    ementa          TEXT,
    periodo         INT COMMENT 'Período/semestre da grade',
    ativo           TINYINT(1) DEFAULT 1,
    dataCriacao     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idCurso) REFERENCES Cursos(idCurso) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Turmas
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Turmas (
    idTurma         INT AUTO_INCREMENT PRIMARY KEY,
    idDisciplina    INT NOT NULL,
    idProfessor     INT NOT NULL,
    codigo          VARCHAR(30) NOT NULL UNIQUE,
    ano             YEAR NOT NULL,
    semestre        TINYINT NOT NULL COMMENT '1 ou 2',
    horario         VARCHAR(200),
    sala            VARCHAR(50),
    limiteAlunos    INT DEFAULT 40,
    cargaHorariaCalc INT DEFAULT 0,
    limiteHorasFalta INT DEFAULT 0,
    ativo           TINYINT(1) DEFAULT 1,
    dataCriacao     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idDisciplina) REFERENCES Disciplinas(idDisciplina),
    FOREIGN KEY (idProfessor) REFERENCES Usuarios(idUsuario)
) ENGINE=InnoDB;



-- ------------------------------------------------------------
-- TABELA: TurmaMetadata (informações de curso/módulo/turno da turma)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS TurmaMetadata (
    idTurma  INT NOT NULL PRIMARY KEY,
    idCurso  INT NOT NULL,
    modulo   INT NOT NULL DEFAULT 1 COMMENT 'Módulo/período do curso',
    turno    ENUM('M','T','N','I') NOT NULL DEFAULT 'M' COMMENT 'M=Matutino T=Tarde N=Noturno I=Integral',
    FOREIGN KEY (idTurma) REFERENCES Turmas(idTurma) ON DELETE CASCADE,
    FOREIGN KEY (idCurso) REFERENCES Cursos(idCurso)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Matriculas (aluno em turma)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Matriculas (
    idMatricula     INT AUTO_INCREMENT PRIMARY KEY,
    idAluno         INT NOT NULL,
    idTurma         INT NOT NULL,
    dataMatricula   DATE NOT NULL,
    status          ENUM('ativa','trancada','concluida','reprovada') DEFAULT 'ativa',
    mediaFinal      DECIMAL(5,2),
    situacao        ENUM('cursando','aprovado','reprovado','trancado') DEFAULT 'cursando',
    UNIQUE KEY uq_aluno_turma (idAluno, idTurma),
    FOREIGN KEY (idAluno) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idTurma) REFERENCES Turmas(idTurma)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Aulas (registro de aulas ministradas)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Aulas (
    idAula          INT AUTO_INCREMENT PRIMARY KEY,
    idTurma         INT NOT NULL,
    dataAula        DATE NOT NULL,
    horaInicio      TIME,
    horaFim         TIME,
    conteudo        TEXT,
    observacoes     TEXT,
    registradaEm    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idTurma) REFERENCES Turmas(idTurma)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Frequencias
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Frequencias (
    idFrequencia    INT AUTO_INCREMENT PRIMARY KEY,
    idAula          INT NOT NULL,
    idAluno         INT NOT NULL,
    idTurma         INT NOT NULL,
    presente        TINYINT(1) DEFAULT 0,
    justificativa   TEXT,
    registradaEm    DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_aula_aluno (idAula, idAluno),
    FOREIGN KEY (idAula) REFERENCES Aulas(idAula),
    FOREIGN KEY (idAluno) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idTurma) REFERENCES Turmas(idTurma)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Questionarios
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Questionarios (
    idQuestionario  INT AUTO_INCREMENT PRIMARY KEY,
    idTurma         INT NOT NULL,
    idProfessor     INT NOT NULL,
    titulo          VARCHAR(200) NOT NULL,
    descricao       TEXT,
    dataInicio      DATETIME,
    dataFim         DATETIME,
    tempoLimite     INT COMMENT 'Em minutos, 0 = sem limite',
    tentativasPermitidas INT DEFAULT 1,
    embaralharQuestoes TINYINT(1) DEFAULT 0,
    embaralharAlternativas TINYINT(1) DEFAULT 0,
    publicado       TINYINT(1) DEFAULT 0,
    geradoPorIA     TINYINT(1) DEFAULT 0,
    promptIA        TEXT,
    dataCriacao     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idTurma) REFERENCES Turmas(idTurma),
    FOREIGN KEY (idProfessor) REFERENCES Usuarios(idUsuario)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Questoes
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Questoes (
    idQuestao       INT AUTO_INCREMENT PRIMARY KEY,
    idQuestionario  INT NOT NULL,
    enunciado       TEXT NOT NULL,
    tipo            ENUM('multipla_escolha','verdadeiro_falso','dissertativa') DEFAULT 'multipla_escolha',
    pontos          DECIMAL(5,2) DEFAULT 1.00,
    ordemExibicao   INT DEFAULT 0,
    geradaPorIA     TINYINT(1) DEFAULT 0,
    FOREIGN KEY (idQuestionario) REFERENCES Questionarios(idQuestionario) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Alternativas das questões
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Alternativas (
    idAlternativa   INT AUTO_INCREMENT PRIMARY KEY,
    idQuestao       INT NOT NULL,
    texto           TEXT NOT NULL,
    correta         TINYINT(1) DEFAULT 0,
    ordemExibicao   INT DEFAULT 0,
    FOREIGN KEY (idQuestao) REFERENCES Questoes(idQuestao) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Respostas dos alunos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS RespostasAluno (
    idResposta      INT AUTO_INCREMENT PRIMARY KEY,
    idQuestionario  INT NOT NULL,
    idAluno         INT NOT NULL,
    idQuestao       INT NOT NULL,
    idAlternativa   INT,
    respostaTexto   TEXT,
    correta         TINYINT(1),
    pontosObtidos   DECIMAL(5,2) DEFAULT 0,
    dataResposta    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idQuestionario) REFERENCES Questionarios(idQuestionario),
    FOREIGN KEY (idAluno) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idQuestao) REFERENCES Questoes(idQuestao),
    FOREIGN KEY (idAlternativa) REFERENCES Alternativas(idAlternativa)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Tentativas de questionário
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS TentativasQuestionario (
    idTentativa     INT AUTO_INCREMENT PRIMARY KEY,
    idQuestionario  INT NOT NULL,
    idAluno         INT NOT NULL,
    numerTentativa  INT DEFAULT 1,
    notaObtida      DECIMAL(5,2) DEFAULT 0,
    notaMaxima      DECIMAL(5,2) DEFAULT 0,
    iniciouEm       DATETIME DEFAULT CURRENT_TIMESTAMP,
    finalizouEm     DATETIME,
    concluida       TINYINT(1) DEFAULT 0,
    FOREIGN KEY (idQuestionario) REFERENCES Questionarios(idQuestionario),
    FOREIGN KEY (idAluno) REFERENCES Usuarios(idUsuario)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Notas (avaliações gerais além de questionários)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Notas (
    idNota          INT AUTO_INCREMENT PRIMARY KEY,
    idMatricula     INT NOT NULL,
    idTurma         INT NOT NULL,
    idAluno         INT NOT NULL,
    tipo            ENUM('prova','trabalho','questionario','participacao','outro') DEFAULT 'prova',
    descricao       VARCHAR(200),
    nota            DECIMAL(5,2) NOT NULL,
    notaMaxima      DECIMAL(5,2) DEFAULT 10.00,
    peso            DECIMAL(3,2) DEFAULT 1.00,
    dataLancamento  DATETIME DEFAULT CURRENT_TIMESTAMP,
    observacao      TEXT,
    FOREIGN KEY (idMatricula) REFERENCES Matriculas(idMatricula),
    FOREIGN KEY (idTurma) REFERENCES Turmas(idTurma),
    FOREIGN KEY (idAluno) REFERENCES Usuarios(idUsuario)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Trabalhos/Arquivos enviados
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Trabalhos (
    idTrabalho      INT AUTO_INCREMENT PRIMARY KEY,
    idTurma         INT NOT NULL,
    idProfessor     INT NOT NULL,
    titulo          VARCHAR(200) NOT NULL,
    descricao       TEXT,
    dataEntrega     DATETIME,
    permiteAtraso   TINYINT(1) DEFAULT 0,
    publicado       TINYINT(1) DEFAULT 0,
    dataCriacao     DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idTurma) REFERENCES Turmas(idTurma),
    FOREIGN KEY (idProfessor) REFERENCES Usuarios(idUsuario)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Entregas de trabalhos pelos alunos
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS EntregasTrabalho (
    idEntrega       INT AUTO_INCREMENT PRIMARY KEY,
    idTrabalho      INT NOT NULL,
    idAluno         INT NOT NULL,
    arquivoNome     VARCHAR(255),
    arquivoCaminho  VARCHAR(500),
    comentario      TEXT,
    dataEnvio       DATETIME DEFAULT CURRENT_TIMESTAMP,
    nota            DECIMAL(5,2),
    feedback        TEXT,
    status          ENUM('enviado','corrigido','reprovado') DEFAULT 'enviado',
    FOREIGN KEY (idTrabalho) REFERENCES Trabalhos(idTrabalho),
    FOREIGN KEY (idAluno) REFERENCES Usuarios(idUsuario)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Forum / Avisos / Novidades
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS Forum (
    idPost          INT AUTO_INCREMENT PRIMARY KEY,
    idTurma         INT,                              -- NULL = aviso geral
    idAutor         INT NOT NULL,
    idPostPai       INT,                              -- para respostas/threads
    titulo          VARCHAR(200),
    conteudo        TEXT NOT NULL,
    tipo            ENUM('aviso','discussao','duvida','material') DEFAULT 'aviso',
    fixado          TINYINT(1) DEFAULT 0,
    dataPostagem    DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idTurma) REFERENCES Turmas(idTurma),
    FOREIGN KEY (idAutor) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (idPostPai) REFERENCES Forum(idPost)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: Logs de atividade para IA aprender
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS LogsIA (
    idLog           INT AUTO_INCREMENT PRIMARY KEY,
    idAluno         INT NOT NULL,
    idTurma         INT,
    idQuestionario  INT,
    tipo            ENUM('nota_baixa','frequencia_baixa','desempenho_geral','estudo_recomendado') DEFAULT 'desempenho_geral',
    analise         TEXT,
    recomendacao    TEXT,
    criadoEm        DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idAluno) REFERENCES Usuarios(idUsuario)
) ENGINE=InnoDB;

-- ============================================================
-- DADOS INICIAIS
-- ============================================================

-- Admin padrão (senha: admin123)
INSERT INTO Usuarios (matricula, email, nome, senha, tipo) VALUES
('ADM-2024-0001', 'admin@portal.edu.br', 'Administrador do Sistema',
 '$2y$10$TKh8H1.PfxIjmBBPl1K9GOoHJqOFMDW3Dz6LHkJVpjDJkOBfP8Mey', 'admin');

-- Curso de exemplo
INSERT INTO Cursos (nome, codigo, descricao, cargaHorariaTotal, duracao) VALUES
('Técnico em Informática', 'TI-001', 'Curso Técnico em Informática para Internet', 1200, 3);

-- ============================================================
-- RETENÇÃO DE DADOS: 10 anos após formação/desligamento
-- ============================================================

-- Tabela de histórico de status (trilha de auditoria)
CREATE TABLE IF NOT EXISTS HistoricoStatus (
    idHistorico     INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario       INT NOT NULL,
    statusAnterior  TINYINT(1),
    statusNovo      TINYINT(1),
    motivo          VARCHAR(200),
    alteradoPor     INT,
    dataAlteracao   DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (alteradoPor) REFERENCES Usuarios(idUsuario)
) ENGINE=InnoDB;

-- Tabela de arquivamento (snapshot do aluno ao ser desligado)
CREATE TABLE IF NOT EXISTS RegistrosArquivados (
    idRegistro      INT AUTO_INCREMENT PRIMARY KEY,
    idUsuario       INT NOT NULL,
    tipoEvento      ENUM('formatura','desligamento','transferencia','falecimento','outros') NOT NULL,
    dadosSnapshot   JSON NOT NULL COMMENT 'Snapshot completo do aluno em JSON',
    historicoDados  JSON COMMENT 'Notas, frequências, turmas, questionários',
    dataEvento      DATE NOT NULL,
    dataArquivamento DATETIME DEFAULT CURRENT_TIMESTAMP,
    dataExpiracao   DATE NOT NULL COMMENT '10 anos após o evento',
    expirado        TINYINT(1) DEFAULT 0,
    arquivadoPor    INT,
    observacoes     TEXT,
    FOREIGN KEY (idUsuario) REFERENCES Usuarios(idUsuario),
    FOREIGN KEY (arquivadoPor) REFERENCES Usuarios(idUsuario)
) ENGINE=InnoDB;

-- ============================================================
-- EVENT SCHEDULER (requer SUPER ou EVENT privilege)
-- Execute separadamente se necessário:
-- -- ============================================================
-- Event do MySQL para marcar registros expirados automaticamente
CREATE EVENT IF NOT EXISTS evt_marcar_expirados
ON SCHEDULE EVERY 1 DAY
DO
  UPDATE RegistrosArquivados SET expirado = 1
  WHERE dataExpiracao < CURDATE() AND expirado = 0;

-- View para consulta rápida de alunos inativos com status de retenção
CREATE OR REPLACE VIEW vw_inativos_retencao AS
SELECT
    u.idUsuario, u.matricula, u.nome, u.email, u.cpf,
    u.ativo, u.dataCriacao, u.dataFormacao, u.dataExpiracao,
    DATEDIFF(u.dataExpiracao, CURDATE()) AS diasRestantes,
    CASE
        WHEN u.dataExpiracao < CURDATE() THEN 'expirado'
        WHEN DATEDIFF(u.dataExpiracao, CURDATE()) <= 180 THEN 'expirando_em_breve'
        ELSE 'retido'
    END AS statusRetencao,
    ra.tipoEvento, ra.dataEvento, ra.dataArquivamento
FROM Usuarios u
LEFT JOIN RegistrosArquivados ra ON ra.idUsuario = u.idUsuario
WHERE u.ativo = 0;

-- Professor de teste (senha: prof123)
INSERT INTO Usuarios (matricula, email, nome, senha, tipo) VALUES
('PROF-2024-0001', 'professor@portal.edu.br', 'Professor Teste',
 '$2y$10$5hDXmV2JSLl1AYdVV2Jxku9oTHKzwD7qM5TJqGakV4WTZ4.cNSN4K', 'professor');

-- Aluno de teste (senha: aluno123)
INSERT INTO Usuarios (matricula, email, nome, senha, tipo) VALUES
('ALU-2024-0001', 'aluno@portal.edu.br', 'Aluno Teste',
 '$2y$10$YL8tE5nT6jKQIGf7Fyz7Iu.gKMl.j6BUJQe7qmN3oGUkN5aJRuvAu', 'aluno');
