<?php

declare(strict_types=1);

header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store');

$mensagem = null;
$login = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = is_string($_POST['login'] ?? null) ? trim($_POST['login']) : '';
    $senha = is_string($_POST['senha'] ?? null) ? $_POST['senha'] : '';
    $mensagem = 'ERRADO';

    if (
        preg_match('/\A[^\x00-\x1F\x7F]{1,100}\z/u', $login) === 1
        && $senha !== '' && strlen($senha) <= 4096
    ) {
        try {
            $usuarioBanco = getenv('DB_USER');
            $senhaBanco = getenv('DB_PASSWORD');
            $pdo = new PDO(
                getenv('DB_DSN') ?: 'mysql:host=127.0.0.1;port=3306;dbname=login_demo;charset=utf8mb4',
                $usuarioBanco !== false ? $usuarioBanco : '',
                $senhaBanco !== false ? $senhaBanco : '',
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ],
            );

            $consulta = $pdo->prepare('SELECT senha_hash FROM usuarios WHERE login = :login LIMIT 1');
            $consulta->execute(['login' => $login]);
            $hash = $consulta->fetchColumn();

            if (is_string($hash) && password_verify($senha, $hash)) {
                $mensagem = 'OK';
            }
        } catch (PDOException) {
            // A resposta não revela detalhes do banco nem credenciais.
            error_log('Falha ao consultar o banco durante a verificação de login.');
            http_response_code(503);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verificar login</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 24px; background: #f1f5f9; color: #172033; font-family: system-ui, sans-serif; }
        main { max-width: 380px; margin: 10vh auto; padding: 28px; border-radius: 12px; background: white; }
        h1 { margin-top: 0; font-size: 1.5rem; }
        label { display: block; margin-top: 16px; font-weight: 600; }
        input, button { width: 100%; padding: 12px; margin-top: 6px; border: 1px solid #64748b; border-radius: 6px; font: inherit; }
        button { margin-top: 24px; background: #1d4ed8; color: white; border: 0; cursor: pointer; }
        .resultado { padding: 12px; border: 1px solid currentColor; border-radius: 6px; font-weight: 700; }
    </style>
</head>
<body>
    <main>
        <h1>Verificar login</h1>

        <?php if ($mensagem !== null): ?>
            <p class="resultado" role="status"><?= $mensagem ?></p>
        <?php endif; ?>

        <form method="post">
            <label for="login">Login</label>
            <input id="login" name="login" type="text" maxlength="100" autocomplete="username" required
                   value="<?= htmlspecialchars($login, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">

            <label for="senha">Senha</label>
            <input id="senha" name="senha" type="password" autocomplete="current-password" required>

            <button type="submit">Verificar</button>
        </form>
    </main>
</body>
</html>
