<?php
session_start();
require_once('conectar.php');
$conexao = obterConexao();
$mensagem = '';

// Processa requisição AJAX para buscar horários disponíveis
if (isset($_POST['action']) && $_POST['action'] == 'fetch_available_times') {
    $estabelecimento = intval($_POST['estabelecimento']);
    $dt = $_POST['data_reserva'] ?? '';

    $horarios_disponiveis = [];
    $max_tables = 20;
    $horario_inicio = strtotime('08:00');
    $horario_fim = strtotime('22:00');
    $intervalo = 120 * 60;

    while ($horario_inicio <= $horario_fim) {
        $hora_formatada = date('H:i', $horario_inicio);

        try {
            $stmt_time = $conexao->prepare("SELECT SUM(qtdmesa) AS total_reservadas FROM tb_reservas WHERE dt = ? AND (hora = ? OR hora = ? || ':00') AND cod_estabelecimento = ?");
            $stmt_time->execute([$dt, $hora_formatada, $hora_formatada, $estabelecimento]);
            $row_time = $stmt_time->fetch(PDO::FETCH_ASSOC);
            $reserved_tables = $row_time['total_reservadas'] ?? 0;

            if ($reserved_tables < $max_tables) {
                $horarios_disponiveis[] = [
                    'hora' => $hora_formatada,
                    'mesas_disponiveis' => $max_tables - $reserved_tables
                ];
            }
        } catch (PDOException $e) {
            // Tratar erros silenciosamente em AJAX
        }

        $horario_inicio += $intervalo;
    }

    echo json_encode($horarios_disponiveis);
    exit();
}

$filiais = [];
try {
    $stmt_filiais = $conexao->prepare("SELECT cod_estabelecimento, nome FROM tb_estabelecimentos WHERE tipo = 'R'");
    $stmt_filiais->execute();
    $filiais = $stmt_filiais->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Tratar erro
}

