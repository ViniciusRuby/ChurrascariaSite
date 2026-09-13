<?php
session_start();
require_once('../conectar.php');
$conexao = obterConexao();

if (isset($_GET['cod_imagem'])) {
    $cod_imagem = intval($_GET['cod_imagem']);

    try {
        $stmt = $conexao->prepare("SELECT diretorio FROM tb_imagens WHERE cod_imagem = ?");
        $stmt->execute([$cod_imagem]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $diretorio = $row['diretorio'];
            $caminho = dirname(__DIR__) . '/' . ltrim($diretorio, '/');
            
            if (file_exists($caminho)) {
                $mime = mime_content_type($caminho) ?: 'image/jpeg';
                header("Content-Type: " . $mime);
                readfile($caminho);
                exit();
            } else {
                echo "Arquivo de imagem não encontrado no servidor.";
            }
        } else {
            echo "Imagem não encontrada no banco de dados.";
        }
    } catch (PDOException $e) {
        error_log("Erro ao buscar imagem: " . $e->getMessage());
        echo "Erro ao carregar a imagem.";
    }
} else {
    echo "Código da imagem não fornecido.";
}
?>