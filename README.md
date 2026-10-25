# TestSW

Formulário em PHP 8.4 que consulta um banco MySQL com PDO. Exibe `OK` para
credenciais válidas e `ERRADO` para login inexistente ou senha incorreta.
A consulta usa parâmetros e a senha é verificada com `password_verify()`.

## Preparação

Requisitos: PHP 8.4 com as extensões `PDO` e `pdo_mysql`, e MySQL 8.4.

1. Execute [database.sql](database.sql) no MySQL para criar o banco `login_demo`
   e a tabela `usuarios`.
2. Gere o hash de uma senha de exemplo:

   ```sh
   php -r 'echo password_hash("minha-senha-de-teste", PASSWORD_DEFAULT), PHP_EOL;'
   ```

3. Copie o hash gerado para a coluna `senha_hash` ao cadastrar o usuário:

   ```sql
   INSERT INTO usuarios (login, senha_hash)
   VALUES ('usuario_teste', 'COLE_AQUI_O_HASH_GERADO');
   ```

   Não armazene a senha em texto puro. O formulário verifica usuários já
   cadastrados; ele não faz o cadastro.

## Execução local

Na raiz do repositório, configure a conexão no ambiente do processo:

```bash
export DB_DSN='mysql:host=127.0.0.1;port=3306;dbname=login_demo;charset=utf8mb4'
export DB_USER='seu_usuario_mysql'
read -r -s -p 'Senha do MySQL: ' DB_PASSWORD
echo
export DB_PASSWORD
php -S 127.0.0.1:8000 -t public
```

Abra o servidor na porta 8000 e informe `usuario_teste` e a senha usada na
geração do hash. O resultado será `OK`; uma senha incorreta retorna `ERRADO`.
O servidor embutido do PHP serve para desenvolvimento local.

As credenciais do MySQL vêm de `DB_USER` e `DB_PASSWORD`, sem serem gravadas
no código. `DB_DSN` define o endereço e o banco; se omitida, usa o endereço
mostrado acima. Falhas de conexão ou consulta retornam `ERRADO` com HTTP 503
e registram uma mensagem genérica no log do servidor.

O formulário apenas verifica as credenciais; não cria uma sessão de usuário.
