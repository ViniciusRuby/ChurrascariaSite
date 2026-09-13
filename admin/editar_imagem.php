<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensagem = '';
$codImagem = '';
$nomeImagem = '';
$linkImagem = '';

if (isset($_POST['submit'])) {
    require_once('../conectar.php');
    $conexao = obterConexao();
    $codImagem = intval($_POST['cod_imagem'] ?? 0);
    $nomeImagem = $_POST['nome_imagem'] ?? '';

    if (isset($_FILES["imagem"])) {
        try {
            if (!empty($_FILES["imagem"]["name"])) {
                $targetDir = '../img_banco/';
            
                $fileName = $_FILES["imagem"]["name"];
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                $newFileName = uniqid() . '.' . $fileExtension;
            
                $targetFile = $targetDir . $newFileName;
                if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $targetFile)) {
                    $stmt = $conexao->prepare("UPDATE tb_imagens SET nome = ?, diretorio = ? WHERE cod_imagem = ?");
                    if ($stmt->execute([$nomeImagem, $targetFile, $codImagem])) {
                        $mensagem = 'Imagem atualizada com sucesso!';
                        $linkImagem = $targetFile;
                    } else {
                        $mensagem = 'Erro ao atualizar imagem no banco de dados.';
                    }
                } else {
                    $mensagem = 'Erro ao fazer upload da imagem.'; 
                }
            } else {
                // Caso nenhum arquivo de imagem tenha sido enviado, atualiza apenas o nome
                $stmt = $conexao->prepare("UPDATE tb_imagens SET nome = ? WHERE cod_imagem = ?");
                if ($stmt->execute([$nomeImagem, $codImagem])) {
                    $mensagem = 'Nome da imagem atualizado com sucesso!';
                } else {
                    $mensagem = 'Erro ao atualizar nome da imagem no banco de dados.';
                }
            }
        } catch (PDOException $e) {
            error_log("Erro no UPDATE de imagem: " . $e->getMessage());
            $mensagem = 'Erro ao atualizar imagem no banco de dados.';
        }
    }
}

if (isset($_GET['cod_imagem'])) {
    require_once('../conectar.php');
    $conexao = obterConexao();
    $codImagem = intval($_GET['cod_imagem']);

    try {
        $stmt = $conexao->prepare("SELECT nome, diretorio FROM tb_imagens WHERE cod_imagem = ?");
        $stmt->execute([$codImagem]);
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dados) {
            $nomeImagem = $dados['nome'];
            $linkImagem = $dados['diretorio'];
        }
    } catch (PDOException $e) {
        error_log("Erro na busca de imagem: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Editar Imagem</title>
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
            <h1>Editar Imagem</h1>
            <?php if ($mensagem): ?>
                <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error' ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            <form action="editar_imagem.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="cod_imagem" value="<?= htmlspecialchars($codImagem) ?>">
                
                <div class="input-container">
                    <input type="text" name="nome_imagem" value="<?= htmlspecialchars($nomeImagem) ?>" required>
                    <label class="label">Nome da Imagem</label>
                    <div class="underline"></div>
                </div>
                
                <div>
                    <p>Imagem atual:</p>
                    <?php if (!empty($linkImagem)): ?>
                        <img src="<?= htmlspecialchars($linkImagem) ?>" alt="Imagem Atual" style="max-width: 300px; max-height: 200px;">
                    <?php else: ?>
                        <p>Nenhuma imagem inserida.</p>
                    <?php endif; ?>
                </div>

                <div class="input-container">
                    <input type="file" name="imagem" accept="image/*" class="form-control" />
                    <label class="label">Escolha uma nova imagem</label>
                    <div class="underline"></div>
                </div>

                <button type="submit" name="submit">Atualizar</button>
                <a href="registrar_imagem.php">Voltar</a>
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