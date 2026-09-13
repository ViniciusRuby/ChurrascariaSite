<?php
$conexao = require_once('../conectar.php');

if (isset($_POST['update'])) {
    $cod_agendamento = $_POST['cod_agendamento'] ?? null;
    $hora = $_POST['hora'] ?? null;
    $dt = $_POST['dt'] ?? null;
    $cod_estabelecimento = $_POST['cod_estabelecimento'] ?? null;
    $cod_cliente = $_POST['cod_cliente'] ?? null;
    $cod_evento = $_POST['cod_evento'] ?? null;

    if ($cod_agendamento) {
        try {
            $sqlUpdate = "UPDATE tb_agendamentos 
                          SET hora = ?, dt = ?, cod_estabelecimento = ?, cod_cliente = ?, cod_evento = ? 
                          WHERE cod_agendamento = ?";

            $stmt = $conexao->prepare($sqlUpdate);
            $stmt->execute([
                $hora,
                $dt,
                $cod_estabelecimento,
                $cod_cliente,
                $cod_evento,
                $cod_agendamento
            ]);

            if ($stmt->rowCount() > 0) {
                header('Location: registrar_agendamento.php');
                exit();
            } else {
                error_log("Nenhum registro alterado ou agendamento não encontrado.");
            }
        } catch (PDOException $e) {
            error_log("Erro na atualização do agendamento: " . $e->getMessage());
        }
    }
}

$conexao = null;
header('Location: registrar_agendamento.php');
exit();
?>