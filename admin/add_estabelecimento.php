<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensagem = '';

if (isset($_POST['submit'])) {
    $conexao = require_once('../conectar.php');

    $nome = $_POST['nome'] ?? '';
    $ativo = $_POST['ativo'] ?? 'S';
    $tipo = $_POST['tipo'] ?? 'R';
    $cnpj = preg_replace('/[^0-9]/', '', $_POST['cnpj'] ?? '');
    $fone = preg_replace('/[^0-9]/', '', $_POST['fone'] ?? '');

    try {
        $stmt = $conexao->prepare("SELECT cnpj, fone FROM tb_estabelecimentos WHERE cnpj = ? OR fone = ?");
        $stmt->execute([$cnpj, $fone]);

        if ($stmt->fetch()) {
            $mensagem = 'CNPJ ou Telefone já cadastrado.';
        } else {
            $stmt_insert = $conexao->prepare("INSERT INTO tb_estabelecimentos (nome, cnpj, fone, ativo, tipo) VALUES (?, ?, ?, ?, ?)");
            $executado = $stmt_insert->execute([$nome, $cnpj, $fone, $ativo, $tipo]);

            if ($executado) {
                $mensagem = 'Cadastro realizado com sucesso!';
            } else {
                $mensagem = 'Erro ao realizar o cadastro.';
            }
        }
    } catch (PDOException $e) {
        $mensagem = 'Erro no servidor: ' . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cadastro</title>
    <link rel="icon" type="image/x-icon" href="../img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/cadastro.css">
    <link rel="stylesheet" href="../css/barras.css">
    <link rel="stylesheet" href="../css/alert.css">
</head>

<body>

    <div class="barra-superior slide-in-down">
        <div>
            <img src="../home.svg" alt="icone de home">
            <h2>Delicious Churras</h2>
        </div>
        <div>
            <a href="registrar_estabelecimento.php">Voltar</a>
        </div>
    </div>

    <div class="register-container">
        <div class="register">
            <h1>Adicionar Estabelecimento</h1>
            <?php if ($mensagem): ?>
                <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error' ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            <form action="add_estabelecimento.php" method="post">
                <input type="hidden" name="ativo" value="S">

                <div class="input-container">
                    <label class="label" for="tipo">Tipo:</label>
                    <select name="tipo" id="tipo" required>
                        <option value="R">Reservas</option>
                        <option value="E">Eventos</option>
                    </select>
                </div>

                <div class="input-container">
                    <input type="text" name="nome" id="nome" required>
                    <label class="label" for="nome">Nome</label>
                </div>

                <div class="input-container">
                    <input type="text" name="cnpj" id="cnpj" maxlength="18" onkeyup="formatarCampo(this);" required>
                    <label class="label" for="cnpj">CNPJ</label>
                </div>

                <div class="input-container">
                    <input type="tel" name="fone" id="fone" maxlength="15" onkeyup="formatarTelefone(this);" required>
                    <label class="label" for="fone">Telefone</label>
                </div>

                <button type="submit" name="submit">Enviar</button>
            </form>
        </div>
    </div>

    <div class="background-image fade-out"></div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelector('form').addEventListener('submit', function (event) {
                var cnpjInput = document.getElementById('cnpj');
                var telefoneInput = document.getElementById('fone');

                if (cnpjInput.value.length !== 18) {
                    alert('Campo de CNPJ não preenchido.');
                    event.preventDefault();
                }

                if (telefoneInput.value.length !== 15) {
                    alert('Campo de Telefone não preenchido.');
                    event.preventDefault();
                }
            });
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="../js/animacao.js"></script>
    <script src="../js/cnpj_funcao.js"></script>
    <script src="../js/numero_funcao.js"></script>
</body>

</html>