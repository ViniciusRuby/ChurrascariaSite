<?php
if (!empty($_GET['cod_evento'])) {
    $conexao = require_once('../conectar.php');
    $cod_evento = intval($_GET['cod_evento']);

    try {
        // Verifica se o evento existe
        $sqlSelect = "SELECT cod_evento FROM tb_eventos WHERE cod_evento = ?";
        $stmtSelect = $conexao->prepare($sqlSelect);
        $stmtSelect->execute([$cod_evento]);

        if ($stmtSelect->fetch()) {
            // Tenta deletar o evento
            $sqlDelete = "DELETE FROM tb_eventos WHERE cod_evento = ?";
            $stmtDel = $conexao->prepare($sqlDelete);
            $stmtDel->execute([$cod_evento]);

            echo '<script>alert("Evento excluído com sucesso."); window.location.href = "registrar_evento.php";</script>';
            exit;
        } else {
            header('Location: registrar_evento.php');
            exit;
        }
    } catch (PDOException $e) {
        // Código 23000 trata violações de chave estrangeira no PDO SQLite
        if ($e->getCode() == '23000' || strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
            echo '<script>alert("Não é possível excluir este evento pois está sendo usado em outros registros."); window.location.href = "registrar_evento.php";</script>';
        } else {
            echo '<script>alert("Ocorreu um erro ao excluir o evento."); window.location.href = "registrar_evento.php";</script>';
        }
        exit;
    }
} else {
    header('Location: registrar_evento.php');
    exit;
}
?>