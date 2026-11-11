# Vidraria Fácil 🧪

**Identificação Inteligente e Instantânea de Vidrarias de Laboratório com Visão Computacional**  
Projeto de Tecnologia da Informação e Comunicação (PTIC)

---

## 📋 Sumário
- [Objetivo do Projeto](#-objetivo-do-projeto)
- [Stack Tecnológica](#-stack-tecnológica)
- [Regras de Negócio (CORE) Implementadas](#-regras-de-negócio-core-implementadas)
- [Estrutura do Projeto](#-estrutura-do-projeto)
- [Credenciais Demo para Avaliação](#-credenciais-demo-para-avaliação)
- [Como Executar o Projeto](#-como-executar-o-projeto)
- [Configuração da Hugging Face API](#-configuração-da-hugging-face-api)
- [Autores](#-autores)

---

## 🎯 Objetivo do Projeto
O **Vidraria Fácil** foi concebido para resolver um problema recorrente em ambientes de laboratório de química e biologia: a identificação incorreta ou demorada de vidrarias e a falta de orientações imediatas de biossegurança.

Com a aplicação, estudantes, professores e pesquisadores podem:
1. **Escanear por Foto / Câmera**: Apontar a câmera do smartphone ou carregar uma imagem para reconhecimento automático via Inteligência Artificial.
2. **Filtrar por Características Anatômicas**: Buscar guiadamente por tipo de fundo (redondo, plano), tipo de gargalo (estreito, esmerilhado), função e tolerância ao fogo.
3. **Consultar Fichas Técnicas & Normas de Segurança**: Acessar utilidade prática, nome científico, orientações de prevenção primária de acidentes e cuidados de manuseio.

---

## 🛠️ Stack Tecnológica
- **Backend**: PHP estruturado com sessões nativas (`session_start()`).
- **Banco de Dados**: MySQL utilizando **PDO** (`PDO::ATTR_ERRMODE => PDO_ERRMODE_EXCEPTION`, `PDO::ATTR_EMULATE_PREPARES => false`) para blindagem total contra SQL Injection, com fallback automático para SQLite em ambientes de desenvolvimento sem serviço MySQL ativo.
- **Frontend**: HTML5 semântico, PHP, CSS3, e **Tailwind CSS** priorizando a abordagem **Mobile-First**.
- **Inteligência Artificial**: **Hugging Face Inference API** (modelo de visão computacional `google/vit-base-patch16-224` ou customizado), com classificador óptico heurístico de laboratório como fallback de alta precisão.
- **Câmera**: HTML5 `navigator.mediaDevices.getUserMedia` com comutação de câmera traseira/frontal e captura via Canvas.

---

## ⚖️ Regras de Negócio (CORE) Implementadas

1. **Criptografia Bcrypt para Senhas**:
   - Todas as senhas são processadas com `password_hash($senha, PASSWORD_BCRYPT)` e autenticadas com `password_verify()`.

2. **Auditoria Permanente & Logs do Sistema**:
   - Tabela `logs_auditoria` rastreando qualquer alteração no sistema (cadastros, logins, edições de catálogo, ajustes de limites, detecções por foto e upgrades de plano) com registro de usuário, IP, navegador e timestamp.
   - Painel exclusivo de Auditoria (`/admin/auditoria.php`) com filtros avançados e exportação para CSV.

3. **Plano Gratuito com Limites Configuráveis pelo ADM**:
   - Usuários no plano gratuito possuem uma cota diária de pesquisas por foto (padrão: 5 buscas/dia).
   - O administrador pode alterar esse limite dinamicamente na interface (`/admin/configuracoes.php`).

4. **Histórico Diferenciado (Free vs. Premium)**:
   - Usuário Gratuito: visualização restrita aos últimos dias (padrão: 7 dias) e quantidade limitada de registros configurados pelo ADM.
   - Usuário Premium: acesso irrestrito e vitalício ao histórico completo de todo o período, com busca e detalhes de todas as consultas.

5. **Interface Moderna (Toasts e Modais)**:
   - Mensagens de feedback, erros e avisos são apresentadas em **TOASTS** animados e não intrusivos.
   - Caixas de diálogo e confirmações nativas do navegador (`alert`, `confirm`) são tratadas em **MODAIS** acessíveis estilizados com Tailwind CSS.

---

## 📂 Estrutura do Projeto

```text
vidraria-facil/
├── bd.php                         # Conexão central PDO com MySQL (e fallback SQLite)
├── index.php                      # Dashboard / Tela Inicial Mobile-First
├── camera.php                     # Caso de Uso 1: Escanear com Câmera e IA
├── filtro.php                     # Caso de Uso 2: Filtragem por atributos anatômicos
├── detalhes.php                   # Caso de Uso 3: Ficha técnica e segurança
├── catalogo.php                   # Catálogo completo e busca textual
├── historico.php                  # Histórico com restrições Free vs Premium
├── planos.php                     # Comparativo de planos e upgrade
├── perfil.php                     # Perfil do usuário e medidor de cota
│
├── auth/                          # Autenticação e Sessões
│   ├── login.php                  # Login seguro com bcrypt
│   ├── cadastro.php               # Cadastro de novos usuários
│   └── logout.php                 # Encerramento seguro de sessão
│
├── admin/                         # Painel Administrativo
│   ├── index.php                  # Dashboard com KPIs e gráficos
│   ├── configuracoes.php          # Configuração dos limites Free e Hugging Face
│   ├── auditoria.php              # Logs de auditoria e exportação CSV
│   ├── usuarios.php               # Gestão de usuários e permissões
│   ├── vidrarias.php              # Catálogo administrativo
│   └── vidraria_form.php          # Formulário de inclusão/edição de vidrarias
│
├── api/                           # Endpoints AJAX / JSON
│   ├── identificar.php            # Processamento de imagem via IA e limites
│   ├── vidrarias.php              # Filtragem dinâmica
│   └── upgrade_plano.php          # Simulação de adesão ao Premium
│
├── database/                      # Scripts de Banco de Dados
│   ├── schema.sql                 # Estrutura das tabelas MySQL
│   └── seed.sql                   # Catálogo completo e usuários iniciais
│
├── includes/                      # Componentes e Regras Isoladas
│   ├── header.php                 # Cabeçalho responsivo e navegação
│   ├── footer.php                 # Rodapé e bottom-bar mobile
│   ├── funcoes.php                # Funções de auditoria, limites e sessão
│   ├── toast.php                  # Componente de Notificações Toast
│   ├── modal.php                  # Componente de Modal
│   └── ia_huggingface.php         # Integração com Hugging Face Inference API
│
└── assets/                        # Recursos Estáticos
    ├── img/                       # Imagens e ilustrações de vidrarias
    └── css/                       # Estilos complementares
```

---

## 🔑 Credenciais Demo para Avaliação

O sistema já possui 3 contas pré-configuradas (com botões de preenchimento rápido de 1 clique na tela de login):

| Perfil | E-mail | Senha | Benefícios do Perfil |
| :--- | :--- | :--- | :--- |
| **Administrador** | `admin@vidrariafacil.com` | `admin123` | Acesso ao Painel ADM, Auditoria, Configuração de Cotas e Catálogo |
| **Aluno (Free)** | `aluno@vidrariafacil.com` | `aluno123` | Cota diária limitada de pesquisas e histórico restrito |
| **Pesquisador (Premium)** | `pesquisador@vidrariafacil.com` | `premium123` | Pesquisas ilimitadas e histórico vitalício completo |

---

## 🚀 Como Executar o Projeto

### Opção 1: Servidor Integrado do PHP (Recomendado e Mais Rápido)
Abra o terminal na pasta do projeto e execute:
```bash
php -S localhost:8000
```
Em seguida, acesse no navegador: `http://localhost:8000`

> **Nota:** Caso o serviço MySQL não esteja rodando, o sistema ativa automaticamente o fallback local SQLite com todas as tabelas e dados já prontos!

### Opção 2: Com XAMPP / WAMP / Apache
1. Copie a pasta do projeto para o diretório web (`htdocs` no XAMPP ou `www` no WAMP).
2. Abra o phpMyAdmin e crie o banco de dados `vidraria_facil`.
3. Importe os arquivos `database/schema.sql` e `database/seed.sql`.
4. Configure as credenciais no arquivo `config/config.php` (padrão: `root` sem senha).
5. Acesse no navegador: `http://localhost/vidraria-facil`

---

## 🤖 Configuração da Hugging Face API
1. Acesse o sistema com a conta de Administrador.
2. Navegue até **Painel ADM > Limites & Hugging Face** (`/admin/configuracoes.php`).
3. Insira seu token da Hugging Face (`hf_...`) e o modelo de visão desejado.
4. Clique em **Salvar Configurações**. Todas as próximas leituras por câmera consultarão a Inference API diretamente.

---

## 👥 Autores (Grupo PTIC)
- Nicolas Sanches Passos Araújo
- Thais Chambilla
- Laís
- Kauanny
