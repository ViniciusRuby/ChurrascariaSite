<?php
$conexao = require_once('../conectar.php');

if (isset($_POST['update'])) {
    $cod_evento = $_POST['cod_evento'] ?? null;
    $tipo = $_POST['tipo'] ?? null;
    $valor = $_POST['valor'] ?? null;
    $nome = $_POST['nome'] ?? null;
    $ativo = $_POST['ativo'] ?? null;
    $data_inicio = $_POST['data_inicio'] ?? null;
    $data_fim = $_POST['data_fim'] ?? null;

    if ($cod_evento) {
        try {
            $sqlUpdate = "UPDATE tb_eventos 
                          SET tipo = ?, valor = ?, nome = ?, ativo = ?, data_inicio = ?, data_fim = ? 
                          WHERE cod_evento = ?";

            $stmt = $conexao->prepare($sqlUpdate);
            $stmt->execute([
                $tipo,
                $valor,
                $nome,
                $ativo,
                $data_inicio,
                $data_fim,
                $cod_evento
            ]);

            if ($stmt->rowCount() > 0) {
                $conexao = null;
                header('Location: registrar_evento.php');
                exit();
            } else {
                error_log("Nenhum registro atualizado ou evento não encontrado.");
            }
        } catch (PDOException $e) {
            error_log("Erro na atualização do evento: " . $e->getMessage());
        }
    }
}

$conexao = null;
header('Location: registrar_evento.php');
exit();
?>