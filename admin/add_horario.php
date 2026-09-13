<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $conexao = require_once('../conectar.php');

    $entradaManha = $_POST['entrada_manha'] ?? '';
    $entradaTarde = $_POST['entrada_tarde'] ?? '';
    $saidaManha   = $_POST['saida_manha'] ?? '';
    $saidaTarde   = $_POST['saida_tarde'] ?? '';
    $entradaNoite = $_POST['entrada_noite'] ?? '';
    $saidaNoite   = $_POST['saida_noite'] ?? '';
    $diaSemana    = $_POST['dia_semana'] ?? '';

    try {
        // Verificar se já existe um registro para o mesmo dia da semana
        $sql_check = "SELECT COUNT(*) AS total FROM tb_horarios WHERE diasemana = ?";
        $stmt_check = $conexao->prepare($sql_check);
        $stmt_check->execute([$diaSemana]);
        $row = $stmt_check->fetch(PDO::FETCH_ASSOC);

        if ($row && $row['total'] > 0) {
            $mensagem = "Já existe um horário cadastrado para este dia da semana.";
        } else {
            // Validar se os horários de entrada são menores que os horários de saída
            if ($entradaManha < $saidaManha && $entradaTarde < $saidaTarde && $entradaNoite < $saidaNoite) {
                $sql = "INSERT INTO tb_horarios (entrada_manha, entrada_tarde, saida_manha, saida_tarde, entrada_noite, saida_noite, diasemana) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conexao->prepare($sql);
                $executado = $stmt->execute([$entradaManha, $entradaTarde, $saidaManha, $saidaTarde, $entradaNoite, $saidaNoite, $diaSemana]);

                if ($executado) {
                    $mensagem = "Horário adicionado com sucesso ao banco de dados.";
                } else {
                    $mensagem = "Erro ao adicionar horário ao banco de dados.";
                }
            } else {
                $mensagem = "As entradas devem ser maiores que os horários de saída.";
            }
        }
    } catch (PDOException $e) {
        $mensagem = "Erro de banco de dados: " . htmlspecialchars($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cadastro de Horários</title>
    <link rel="icon" type="image/x-icon" href="../img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/add_horario.css">
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
            <a href="../admin/registrar_horario.php">Voltar</a>
        </div>
    </div>

    <div class="register-container">
        <div class="register">
            <h1>Adicionar Horários</h1>
            <?php if ($mensagem): ?>
                <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error' ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            <form action="add_horario.php" method="post">
                <div class="input-container">
                    <input type="time" name="entrada_manha" required>
                    <label class="label" style="top: -20px; font-size: 14px;">Entrada Manhã</label>
                    <div class="underline"></div>
                </div>
                <div class="input-container">
                    <input type="time" name="saida_manha" required>
                    <label class="label" style="top: -20px; font-size: 14px;">Saída Manhã</label>
                    <div class="underline"></div>
                </div>
                <div class="input-container">
                    <input type="time" name="entrada_tarde" required>
                    <label class="label" style="top: -20px; font-size: 14px;">Entrada Tarde</label>
                    <div class="underline"></div>
                </div>
                <div class="input-container">
                    <input type="time" name="saida_tarde" required>
                    <label class="label" style="top: -20px; font-size: 14px;">Saída Tarde</label>
                    <div class="underline"></div>
                </div>
                <div class="input-container">
                    <input type="time" name="entrada_noite" required>
                    <label class="label" style="top: -20px; font-size: 14px;">Entrada Noite</label>
                    <div class="underline"></div>
                </div>
                <div class="input-container">
                    <input type="time" name="saida_noite" required>
                    <label class="label" style="top: -20px; font-size: 14px;">Saída Noite</label>
                    <div class="underline"></div>
                </div>

                <select name="dia_semana" class="input-container" required>
                    <option value="">Dia da semana</option>
                    <option value="Segunda">Segunda-feira</option>
                    <option value="Terça">Terça-feira</option>
                    <option value="Quarta">Quarta-feira</option>
                    <option value="Quinta">Quinta-feira</option>
                    <option value="Sexta">Sexta-feira</option>
                    <option value="Sábado">Sábado</option>
                </select>

                <button type="submit">Adicionar Horário</button>
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