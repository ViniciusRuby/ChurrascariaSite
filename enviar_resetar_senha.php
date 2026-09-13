<?php
session_start();
$conexao = require_once('conectar.php');

$email = $_POST["email"] ?? '';
if (empty($email)) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Campo de e-mail vazio.';
    header("Location: resetar_senha.php");
    exit();
}

// Geração do token
$token = bin2hex(random_bytes(16));
$token_hash = hash("sha256", $token);
$expiry = date("Y-m-d H:i:s", time() + 60 * 30);

try {
    // Verificação se o e-mail existe na base de dados
    $sql_check_email = "SELECT email FROM tb_clientes WHERE email = ?";
    $stmt_check_email = $conexao->prepare($sql_check_email);
    $stmt_check_email->execute([$email]);
    $usuario = $stmt_check_email->fetch();

    if (!$usuario) {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'O E-mail informado não foi encontrado.';
        header("Location: resetar_senha.php");
        exit();
    }

    // Atualiza o banco de dados com o token e a data de expiração
    $sql = "UPDATE tb_clientes SET token_resetar = ?, token_expirar = ? WHERE email = ?";
    $stmt = $conexao->prepare($sql);
    $executou = $stmt->execute([$token_hash, $expiry, $email]);

    if ($executou) {
        require __DIR__ . "/mailer.php"; // Carrega o mailer
        sendPasswordResetEmail($email, $token);
        $_SESSION['status'] = 'success';
        $_SESSION['message'] = 'E-mail enviado! Cheque sua caixa de entrada';
    } else {
        $_SESSION['status'] = 'error';
        $_SESSION['message'] = 'Erro ao executar a consulta.';
    }
} catch (PDOException $e) {
    $_SESSION['status'] = 'error';
    $_SESSION['message'] = 'Erro na base de dados: ' . $e->getMessage();
}

// Redireciona para a página de resetar senha
header("Location: resetar_senha.php");
exit();
?>