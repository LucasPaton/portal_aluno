# Portal do Aluno — PHP MVC
> Sistema educacional completo com sessões, MVC, banco MySQL, IA Gemini e retenção de dados por 10 anos.

## Estrutura do Projeto

```
portal_aluno/
├── persistencia/
│   ├── banco.sql          ← Execute no MySQL primeiro
│   ├── conexao.php        ← Configurações de banco
│   └── persistencia.php   ← Funções genéricas (conectar, SELECT, INSERT...)
│
├── model/
│   ├── usuarios.php       ← Gestão de Admin, Professor, Aluno
│   ├── turmas.php         ← Cursos, Disciplinas, Turmas, Matrículas
│   ├── frequencias.php    ← Aulas, Frequências, Notas
│   ├── questionarios.php  ← Quizzes + IA Gemini
│   ├── forum.php          ← Fórum, Avisos, Trabalhos
│   └── retencao.php       ← Arquivamento e retenção 10 anos
│
├── controller/
│   ├── controlador.php         ← Ponto central de todas as requisições
│   ├── controladorRetencao.php ← Operações de arquivamento
│   ├── validar.php             ← Proteção de sessão
│   ├── iaAluno.php             ← Endpoint AJAX — IA do aluno
│   └── iaAnalise.php           ← Endpoint AJAX — IA do professor
│
├── view/
│   ├── _layout.php        ← Sidebar + topbar compartilhados
│   ├── formlogin.php      ← Página pública de login
│   ├── admin/
│   │   ├── dashboard.php
│   │   ├── usuarios.php       ← Listagem com pesquisa + paginação
│   │   ├── formUsuario.php    ← Cadastro completo de usuário
│   │   ├── inativos.php       ← Gestão de registros inativos (retenção)
│   │   └── snapshotUsuario.php← Histórico completo arquivado
│   ├── professor/
│   │   ├── dashboard.php
│   │   ├── turmaDetalhe.php   ← Gestão da turma + gráficos
│   │   └── estatQuestionario.php ← Stats + análise IA
│   └── aluno/
│       ├── dashboard.php
│       ├── disciplinas.php    ← Notas + frequência por disciplina
│       ├── frequencia.php     ← Histórico aula a aula
│       ├── notas.php          ← Gráficos de desempenho
│       ├── questionarios.php  ← Lista de quizzes
│       ├── responderQuiz.php  ← Responder questionário (com timer)
│       ├── trabalhos.php      ← Entregas de trabalhos
│       └── tutor.php          ← Chat com IA + análise personalizada
│
└── assets/
    ├── css/style.css      ← Design system completo
    └── uploads/           ← Arquivos enviados pelos alunos
```

## Instalação

### 1. Banco de dados
```sql
-- No MySQL/phpMyAdmin, execute:
source /caminho/do/projeto/persistencia/banco.sql
```

### 2. Configuração
Edite `persistencia/conexao.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'sua_senha');
define('DB_NAME', 'PortalAlunoBD');
```

### 3. Servidor
- **XAMPP/LAMP**: Coloque a pasta `portal_aluno/` dentro de `htdocs/`
- Acesse: `http://localhost/portal_aluno/view/formlogin.php`

### 4. Login inicial
| Tipo | E-mail | Senha |
|------|--------|-------|
| Admin | admin@portal.edu.br | admin123 |

## Funcionalidades

### Administrador
- Cadastro de alunos e professores (com todos os dados pessoais e endereço)
- Matrícula automática: `ADM-2024-0001`, `PROF-2024-0001`, `ALU-2024-0001`
- Gestão de cursos, disciplinas e turmas (grade curricular)
- Alocação de alunos em turmas
- **Retenção de dados**: arquivamento com snapshot completo (JSON) por 10 anos
- Consulta de histórico acadêmico de alunos inativos/formados
- Purga de dados pessoais após vencimento (anonimização, mantendo histórico)

### Professor
- Visualização das turmas com gráficos de desempenho
- Registro de aulas e frequência
- Lançamento de notas
- Criação de questionários (manual ou com IA Gemini)
- Estatísticas de questionários: % acerto por questão, distribuição de notas
- **IA**: análise pedagógica com sugestão de reforço de conteúdo
- Criação de trabalhos e correção de entregas
- Fórum por turma

### Aluno
- Dashboard com alertas de frequência
- Frequência detalhada aula a aula com barra de progresso
- Notas com gráficos de evolução
- Resposta de questionários com timer opcional
- Entrega de trabalhos com upload de arquivo
- **Tutor IA**: chat com Gemini + análise de desempenho + rotina de estudos personalizada
- Fórum e avisos

## Retenção de Dados (10 anos)
Ao arquivar um aluno (formatura, desligamento, etc.):
1. Um **snapshot JSON** com todos os dados pessoais e acadêmicos é salvo
2. O campo `dataExpiracao = dataEvento + 10 anos` é definido
3. O aluno fica inativo mas todos os dados permanecem acessíveis
4. Após 10 anos, apenas os **dados pessoais** são anonimizados (nome, CPF, e-mail, endereço)
5. O **histórico acadêmico** (notas, frequências, disciplinas) é mantido permanentemente

## Segurança
- Senhas com `password_hash()` (bcrypt)
- Prepared Statements em todas as queries (sem SQL Injection)
- `htmlspecialchars()` em todas as saídas (sem XSS)
- `filter_input()` em todos os inputs
- Controle de sessão com verificação de tipo de usuário
- Soft delete (nunca apaga fisicamente registros ativos)

## IA (Google Gemini)
Chave configurada em `model/questionarios.php` — substitua pela sua:
```php
$apiKey = 'SUA_CHAVE_GEMINI_AQUI';
```
Funcionalidades de IA:
- Geração automática de questões de múltipla escolha
- Análise pedagógica por questionário
- Análise de desempenho individual do aluno
- Rotina de estudos semanal personalizada
- Chat educacional em tempo real
