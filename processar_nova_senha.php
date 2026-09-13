<?php
session_start();

$token = $_POST["token"] ?? '';
$token_hash = hash("sha256", $token);
$conexao = require_once('conectar.php');

$message = "";

try {
    $sql = "SELECT * FROM tb_clientes WHERE token_resetar = ?";
    $stmt = $conexao->prepare($sql);
    $stmt->execute([$token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $message = "Token não encontrado.";
    } elseif (strtotime($user["token_expirar"]) <= time()) {
        $message = "Token expirou.";
    } elseif (strlen($_POST["password"] ?? '') < 8) {
        $message = "A senha deve conter 8 carácteres.";
    } elseif (($_POST["password"] ?? '') !== ($_POST["password_confirmation"] ?? '')) {
        $message = "As senhas não são iguais";
    } else {
        $password_hash = md5($_POST["password"]);
        $sql = "UPDATE tb_clientes SET senha = ?, token_resetar = NULL, token_expirar = NULL WHERE cod_cliente = ?";
        $stmt_update = $conexao->prepare($sql);
        $stmt_update->execute([$password_hash, $user["cod_cliente"]]);

        if ($stmt_update->rowCount() > 0) {
            $message = "Senha atualizada.";
        } else {
            $message = "Falha em atualizar a senha";
        }
    }
} catch (PDOException $e) {
    $message = "Erro ao processar a solicitação: " . $e->getMessage();
}

$_SESSION['message'] = $message;

header("Location: nova_senha.php?token=" . urlencode($token));
exit();
?>