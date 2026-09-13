<?php
if (!empty($_GET['cod_imagem'])) {
    $conexao = require_once('../conectar.php');
    $cod_imagem = intval($_GET['cod_imagem']);

    try {
        // Seleciona o diretório da imagem para poder apagar o arquivo físico depois
        $sqlSelect = "SELECT diretorio FROM tb_imagens WHERE cod_imagem = ?";
        $stmtSelect = $conexao->prepare($sqlSelect);
        $stmtSelect->execute([$cod_imagem]);
        $row = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $caminhoImagem = $row['diretorio'];

            // Tenta deletar o registro no banco
            $sqlDelete = "DELETE FROM tb_imagens WHERE cod_imagem = ?";
            $stmtDel = $conexao->prepare($sqlDelete);
            $stmtDel->execute([$cod_imagem]);

            // Se for excluído do banco com sucesso, apaga o arquivo do servidor
            if (file_exists($caminhoImagem)) {
                @unlink($caminhoImagem);
            }

            echo '<script>alert("Imagem excluída com sucesso."); window.location.href = "registrar_imagem.php";</script>';
            exit;
        } else {
            header('Location: registrar_imagem.php');
            exit;
        }
    } catch (PDOException $e) {
        // Código 23000 trata violações de chave estrangeira no PDO SQLite
        if ($e->getCode() == '23000' || strpos($e->getMessage(), 'FOREIGN KEY') !== false) {
            echo '<script>alert("Não é possível excluir esta imagem pois está sendo usada em outros registros."); window.location.href = "registrar_imagem.php";</script>';
        } else {
            echo '<script>alert("Ocorreu um erro ao excluir a imagem."); window.location.href = "registrar_imagem.php";</script>';
        }
        exit;
    }
} else {
    header('Location: registrar_imagem.php');
    exit;
}
?>