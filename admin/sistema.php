<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['senha']) || $_SESSION['tipo'] !== 'A') {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    unset($_SESSION['tipo']);
    header('Location: ../acesso_negado.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema</title>
    <link rel="icon" type="image/x-icon" href="../img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/barras.css">
    <link rel="stylesheet" href="../css/sistema.css">
</head>
<body>
    <div class="barra-superior">
        <div>
            <img src="../home.svg" alt="icone de home">
            <h2>Delicious Churras</h2>
        </div>
        <div>
            <a href="../index.php">Home</a> |
            <a href="../sair.php">Sair</a>
        </div>
    </div>

    <div class="container-conteudo">
        <div class="conteudo">
            <h1>Sistema de Gerenciamento</h1>
            
        </div>
    </div>

    <footer class="abas-footer">
        <div class="abas-container">
            <a class="aba_ativa">Sistema</a>
            <a class="aba" href="registrar_cliente.php">Clientes</a>
            <a class="aba" href="registrar_reserva.php">Reservas</a>
            <a class="aba" href="registrar_estabelecimento.php">Estabelecimentos</a>
            <a class="aba" href="registrar_horario.php">Horarios</a>
            <a class="aba" href="registrar_evento.php">Eventos</a>
            <a class="aba" href="registrar_agendamento.php">Agendamentos</a>
            <a class="aba" href="registrar_imagem.php">Imagens</a>
            <div class="abas-direita">
            </div>
        </div>
    </footer>
</body>
</html>