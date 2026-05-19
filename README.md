📘 Projeto Integrador I – Prova de Conceito
Aplicativo Web: Agenda de Estudos

Desenvolvido por: Grupo 6 - SENAC EAD 2026

Equipe
Felipe Alves da Silva
Isaías Costa Araújo
Nelson da Silva Oliveira
Vitor David de Moraes Marques
Matheus Henrique de Souza Morete
Renan Henrique Dias Pires
Alexandre Vagner Ramos Sousa
Caio Yamanaka Segatto
📌 Sobre o Projeto

O Agenda de Estudos é um sistema web desenvolvido como Produto Mínimo Viável (MVP) com o objetivo de auxiliar estudantes na organização de rotinas acadêmicas, gerenciamento de tarefas e controle do tempo de estudo.

A aplicação permite:

autenticação de usuários;
gerenciamento completo de tarefas (CRUD);
controle de status das atividades;
cronômetro de produtividade;
pausa e retomada de tarefas;
categorização por prioridade;
acompanhamento do tempo líquido de estudo.
🛠 Tecnologias Utilizadas
Backend
Node.js
Express.js
Frontend
HTML5
CSS3
Bootstrap 5
EJS
Banco de Dados
MySQL
⚙️ Instruções de Execução
1. Extraia o projeto

Extraia a pasta do projeto para qualquer diretório do computador.

Exemplo:

C:\Projetos\agenda_estudos
2. Instale as dependências

Abra o terminal na pasta do projeto e execute:

npm install
3. Configure o MySQL

Inicie o serviço do MySQL utilizando:

XAMPP;
WAMP;
Laragon;
MySQL Server.

O sistema criará automaticamente:

banco de dados;
tabelas;
usuário inicial.
4. Execute o sistema

No terminal execute:

npm start
5. Acesse no navegador
http://localhost:3000

Caso a porta 3000 esteja ocupada:

Abra o arquivo .env;
Altere:
PORT=3000

Exemplo:

PORT=8050

Depois execute novamente:

npm start

E acesse:

http://localhost:8050
🔐 Usuário de Teste

Usuário padrão criado automaticamente:

Email: admin@example.com
Senha: senha123

Recomenda-se alterar a senha após o primeiro acesso.

✅ Funcionalidades Implementadas
Login e Logout;
Sessão autenticada;
Dashboard interativo;
Cadastro de tarefas;
Edição de tarefas;
Exclusão de tarefas;
Controle de status;
Sistema de prioridades;
Cronômetro de estudo;
Pausa e retomada de tarefas;
Contagem de tempo líquido;
Responsividade com Bootstrap.
🚀 Melhorias Futuras
Relatórios gráficos;
Dashboard analítico;
Notificações inteligentes;
Organização por disciplinas;
Exportação de relatórios;
Aplicativo mobile;
API REST.
📄 Licença

Projeto acadêmico desenvolvido para fins educacionais no curso de Tecnologia em Análise e Desenvolvimento de Sistemas – SENAC EAD 2026.
