-- ============================================================
-- UPGRADE: Novas tabelas para atender requisitos do documento
-- Execute no phpMyAdmin com PortalAlunoBD selecionado
-- ============================================================
USE PortalAlunoBD;

-- ------------------------------------------------------------
-- TABELA: MateriaisDidaticos (equivalente a 'produtos' do doc)
-- Materiais educacionais disponibilizados pela instituição
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS MateriaisDidaticos (
    idMaterial      INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(200) NOT NULL,
    descricao       TEXT,
    tipo            ENUM('apostila','video','software','equipamento','livro','outro') DEFAULT 'apostila',
    preco           DECIMAL(10,2) DEFAULT 0.00,
    imagem          VARCHAR(300),
    idCurso         INT,
    disponivel      TINYINT(1) DEFAULT 1,
    ativo           TINYINT(1) DEFAULT 1,
    criadoEm        DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizadoEm    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (idCurso) REFERENCES Cursos(idCurso) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: ServicosAcademicos (equivalente a 'servicos' do doc)
-- Serviços oferecidos pela instituição (monitoria, biblioteca, etc.)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ServicosAcademicos (
    idServico       INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(200) NOT NULL,
    descricao       TEXT,
    categoria       ENUM('monitoria','biblioteca','laboratorio','secretaria','orientacao','outro') DEFAULT 'outro',
    valorEstimado   DECIMAL(10,2) DEFAULT 0.00,
    imagem          VARCHAR(300),
    horarioFunc     VARCHAR(200),
    responsavel     VARCHAR(100),
    disponivel      TINYINT(1) DEFAULT 1,
    ativo           TINYINT(1) DEFAULT 1,
    criadoEm        DATETIME DEFAULT CURRENT_TIMESTAMP,
    atualizadoEm    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- TABELA: MensagensContato (formulário de contato do doc)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS MensagensContato (
    idMensagem      INT AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(100) NOT NULL,
    email           VARCHAR(100) NOT NULL,
    telefone        VARCHAR(20),
    assunto         VARCHAR(200),
    mensagem        TEXT NOT NULL,
    formaContato    ENUM('email','telefone','whatsapp','presencial') DEFAULT 'email',
    respondida      TINYINT(1) DEFAULT 0,
    respostaAdmin   TEXT,
    respondidaPor   INT,
    criadoEm        DATETIME DEFAULT CURRENT_TIMESTAMP,
    respondidaEm    DATETIME,
    FOREIGN KEY (respondidaPor) REFERENCES Usuarios(idUsuario) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Dados iniciais de exemplo
-- ------------------------------------------------------------
INSERT INTO MateriaisDidaticos (nome, descricao, tipo, preco, idCurso) VALUES
('Apostila de HTML5 e CSS3', 'Material completo sobre desenvolvimento web front-end', 'apostila', 0.00, 1),
('Kit Arduino Iniciante', 'Kit com Arduino Uno, sensores e componentes eletrônicos', 'equipamento', 89.90, 1),
('Livro: Algoritmos e Lógica', 'Introdução à programação com pseudocódigo e fluxogramas', 'livro', 45.00, 1);

INSERT INTO ServicosAcademicos (nome, descricao, categoria, horarioFunc, responsavel) VALUES
('Monitoria de Programação', 'Atendimento individual para dúvidas em lógica e PHP', 'monitoria', 'Seg a Sex, 14h-17h', 'Prof. Silva'),
('Biblioteca Digital', 'Acesso a e-books e materiais acadêmicos online', 'biblioteca', '24 horas (online)', 'Equipe TI'),
('Laboratório de Informática', 'Uso livre de computadores com internet e softwares instalados', 'laboratorio', 'Seg a Sex, 8h-22h / Sáb, 8h-12h', 'Coord. Labs'),
('Secretaria Acadêmica', 'Emissão de documentos, matrículas e atendimento geral', 'secretaria', 'Seg a Sex, 8h-17h', 'Secretaria');

-- Confirmar
SELECT 'Upgrade concluído!' AS status;
