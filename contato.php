<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Contato</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/contato.css">
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
        <?php if (isset($_SESSION['nome'])): ?>
            <span class="item-description">Bem-vindo, <?php echo htmlspecialchars($_SESSION['nome']); ?>!</span>
        <?php endif; ?>
        <span class="item-description">A melhor churrascaria!</span>
    </p>
        </div>
        <ul id="side_items">
            <li class="side-item"><a href="index.php"><i class="fa-solid fa-house"></i><span class="item-description">Home</span></a></li>
            <?php if (!isset($_SESSION['email'])): ?>
                <li class="side-item"><a href="login.php"><i class="fa-solid fa-user"></i><span class="item-description">Logar</span></a></li>
                <li class="side-item"><a href="cadastrar.php"><i class="fa-solid fa-paperclip"></i><span class="item-description">Cadastrar</span></a></li>
            <?php else: ?>
                <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'A'): ?>
                    <li class="side-item"><a href="admin/sistema.php"><i class="fas fa-desktop"></i><span class="item-description">Sistema</span></a></li>
                <?php endif; ?>
                <li class="side-item"><a href="reservas.php"><i class="fa-solid fa-utensils"></i><span class="item-description">Reservas</span></a></li>
                <li class="side-item"> <a href="minhas_reservas.php"> <i class="fa-solid fa-bacon"></i><span class="item-description">Minhas Reservas</span> <?php if (isset($_SESSION['nova_reserva'])): ?> <span class="notificacao"></span> <?php endif; ?></a> </li>
                <li class="side-item"><a href="eventos.php"><i class="fas fa-campground"></i><span class="item-description">Eventos</span></a></li>
                <li class="side-item"><a href="meus_agendamentos.php"><i class="fa-solid fa-calendar-days"></i><span class="item-description">Meus Agendamentos</span></a></li>
                <li class="side-item"><a href="cardapio.php"><i class="fa-solid fa-clipboard-list"></i><span class="item-description">Cardápio</span></a></li>
            <?php endif; ?>
            <li class="side-item"><a href="sobre_nos.php"><i class="fa-solid fa-drumstick-bite"></i><span class="item-description">Sobre nós</span></a></li>
            <li class="side-item active"><a href="contato.php"><i class="fa-solid fa-phone"></i><span class="item-description">Contato</span></a></li>
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

    <main class="flex-grow-1">
        <div class="estrutura-main fade-in">
            <div class="container">
                <div class="redes-sociais">
                    <img src="img/icone.png" alt="Imagem de redes sociais">
                    <div class="navbar">
                        <div class="box">
                            <div class="icon instagram">
                                <ion-icon name="logo-instagram"></ion-icon>
                            </div>
                            <p>@delicious_churras</p>
                        </div>

                        <div class="box">
                            <div class="icon whatsapp">
                                <ion-icon name="logo-whatsapp"></ion-icon>
                            </div>
                            <p>(45) 91234 - 5678</p>
                        </div>

                        <div class="box">
                            <div class="icon facebook">
                                <ion-icon name="logo-facebook"></ion-icon>
                            </div>
                            <p> Delicious Churras</p>
                        </div>

                        <div class="box">
                            <div class="icon twitter">
                                <ion-icon name="logo-twitter"></ion-icon>
                            </div>
                            <p>@Delicious_churras</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script src="js/animacao.js"></script>
<script src="js/barra_lateral.js"></script>
<script src="https://unpkg.com/ionicons@4.5.10-0/dist/ionicons.js"></script>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
</body>

</html>