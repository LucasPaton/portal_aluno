# Portal do Aluno

Este é um sistema Web (PHP + MySQL + MVC) de gerenciamento acadêmico que passou por um **upgrade visual e de funcionalidades** para atingir um padrão profissional premium. O sistema lida com diferentes níveis de acesso (Admin, Professor e Aluno), além de integrar com a API do Gemini para Tutoria com IA e Análise Preditiva de Retenção.

## 🚀 Novidades do Upgrade

- **Segurança Reforçada:** Centralização das chaves de API (Gemini) em um arquivo `config.php` não versionado e remoção de scripts de debug inseguros do ambiente de produção.
- **Novas Tabelas e CRUDs Completos:**
  - `Materiais Didáticos` (cadastro de apostilas, livros, vídeos, etc.)
  - `Serviços Acadêmicos` (monitoria, laboratório, biblioteca, etc.)
  - `Mensagens de Contato` (fale conosco integrado para alunos)
- **Design "Glassmorphism" Premium:** Nova interface de usuário com tema visual profissional, barra lateral responsiva, efeitos em vidro (*glass*), transições suaves e tipografia moderna (Inter).
- **Dark Mode Integrado:** Suporte completo a tema claro e escuro, com preferência salva localmente.
- **Painéis Administrativos Refinados:** Listagens e formulários otimizados e mais intuitivos para todas as operações do sistema.
- **Alertas Dinâmicos:** Sistema de toast notifications e modais integrados.
- **Módulo de Troca de Senha:** Recurso nativo para todos os usuários logados alterarem suas senhas com segurança.

## 🛠️ Tecnologias Utilizadas

- **Backend:** PHP 8+ (Orientação a Objetos básica, Padrão MVC manual)
- **Banco de Dados:** MySQL (via PDO)
- **Frontend:** HTML5, CSS3 Nativo (Variáveis CSS, Flexbox, CSS Grid), JavaScript Vanilla
- **APIs Externas:** Google Gemini AI (Geração de texto para Tutor e Análises), ViaCEP (Busca de endereços), Chart.js (Gráficos)

## 📁 Estrutura do Projeto

O sistema é construído sob um padrão **MVC Simplificado (Front Controller)**:

- `/assets/`: Scripts JS globais, estilos CSS e eventuais imagens.
- `/controller/`: Contém o `controlador.php` central (que roteia as ações via GET/POST da variável `operacao`), além de scripts de validação de acesso.
- `/model/`: Funções de interação com o banco de dados e regras de negócio para cada entidade (Usuários, Turmas, Materiais, Serviços, etc).
- `/persistencia/`: Arquivos `.sql` para criação/upgrade do esquema e arquivo de conexão `persistencia.php` (PDO).
- `/view/`: Interfaces do usuário separadas por perfil (`/admin`, `/professor`, `/aluno`). Inclui o arquivo `/view/_layout.php` que monta as estruturas de topo e lateral da página.
- `config.php`: Arquivo central de configurações do ambiente (chaves de API, banco). *Requerido, criado a partir de config.example.php*.

## ⚙️ Instalação e Configuração

1. **Requisitos:** Servidor local rodando PHP (ex: XAMPP, Laragon, WAMP) e MySQL.
2. **Banco de Dados:** 
   - Crie o banco `PortalAlunoBD` em seu gerenciador (phpMyAdmin).
   - Importe os arquivos da pasta `/persistencia/` em ordem (o banco original primeiro, se aplicável, e depois o script `upgrade.sql`).
3. **Configuração Ambiental:**
   - Renomeie (ou copie) `config.example.php` para `config.php`.
   - Adicione suas credenciais do Banco de Dados e, principalmente, sua `GEMINI_API_KEY`.
4. **Hospedagem:** Coloque a pasta do projeto dentro de `htdocs` (se usando XAMPP) ou no diretório público do seu servidor web. Acesse via navegador em `http://localhost/portal_aluno`.

## 👨‍💻 Autor

Atualizado e Mantido por **Lucas Paton** ([@LucasPaton](https://github.com/LucasPaton)).
Email de Contato: [lcspaton@gmail.com](mailto:lcspaton@gmail.com)
