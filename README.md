# Projeto Dicionário

Este projeto é uma aplicação Laravel no intuito de realizar pesquisas de palavras em ingles em um dicionário utilizando como base Free Dictionary API, proporcionando ao usuário obter detalhes especificados da palavra selecionada.

O sistema permite relizar registros de usuários para login, definir e listar as palavras favoritas do usuário, pesquisar os detalhes das palavras selecionadas e registrar e visualizar o histórico de pesquisa do próprio usuário.

## Tecnologias e Ferramentas

- **PHP:** 8.2.12
- **Laravel:** 10.48.22
- **Composer:** 2.7.7
- **Insomnia:** Para testar as rotas da API
- **Swagger:** Para a documentação da API
- **Sanctum:** Para gerar token de autenticação
- **PHPMailer:** Para envio de emails via SMTP
- **API:** Free Dictionary API

## Requisitos

- Sistema operacional de preferência uma " Distribuição do Linux".
- GitHub Instalado.

## Configuração do Projeto

1. **Clonar o Repositório:**

   Em um diretório, clone o repositório e entre na pasta do projeto.
   `git clone https://github.com/Ricardov7070/Dictionary.git`

2. **Instalando as Dependências:**

   Após clonar o projeto, é nescessário renomear ou copiar o arquivo `.env.example` para `.env` e ajustar as variáveis de ambiente conforme necessário, incluindo as configurações para acesso ao banco de dados, para funcionando do serviço de email "PHPMailer" e para funcionamento correto do "Redis".
   Caso esteja em um amiente linux, basta somente rodar o comando abaixo dentro da pasta do projeto:
       
    `cp .env.example .env`

   O projeto se encontra configurado e ambientado para rodar os containeres utilizando a ferramenta do Docker. O sistema está configurado em 5 containeres de aplicação para assim a ferramenta conseguir realizar o balanceamento de carga de acesso entre eles. Para inicializar, em um ambiente onde se encontra instalado o docker, você precisará entrar na pasta do projeto e executar o comando abaixo para subir os containeres já configurados:
      
    `docker compose up -d`

   Os containeres já estão configurados e prontos para rodarem o projeto com todas suas dependências nescessárias. Mas caso haja a nescessidade de realizar alguma reinstalação de algum pacote ainda pendente, recomento entrar no terminal de um dos containeres de aplicação listados abaixo e rodar cada um desses comando caso haja a nescessidade:

   Os containeres de aplicação possui os seguintes nomes:
     
    `laravel-1`
    `laravel-2`
    `laravel-3` 
    `laravel-4`
    `laravel-5` 

    Comando para acessar o terminal do container, exemplo:

    Para entrar: `docker exec -it laravel-1 bash`
    Para sair: `exit`

    Após acessar o container, você pode:

    Instalar as dependências do projeto usando o Composer.
    `Composer install`

    Instalar e publicar a extensão do Sanctum.
    `composer require laravel/sanctum`
    `php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider`

    Instalar a biblioteca do "PHPMailer".
    `composer require phpmailer/phpmailer`

    Instalar e publicar a exetensão do Swagger.
    `composer require darkaonline/l5-swagger`
    `php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"`

    Gerar uma chave de aplicação Laravel para configurar a criptografia.
    `php artisan key:generate`

    Definir permissões de acesso a diretórios e configurações.
    `chmod -R 775 storage bootstrap/cache`
    `chown -R www-data:www-data /var/www/laravel`

    Limpar os caches do framework para garantir que todas as configurações sejam aplicadas corretamente.
    `php artisan config:cache`
    `php artisan route:cache`
    `php artisan view:cache`

3. **Reiniciando os Containeres:**

   Caso haja a nescessidade, você pode reiniciar todos os containeres rodando os comando abaixo:

   `docker stop $(docker ps -q)` --> para parar.
   `docker start $(docker ps -q)` --> para iniciar.

4. **Colocando os containeres na rede:**

   Para adiciona-los na mesma rede no intuito de conectar um container no outro, basta somente rodar os comandos abaixo no terminal:

   `docker network connect laravel_app nginx-container`
   `docker network connect laravel_app laravel-1`
   `docker network connect laravel_app laravel-2`
   `docker network connect laravel_app laravel-3`
   `docker network connect laravel_app laravel-4`
   `docker network connect laravel_app laravel-5`
   `docker network connect laravel_app redis-container`
   `docker network connect laravel_app mysql-container`
   `docker network connect laravel_app ngrok-container`

5. **Rodar as Migrações:**

   Execute as migrações para criar as tabelas no banco de dados.
    `php artisan migrate`

   Não esqueça de realizar as configurações do banco de dados desejado no arquivo .env antes de executar o comando!

6. **Rodar script para alimentar o banco:**

    Na pasta do projeto, existe um arquivo com a extensão .php denominado "import_words.php".
    Esse arquivo executa um comando responsável por ler os registros de um arquivo json ("words_dictionary.json") e armazenar esses dados na tabela do banco. Os dados em si são palavras que nescessitam estar presentes no dicionário, para adicioná-las ao banco, basta dentro do terminal de um container rodar o comando abaixo:  
        
    `php import_words.php`

