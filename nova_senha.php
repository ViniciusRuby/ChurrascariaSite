<?php
session_start();
require_once('conectar.php');
$conexao = obterConexao();

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);

$token = $_GET["token"] ?? '';
$token_hash = hash("sha256", $token);
$user = null;

if (!empty($token)) {
    try {
        $sql = "SELECT * FROM tb_clientes WHERE token_resetar = ?";
        $stmt = $conexao->prepare($sql);
        $stmt->execute([$token_hash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die('Erro na preparação da consulta: ' . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Redefinir Senha</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="css/alert.css">
    <link rel="stylesheet" href="css/cadastro.css">
    <script>
        function redirectToLogin() {
            window.location.href = 'login.php';
        }
        
        window.onload = function() {
            const message = '<?= addslashes($message) ?>';
            if (message.includes("Senha atualizada")) {
                let countdown = 3;
                const countdownDisplay = document.getElementById("countdown");
                countdownDisplay.innerText = `Redirecionando em ${countdown} segundos...`;
                const interval = setInterval(function() {
                    countdown--;
                    if (countdown <= 0) {
                        clearInterval(interval);
                        redirectToLogin();
                    }
                    countdownDisplay.innerText = `Redirecionando em ${countdown} segundos...`;
                }, 1000);
            }
        };
    </script>
</head>
<body>
    <div class="background-image"></div>
    
    <div class="register-container">
        <div class="register">
            <h1>Redefinir Senha</h1>
            <?php if ($message): ?>
                <div class="alert <?= strpos($message, 'Falha') !== false ? 'alert-error' : 'alert-success' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
                <div id="countdown"></div>
            <?php endif; ?>
            <form method="post" action="processar_nova_senha.php">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div class="input-container">
                    <input type="password" id="password" name="password" required>
                    <label for="password" class="label">Senha nova</label>
                    <div class="underline"></div>
                </div>
                <div class="input-container">
                    <input type="password" id="password_confirmation" name="password_confirmation" required>
                    <label for="password_confirmation" class="label">Confirmar senha</label>
                    <div class="underline"></div>
                </div>
                <button class="logina">Enviar</button>
            </form>
        </div>
    </div>

</body>
</html>