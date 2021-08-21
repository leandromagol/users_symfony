# users_symfony
Instruções de Instalação
=================

Clone esse repositório e execute

```bash
$ composer install
```
Preencha os campos abaixo no arquivo .env
Para conexão com banco de dados preencha
```
DATABASE_URL=
```
Para conexão com mongodb
```
MONGODB_URL=
MONGODB_DB=
```
Para conexão com o servidor de emails
```
MAILER_DSN=
```
Execute o comando para criar as tabelas do banco de dados
```bash
$ php bin/console doctrine:migrations:migrate
```
Execute o comando abaixo para inserir o primeiro usuário no banco de dados

```bash
$ php bin/console doctrine:fixtures:load
```
Os dados de login desse user são

```
username:admin@admin.com
password:12345678
```
Execute para iniciar o servidor da aplicação localmente

```
$  php bin/console server:start -d
```
Como os emails para envio de recuperação de senha estão sendo enviados de forma assíncrona execute
```
$ php bin/console messenger:consume async
```



