<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Esqueci minha senha</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/barras.css">
    <link rel="stylesheet" href="css/alert.css">
    <link rel="stylesheet" href="css/cadastro.css">
</head>

<body>

    <div class="barra-superior">
        <div>
            <img src="home.svg" alt="icone de home">
            <h2>Delicious Churras</h2>
        </div>
    </div>

    <div class="register-container">
        <div class="register">
            <h3>Esqueci minha senha</h3>
            
            <?php if (isset($_SESSION['status'])): ?>
                <div class="alert alert-<?php echo $_SESSION['status'] === 'success' ? 'success' : 'error'; ?>">
                    <?php
                    echo htmlspecialchars($_SESSION['message']);
                    unset($_SESSION['status']);
                    unset($_SESSION['message']);
                    ?>
                </div>
            <?php endif; ?>

            <form method="post" action="enviar_resetar_senha.php">
                <div class="input-container">
                    <input type="email" name="email" id="email" required>
                    <label for="email" class="label">Insira seu e-mail</label>
                    <div class="underline"></div>
                </div>
                <button class="logina">Enviar</button>
            </form>
        </div>
    </div>

    <div class="barra-inferior">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 text-center">
                    <p>&copy; <?= date('Y') ?> Churrascaria Delicious Churras. Todos os direitos reservados.</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>

</html>