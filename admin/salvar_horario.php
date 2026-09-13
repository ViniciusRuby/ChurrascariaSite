<?php
$conexao = require_once('../conectar.php');

if (isset($_POST['update'])) {
    $cod_horario = $_POST['cod_horario'] ?? null;
    $entrada_manha = $_POST['entrada_manha'] ?? null;
    $entrada_tarde = $_POST['entrada_tarde'] ?? null;
    $saida_manha = $_POST['saida_manha'] ?? null;
    $diasemana = $_POST['diasemana'] ?? null;
    $saida_tarde = $_POST['saida_tarde'] ?? null;
    $entrada_noite = $_POST['entrada_noite'] ?? null;
    $saida_noite = $_POST['saida_noite'] ?? null;

    if ($cod_horario) {
        try {
            $sqlUpdate = "UPDATE tb_horarios 
                          SET entrada_manha = ?, entrada_tarde = ?, saida_manha = ?, diasemana = ?, saida_tarde = ?, entrada_noite = ?, saida_noite = ? 
                          WHERE cod_horario = ?";

            $stmt = $conexao->prepare($sqlUpdate);
            $stmt->execute([
                $entrada_manha,
                $entrada_tarde,
                $saida_manha,
                $diasemana,
                $saida_tarde,
                $entrada_noite,
                $saida_noite,
                $cod_horario
            ]);

            if ($stmt->rowCount() > 0) {
                $conexao = null;
                header('Location: registrar_horario.php');
                exit();
            } else {
                error_log("Nenhum registro atualizado ou horário não encontrado.");
            }
        } catch (PDOException $e) {
            error_log("Erro na atualização do horário: " . $e->getMessage());
        }
    }
}

$conexao = null;
header('Location: registrar_horario.php');
exit();
?>