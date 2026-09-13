<?php
// Caminho absoluto para o banco local SQLite mantido na pasta do projeto
$dbPath = __DIR__ . '/tcc.db';

if (!file_exists($dbPath)) {
    // Tenta verificar se o banco está na raiz se chamado a partir de um subdiretório
    $parentDbPath = dirname(__DIR__) . '/tcc.db';
    if (file_exists($parentDbPath)) {
        $dbPath = $parentDbPath;
    }
}

if (!isset($GLOBALS['pdo_sqlite_instance']) || !($GLOBALS['pdo_sqlite_instance'] instanceof PDO)) {
    try {
        $pdo = new PDO("sqlite:" . $dbPath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        // Habilita suporte a chaves estrangeiras no SQLite
        $pdo->exec("PRAGMA foreign_keys = ON;");
        
        // Aumenta o tempo limite de espera em caso de concorrência
        $pdo->exec("PRAGMA busy_timeout = 5000;");
        
        $GLOBALS['pdo_sqlite_instance'] = $pdo;
    } catch (PDOException $e) {
        die("Erro de conexão com o banco SQLite (tcc.db): " . $e->getMessage());
    }
}

$conexao = $GLOBALS['pdo_sqlite_instance'];

if (!function_exists('obterConexao')) {
    function obterConexao() {
        return $GLOBALS['pdo_sqlite_instance'];
    }
}

return $conexao;
?>
