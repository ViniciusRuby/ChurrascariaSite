<?php
if (!empty($_GET['cod_cliente'])) {
    $conexao = require_once('../conectar.php');
    $cod_cliente = intval($_GET['cod_cliente']);

    try {
        // Verifica se o cliente existe
        $sqlSelect = "SELECT cod_cliente FROM tb_clientes WHERE cod_cliente = ?";
        $stmtSelect = $conexao->prepare($sqlSelect);
        $stmtSelect->execute([$cod_cliente]);

        if ($stmtSelect->fetch()) {
            // Tenta deletar o cliente
            $sqlDelete = "DELETE FROM tb_clientes WHERE cod_cliente = ?";
            $stmtDel = $conexao->prepare($sqlDelete);
            $stmtDel->execute([$cod_cliente]);

            if ($stmtDel->rowCount() > 0) {
                echo '<script>alert("Cliente excluído com sucesso."); window.location.href = "registrar_cliente.php";</script>';
            } else {
                echo '<script>alert("Falha na exclusão do cliente. Nenhuma alteração foi feita."); window.location.href = "registrar_cliente.php";</script>';
            }
            exit;
        } else {
            header('Location: registrar_cliente.php');
            exit;
        }
    } catch (PDOException $e) {
        // Código 23000 trata violações de chave estrangeira no PDO SQLite
        if ($e->getCode() == '23000' || strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
            echo '<script>alert("Não é possível excluir este cliente pois ele possui registros vinculados (como reservas ou agendamentos)."); window.location.href = "registrar_cliente.php";</script>';
        } else {
            echo '<script>alert("Ocorreu um erro ao excluir o cliente."); window.location.href = "registrar_cliente.php";</script>';
        }
        exit;
    }
} else {
    header('Location: registrar_cliente.php');
    exit;
}
?>