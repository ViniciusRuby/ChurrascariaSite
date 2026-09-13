<?php
session_start();
require_once('conectar.php');
$conexao = obterConexao();

// Processa o cancelamento antes de realizar as buscas no banco
if (isset($_GET['cancelar_reserva'])) {
    $cod_reserva = $_GET['cancelar_reserva'];
    try {
        $stmt_cancelar = $conexao->prepare("DELETE FROM tb_reservas WHERE cod_reserva = ?");
        $stmt_cancelar->execute([$cod_reserva]);
    } catch (PDOException $e) {
        // Log do erro se necessário
    }

    header("Location: minhas_reservas.php");
    exit;
}

$reservas = [];

if (isset($_SESSION['nova_reserva'])) {
    unset($_SESSION['nova_reserva']);
}

if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
} else {
    try {
        $email = $_SESSION['email'];
        $cod_cliente = $_SESSION['cod_cliente'] ?? null;
        
        if (!$cod_cliente) {
            $stmt_cliente = $conexao->prepare("SELECT cod_cliente FROM tb_clientes WHERE email = ?");
            $stmt_cliente->execute([$email]);
            $row_cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
            if ($row_cliente) {
                $cod_cliente = $row_cliente['cod_cliente'];
                $_SESSION['cod_cliente'] = $cod_cliente;
            }
        }

        if ($cod_cliente) {
            $data_atual = date('Y-m-d');

            $stmt_reservas = $conexao->prepare("
                SELECT r.cod_reserva, r.dt, r.hora, r.qtdmesa, r.valor, e.nome AS estabelecimento
                FROM tb_reservas r
                JOIN tb_estabelecimentos e ON r.cod_estabelecimento = e.cod_estabelecimento
                WHERE r.cod_cliente = ? AND r.dt >= ?
                ORDER BY r.cod_reserva ASC
            ");
            $stmt_reservas->execute([$cod_cliente, $data_atual]);
            $reservas = $stmt_reservas->fetchAll(PDO::FETCH_ASSOC);
        } else {
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $e) {
        // Tratamento de erro de banco de dados
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Minhas Reservas</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/barra_lateral.css">
    <link rel="stylesheet" href="css/minhas_reservas.css">
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
                <li class="side-item active"><a href="minhas_reservas.php"><i class="fa-solid fa-bacon"></i><span class="item-description">Minhas Reservas</span></a></li>
                <li class="side-item"><a href="eventos.php"><i class="fas fa-campground"></i><span class="item-description">Eventos</span></a></li>
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
            <h1>Minhas Reservas</h1>
        </div>
    </div>
    <div class="row cardapio-opcoes">
        <?php if (!empty($reservas)): ?>
            <?php foreach ($reservas as $reserva): ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="img/reserva.png" class="card-img-top" alt="Reserva">
                        <div class="card-body">
                            <h5 class="card-title">Reserva na <?php echo htmlspecialchars($reserva['estabelecimento']); ?></h5>
                            <p class="card-text"><strong>Data:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($reserva['dt']))); ?></p>
                            <p class="card-text"><strong>Horário:</strong> <?php echo htmlspecialchars($reserva['hora']); ?></p>
                            <p class="card-text"><strong>Quantidade de Mesas:</strong> <?php echo htmlspecialchars($reserva['qtdmesa']); ?></p>
                            <p class="card-text"><strong>Valor:</strong> R$ <?php echo number_format((float)$reserva['valor'], 2, ',', '.'); ?></p>
                        </div>
                        <button class="btn btn-danger cancelar-btn" data-toggle="modal" data-target="#confirmCancelModal" data-cod-reserva="<?php echo $reserva['cod_reserva']; ?>">
                            Cancelar Reserva
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning" role="alert">
                    Você não possui reservas futuras.
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
</div>

<!-- Modal de Confirmação -->
<div class="modal fade" id="confirmCancelModal" tabindex="-1" role="dialog" aria-labelledby="confirmCancelModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmCancelModalLabel">Confirmar Cancelamento</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        Tem certeza que deseja cancelar esta reserva?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
        <a href="#" id="confirmCancelBtn" class="btn btn-danger">Cancelar Reserva</a>
      </div>
    </div>
  </div>
</div>

<script src="js/barra_lateral.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    $('.cancelar-btn').click(function() {
        var reservaId = $(this).data('cod-reserva');
        $('#confirmCancelBtn').attr('href', 'minhas_reservas.php?cancelar_reserva=' + reservaId);
    });
});
</script>

</body>
</html>