-- ============================================================
-- MIGRAÇÃO: Execute no phpMyAdmin com PortalAlunoBD selecionado
-- Adiciona funcionalidades de turma completa (módulo/turno/curso)
-- ============================================================
USE PortalAlunoBD;

-- Tabela de metadata das turmas (curso, módulo, turno)
CREATE TABLE IF NOT EXISTS TurmaMetadata (
    idTurma  INT NOT NULL PRIMARY KEY,
    idCurso  INT NOT NULL,
    modulo   INT NOT NULL DEFAULT 1,
    turno    ENUM('M','T','N','I') NOT NULL DEFAULT 'M',
    FOREIGN KEY (idTurma) REFERENCES Turmas(idTurma) ON DELETE CASCADE,
    FOREIGN KEY (idCurso) REFERENCES Cursos(idCurso)
) ENGINE=InnoDB;

-- Permitir idDisciplina = 0 em Turmas (agora é opcional)
ALTER TABLE Turmas MODIFY idDisciplina INT NOT NULL DEFAULT 0;
ALTER TABLE Turmas MODIFY idProfessor  INT NOT NULL DEFAULT 0;

-- Confirmar
SELECT 'Migração concluída!' AS status;
SELECT TABLE_NAME FROM information_schema.TABLES 
WHERE TABLE_SCHEMA = 'PortalAlunoBD' ORDER BY TABLE_NAME;
