<?php
$conexao = require_once('../conectar.php');

$cod_estabelecimento = '';
$nome = '';
$cnpj = '';
$fone = '';
$ativo = 'S';
$tipo = 'R';

if (isset($_POST['update'])) {
    $cod_estabelecimento = intval($_POST['cod_estabelecimento'] ?? 0);
    $nome = $_POST['nome'] ?? '';
    $cnpj = $_POST['cnpj'] ?? '';
    $fone = $_POST['fone'] ?? '';
    $ativo = $_POST['ativo'] ?? 'S';
    $tipo = $_POST['tipo'] ?? 'R';

    try {
        $sqlUpdate = "UPDATE tb_estabelecimentos 
                      SET nome = ?, cnpj = ?, fone = ?, ativo = ?, tipo = ? 
                      WHERE cod_estabelecimento = ?";

        $stmt = $conexao->prepare($sqlUpdate);
        $executado = $stmt->execute([$nome, $cnpj, $fone, $ativo, $tipo, $cod_estabelecimento]);

        if ($executado) {
            header('Location: registrar_estabelecimento.php');
            exit();
        } else {
            error_log("Nenhum registro atualizado.");
        }
    } catch (PDOException $e) {
        error_log("Falha na execução do UPDATE: " . $e->getMessage());
    }
} else {
    if (isset($_GET['cod_estabelecimento'])) {
        $cod_estabelecimento = intval($_GET['cod_estabelecimento']);

        try {
            $sqlSelect = "SELECT nome, cnpj, fone, ativo, tipo 
                          FROM tb_estabelecimentos 
                          WHERE cod_estabelecimento = ?";

            $stmt = $conexao->prepare($sqlSelect);
            $stmt->execute([$cod_estabelecimento]);
            $dados = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dados) {
                $nome = $dados['nome'];
                $cnpj = $dados['cnpj'];
                $fone = $dados['fone'];
                $ativo = $dados['ativo'];
                $tipo = $dados['tipo'];
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
            <form action="salvar_estabelecimento.php" method="post">
                <input type="hidden" name="cod_estabelecimento" value="<?php echo htmlspecialchars($cod_estabelecimento); ?>">
                
                <div class="input-container">
                    <input type="text" name="nome" required value="<?php echo htmlspecialchars($nome); ?>" />
                    <label class="label">Nome</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="text" name="cnpj" id="cnpj" maxlength="14" required value="<?php echo htmlspecialchars($cnpj); ?>" />
                    <label class="label">CNPJ</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="tel" name="fone" maxlength="11" required value="<?php echo htmlspecialchars($fone); ?>" />
                    <label class="label">Telefone</label>
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
                            <label for="ativo_n">Não</label><br>
                        </div>
                    </div>
                </div>

                <div class="input-container">
                    <div style="text-align: left;"><label for="tipo">Tipo:</label></div>
                    <div class="status-active-father">
                        <div class="status-active">
                            <input type="radio" id="tipo_r" name="tipo" value="R" <?php echo ($tipo == 'R') ? 'checked' : ''; ?> required>
                            <label for="tipo_r">Reservas</label>
                        </div>
                        <div class="status-active">
                            <input type="radio" id="tipo_e" name="tipo" value="E" <?php echo ($tipo == 'E') ? 'checked' : ''; ?> required>
                            <label for="tipo_e">Eventos</label>
                        </div>
                    </div>
                </div>

                <br>
                <button type="submit" name="update" style="margin-bottom: 15px;">Alterar</button>
                <p><a href="registrar_estabelecimento.php">Voltar</a></p>
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