<?php
$nome = '';
$valor = '';
$tipo = 'P';
$ativo = 'S';
$cod_evento = '';

if (!empty($_GET['cod_evento'])) {
    $conexao = require_once('../conectar.php');
    $cod_evento = intval($_GET['cod_evento']);

    try {
        $sqlSelect = "SELECT * FROM tb_eventos WHERE cod_evento = ?";
        $stmt = $conexao->prepare($sqlSelect);
        $stmt->execute([$cod_evento]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $nome = $row['nome'];
            $valor = $row['valor'];
            $tipo = $row['tipo'];
            $ativo = $row['ativo'];
        } else {
            header('Location: sistema.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar dados do evento: " . $e->getMessage());
        header('Location: registrar_evento.php');
        exit;
    }
} else {
    header('Location: registrar_evento.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Editar Evento</title>
    <link rel="icon" type="image/x-icon" href="../img/churrasco.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/cadastro.css">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
</head>

<body>

    <div class="register-container slide-in-down">
        <div class="register">
            <h1>Editar Evento</h1>
            <form action="salvar_evento.php" method="post">
                <input type="hidden" name="cod_evento" value="<?php echo htmlspecialchars($cod_evento); ?>">

                <div class="input-container">
                    <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($nome); ?>" required>
                    <label class="label" for="nome">Nome</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="number" name="valor" id="valor" step="0.01" value="<?php echo htmlspecialchars($valor); ?>" required>
                    <label class="label" for="valor">Valor</label>
                    <div class="underline"></div>
                </div>

                <div class="status-active-container">
                    <div style="text-align: left;"><label for="ativo">Ativo:</label></div>
                    <div class="status-active-father">
                        <div class="status-active">
                            <input type="radio" id="ativo_s" name="ativo" value="S" <?php echo ($ativo == 'S') ? 'checked' : ''; ?> required>
                            <label for="ativo_s">Sim</label>
                        </div>
                        <div class="status-active">
                            <input type="radio" id="ativo_n" name="ativo" value="N" <?php echo ($ativo == 'N') ? 'checked' : ''; ?> required>
                            <label for="ativo_n">Não</label>
                        </div>
                    </div>
                </div>

                <div class="status-active-container">
                    <div style="text-align: left;"><label for="tipo">Tipo:</label></div>
                    <div class="status-active-father">
                        <div class="status-active">
                            <input type="radio" id="tipo_p" name="tipo" value="P" <?php echo ($tipo == 'P') ? 'checked' : ''; ?> required>
                            <label for="tipo_p">Pequeno</label>
                        </div>
                        <div class="status-active">
                            <input type="radio" id="tipo_M" name="tipo" value="M" <?php echo ($tipo == 'M') ? 'checked' : ''; ?> required>
                            <label for="tipo_M">Médio</label>
                        </div>
                        <div class="status-active">
                            <input type="radio" id="tipo_g" name="tipo" value="G" <?php echo ($tipo == 'G') ? 'checked' : ''; ?> required>
                            <label for="tipo_g">Grande</label>
                        </div>
                    </div>
                </div>

                <br>
                <button type="submit" name="update" style="margin-bottom: 15px;">Alterar</button>
                <p><a href="registrar_evento.php">Voltar</a></p>
            </form>
        </div>

        <script src="../js/animacao.js"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
</body>

</html>