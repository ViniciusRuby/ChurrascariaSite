<?php
session_start();
require_once 'conectar.php';
$conexao = obterConexao();

try {
    $query = "SELECT e.cod_evento, e.nome, e.descricao, e.valor, e.tipo, i.diretorio 
              FROM tb_eventos e 
              LEFT JOIN tb_imagens i ON e.cod_evento = i.cod_evento
              WHERE e.ativo = 'S'";

    $stmt = $conexao->query($query);
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $eventos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Eventos</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/barra_lateral.css">
    <link rel="stylesheet" href="css/evento.css">
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
                <li class="side-item"><a href="minhas_reservas.php"><i class="fa-solid fa-bacon"></i><span
                                    class="item-description">Minhas reservas<?php if (isset($_SESSION['nova_reserva'])): ?>
                                        <span class="novo-indicador">Novo</span>
                                    <?php endif; ?></span></a></li>
                <li class="side-item active"><a href="eventos.php"><i class="fas fa-campground"></i><span class="item-description">Eventos</span></a></li>
                <li class="side-item"><a href="meus_agendamentos.php"><i class="fa-solid fa-calendar-days"></i><span class="item-description">Meus Agendamentos</span></a></li>
                <li class="side-item"><a href="cardapio.php"><i class="fa-solid fa-clipboard-list"></i><span class="item-description">Cardápio</span></a></li>
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

<main class="flex-grow-1">
    <div class="top-bar mb-4">
        <div class="cardapio-title-container">
            <h1>EVENTOS DISPONÍVEIS</h1>
        </div>
    </div>
    <div class="row cardapio-opcoes">
        <?php if (count($eventos) > 0): ?>
            <?php foreach ($eventos as $evento): 
                $imgSrc = !empty($evento['diretorio']) ? ltrim($evento['diretorio'], './') : 'img/evento.png';
                if (!file_exists($imgSrc)) {
                    $fallback = 'img_banco/' . str_replace(' ', '_', $evento['nome']) . '.png';
                    if (file_exists($fallback)) {
                        $imgSrc = $fallback;
                    } else {
                        $imgSrc = 'img/evento.png';
                    }
                }
            ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($evento['nome']); ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($evento['nome']); ?></h5>
                            <p class="card-text"><?php echo htmlspecialchars($evento['descricao']); ?></p>
                            <p class="card-text"><strong>Valor:</strong> R$ <?php echo number_format((float)$evento['valor'], 2, ',', '.'); ?></p>
                        </div>
                        <a href="agendar_evento.php?cod_evento=<?php echo $evento['cod_evento']; ?>" class="btn btn-primary agendar-btn">Agendar evento</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning" role="alert">
                    Nenhum evento disponível no momento.
                </div>
            </div>
        <?php endif; ?>
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