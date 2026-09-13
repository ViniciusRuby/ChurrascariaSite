<?php
session_start();
require_once 'conectar.php';
$conexao = obterConexao();

if (!isset($_SESSION['email'])) {
    echo "<script>alert('Você precisa estar logado para agendar um evento.'); window.location.href='login.php';</script>";
    exit;
}

$email_cliente = $_SESSION['email'];
$cod_cliente = $_SESSION['cod_cliente'] ?? null;

if (!$cod_cliente) {
    // Obter código do cliente
    $query = "SELECT cod_cliente FROM tb_clientes WHERE email = ?";
    $stmt = $conexao->prepare($query);
    $stmt->execute([$email_cliente]);
    $cliente = $stmt->fetch();

    if ($cliente) {
        $cod_cliente = $cliente['cod_cliente'];
        $_SESSION['cod_cliente'] = $cod_cliente;
    } else {
        echo "<script>alert('Cliente não encontrado.'); window.location.href='login.php';</script>";
        exit;
    }
}

$cod_evento = $_GET['cod_evento'] ?? null;
$evento = null;
$lugares_disponiveis = 0;

if ($cod_evento) {
    $query = "SELECT nome, descricao, valor, data_inicio, data_fim, tipo FROM tb_eventos WHERE cod_evento = ?";
    $stmt = $conexao->prepare($query);
    $stmt->execute([$cod_evento]);
    $evento = $stmt->fetch();

    if (!$evento) {
        echo "<script>alert('Evento não encontrado.'); window.location.href='eventos.php';</script>";
        exit;
    }

    switch ($evento['tipo']) {
        case 'P':
            $lugares_disponiveis = 30;
            break;
        case 'M':
            $lugares_disponiveis = 50;
            break;
        case 'G':
            $lugares_disponiveis = 70;
            break;
        default:
            $lugares_disponiveis = 0;
    }
}

// Obter estabelecimentos ativos
$query = "SELECT cod_estabelecimento, nome FROM tb_estabelecimentos WHERE (tipo = 'E' OR tipo = 'R') AND ativo = 'S'";
$stmt = $conexao->prepare($query);
$stmt->execute();
$estabelecimentos = $stmt->fetchAll();

// Processar agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
    $dt = $_POST['dt'];
    $hora = $_POST['hora'];
    $cod_estabelecimento = $_POST['estabelecimento'];
    $pagamento = $_POST['pagamento'] ?? 'Dinheiro';

    if ($evento && ($dt < $evento['data_inicio'] || $dt > $evento['data_fim'])) {
        echo "<script>alert('Data de agendamento fora do período permitido para o evento.'); window.history.back();</script>";
        exit;
    }

    $query = "INSERT INTO tb_agendamentos (cod_cliente, cod_evento, dt, hora, cod_estabelecimento, pagamento) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conexao->prepare($query);
    
    if ($stmt->execute([$cod_cliente, $cod_evento, $dt, $hora, $cod_estabelecimento, $pagamento])) {
        $_SESSION['novo_agendamento'] = true;
        echo "<script>alert('Evento agendado com sucesso!'); window.location.href='meus_agendamentos.php';</script>";
        exit;
    } else {
        echo "<script>alert('Erro ao agendar o evento. Tente novamente.'); window.history.back();</script>";
        exit;
    }
}

