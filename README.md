# users_symfony
Instruções de Instalação
=================

Clone esse repositorio e execute 

```bash
$ composer install
```
Preencha os campos abaixo no arquivo .env
Para conexão com banco de dados preencha
```
DATABASE_URL=
```
Para conexão com o servidor de emails
```
MAILER_DSN=
```
Execute o comando para criar as tabelas do banco de dados 
```bash
$ php bin/console doctrine:migrations:migrate
```
Execute para iniciar o servidor da aplicação localmente

```
$  symfony server:start -d
```
Como os emails para envio de recuperação de senha estão sendo enviados de forma assincrona execute 
```
$ php bin/console messenger:consume async
```


