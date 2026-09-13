<?php
session_start();

if (!empty($_POST['email']) && !empty($_POST['senha'])) {
    require_once('conectar.php');
    $conexao = obterConexao();
    
    $email = trim($_POST['email']);
    $senha = md5($_POST['senha']); // Criptografa a senha usando MD5

    try {
        $stmt = $conexao->prepare("SELECT * FROM tb_clientes WHERE email = ? AND senha = ?");
        $stmt->execute([$email, $senha]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Se não encontrar o usuário, define uma mensagem de erro
            unset($_SESSION['email']);
            unset($_SESSION['senha']);
            unset($_SESSION['cod_cliente']);
            $_SESSION['mensagem'] = 'Email ou senha incorretos. Tente novamente.';
            header('Location: login.php');
            exit();
        } else {
            // Se o usuário for encontrado, salva dados na sessão
            $_SESSION['cod_cliente'] = $row['cod_cliente'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['senha'] = $senha;
            $_SESSION['tipo'] = $row['tipo'];
            $_SESSION['nome'] = $row['nome'];

            // Redireciona o usuário
            header('Location: index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = 'Erro no servidor: ' . $e->getMessage();
        header('Location: login.php');
        exit();
    }

} else {
    // Se os campos não forem preenchidos
    $_SESSION['mensagem'] = 'Por favor, preencha todos os campos.';
    header('Location: login.php');
    exit();
}
?>