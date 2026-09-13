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

// Construção dinâmica das condições WHERE
$where = " WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (cod_reserva LIKE ? OR cod_cliente LIKE ? OR cod_estabelecimento LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($filter === 'ativos') {
    $where .= " AND ativo = 'S'";
} elseif ($filter === 'inativos') {
    $where .= " AND ativo = 'N'";
}

// Obter o número total de registros
try {
    $total_sql = "SELECT COUNT(*) FROM tb_reservas" . $where;
    $total_stmt = $conexao->prepare($total_sql);
    $total_stmt->execute($params);
    $total = (int)$total_stmt->fetchColumn();
    $total_pages = ceil($total / $limit);
} catch (PDOException $e) {
    error_log("Erro na contagem de reservas: " . $e->getMessage());
    $total = 0;
    $total_pages = 1;
}

// Buscar dados paginados da tabela tb_reservas
try {
    $sql = "SELECT * FROM tb_reservas" . $where . " ORDER BY cod_reserva ASC LIMIT ? OFFSET ?";
    $stmt = $conexao->prepare($sql);

    $paramIndex = 1;
    foreach ($params as $param) {
        $stmt->bindValue($paramIndex++, $param, PDO::PARAM_STR);
    }

    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro na consulta de reservas: " . $e->getMessage());
    $reservas = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Reservas</title>
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
            <h1>Registros de Reservas</h1>

            <div class="box-search">
                <form method="GET" action="registrar_reserva.php" class="form-inline">
                    <input type="search" name="search" class="form-control w-50" placeholder="Pesquisar" id="pesquisar" value="<?php echo htmlspecialchars($search); ?>">
                    <input type="hidden" name="filter" value="<?php echo htmlspecialchars($filter); ?>">
                    <button type="submit" class="btn btn-dark ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
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
                            <th scope="col">Valor</th>
                            <th scope="col">Data</th>
                            <th scope="col">Hora</th>
                            <th scope="col">Quantidade de Mesas</th>
                            <th scope="col">Código do Cliente</th>
                            <th scope="col">Código do Estabelecimento</th>
                            <th scope="col">Forma de Pagamento</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $reserva_data): ?>
                            <tr class="selectable-row">
                                <td>
                                    <input type="checkbox" name="select_row" class="select-row" value="<?php echo htmlspecialchars($reserva_data['cod_reserva']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($reserva_data['cod_reserva']); ?></td>
                                <td><?php echo htmlspecialchars($reserva_data['valor']); ?></td>
                                <td><?php echo htmlspecialchars($reserva_data['dt']); ?></td>
                                <td><?php echo htmlspecialchars($reserva_data['hora']); ?></td>
                                <td><?php echo htmlspecialchars($reserva_data['qtdmesa']); ?></td>
                                <td><?php echo htmlspecialchars($reserva_data['cod_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($reserva_data['cod_estabelecimento']); ?></td>
                                <td><?php echo htmlspecialchars($reserva_data['pagamento']); ?></td>
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

            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo urlencode($filter); ?>"><?php echo $i; ?></a>
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
            <a class="aba_ativa">Reservas</a>
            <a class="aba" href="registrar_estabelecimento.php">Estabelecimentos</a>
            <a class="aba" href="registrar_horario.php">Horarios</a>
            <a class="aba" href="registrar_evento.php">Eventos</a>
            <a class="aba" href="registrar_agendamento.php">Agendamentos</a>
            <a class="aba" href="registrar_imagem.php">Imagens</a>
            <div class="abas-direita"></div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.11.0/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script>
        const editBtn = document.getElementById('edit-btn');
        const deleteBtn = document.getElementById('delete-btn');
        const rows = document.querySelectorAll('.selectable-row');
        let selectedCheckbox = null;

        rows.forEach(row => {
            row.addEventListener('click', function() {
                const checkbox = this.querySelector('.select-row');
                
                if (checkbox.checked) {
                    checkbox.checked = false;
                    selectedCheckbox = null;
                    editBtn.disabled = true;
                    deleteBtn.disabled = true;
                } else {
                    if (selectedCheckbox) {
                        selectedCheckbox.checked = false;
                    }
                    checkbox.checked = true;
                    selectedCheckbox = checkbox;
                    editBtn.disabled = false;
                    deleteBtn.disabled = false;
                }
            });
        });

        editBtn.addEventListener('click', function() {
            const selectedId = selectedCheckbox.value;
            window.location.href = 'editar_reserva.php?cod_reserva=' + selectedId;
        });

        deleteBtn.addEventListener('click', function() {
            const selectedId = selectedCheckbox.value;
            if (confirm("Tem certeza que deseja deletar esta reserva?")) {
                window.location.href = 'apagar_reserva.php?cod_reserva=' + selectedId;
            }
        });
    </script>
</body>
</html>