<?php
require_once('conectar.php');
$conexao = obterConexao();

if (isset($_POST['estabelecimento']) && isset($_POST['data_reserva'])) {
    $estabelecimento = intval($_POST['estabelecimento']);
    $data_reserva = $_POST['data_reserva'];

    $horarios_lotados = [];

    // Busca o total de mesas reservadas para cada horário
    $stmt_horarios = $conexao->prepare("
        SELECT hora, SUM(qtdmesa) as total_mesas 
        FROM tb_reservas 
        WHERE cod_estabelecimento = ? AND dt = ? 
        GROUP BY hora
    ");
    $stmt_horarios->execute([$estabelecimento, $data_reserva]);

    while ($row_horario = $stmt_horarios->fetch()) {
        if ($row_horario['total_mesas'] >= 20) {
            $horaNormalizada = substr($row_horario['hora'], 0, 5);
            $horarios_lotados[] = $horaNormalizada;
        }
    }

    $horario_inicio = strtotime('08:00');
    $horario_fim = strtotime('22:00');
    $intervalo = 120 * 60; 

    while ($horario_inicio <= $horario_fim) {
        $horario_formatado = date('H:i', $horario_inicio);
        if (!in_array($horario_formatado, $horarios_lotados)) {
            echo '<option value="' . $horario_formatado . '">' . $horario_formatado . '</option>';
        }
        $horario_inicio += $intervalo;
    }
}
?>