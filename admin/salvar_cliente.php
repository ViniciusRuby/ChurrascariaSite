<?php
$conexao = require_once('../conectar.php');

if (isset($_POST['update'])) {
    $cod_cliente = $_POST['cod_cliente'] ?? null;
    $nome = $_POST['nome'] ?? null;
    $email = $_POST['email'] ?? null;
    $senha = $_POST['senha'] ?? null;
    $cpf = $_POST['cpf'] ?? null;
    $fone = $_POST['fone'] ?? null;
    $ativo = $_POST['ativo'] ?? null;
    $tipo = $_POST['tipo'] ?? null;

    if ($cod_cliente) {
        try {
            $sqlUpdate = "UPDATE tb_clientes 
                          SET nome = ?, email = ?, senha = ?, cpf = ?, fone = ?, ativo = ?, tipo = ? 
                          WHERE cod_cliente = ?";

            $stmt = $conexao->prepare($sqlUpdate);
            $stmt->execute([
                $nome,
                $email,
                $senha,
                $cpf,
                $fone,
                $ativo,
                $tipo,
                $cod_cliente
            ]);

            if ($stmt->rowCount() > 0) {
                header('Location: registrar_cliente.php');
                exit();
            } else {
                error_log("Nenhum registro atualizado ou cliente não encontrado.");
            }
        } catch (PDOException $e) {
            error_log("Erro na atualização do cliente: " . $e->getMessage());
        }
    }
}

$conexao = null;
header('Location: registrar_cliente.php');
exit();
?>