$max_tables = 20;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    if (isset($_SESSION['email'])) {
        $email = $_SESSION['email'];
        try {
            $stmt_cliente = $conexao->prepare("SELECT cod_cliente FROM tb_clientes WHERE email = ?");
            $stmt_cliente->execute([$email]);
            $row_cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

            if ($row_cliente) {
                $cod_cliente = $row_cliente['cod_cliente'];
                $qtdmesa = isset($_POST['quantidade_mesas']) ? intval($_POST['quantidade_mesas']) : 0;
                $valor = $qtdmesa * 40;
                $dt = $_POST['data_reserva'] ?? '';
                $hora = $_POST['horario_reserva'] ?? '';
                $estabelecimento = isset($_POST['estabelecimento']) ? intval($_POST['estabelecimento']) : 0;
                $pagamentoRaw = $_POST['pagamento'] ?? 'Dinheiro';
                $mapPagamento = [
                    'cartao' => 'Cartão Débito',
                    'pix' => 'Pix',
                    'dinheiro' => 'Dinheiro',
                    'Cartão Débito' => 'Cartão Débito',
                    'Cartão Crédito' => 'Cartão Crédito',
                    'Pix' => 'Pix',
                    'Dinheiro' => 'Dinheiro'
                ];
                $pagamento = $mapPagamento[$pagamentoRaw] ?? 'Dinheiro';

                $stmt_check = $conexao->prepare("SELECT SUM(qtdmesa) AS total_reservadas FROM tb_reservas WHERE dt = ? AND (hora = ? OR hora = ? || ':00') AND cod_estabelecimento = ?");
                $stmt_check->execute([$dt, $hora, $hora, $estabelecimento]);
                $row_check = $stmt_check->fetch(PDO::FETCH_ASSOC);
                $total_reservadas = $row_check['total_reservadas'] ?? 0;
                $available_tables = $max_tables - $total_reservadas;

                if ($qtdmesa <= $available_tables) {
                    $stmt_reserva = $conexao->prepare("INSERT INTO tb_reservas (cod_cliente, qtdmesa, valor, dt, hora, cod_estabelecimento, pagamento) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $executado = $stmt_reserva->execute([$cod_cliente, $qtdmesa, $valor, $dt, $hora, $estabelecimento, $pagamento]);

                    if ($executado) {
                        $_SESSION['nova_reserva'] = true;
                        $_SESSION['success_message'] = 'Reserva realizada com sucesso!';
                        header("Location: reservas.php");
                        exit();
                    } else {
                        $mensagem = 'Erro ao realizar reserva.';
                    }
                } else {
                    $mensagem = 'Erro: Mesas insuficientes para o horário selecionado.';
                }
            } else {
                $mensagem = 'Erro: Cliente não encontrado.';
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro de banco de dados: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $mensagem = 'Erro: Usuário não está logado.';
    }
}

if (isset($_SESSION['success_message'])) {
    $mensagem = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Reservas</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/reserva.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/barra_lateral.css">
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
                <li class="side-item active"><a href="reservas.php"><i class="fa-solid fa-utensils"></i><span class="item-description">Reservas</span></a></li>
                <li class="side-item"><a href="minhas_reservas.php"><i class="fa-solid fa-bacon"></i><span
                                    class="item-description">Minhas reservas<?php if (isset($_SESSION['nova_reserva'])): ?>
                                        <span class="novo-indicador">Novo</span>
                                    <?php endif; ?></span></a></li>
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
    </div>

    <div class="reserva-container">
    <div class="reserva-content d-flex">
        <div class="imagem-container">
            <img src="img/fundo_reservas.jpg" alt="Imagem de Reserva" class="imagem-reserva">
        </div>
        <div class="form-container">
            <h1>Reservar Mesas</h1>
            <?php if (!empty($mensagem)): ?>
                <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-danger' ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            <form action="reservas.php" method="post" id="reservaForm">
                <label for="estabelecimento">Estabelecimento:</label>
                <select id="estabelecimento" name="estabelecimento" required>
                    <option value="">Selecione um estabelecimento</option>
                    <?php foreach ($filiais as $estabelecimento): ?>
                        <option value="<?= $estabelecimento['cod_estabelecimento'] ?>"><?= htmlspecialchars($estabelecimento['nome']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="data_reserva">Data da Reserva:</label>
                <input type="date" id="data_reserva" name="data_reserva" required min="<?= date('Y-m-d') ?>">

                <label for="horario_reserva">Horário da Reserva:</label>
                <select id="horario_reserva" name="horario_reserva" required>
                    <option value="">Selecione um horário</option>
                </select>

                <label for="quantidade_mesas">Quantidade de Mesas:</label>
                <input type="number" id="quantidade_mesas" name="quantidade_mesas" value="1" min="1" max="20" required>
                <br>

                <label for="pagamento">Pagamento:</label>
                <select id="pagamento" name="pagamento" required>
                    <option value="">Selecione o tipo de pagamento</option>
                    <option value="cartao">Cartão de Crédito/Débito</option>
                    <option value="pix">Pix</option>
                    <option value="dinheiro">Dinheiro no local</option>
                </select>

                <div class="valor-container">
                    <span id="limite_mesas" class="text-muted">Mesas disponíveis: </span>
                    <br>
                    <label for="valor_reserva">Valor: <span id="valor_reserva" class="text-muted">R$ 40,00</span></label>
                </div>

                <button type="submit" name="submit" class="btn btn-primary mt-3">Reservar</button>
            </form>
        </div>
    </div>
</div>

    <script>
        $(document).ready(function () {
            $('#estabelecimento, #data_reserva').on('change', function () {
                const estabelecimento = $('#estabelecimento').val();
                const data_reserva = $('#data_reserva').val();

                if (data_reserva) {
                    const selectedDate = new Date(data_reserva);
                    if (selectedDate.getDay() === 6) {
                        alert('Domingos não disponíveis para reservas.');
                        $('#data_reserva').val('');
                        $('#horario_reserva').empty().append('<option value="">Selecione um horário</option>');
                        $('#limite_mesas').text(`Mesas disponíveis: `);
                        return;
                    }
                }

                if (estabelecimento && data_reserva) {
                    $.ajax({
                        type: 'POST',
                        url: 'reservas.php',
                        data: {
                            action: 'fetch_available_times',
                            estabelecimento: estabelecimento,
                            data_reserva: data_reserva
                        },
                        success: function (response) {
                            const horarios = JSON.parse(response);
                            const horarioSelect = $('#horario_reserva');
                            horarioSelect.empty().append('<option value="">Selecione um horário</option>');

                            let maxAvailableTables = 20;

                            horarios.forEach(function (horario) {
                                horarioSelect.append(`<option value="${horario.hora}" data-max="${horario.mesas_disponiveis}">${horario.hora}</option>`);
                                maxAvailableTables = Math.min(maxAvailableTables, horario.mesas_disponiveis);
                            });

                            $('#quantidade_mesas').attr('max', maxAvailableTables).val(1);
                            if (horarios.length > 0) {
                                $('#limite_mesas').text(`Mesas disponíveis: ${maxAvailableTables}`);
                            } else {
                                $('#limite_mesas').text(`Mesas disponíveis: `);
                            }

                            atualizarValorTotal();
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                } else {
                    $('#horario_reserva').empty().append('<option value="">Selecione um horário</option>');
                    $('#quantidade_mesas').attr('max', 20).val(1);
                    $('#limite_mesas').text(`Mesas disponíveis: `);
                }
            });

            $('#horario_reserva').on('change', function () {
                const maxAvailableTables = $(this).find(':selected').data('max');
                $('#quantidade_mesas').attr('max', maxAvailableTables);

                if ($('#estabelecimento').val() && $('#data_reserva').val()) {
                    $('#limite_mesas').text(`Mesas disponíveis: ${maxAvailableTables}`);
                }

                atualizarValorTotal();
            });

            $('#quantidade_mesas').on('input', function () {
                atualizarValorTotal();
            });

            function atualizarValorTotal() {
                const qtdMesas = parseInt($('#quantidade_mesas').val()) || 0;
                const valorPorMesa = 40;
                const valorTotal = qtdMesas * valorPorMesa;

                $('#valor_reserva').text(`R$ ${valorTotal.toFixed(2)}`);
            }
        });

    </script>
    <script src="js/barra_lateral.js"></script>
</body>

</html>