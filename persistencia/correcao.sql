-- ============================================================
-- ARQUIVO: correcao.sql
-- Execute este arquivo com o banco PortalAlunoBD selecionado
-- Corrige senhas e problemas de setup inicial
-- ============================================================

USE PortalAlunoBD;

-- ============================================================
-- PASSO 1: Limpar admins com senha errada
-- ============================================================
DELETE FROM Usuarios WHERE matricula IN ('ADM-001', 'ADM-002');

-- ============================================================
-- PASSO 2: Inserir admin com hash CORRETO
-- Senha: admin123
-- Hash gerado com password_hash('admin123', PASSWORD_BCRYPT)
-- ============================================================
INSERT INTO Usuarios (matricula, email, nome, senha, tipo) VALUES
(
  'ADM-2024-0001',
  'admin@portal.edu.br',
  'Administrador do Sistema',
  '$2y$10$TKh8H1.PfxIjmBBPl1K9GOoHJqOFMDW3Dz6LHkJVpjDJkOBfP8Mey',
  'admin'
);

-- ============================================================
-- PASSO 3: Criar professor de teste
-- Senha: prof123
-- ============================================================
INSERT INTO Usuarios (matricula, email, nome, senha, tipo) VALUES
(
  'PROF-2024-0001',
  'professor@portal.edu.br',
  'Professor Teste',
  '$2y$10$5hDXmV2JSLl1AYdVV2Jxku9oTHKzwD7qM5TJqGakV4WTZ4.cNSN4K',
  'professor'
);

-- ============================================================
-- PASSO 4: Criar aluno de teste
-- Senha: aluno123
-- ============================================================
INSERT INTO Usuarios (matricula, email, nome, senha, tipo) VALUES
(
  'ALU-2024-0001',
  'aluno@portal.edu.br',
  'Aluno Teste',
  '$2y$10$YL8tE5nT6jKQIGf7Fyz7Iu.gKMl.j6BUJQe7qmN3oGUkN5aJRuvAu',
  'aluno'
);

-- ============================================================
-- PASSO 5: EVENT e VIEW (executar dentro do banco selecionado)
-- ============================================================

-- Ativar event scheduler (pode precisar de privilégio SUPER)
-- Se der erro, ignore esta linha - não afeta o funcionamento do portal
-- SET GLOBAL event_scheduler = ON;

-- Recriar o evento dentro do banco correto
DROP EVENT IF EXISTS evt_marcar_expirados;
CREATE EVENT IF NOT EXISTS evt_marcar_expirados
  ON SCHEDULE EVERY 1 DAY
  DO
    UPDATE RegistrosArquivados
    SET expirado = 1
    WHERE dataExpiracao < CURDATE() AND expirado = 0;

-- Recriar a VIEW
CREATE OR REPLACE VIEW vw_inativos_retencao AS
SELECT
    u.idUsuario, u.matricula, u.nome, u.email, u.cpf,
    u.ativo, u.dataCriacao, u.dataFormacao, u.dataExpiracao,
    DATEDIFF(u.dataExpiracao, CURDATE()) AS diasRestantes,
    CASE
        WHEN u.dataExpiracao < CURDATE()                              THEN 'expirado'
        WHEN DATEDIFF(u.dataExpiracao, CURDATE()) <= 180             THEN 'expirando_em_breve'
        ELSE 'retido'
    END AS statusRetencao,
    ra.tipoEvento, ra.dataEvento, ra.dataArquivamento
FROM Usuarios u
LEFT JOIN RegistrosArquivados ra ON ra.idUsuario = u.idUsuario
WHERE u.ativo = 0;

-- ============================================================
-- VERIFICAR: confirmar que os usuários foram criados
-- ============================================================
SELECT idUsuario, matricula, email, nome, tipo, ativo
FROM Usuarios
ORDER BY tipo, idUsuario;
