<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensagem = '';

if (isset($_POST['submit'])) {
    $conexao = require_once('../conectar.php');

    $nomeEvento = $_POST['nome_evento'] ?? '';
    $descricaoEvento = $_POST['descricao_evento'] ?? '';
    $valorEvento = $_POST['valor_evento'] ?? 0;
    $tipoEvento = $_POST['tipo_evento'] ?? '';
    $dataInicio = $_POST['data_inicio'] ?? '';
    $dataFim = $_POST['data_fim'] ?? '';

    if (isset($_FILES["imagem"]) && !empty($_FILES["imagem"]["name"])) {
        $extensao = pathinfo($_FILES["imagem"]["name"], PATHINFO_EXTENSION);
        $nomeImagemTemp = uniqid('img_') . '.' . $extensao;
        $targetDir = '../img_banco/';
        $targetFileTemp = $targetDir . $nomeImagemTemp;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($_FILES["imagem"]["tmp_name"], $targetFileTemp)) {
            $nomeImagemFinal = str_replace(' ', '_', $nomeEvento) . '.png';
            $targetFileFinal = $targetDir . $nomeImagemFinal;

            try {
                // Checa se imagem já existe
                $stmt = $conexao->prepare("SELECT COUNT(*) FROM tb_imagens WHERE diretorio = ?");
                $stmt->execute([$targetFileFinal]);
                $count = $stmt->fetchColumn();

                if ($count > 0) {
                    $mensagem = 'Erro: Já existe um arquivo com este nome.';
                    if (file_exists($targetFileTemp)) {
                        unlink($targetFileTemp);
                    }
                } else {
                    rename($targetFileTemp, $targetFileFinal);

                    // Transação para manter consistência entre as duas tabelas
                    $conexao->beginTransaction();

                    $stmtEvento = $conexao->prepare("INSERT INTO tb_eventos (nome, descricao, valor, tipo, data_inicio, data_fim) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmtEvento->execute([$nomeEvento, $descricaoEvento, $valorEvento, $tipoEvento, $dataInicio, $dataFim]);
                    $codEventoId = $conexao->lastInsertId();

                    $stmtImg = $conexao->prepare("INSERT INTO tb_imagens (nome, diretorio, cod_evento) VALUES (?, ?, ?)");
                    $stmtImg->execute([$nomeEvento, 'img_banco/' . $nomeImagemFinal, $codEventoId]);

                    $conexao->commit();
                    $mensagem = 'Evento adicionado com sucesso!';
                }
            } catch (PDOException $e) {
                if ($conexao->inTransaction()) {
                    $conexao->rollBack();
                }
                $mensagem = 'Erro ao adicionar evento: ' . htmlspecialchars($e->getMessage());
            }
        } else {
            $mensagem = 'Erro ao fazer upload da imagem.';
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
    <title>Cadastro de Eventos</title>
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
            <a href="../admin/registrar_evento.php">Voltar</a>
        </div>
    </div>

    <div class="register-container fade-in">
        <div class="register">
            <h1>Adicionar Evento</h1>
            <?php if ($mensagem): ?>
                <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error' ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            <form action="add_evento.php" method="post" enctype="multipart/form-data" onsubmit="return validarDatas()">
                <div class="input-container">
                    <input type="text" name="nome_evento" id="nome_evento" required>
                    <label for="nome_evento" class="label">Nome do Evento</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <textarea name="descricao_evento" id="descricao_evento" maxlength="200" required></textarea>
                    <label for="descricao_evento" class="label">Descrição:<br></label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="number" step="0.01" name="valor_evento" id="valor_evento" required>
                    <label for="valor_evento" class="label">Valor do Evento</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="date" name="data_inicio" id="data_inicio" required>
                    <label for="data_inicio" class="label" style="top: -20px; font-size: 14px;">Data de Início</label>
                    <div class="underline"></div>
                </div>

                <div class="input-container">
                    <input type="date" name="data_fim" id="data_fim" required>
                    <label for="data_fim" class="label" style="top: -20px; font-size: 14px;">Data de Fim</label>
                    <div class="underline"></div>
                </div>

                <select name="tipo_evento" required>
                    <option value="P">Evento Pequeno</option>
                    <option value="M">Evento Médio</option>
                    <option value="G">Evento Grande</option>
                </select>

                <div class="input-container">
                    <input type="file" name="imagem" accept="image/*" class="form-control" required/>
                    <label for="imagem" class="label"></label>
                    <div class="underline"></div>
                </div>

                <button type="submit" name="submit">Enviar</button>
            </form>
        </div>
    </div>

    <div class="background-image fade-out"></div>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const hoje = new Date().toISOString().split('T')[0];
            document.getElementById('data_inicio').setAttribute('min', hoje);
            document.getElementById('data_fim').setAttribute('min', hoje);
        });

        function validarDatas() {
            const dataInicio = document.getElementById('data_inicio').value;
            const dataFim = document.getElementById('data_fim').value;

            if (dataInicio > dataFim) {
                alert('A data de início não pode ser maior que a data de fim.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>