<?php
$conexao = require_once('../conectar.php');

$cod_horario = '';
$entrada_manha = '';
$entrada_tarde = '';
$saida_manha = '';
$diasemana = '';
$saida_tarde = '';
$entrada_noite = '';
$saida_noite = '';

if (isset($_POST['update'])) {
    $cod_horario = intval($_POST['cod_horario'] ?? 0);
    $diasemana = $_POST['diasemana'] ?? '';
    $entrada_manha = $_POST['entrada_manha'] ?? '';
    $entrada_tarde = $_POST['entrada_tarde'] ?? '';
    $saida_manha = $_POST['saida_manha'] ?? '';
    $saida_tarde = $_POST['saida_tarde'] ?? '';
    $entrada_noite = $_POST['entrada_noite'] ?? '';
    $saida_noite = $_POST['saida_noite'] ?? '';

    try {
        $sqlUpdate = "UPDATE tb_horarios 
                      SET entrada_manha = ?, entrada_tarde = ?, saida_manha = ?, diasemana = ?, saida_tarde = ?, entrada_noite = ?, saida_noite = ? 
                      WHERE cod_horario = ?";

        $stmt = $conexao->prepare($sqlUpdate);
        $executado = $stmt->execute([
            $entrada_manha,
            $entrada_tarde,
            $saida_manha,
            $diasemana,
            $saida_tarde,
            $entrada_noite,
            $saida_noite,
            $cod_horario
        ]);

        if ($executado) {
            header('Location: registrar_horario.php');
            exit();
        } else {
            error_log("Nenhum registro atualizado.");
        }
    } catch (PDOException $e) {
        error_log("Falha na execução do UPDATE: " . $e->getMessage());
    }
} else {
    if (isset($_GET['cod_horario'])) {
        $cod_horario = intval($_GET['cod_horario']);

        try {
            $sqlSelect = "SELECT entrada_manha, entrada_tarde, saida_manha, diasemana, saida_tarde, entrada_noite, saida_noite 
                          FROM tb_horarios 
                          WHERE cod_horario = ?";

            $stmt = $conexao->prepare($sqlSelect);
            $stmt->execute([$cod_horario]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dados) {
                $entrada_manha = $dados['entrada_manha'];
                $entrada_tarde = $dados['entrada_tarde'];
                $saida_manha = $dados['saida_manha'];
                $diasemana = $dados['diasemana'];
                $saida_tarde = $dados['saida_tarde'];
                $entrada_noite = $dados['entrada_noite'];
                $saida_noite = $dados['saida_noite'];
            } else {
                error_log("Nenhum dado encontrado para o código: " . $cod_horario);
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
    <title>Editar Registros de Horário</title>
    <link rel="icon" type="image/x-icon" href="../img/churrasco.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/cadastro.css">
</head>
<body>

    <div class="register-container slide-in-down">
        <div class="register">
            <h1>Editar Dados de Horário</h1>
            <form action="salvar_horario.php" method="post">
                <input type="hidden" name="cod_horario" value="<?php echo htmlspecialchars($cod_horario); ?>">

                <div class="input-container">
                    <select name="diasemana" required>
                        <option value="Segunda" <?php if ($diasemana == 'Segunda') echo 'selected'; ?>>Segunda</option>
                        <option value="Terça" <?php if ($diasemana == 'Terça') echo 'selected'; ?>>Terça</option>
                        <option value="Quarta" <?php if ($diasemana == 'Quarta') echo 'selected'; ?>>Quarta</option>
                        <option value="Quinta" <?php if ($diasemana == 'Quinta') echo 'selected'; ?>>Quinta</option>
                        <option value="Sexta" <?php if ($diasemana == 'Sexta') echo 'selected'; ?>>Sexta</option>
                        <option value="Sábado" <?php if ($diasemana == 'Sábado') echo 'selected'; ?>>Sábado</option>
                    </select>
                </div>

                <div class="input-container">
                    <input type="text" name="entrada_manha" required value="<?php echo htmlspecialchars($entrada_manha); ?>">
                    <label class="label">Entrada Manhã</label>
                </div>
                <div class="input-container">
                    <input type="text" name="entrada_tarde" required value="<?php echo htmlspecialchars($entrada_tarde); ?>">
                    <label class="label">Entrada Tarde</label>
                </div>
                <div class="input-container">
                    <input type="text" name="saida_manha" required value="<?php echo htmlspecialchars($saida_manha); ?>">
                    <label class="label">Saída Manhã</label>
                </div>
                <div class="input-container">
                    <input type="text" name="saida_tarde" required value="<?php echo htmlspecialchars($saida_tarde); ?>">
                    <label class="label">Saída Tarde</label>
                </div>
                <div class="input-container">
                    <input type="text" name="entrada_noite" required value="<?php echo htmlspecialchars($entrada_noite); ?>">
                    <label class="label">Entrada Noite</label>
                </div>
                <div class="input-container">
                    <input type="text" name="saida_noite" required value="<?php echo htmlspecialchars($saida_noite); ?>">
                    <label class="label">Saída Noite</label>
                </div>

                <br>
                <button type="submit" name="update" style="margin-bottom: 15px;">Alterar</button>
                <p><a href="registrar_horario.php">Voltar</a></p>
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