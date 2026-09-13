<?php
session_start();
require_once('conectar.php');
$conexao = obterConexao();

$mensagem = '';
$agendamentos = [];

if (isset($_SESSION['novo_agendamento'])) {
    unset($_SESSION['novo_agendamento']);
}

// Processa o cancelamento de agendamento via POST
if (isset($_POST['cancelar_agendamento'])) {
    $cod_agendamento = $_POST['cod_agendamento'] ?? null;
    if ($cod_agendamento) {
        try {
            $stmt_cancelar = $conexao->prepare("DELETE FROM tb_agendamentos WHERE cod_agendamento = ?");
            $stmt_cancelar->execute([$cod_agendamento]);
        } catch (PDOException $e) {
            // Em produção pode logar o erro
        }
    }
    header("Location: meus_agendamentos.php");
    exit();
}

if (!isset($_SESSION['email'])) {
    $mensagem = 'Erro: Usuário não está logado.';
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
            $current_date = date('Y-m-d');

            // Busca os agendamentos do cliente
            $stmt_agendamentos = $conexao->prepare("
                SELECT a.cod_agendamento, a.dt, a.hora, e.nome AS estabelecimento, ev.nome AS evento, a.pagamento
                FROM tb_agendamentos a
                INNER JOIN tb_estabelecimentos e ON a.cod_estabelecimento = e.cod_estabelecimento
                INNER JOIN tb_eventos ev ON a.cod_evento = ev.cod_evento
                WHERE a.cod_cliente = ? 
                ORDER BY a.cod_agendamento ASC");

            $stmt_agendamentos->execute([$cod_cliente]);
            $todos_agendamentos = $stmt_agendamentos->fetchAll(PDO::FETCH_ASSOC);

            // Filtragem por data atual ou posterior
            foreach ($todos_agendamentos as $row_agendamento) {
                if ($row_agendamento['dt'] >= $current_date) {
                    $agendamentos[] = $row_agendamento;
                }
            }
        } else {
            $mensagem = 'Erro: Cliente não encontrado.';
        }
    } catch (PDOException $e) {
        $mensagem = 'Erro na base de dados: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Meus Agendamentos</title>
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
                <li class="side-item"><a href="minhas_reservas.php"><i class="fa-solid fa-bacon"></i><span class="item-description">Minhas Reservas</span></a></li>
                <li class="side-item"><a href="eventos.php"><i class="fas fa-campground"></i><span class="item-description">Eventos</span></a></li>
                <li class="side-item active"><a href="meus_agendamentos.php"><i class="fa-solid fa-calendar-days"></i><span class="item-description">Meus Agendamentos</span></a></li>
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
            <h1>Meus Agendamentos</h1>
        </div>
    </div>
    <div class="row cardapio-opcoes">
        <?php if (!empty($agendamentos)): ?>
            <?php foreach ($agendamentos as $agendamento): ?>
                <div class="col-md-3 mb-4">
                    <div class="card">
                        <img src="img/evento.png" class="card-img-top" alt="Evento">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($agendamento['evento']); ?></h5>
                            <p class="card-text"><strong>Data:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($agendamento['dt']))); ?></p>
                            <p class="card-text"><strong>Horário:</strong> <?php echo htmlspecialchars($agendamento['hora']); ?></p>
                            <p class="card-text"><strong>Local:</strong> <?php echo htmlspecialchars($agendamento['estabelecimento']); ?></p>
                            <p class="card-text"><strong>Pagamento:</strong> <?php echo htmlspecialchars($agendamento['pagamento']); ?></p>
                        </div>
                        <button class="btn btn-danger cancelar-btn" data-toggle="modal" data-target="#cancelModal" data-agendamento="<?php echo $agendamento['cod_agendamento']; ?>">
                            Cancelar Agendamento
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning" role="alert">
                    Você não possui agendamentos de eventos futuros.
                </div>
            </div>
        <?php endif; ?>
    </div>
</main>
</div>

<!-- Modal de Cancelamento -->
<div class="modal fade" id="cancelModal" tabindex="-1" role="dialog" aria-labelledby="cancelModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="cancelModalLabel">Cancelar Agendamento</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Tem certeza que deseja cancelar este agendamento?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Voltar</button>
        <form method="POST" action="meus_agendamentos.php" id="cancelForm">
            <input type="hidden" name="cod_agendamento" id="cod_agendamento">
            <button type="submit" name="cancelar_agendamento" class="btn btn-danger">Confirmar</button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
<script>
    $('#cancelModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var agendamentoId = button.data('agendamento');
        var modal = $(this);
        modal.find('#cod_agendamento').val(agendamentoId);
    });
</script>
<script src="js/barra_lateral.js"></script>
</body>
</html>