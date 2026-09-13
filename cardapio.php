<?php
session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cardápio</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/barra_lateral.css">
    <link rel="stylesheet" href="css/cardapio.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <li class="side-item active"><a href="cardapio.php"><i class="fa-solid fa-clipboard-list"></i><span class="item-description">Cardápio</span></a></li>
            <?php endif; ?>
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

    <main class="container">
        <div class="top-bar">
            <div class="cardapio-title-container">
                <h1>Nosso Cardápio</h1>
            </div>
        </div>
        <div class="row cardapio-opcoes">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="img/picanha.jpg" class="card-img-top" alt="Picanha">
                    <div class="card-body">
                        <h5 class="card-title">Picanha</h5>
                        <p class="card-text">A clássica picanha no ponto certo.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="img/costela.jfif" class="card-img-top" alt="Costela">
                    <div class="card-body">
                        <h5 class="card-title">Costela</h5>
                        <p class="card-text">Costela assada lentamente por 6 horas.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="img/Fraldinha.jfif" class="card-img-top" alt="Fraldinha">
                    <div class="card-body">
                        <h5 class="card-title">Fraldinha</h5>
                        <p class="card-text">Fraldinha suculenta e macia.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="img/Alcatra.jfif" class="card-img-top" alt="Alcatra">
                    <div class="card-body">
                        <h5 class="card-title">Alcatra</h5>
                        <p class="card-text">Alcatra tenra e saborosa.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="img/Linguiça.jfif" class="card-img-top" alt="Linguiça">
                    <div class="card-body">
                        <h5 class="card-title">Linguiça</h5>
                        <p class="card-text">Linguiça caseira grelhada.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="img/Frango.jpg" class="card-img-top" alt="Frango">
                    <div class="card-body">
                        <h5 class="card-title">Frango</h5>
                        <p class="card-text">Frango grelhado com tempero especial.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="img/Cupim.jpg" class="card-img-top" alt="Cupim">
                    <div class="card-body">
                        <h5 class="card-title">Cupim</h5>
                        <p class="card-text">Cupim grelhado e suculento.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="img/Salada.jfif" class="card-img-top" alt="Salada">
                    <div class="card-body">
                        <h5 class="card-title">Salada</h5>
                        <p class="card-text">Salada fresca e colorida.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="img/Pudim.webp" class="card-img-top" alt="Pudim">
                    <div class="card-body">
                        <h5 class="card-title">Pudim</h5>
                        <p class="card-text">Pudim cremoso para a sobremesa.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
<script src="js/barra_lateral.js"></script>
<script src="js/pesquisar.js"></script>
</body>
</html>