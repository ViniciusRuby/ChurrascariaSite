<?php
session_start();
$mensagem = '';
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Login</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/cadastro.css">
    <link rel="stylesheet" href="css/alert.css">
    <link rel="stylesheet" href="css/barra_lateral.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="d-flex">
        <nav id="sidebar">
            <div id="sidebar_content">
                <div id="user">
                    <img src="img/icone.png" id="user_avatar" alt="Avatar">
                    <p id="user_infos">
                        <span class="item-description">Delicious Churras</span>
                        <span class="item-description">A melhor churrascaria!</span>
                    </p>
                </div>
                <ul id="side_items">
                    <li class="side-item"><a href="index.php"><i class="fa-solid fa-house"></i><span class="item-description">Home</span></a></li>
                    <li class="side-item active"><a href="login.php"><i class="fa-solid fa-user"></i><span class="item-description">Logar</span></a></li>
                    <li class="side-item"><a href="cadastrar.php"><i class="fa-solid fa-paperclip"></i><span class="item-description">Cadastrar</span></a></li>
                    <li class="side-item"><a href="sobre_nos.php"><i class="fa-solid fa-drumstick-bite"></i><span class="item-description">Sobre nós</span></a></li>
                    <li class="side-item"><a href="contato.php"><i class="fa-solid fa-phone"></i><span class="item-description">Contato</span></a></li>
                </ul>
                <button id="open_btn">
                    <i id="open_btn_icon" class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            <div id="logout">
                <button id="logout_btn" onclick="window.location.href='sair.php';">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span class="item-description">Sair</span>
                </button>
            </div>
        </nav>

        <main class="flex-grow-1 d-flex justify-content-center align-items-center">
            <div class="register-container">
                <div class="register">
                    <h1>Login</h1>
                    <?php if ($mensagem): ?>
                        <div class="alert alert-error"><?= htmlspecialchars($mensagem) ?></div>
                    <?php endif; ?>
                    <form action="salvar_login.php" method="post">
                        <div class="input-container">
                            <input type="text" name="email" required>
                            <label class="label">E-mail</label>
                            <div class="underline"></div>
                        </div>
                        <div class="input-container">
                            <input type="password" name="senha" required>
                            <label class="label">Senha</label>
                            <div class="underline"></div>
                        </div>
                        <button class="loginb" type="submit" name="submit">Entrar</button>
                        <p>Não tem uma conta? <a href="cadastrar.php">Clique aqui</a> para se cadastrar.</p>
                        <p><a href="resetar_senha.php">Esqueceu a senha?</a></p>
                    </form>
                </div>
            </div>

            <div class="background-image fade-out"></div>
        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="js/animacao.js"></script>
    <script src="js/barra_lateral.js"></script>
</body>

</html>