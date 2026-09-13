<?php
$mensagem = '';
$nome = '';
$email = '';
$cpf = '';
$fone = '';
$senha = '';
$ativo = 'S';
$tipo = 'U';
$dtnasc = '';
$cod_cliente = '';

if (!empty($_GET['cod_cliente'])) {
    $conexao = require_once('../conectar.php');
    $cod_cliente = intval($_GET['cod_cliente']);

    try {
        $sqlSelect = "SELECT * FROM tb_clientes WHERE cod_cliente = ?";
        $stmt = $conexao->prepare($sqlSelect);
        $stmt->execute([$cod_cliente]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $nome = $row['nome'];
            $email = $row['email'];
            $cpf = $row['cpf'];
            $fone = $row['fone'];
            $senha = $row['senha'];
            $ativo = $row['ativo'];
            $tipo = $row['tipo'];
            $dtnasc = $row['dtnasc'];
        } else {
            header('Location: sistema.php');
            exit;
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar dados do cliente: " . $e->getMessage());
        header('Location: registrar_cliente.php');
        exit;
    }
} else {
    header('Location: registrar_cliente.php');
    exit;
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
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="js/cpf_funcao.js"></script>
    <script src="js/numero_funcao.js"></script>
</head>

<body>

    <div class="register-container slide-in-down">
        <div class="register">
            <h1>Editar Dados</h1>
            <?php if (!empty($mensagem)): ?>
                <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error' ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            <form action="salvar_cliente.php" method="post">
                <input type="hidden" name="cod_cliente" value="<?php echo htmlspecialchars($cod_cliente); ?>">

                <div class="input-container">
                    <input type="text" name="nome" required value="<?php echo htmlspecialchars($nome); ?>" />
                    <label class="label">Nome</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="email" name="email" required value="<?php echo htmlspecialchars($email); ?>" />
                    <label class="label">Email</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="text" name="cpf" id="cpf" maxlength="11" required value="<?php echo htmlspecialchars($cpf); ?>" />
                    <label class="label">CPF</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="tel" name="fone" maxlength="11" required value="<?php echo htmlspecialchars($fone); ?>" />
                    <label class="label">Telefone</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="text" name="senha" required value="<?php echo htmlspecialchars($senha); ?>" />
                    <label class="label">Senha</label>
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
                            <input type="radio" id="tipo_u" name="tipo" value="U" <?php echo ($tipo == 'U') ? 'checked' : ''; ?> required>
                            <label for="tipo_u">Usuário</label>
                        </div>
                        <div class="status-active">
                            <input type="radio" id="tipo_a" name="tipo" value="A" <?php echo ($tipo == 'A') ? 'checked' : ''; ?> required>
                            <label for="tipo_a">Admin</label>
                        </div>
                    </div>
                </div>

                <div class="input-container">
                    <input type="date" name="dtnasc" required value="<?php echo htmlspecialchars($dtnasc); ?>" />
                    <label class="label">Data de Nascimento</label>
                    <div class="underline"></div>
                </div>

                <br>
                <button type="submit" name="update" style="margin-bottom: 15px;">Alterar</button>
                <p><a href="registrar_cliente.php">Voltar</a></p>
            </form>
        </div>

        <script src="../js/animacao.js"></script>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
    </div>
</body>

</html>