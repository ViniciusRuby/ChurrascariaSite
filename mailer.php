<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . "/vendor/autoload.php";

function sendPasswordResetEmail($email, $token_hash) {
    // Instanciar PHPMailer
    $mail = new PHPMailer(true);
    
    try {
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Desativar depuração para e-mails
        $mail->SMTPAuth = true;

        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465; // Porta SSL
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // Usar SMTPS
        $mail->Username = 'deliciouschurras@gmail.com'; // E-mail que irá enviar
        $mail->Password = 'rjgoxdsvohfoknxe'; // Senha de APP

        // Idealmente, o setFrom deve ser o mesmo e-mail autenticado para evitar bloqueios de spam do Gmail
        $mail->setFrom("deliciouschurras@gmail.com", "Delicious Churras");
        $mail->addAddress($email);
        $mail->Subject = "Redefinir Senha";
        $mail->isHTML(true);
        
        $ano_atual = date('Y'); // Pega o ano atual automaticamente
        $baseUrl = isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : 'http://127.0.0.1:8000';
        
        $mail->Body = <<<END
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinição de Senha</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #0f0f0f;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #1c1c1c;
            padding: 15px;
            border-radius: 5px;
        }
        .header {
            background-color: #652F00;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .message {
            margin: 20px 0;
            line-height: 1.6;
            color: #fff;
        }
        .footer {
            font-size: 12px;
            color: #aaa;
            text-align: center;
            margin-top: 20px;
        }
        a {
            color: #FFD700;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Redefinição de Senha</h2>
        </div>
        <div class="message">
            <p>Olá,</p>
            <p>Você solicitou uma redefinição de senha.</p>
            <p>Caso não tenha solicitado essa alteração, ignore este e-mail.</p>
            <p>Para continuar, clique no link abaixo:</p>
            <p><a href="{$baseUrl}/nova_senha.php?token={$token_hash}">Redefinir Senha</a></p>
        </div>
        <div class="footer">
            <p>&copy; $ano_atual Delicious Churras. Todos os direitos reservados.</p>
        </div>
    </div>
</body>
</html>
END;

        // Enviar o e-mail
        $mail->send();
        // Nota: O ideal é não dar 'echo' dentro de uma função se ela for usada no meio de um processamento de cabeçalhos (headers).
        // Mas manteremos como você fez.
        // echo "Mensagem enviada, verifique sua caixa de entrada."; 
    } catch (Exception $e) {
        // Não exibir a mensagem de erro em produção
        // echo "Ocorreu um erro ao enviar o e-mail. Tente novamente mais tarde.";
    }
}
?>