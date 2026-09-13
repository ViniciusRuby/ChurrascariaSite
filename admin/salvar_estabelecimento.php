<?php
$conexao = require_once('../conectar.php');

if (isset($_POST['update'])) {
    $cod_estabelecimento = $_POST['cod_estabelecimento'] ?? null;
    $nome = $_POST['nome'] ?? null;
    $cnpj = $_POST['cnpj'] ?? null;
    $fone = $_POST['fone'] ?? null;
    $ativo = $_POST['ativo'] ?? null;
    $tipo = $_POST['tipo'] ?? null;

    if ($cod_estabelecimento) {
        try {
            $sqlUpdate = "UPDATE tb_estabelecimentos 
                          SET nome = ?, cnpj = ?, fone = ?, ativo = ?, tipo = ? 
                          WHERE cod_estabelecimento = ?";

            $stmt = $conexao->prepare($sqlUpdate);
            $stmt->execute([
                $nome,
                $cnpj,
                $fone,
                $ativo,
                $tipo,
                $cod_estabelecimento
            ]);

            if ($stmt->rowCount() > 0) {
                $conexao = null;
                header('Location: registrar_estabelecimento.php');
                exit();
            } else {
                error_log("Nenhum registro atualizado ou estabelecimento não encontrado.");
            }
        } catch (PDOException $e) {
            error_log("Erro na atualização do estabelecimento: " . $e->getMessage());
        }
    }
}

$conexao = null;
header('Location: registrar_estabelecimento.php');
exit();
?>