// Processar AJAX para lugares disponíveis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'lugares_disponiveis') {
    $dt = $_POST['dt'];
    $hora = $_POST['hora'];
    $lugares_ocupados = 0;

    $query = "SELECT COUNT(*) as ocupados FROM tb_agendamentos WHERE cod_evento = ? AND dt = ? AND hora = ?";
    $stmt = $conexao->prepare($query);
    $stmt->execute([$cod_evento, $dt, $hora]);
    $row = $stmt->fetch();

    if ($row) {
        $lugares_ocupados = $row['ocupados'];
    }

    $lugares_disponiveis -= $lugares_ocupados;

    echo json_encode(['lugares_disponiveis' => max(0, $lugares_disponiveis)]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Agendar Evento</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <link rel="stylesheet" href="css/barra_lateral.css">
    <link rel="stylesheet" href="css/agendar_evento.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
                            <span class="item-description">Bem-vindo,
                                <?php echo htmlspecialchars($_SESSION['nome']); ?>!</span>
                        <?php endif; ?>
                        <span class="item-description">A melhor churrascaria!</span>
                    </p>
                </div>
                <ul id="side_items">
                    <li class="side-item"><a href="index.php"><i class="fa-solid fa-house"></i><span
                                class="item-description">Home</span></a></li>
                    <?php if (!isset($_SESSION['email'])): ?>
                        <li class="side-item"><a href="login.php"><i class="fa-solid fa-user"></i><span
                                    class="item-description">Logar</span></a></li>
                        <li class="side-item"><a href="cadastrar.php"><i class="fa-solid fa-paperclip"></i><span
                                    class="item-description">Cadastrar</span></a></li>
                    <?php else: ?>
                        <?php if (isset($_SESSION['tipo']) && $_SESSION['tipo'] === 'A'): ?>
                            <li class="side-item"><a href="admin/sistema.php"><i class="fas fa-desktop"></i><span
                                        class="item-description">Sistema</span></a></li>
                        <?php endif; ?>
                        <li class="side-item"><a href="reservas.php"><i class="fa-solid fa-utensils"></i><span
                                    class="item-description">Reservas</span></a></li>

                        <li class="side-item"><a href="minhas_reservas.php"><i class="fa-solid fa-bacon"></i><span
                                    class="item-description">Minhas reservas<?php if (isset($_SESSION['nova_reserva'])): ?>
                                        <span class="novo-indicador">Novo</span>
                                    <?php endif; ?></span></a></li>

                        <li class="side-item active"><a href="eventos.php"><i class="fas fa-campground"></i><span
                                    class="item-description">Eventos</span></a></li>

                        <li class="side-item"><a href="meus_agendamentos.php"><i class="fa-solid fa-calendar-days"></i><span
                                    class="item-description">Meus Agendamentos<?php if (isset($_SESSION['novo_agendamento'])): ?>
                                        <span class="novo-indicador">Novo</span>
                                    <?php endif; ?></span></a></li>

                        <li class="side-item"><a href="cardapio.php"><i class="fa-solid fa-clipboard-list"></i><span
                                    class="item-description">Cardápio</span></a></li>
                    <?php endif; ?>
                    <li class="side-item"><a href="sobre_nos.php"><i class="fa-solid fa-drumstick-bite"></i><span
                                class="item-description">Sobre nós</span></a></li>
                    <li class="side-item"><a href="contato.php"><i class="fa-solid fa-phone"></i><span
                                class="item-description">Contato</span></a></li>
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
    </div>

    <div class="agendamento-container">
        <?php if ($evento): ?>
            <form method="POST" id="form-agendamento">
                <h1 class="titulo-evento"><?php echo htmlspecialchars($evento['nome']); ?></h1>
                <p><strong>Descrição:</strong> <?php echo htmlspecialchars($evento['descricao']); ?></p>
                <p><strong>Valor:</strong> R$ <?php echo number_format($evento['valor'], 2, ',', '.'); ?></p>
                <p><strong>Lugares Disponíveis:</strong> <span
                        id="lugares-disponiveis"><?php echo $lugares_disponiveis; ?></span></p>

                <label for="estabelecimento">Estabelecimento:</label>
                <select id="estabelecimento" name="estabelecimento" required>
                    <option value="">Selecione um estabelecimento</option>
                    <?php foreach ($estabelecimentos as $estabelecimento): ?>
                        <option value="<?php echo htmlspecialchars($estabelecimento['cod_estabelecimento']); ?>">
                            <?php echo htmlspecialchars($estabelecimento['nome']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="dt">Data:</label>
                <input type="date" id="dt" name="dt" required min="<?php echo htmlspecialchars(max(date('Y-m-d'), $evento['data_inicio'])); ?>"
                    max="<?php echo htmlspecialchars($evento['data_fim']); ?>">

                <label for="hora">Hora:</label>
                <select id="hora" name="hora" required>
                    <option value="12:00">12:00</option>
                    <option value="15:00">15:00</option>
                    <option value="18:00">18:00</option>
                    <option value="21:00">21:00</option>
                    <option value="00:00">00:00</option>
                </select>

                <label for="pagamento">Forma de Pagamento:</label>
                <select id="pagamento" name="pagamento" required>
                    <option value="Dinheiro">Dinheiro</option>
                    <option value="Pix">Pix</option>
                    <option value="Cartão Débito">Cartão de Débito</option>
                    <option value="Cartão Crédito">Cartão de Crédito</option>
                </select>

                <button type="submit" class="btn btn-primary mt-3">Agendar</button>
            </form>
        <?php else: ?>
            <p>Evento não encontrado.</p>
        <?php endif; ?>
    </div>
    <script>
        $('#dt, #hora').on('change', function () {
            const dt = $('#dt').val();
            const hora = $('#hora').val();

            if (dt && hora) {
                $.post('', { action: 'lugares_disponiveis', dt, hora }, function (data) {
                    if (data.lugares_disponiveis !== undefined) {
                        $('#lugares-disponiveis').text(data.lugares_disponiveis);
                    } else {
                        alert('Erro ao buscar lugares disponíveis. Tente novamente.');
                    }
                }, 'json').fail(function () {
                    alert('Erro na solicitação AJAX. Verifique sua conexão.');
                });
            }
        });
    </script>
    <script src="js/barra_lateral.js"></script>
</body>

</html>