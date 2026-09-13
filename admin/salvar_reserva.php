<?php
$conexao = require_once('../conectar.php');

if (isset($_POST['update'])) {
    $cod_reserva = $_POST['cod_reserva'] ?? null;
    $valor = $_POST['valor'] ?? null;
    $data = $_POST['data'] ?? null;
    $hora = $_POST['hora'] ?? null;
    $qtdmesa = $_POST['qtdmesa'] ?? null;

    if ($cod_reserva) {
        try {
            $sqlUpdate = "UPDATE tb_reservas 
                          SET valor = ?, dt = ?, hora = ?, qtdmesa = ? 
                          WHERE cod_reserva = ?";

            $stmt = $conexao->prepare($sqlUpdate);
            $stmt->execute([
                $valor,
                $data,
                $hora,
                $qtdmesa,
                $cod_reserva
            ]);

            if ($stmt->rowCount() > 0) {
                $conexao = null;
                header('Location: registrar_reserva.php');
                exit();
            } else {
                error_log("Nenhum registro atualizado ou reserva não encontrada.");
            }
        } catch (PDOException $e) {
            error_log("Erro na atualização da reserva: " . $e->getMessage());
        }
    }
}

$conexao = null;
header('Location: registrar_reserva.php');
exit();
?>