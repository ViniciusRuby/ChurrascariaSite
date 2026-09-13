<?php
if (!empty($_GET['cod_estabelecimento'])) {
    $conexao = require_once('../conectar.php');
    $cod_estabelecimento = intval($_GET['cod_estabelecimento']);

    try {
        // Verifica se o estabelecimento existe
        $sqlSelect = "SELECT cod_estabelecimento FROM tb_estabelecimentos WHERE cod_estabelecimento = ?";
        $stmtSelect = $conexao->prepare($sqlSelect);
        $stmtSelect->execute([$cod_estabelecimento]);

        if ($stmtSelect->fetch()) {
            // Tenta excluir o estabelecimento
            $sqlDelete = "DELETE FROM tb_estabelecimentos WHERE cod_estabelecimento = ?";
            $stmtDel = $conexao->prepare($sqlDelete);
            $stmtDel->execute([$cod_estabelecimento]);

            if ($stmtDel->rowCount() > 0) {
                echo '<script>alert("Estabelecimento excluído com sucesso."); window.location.href = "registrar_estabelecimento.php";</script>';
            } else {
                echo '<script>alert("Falha na exclusão do estabelecimento. Nada foi alterado."); window.location.href = "registrar_estabelecimento.php";</script>';
            }
            exit;
        } else {
            echo '<script>alert("Estabelecimento não encontrado."); window.location.href = "registrar_estabelecimento.php";</script>';
            exit;
        }
    } catch (PDOException $e) {
        // Código 23000 trata violações de chave estrangeira no PDO SQLite
        if ($e->getCode() == '23000' || strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
            echo '<script>alert("Não é possível excluir este estabelecimento porque ele está sendo referenciado em outros registros (como reservas ou agendamentos)."); window.location.href = "registrar_estabelecimento.php";</script>';
        } else {
            echo '<script>alert("Erro ao excluir o estabelecimento."); window.location.href = "registrar_estabelecimento.php";</script>';
        }
        exit;
    }
} else {
    echo '<script>alert("Código de estabelecimento não fornecido."); window.location.href = "registrar_estabelecimento.php";</script>';
    exit;
}
?>