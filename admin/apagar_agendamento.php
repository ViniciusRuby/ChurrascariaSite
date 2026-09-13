<?php
if (!empty($_GET['cod_agendamento'])) {
    $conexao = require_once('../conectar.php');
    $cod_agendamento = intval($_GET['cod_agendamento']);
    
    try {
        $sqlDelete = "DELETE FROM tb_agendamentos WHERE cod_agendamento = ?";
        $stmt = $conexao->prepare($sqlDelete);
        $stmt->execute([$cod_agendamento]);

        echo '<script>alert("Agendamento excluído com sucesso."); window.location.href = "registrar_agendamento.php";</script>';
        exit;
    } catch (PDOException $e) {
        // Código 23000 trata violações de chave estrangeira/restrições no PDO SQLite
        if ($e->getCode() == '23000' || strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
            echo '<script>alert("Não é possível excluir este agendamento pois está sendo usado."); window.location.href = "registrar_agendamento.php";</script>';
        } else {
            echo '<script>alert("Ocorreu um erro ao excluir o agendamento."); window.location.href = "registrar_agendamento.php";</script>';
        }
        exit;
    }
} else {
    header('Location: registrar_agendamento.php');
    exit;
}