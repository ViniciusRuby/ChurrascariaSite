<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensagem = '';

if (isset($_POST['submit'])) {
    $conexao = require_once('../conectar.php');
    $nomeImagem = $_POST['nome_imagem'] ?? '';

    if (isset($_FILES["imagem"]) && !empty($_FILES["imagem"]["name"])) {
        $targetDir = '../img_banco/';
        $targetFile = $targetDir . basename($_FILES["imagem"]["name"]);

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        try {
            // Verifica se a imagem já existe no banco
            $stmt = $conexao->prepare("SELECT COUNT(*) FROM tb_imagens WHERE diretorio = ?");
            $stmt->execute([$targetFile]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $mensagem = 'Erro: Já existe um arquivo com este nome.';
            } else {
                if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $targetFile)) {
                    $stmt_insert = $conexao->prepare("INSERT INTO tb_imagens (nome, diretorio) VALUES (?, ?)");
                    $executado = $stmt_insert->execute([$nomeImagem, $targetFile]);

                    if ($executado) {
                        $mensagem = 'Imagem adicionada com sucesso!';
                    } else {
                        $mensagem = 'Erro ao adicionar imagem.';
                    }
                } else {
                    $mensagem = 'Erro ao fazer upload da imagem.';
                }
            }
        } catch (PDOException $e) {
            $mensagem = 'Erro de banco de dados: ' . htmlspecialchars($e->getMessage());
        }
    } else {
        $mensagem = 'Nenhuma imagem foi enviada.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cadastro de Imagens</title>
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
            <a href="../admin/registrar_imagem.php">Voltar</a>
        </div>
    </div>

    <div class="register-container fade-in">
        <div class="register">
            <h1>Adicionar Imagem</h1>
            <?php if ($mensagem): ?>
                <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error' ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            <form action="add_imagem.php" method="post" enctype="multipart/form-data">
                <div class="input-container">
                    <input type="text" name="nome_imagem" required>
                    <label class="label">Nome da Imagem</label>
                    <div class="underline"></div>
                </div>
                <div class="input-container">
                    <input type="file" name="imagem" accept="image/*" class="form-control" required/>
                    <label class="label"></label>
                    <div class="underline"></div>
                </div>
                <button type="submit" name="submit" class="button">Enviar</button>
            </form>
        </div>
    </div>

    <div class="background-image fade-out"></div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="../js/animacao.js"></script>
    <script src="../js/cnpj_funcao.js"></script>
    <script src="../js/numero_funcao.js"></script>
</body>
</html>