# Teste API Essor

## Objetivos

Desafio/teste realizado por [Victor Dracxler](https://github.com/victordracxler), em fevereiro de 2024, com o objetivo de ingressar em vaga de Desenvolvedor Jr. na empresa Essor.

## Requisitos do Teste:

- Ser capaz de fazer a aplicação funcionar.
- Validar o CEP enviado na requisição de cadastro e update (CEP precisa ser válido e constar na base de dados do ViaCEP: https://viacep.com.br).
- Validar o CPF enviado na requisição de cadastro e update.
- Disponibilizar os serviços de "show", "delete", "update", baseado nos serviços existentes, "store" e "find" no controller "PessoaController".

## Relatório do Desafio

Para começar a rodar a API, tive que alterar a versão do PHP que estava na minha máquina, fazendo o downgrade da versão 8.2 para 7.2. Tive que instalar também outras extensões para a versão 7.2 do PHP.

Após preparar a máquina com as versões corretas, consegui rodar `composer install` com sucesso, sem mensagens de erro.

Criei um arquivo `.env` para configurar o banco de dados PostgreSQL e rodei o comando `php artisan migrate`.
Após as migrations, rodei o server com `php artisan serve`.

Criei um arquivo `.gitignore` para não subir os pacotes instalados pelo composer para o git.

Procurei primeiro por bugs mais óbvios, antes de mergulhar mais a fundo na arquitetura:

- Na Factory de User, a entidade User estava sendo importada incorretamente, `use App\User` ao invés de `use App\Entities\User`;
- Divisão por 0 em `PessoaService`, no método `create()`;
- Namespace incorreto em `Entities/Pessoa.php`.

Estava tentando fazer o `php artisan route:list` para identificar as rotas criadas para fazer as requisições via Postman, mas encontrava um erro de:

```bash
Illuminate\Contracts\Container\BindingResolutionException  : Target [App\Services\PessoaServiceInterface] is not instantiable while building [App\Http\Controllers\Api\PessoaController].
```

Consegui resolver ao verificar os `ServiceProviders` e corrigir duas coisas, uma no `TaskServiceProvider` (Remover o comentário da linha 28, que faz o bind de `PessoaServiceInterface::class` e `PessoaService::class`) e outra no `RepositoryServiceProvider` (no bind do método `boot()`), tendo como saída do comando `php artisan route:list` a mensagem:

```bash
+--------+----------+--------------------+------+------------------------------------------------+------------+
| Domain | Method   | URI                | Name | Action                                         | Middleware |
+--------+----------+--------------------+------+------------------------------------------------+------------+
|        | GET|HEAD | /                  |      | Closure                                        | web        |
|        | POST     | api/v1/pessoa      |      | App\Http\Controllers\Api\PessoaController@stor | api        |
|        | GET|HEAD | api/v1/pessoa/{id} |      | App\Http\Controllers\Api\PessoaController@show | api        |
+--------+----------+--------------------+------+------------------------------------------------+------------+
```

Em `config/auth.php`, na linha 72 corrigi de `App\User::class` para `User::class` e importei User com `use App\Entities\User;`.

Após ter certeza que a API estava funcionando, comecei a trabalhar no CRUD em si.

Primeiramente, no arquivo `api.php` corrigi a action da rota com método POST, direcionando para o método correto de `PessoaController`, pois estava escrito "stor" onde deveria estar escrito "store".

Depois, em `PessoaService`, completei os métodos `all()`, `delete()` e `update()`.

Voltando para `PessoaController`, primeiro fiz o método `index()` funcionar, usando a mesma lógica geral dos métodos `find()` e `store()`.

Ao criar o método `update()` e testar pelo Postman recebi o Status Code 403 e, ao investigar `PessoaUpdateRequest` percebi que o método `authorize()` estava retornando `false`. Trocando para `true`, o update de Pessoa retornou o código 200 e a entidade atualizada.

Implementei o método de `delete/destroy` e tomei o cuidado de adicionar os retornos de NOT_FOUND em update e delete caso a Pessoa não exista no database.

Neste momento, o retorno de `php artisan route:list` é:

```bash
+--------+----------+--------------------+------+---------------------------------------------------+------------+
| Domain | Method   | URI                | Name | Action                                            | Middleware |
+--------+----------+--------------------+------+---------------------------------------------------+------------+
|        | GET|HEAD | /                  |      | Closure                                           | web        |
|        | POST     | api/v1/pessoa      |      | App\Http\Controllers\Api\PessoaController@store   | api        |
|        | GET|HEAD | api/v1/pessoa      |      | App\Http\Controllers\Api\PessoaController@index   | api        |
|        | GET|HEAD | api/v1/pessoa/{id} |      | App\Http\Controllers\Api\PessoaController@show    | api        |
|        | PUT      | api/v1/pessoa/{id} |      | App\Http\Controllers\Api\PessoaController@update  | api        |
|        | DELETE   | api/v1/pessoa/{id} |      | App\Http\Controllers\Api\PessoaController@destroy | api        |
+--------+----------+--------------------+------+---------------------------------------------------+------------+
```

A **aplicação está funcionando** com o **CRUD completo de Pessoa**, satisfazendo, assim, os **requisitos 1 e 4 do teste**.

Para o requisito 2, **validação do CEP**, escolhi criar um Service chamado `CepService` para fazer a requisição à API do ViaCEP.
Para fazer a requisição à API externa utilizei o pacote Guzzle.
Após fazer o Service, o utilizei no Controller via injeção de dependência no `__construct()`, fiz a validação no método de store() e, após confirmar pelo Postman que a validação está OK, repliquei para o método update(), assim **satisfazendo o requisito 2**.

Para **validar o CPF**, busquei na internet as regras de validação de CPF e coloquei em um Service chamado `CpfService`.
Injetei o `CpfService` no Controller e fiz a validação no `store()` e `update()`.
Após confirmar pelo Postman que a validação está OK, **o requisito 3 foi satisfeito**.
