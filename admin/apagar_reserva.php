<?php
if (!empty($_GET['cod_reserva'])) {
    $conexao = require_once('../conectar.php');
    $cod_reserva = intval($_GET['cod_reserva']);

    try {
        // Verifica se a reserva existe
        $sqlSelect = "SELECT cod_reserva FROM tb_reservas WHERE cod_reserva = ?";
        $stmtSelect = $conexao->prepare($sqlSelect);
        $stmtSelect->execute([$cod_reserva]);

        if ($stmtSelect->fetch()) {
            // Tenta deletar a reserva
            $sqlDelete = "DELETE FROM tb_reservas WHERE cod_reserva = ?";
            $stmtDel = $conexao->prepare($sqlDelete);
            $stmtDel->execute([$cod_reserva]);

            echo '<script>alert("Reserva excluída com sucesso."); window.location.href = "registrar_reserva.php";</script>';
            exit;
        } else {
            header('Location: registrar_reserva.php');
            exit;
        }
    } catch (PDOException $e) {
        // Código 23000 trata violações de chave estrangeira no PDO SQLite
        if ($e->getCode() == '23000' || strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
            echo '<script>alert("Não é possível excluir esta reserva porque está sendo usada em outros registros."); window.location.href = "registrar_reserva.php";</script>';
        } else {
            echo '<script>alert("Ocorreu um erro ao excluir a reserva."); window.location.href = "registrar_reserva.php";</script>';
        }
        exit;
    }
} else {
    header('Location: registrar_reserva.php');
    exit;
}
?>