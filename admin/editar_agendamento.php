<?php
$conexao = require_once('../conectar.php');

$cod_agendamento = '';
$hora = '';
$dt = '';
$cod_estabelecimento = '';
$cod_cliente = '';
$cod_evento = '';

if (isset($_POST['update'])) {
    $cod_agendamento = intval($_POST['cod_agendamento'] ?? 0);
    $hora = $_POST['hora'] ?? '';
    $dt = $_POST['dt'] ?? '';
    $cod_estabelecimento = intval($_POST['cod_estabelecimento'] ?? 0);
    $cod_cliente = intval($_POST['cod_cliente'] ?? 0);
    $cod_evento = intval($_POST['cod_evento'] ?? 0);

    try {
        $sqlUpdate = "UPDATE tb_agendamentos 
                      SET hora = ?, dt = ?, cod_estabelecimento = ?, cod_cliente = ?, cod_evento = ? 
                      WHERE cod_agendamento = ?";

        $stmt = $conexao->prepare($sqlUpdate);
        $executado = $stmt->execute([$hora, $dt, $cod_estabelecimento, $cod_cliente, $cod_evento, $cod_agendamento]);

        if ($executado) {
            header('Location: registrar_agendamento.php');
            exit();
        } else {
            error_log("Nenhum registro atualizado.");
        }
    } catch (PDOException $e) {
        error_log("Falha na execução do UPDATE: " . $e->getMessage());
    }
} else {
    if (isset($_GET['cod_agendamento'])) {
        $cod_agendamento = intval($_GET['cod_agendamento']);

        try {
            $sqlSelect = "SELECT hora, dt, cod_estabelecimento, cod_cliente, cod_evento 
                          FROM tb_agendamentos 
                          WHERE cod_agendamento = ?";

            $stmt = $conexao->prepare($sqlSelect);
            $stmt->execute([$cod_agendamento]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dados) {
                $hora = $dados['hora'];
                $dt = $dados['dt'];
                $cod_estabelecimento = $dados['cod_estabelecimento'];
                $cod_cliente = $dados['cod_cliente'];
                $cod_evento = $dados['cod_evento'];
            }
        } catch (PDOException $e) {
            error_log("Falha na preparação da query de seleção: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Editar Registros</title>
    <link rel="icon" type="image/x-icon" href="../img/churrasco.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/cadastro.css">
</head>
<body>

    <div class="register-container slide-in-down">
        <div class="register">
            <h1>Editar Dados</h1>
            <form action="salvar_agendamento.php" method="post">
                <input type="hidden" name="cod_agendamento" value="<?php echo htmlspecialchars($cod_agendamento); ?>">

                <div class="input-container">
                    <input type="text" name="hora" value="<?php echo htmlspecialchars($hora); ?>" required>
                    <label class="label" style="top: -20px; font-size: 14px;">Hora</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="date" name="dt" value="<?php echo htmlspecialchars($dt); ?>" required>
                    <label class="label">Data</label>
                    <div class="underline"></div>
                </div>

                <input type="hidden" name="cod_estabelecimento" value="<?php echo htmlspecialchars($cod_estabelecimento); ?>">
                <input type="hidden" name="cod_cliente" value="<?php echo htmlspecialchars($cod_cliente); ?>">
                <input type="hidden" name="cod_evento" value="<?php echo htmlspecialchars($cod_evento); ?>">
                
                <br>
                <button type="submit" name="update" style="margin-bottom: 15px;">Alterar</button>
                <p><a href="registrar_agendamento.php">Voltar</a></p>
            </form>
        </div>

        <script src="../js/animacao.js"></script>
        <script src="js/cnpj_funcao.js"></script>
        <script src="js/numero_funcao.js"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>
</html>