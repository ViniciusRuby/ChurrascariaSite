<?php
if (!empty($_GET['cod_horario'])) {
    $conexao = require_once('../conectar.php');
    $cod_horario = intval($_GET['cod_horario']);

    try {
        // Verifica se o horário existe
        $sqlSelect = "SELECT cod_horario FROM tb_horarios WHERE cod_horario = ?";
        $stmtSelect = $conexao->prepare($sqlSelect);
        $stmtSelect->execute([$cod_horario]);

        if ($stmtSelect->fetch()) {
            // Tenta deletar o horário
            $sqlDelete = "DELETE FROM tb_horarios WHERE cod_horario = ?";
            $stmtDel = $conexao->prepare($sqlDelete);
            $stmtDel->execute([$cod_horario]);

            echo '<script>alert("Horário excluído com sucesso."); window.location.href = "registrar_horario.php";</script>';
            exit;
        } else {
            header('Location: registrar_horario.php');
            exit;
        }
    } catch (PDOException $e) {
        // Código 23000 trata violações de chave estrangeira no PDO SQLite
        if ($e->getCode() == '23000' || strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
            echo '<script>alert("Não é possível excluir este horário pois está sendo usado em outros registros."); window.location.href = "registrar_horario.php";</script>';
        } else {
            echo '<script>alert("Ocorreu um erro ao excluir o horário."); window.location.href = "registrar_horario.php";</script>';
        }
        exit;
    }
} else {
    header('Location: registrar_horario.php');
    exit;
}
?>