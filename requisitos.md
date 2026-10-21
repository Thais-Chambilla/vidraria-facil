# Vidraria Fácil
## Objetivo
criar um site onde o usuário vai utilizar a câmera para detectar imagens de vidrarias de laboratório, o usuário precisa habilitar o uso de câmera para tirar uma foto e o sistema retorna o tipo de vidraria com seus respectivos usos e etc.

### Stack Tecnlógico
- Backend: PHP estruturado com sessões nativas
- Banco de dados: MySQL (PDO para segurança)
- Frontend: HTML5, PHP, CSS, Tailwind CSS
- Huggingface para criação de um motor de inteligência artificial de detecção de imagem

#### Regras de negócio (CORE)
- Tratar senhas de usuários com hash bcrypt
- O sistema deve ter uma página de históricos e manter sempre os logs de qualquer alteração feita por qualquer usuário, para auditorias futuras.
- um usuário no plano gratuito vai ter uma quantidade limitada de pesquisa, na qual o o adm do site pode configurar esse limite.
- o usuário gratuito vai ter um limite menor de ver o historico. 
- o usuário premium tem buscas mais amplas e um histórico completo de todo o periodo.


##### Regras Globais
- Use sempre PDO para conexão e queries no MySQL para evitar SQL Injections
- Mantenha o código limpo e comente apenas logicas complexas.
- Separe os arquivos de forma lógica: um arquivo para conexão com a base (bd.php) e scripts de backend isolados e views em HTML5/PHP, nunca faça o sistema como um monolito, deixe sempre separados todas as regras para facilitar os futuros upgrades.
 - Estilize as telas em Tailwind de forma responsiva priorizando o MobileFrist.
 - Retorne sempre as mensagens de erros de forma claras na interface para o usuário (TOAST)
 - sempre trate as mensagens de caixa de mensagens nativas do navegador em um MODAL