7. **Testar as Rotas da API:**
    
   Utilize o Insomnia para testar as rotas da API. As rotas principais incluem:

   ** Gerenciamento de Usuário:
   - **GET** `/api/` - Bem-vindo ao dicionário!.
   - **GET** `/api/user/me/` - Realiza a visualização do usuário autenticado.
   - **GET** `/api/viewRecord/` - Realiza a visualização de todos os usuários registrados.
   - **POST** `/api/auth/signin/` - Realiza a autenticação do usuário.
   - **POST** `/api/auth/signup/` - Realiza o registro do usuário.
   - **POST** `/api/auth/forgotPassword/` - Realiza o envio de uma senha aleatória via email para o usuário que esqueceu sua chave de acesso.
   - **POST** `/api/logoutUser/{id_user}/` - Realiza o logout do usuário autenticado.
   - **PUT** `/api/updateRecord/{id_user}/` - Realiza a atualização de dados cadastrais do usuário registrado.
   - **DELETE** `/api/deleteRecord/{id_user}/` - Realiza a exclusão do usuário selecionado do banco de dados.

   ** Gerenciamento de Palavras:
   - **GET** `/api/entries/en?search=fire&limit=15&page=2&order=desc` - Realiza a visualização de todas as palavras presentes no dicionário.
   - **GET** `/api/user/me/{id_user}/history?search=fire&limit=15&page=2&order=desc` - Realiza a visualização do histórico de palavras pesquisadas pelo o usuário.
   - **POST** `/api/entries/en/{id_user}/{word}/favorite` - Adiciona a lista de favoritos a palavra selecionada pelo o usuário.
   - **POST** `/api/user/me/{id_user}/favorites?search=fire&limit=15&page=2&order=desc` - Realiza a visualização de todas as palavras adicionadas na lista de favoritos do usuário..
   - **POST** `/api/entries/en/{word}` - Retorna os dados da palavra pesquisada pelo o usuário.
   - **DELETE** `/api/entries/en/{id_user}/{word}/unfavorite` - Realiza a exclusão das palavras adicionadas na lista de favoritos do usuário.

   ** Words API
   - **GET** `/api/words/{word}` - Integração com o proxy da Words API.

8. **Parâmetros:**

As rotas abaixo recebem os seguintes parâmetros:

- **POST** `/api/auth/signin/`
   'email' => Email do Usuário;
   'password' => Senha do usuário;

- **POST** `/api/auth/signup/`
   'name' => Nome do Usuário;
   'email' => Email do Usuário;
   'password' => Senha do usuário;

- **POST** `/api/auth/forgotPassword/`
   'email' => Email do Usuário;
   'password' => Senha do usuário;

- **POST** `/api/logoutUser/{id_user}/`
   '{id_user}' => Id do Usuário logado;

- **PUT** `/api/updateRecord/{id_user}/`
   '{id_user}' => Id do Usuário logado;
   'name' => Nome do Usuário;
   'email' => Email do Usuário;
   'password' => Senha do usuário;

- **DELETE** `/api/deleteRecord/{id_user}/` 
   '{id_user}' => Id do Usuário logado;

- **GET** `/api/user/me/{id_user}/history?search=fire&limit=15&page=2&order=desc`
   '{id_user}' => Id do Usuário logado;

- **POST** `/api/entries/en/{id_user}/{word}/favorite`
   '{id_user}' => Id do Usuário logado;
   '{word}' => palavra selecionada;

- **POST** `/api/user/me/{id_user}/favorites?search=fire&limit=15&page=2&order=desc`
   '{id_user}' => Id do Usuário logado;

- **POST** `/api/entries/en/{word}`
   '{word}' => palavra selecionada;

- **DELETE** `/api/entries/en/{id_user}/{word}/unfavorite`
   '{id_user}' => Id do Usuário logado;
   '{word}' => palavra selecionada;

- **GET** `/api/words/{word}`
   '{word}' => palavra sde configuração;

## Emails

O projeto utiliza uma funcionalidade de envio de emails para cada interação que o usuário solicitar quando esquecer sua chave de acesso.

- **Email:** Os emails são enviados utilizando um serviço do Laravel (PHPMailer) e pode ser configurado para usar o Mailtrap durante o desenvolvimento.

Não esqueça de realizar as configurações do seu provedor de email SMTP no arquivo .env antes de usar essa funcionalidade!

## Roles e Permissões

O projeto inclui dois tipos de categoria de usuários:

## Ferramentas

- **Mailtrap:** Usado para testar o envio de e-mails durante o desenvolvimento.
- **Insomnia:** Utilizado para testar e documentar as rotas da API.
- **Swagger:** Utilizado para documentar as rotas da APIResources
  `php artisan l5-swagger:generate`

## Contribuição

Contribuições são bem-vindas! Se você encontrar problemas ou tiver sugestões, sinta-se à vontade para abrir uma issue ou enviar um pull request.

## Licença

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
