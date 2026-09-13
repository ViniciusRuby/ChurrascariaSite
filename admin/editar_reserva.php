<?php
$conexao = require_once('../conectar.php');

$cod_reserva = '';
$cod_cliente = '';
$cod_estabelecimento = '';
$valor = '';
$data = '';
$hora = '';
$qtdmesa = '';

if (isset($_POST['update'])) {
    $cod_reserva = intval($_POST['cod_reserva'] ?? 0);
    $cod_cliente = $_POST['cod_cliente'] ?? '';
    $cod_estabelecimento = $_POST['cod_estabelecimento'] ?? '';
    $valor = $_POST['valor'] ?? '';
    $data = $_POST['data'] ?? '';
    $hora = $_POST['hora'] ?? '';
    $qtdmesa = intval($_POST['qtdmesa'] ?? 0);

    try {
        $sqlUpdate = "UPDATE tb_reservas 
                      SET valor = ?, dt = ?, hora = ?, qtdmesa = ? 
                      WHERE cod_reserva = ?";

        $stmt = $conexao->prepare($sqlUpdate);
        $executado = $stmt->execute([
            $valor,
            $data,
            $hora,
            $qtdmesa,
            $cod_reserva
        ]);

        if ($executado) {
            header('Location: registrar_reserva.php');
            exit();
        } else {
            error_log("Nenhum registro atualizado.");
        }
    } catch (PDOException $e) {
        error_log("Falha na execução do UPDATE: " . $e->getMessage());
    }
} else {
    if (isset($_GET['cod_reserva'])) {
        $cod_reserva = intval($_GET['cod_reserva']);

        try {
            $sqlSelect = "SELECT valor, dt, hora, qtdmesa FROM tb_reservas WHERE cod_reserva = ?";

            $stmt = $conexao->prepare($sqlSelect);
            $stmt->execute([$cod_reserva]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dados) {
                $valor = $dados['valor'];
                $data = $dados['dt'];
                $hora = $dados['hora'];
                $qtdmesa = $dados['qtdmesa'];
            } else {
                error_log("Nenhum dado encontrado para o código: " . $cod_reserva);
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
            <form action="salvar_reserva.php" method="post">
                <input type="hidden" name="cod_reserva" value="<?php echo htmlspecialchars($cod_reserva); ?>">
                <input type="hidden" name="cod_cliente" value="<?php echo htmlspecialchars($cod_cliente); ?>">
                <input type="hidden" name="cod_estabelecimento" value="<?php echo htmlspecialchars($cod_estabelecimento); ?>">

                <div class="input-container">
                    <input type="text" name="valor" id="valor" placeholder=" " value="<?php echo htmlspecialchars($valor); ?>" required>
                    <label for="valor" class="label">Valor</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="date" name="data" id="data" placeholder=" " value="<?php echo htmlspecialchars($data); ?>" required>
                    <label for="data" class="label">Data</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="text" name="hora" id="hora" placeholder=" " value="<?php echo htmlspecialchars($hora); ?>" required>
                    <label for="hora" class="label">Hora</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="number" name="qtdmesa" id="qtdmesa" placeholder=" " value="<?php echo htmlspecialchars($qtdmesa); ?>" required>
                    <label for="qtdmesa" class="label">Quantidade de Mesas</label>
                    <div class="underline"></div>
                </div>

                <br>
                <button type="submit" name="update" style="margin-bottom: 15px;">Alterar</button>
                <p><a href="registrar_reserva.php">Voltar</a></p>
            </form>
        </div>

        <script src="../js/animacao.js"></script>
        <script src="js/cnpj_funcao.js"></script>
        <script src="js/numero_funcao.js"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    </div>
</body>
</html>