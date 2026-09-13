<?php
session_start();
$conexao = require_once('../conectar.php');

if (!isset($_SESSION['email']) || !isset($_SESSION['senha']) || $_SESSION['tipo'] !== 'A') {
    unset($_SESSION['email']);
    unset($_SESSION['senha']);
    unset($_SESSION['tipo']);
    header('Location: ../acesso_negado.php');
    exit();
}

$logado = $_SESSION['email'];
$filter = $_GET['filter'] ?? '';
$search = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 8; // Número de registros por página
$offset = ($page - 1) * $limit;

// Montagem da query de busca e contagem com PDO
$where = " WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (cod_agendamento LIKE ? OR dt LIKE ? OR hora LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($filter === 'disponiveis') {
    $where .= " AND disponibilidade = 'S'";
} elseif ($filter === 'indisponiveis') {
    $where .= " AND disponibilidade = 'N'";
}

// Buscar total de registros
try {
    $total_sql = "SELECT COUNT(*) FROM tb_agendamentos" . $where;
    $total_stmt = $conexao->prepare($total_sql);
    $total_stmt->execute($params);
    $total = (int)$total_stmt->fetchColumn();
    $total_pages = ceil($total / $limit);
} catch (PDOException $e) {
    error_log("Erro na contagem de registros: " . $e->getMessage());
    $total = 0;
    $total_pages = 1;
}

// Buscar dados da página atual
try {
    $sql = "SELECT * FROM tb_agendamentos" . $where . " ORDER BY cod_agendamento ASC LIMIT ? OFFSET ?";
    $stmt = $conexao->prepare($sql);
    
    // Vincula os parâmetros do filtro de busca
    $paramIndex = 1;
    foreach ($params as $param) {
        $stmt->bindValue($paramIndex++, $param, PDO::PARAM_STR);
    }
    
    // Vincula o LIMIT e OFFSET como inteiros
    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro na consulta de agendamentos: " . $e->getMessage());
    $agendamentos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Agendamentos</title>
    <link rel="icon" type="image/x-icon" href="../img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/barras.css">
    <link rel="stylesheet" href="../css/sistema.css">
</head>

<body>
    <div class="barra-superior">
        <div>
            <img src="../home.svg" alt="icone de home">
            <h2>Delicious Churras</h2>
        </div>
        <div>
            <a href="../index.php">Home</a> |
            <a href="../sair.php">Sair</a>
        </div>
    </div>

    <div class="container-conteudo">
        <div class="conteudo">
            <h1>Registros de Agendamentos</h1>

            <div class="box-search">
                <form method="GET" action="registrar_agendamento.php" class="form-inline">
                    <input type="search" name="search" class="form-control w-50" placeholder="Pesquisar" id="pesquisar"
                        value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <button type="submit" class="btn btn-dark ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                            class="bi bi-search" viewBox="0 0 16 16">
                            <path
                                d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                        </svg>
                    </button>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Código</th>
                            <th scope="col">Data</th>
                            <th scope="col">Hora</th>
                            <th scope="col">Cliente</th>
                            <th scope="col">Estabelecimento</th>
                            <th scope="col">Evento</th>
                            <th scope="col">Forma de Pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($agendamentos as $agendamento_data): ?>
                            <tr class="selectable-row">
                                <td>
                                    <input type="checkbox" name="select_row" class="select-row"
                                        value="<?php echo htmlspecialchars($agendamento_data['cod_agendamento']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($agendamento_data['cod_agendamento']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento_data['dt']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento_data['hora']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento_data['cod_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento_data['cod_estabelecimento']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento_data['cod_evento']); ?></td>
                                <td><?php echo htmlspecialchars($agendamento_data['pagamento']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="action-buttons text-center mt-4">
                <button id="edit-btn" class="btn btn-primary" disabled>
                    <i class="fas fa-pencil-alt"></i> Editar
                </button>

                <button id="delete-btn" class="btn btn-danger" disabled>
                    <i class="fas fa-trash-alt"></i> Excluir
                </button>
            </div>

            <!-- Pagination -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link"
                                href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        </div>
    </div>

    <footer class="abas-footer">
        <div class="abas-container">
            <a class="aba" href="sistema.php">Sistema</a>
            <a class="aba" href="registrar_cliente.php">Clientes</a>
            <a class="aba" href="registrar_reserva.php">Reservas</a>
            <a class="aba" href="registrar_estabelecimento.php">Estabelecimentos</a>
            <a class="aba" href="registrar_horario.php">Horarios</a>
            <a class="aba" href="registrar_evento.php">Eventos</a>
            <a class="aba_ativa">Agendamentos</a>
            <a class="aba" href="registrar_imagem.php">Imagens</a>
            <div class="abas-direita">
                <a class="aba" href="registrar_agendamento.php?filter=todos">Todos</a>
                <a class="aba" href="registrar_agendamento.php?filter=disponiveis">Disponíveis</a>
                <a class="aba" href="registrar_agendamento.php?filter=indisponiveis">Indisponíveis</a>
            </div>
        </div>
    </footer>

    <script>
        const editBtn = document.getElementById('edit-btn');
        const deleteBtn = document.getElementById('delete-btn');
        const rows = document.querySelectorAll('.selectable-row');
        let selectedCheckbox = null; // Para acompanhar qual linha está selecionada

        rows.forEach(row => {
            row.addEventListener('click', function () {
                const checkbox = this.querySelector('.select-row');

                // Desmarcar a checkbox se já estiver selecionada
                if (checkbox.checked) {
                    checkbox.checked = false;
                    selectedCheckbox = null;
                    editBtn.disabled = true;
                    deleteBtn.disabled = true;
                } else {
                    // Desmarcar a linha anterior, se houver
                    if (selectedCheckbox) {
                        selectedCheckbox.checked = false;
                    }
                    // Marcar a nova linha
                    checkbox.checked = true;
                    selectedCheckbox = checkbox;
                    editBtn.disabled = false;
                    deleteBtn.disabled = false;
                }
            });
        });

        editBtn.addEventListener('click', function () {
            const selectedId = selectedCheckbox.value;
            window.location.href = 'editar_agendamento.php?cod_agendamento=' + selectedId;
        });

        deleteBtn.addEventListener('click', function () {
            const selectedId = selectedCheckbox.value;
            if (confirm("Tem certeza que deseja deletar este agendamento?")) {
                window.location.href = 'apagar_agendamento.php?cod_agendamento=' + selectedId;
            }
        });
    </script>
</body>

</html>