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
$search = $_GET['search'] ?? '';
$filter = $_GET['filter'] ?? 'todos';
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 8; // Número de registros por página
$offset = ($page - 1) * $limit;

// Construção dinâmica das condições WHERE
$where = " WHERE 1=1";
$params = [];

if ($filter === 'ativos') {
    $where .= " AND ativo = 'S'";
} elseif ($filter === 'inativos') {
    $where .= " AND ativo = 'N'";
}

if (!empty($search)) {
    $where .= " AND (nome LIKE ? OR tipo LIKE ? OR valor LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Obter o número total de registros
try {
    $total_sql = "SELECT COUNT(*) FROM tb_eventos" . $where;
    $total_stmt = $conexao->prepare($total_sql);
    $total_stmt->execute($params);
    $total = (int) $total_stmt->fetchColumn();
    $total_pages = ceil($total / $limit);
} catch (PDOException $e) {
    error_log("Erro na contagem de eventos: " . $e->getMessage());
    $total = 0;
    $total_pages = 1;
}

// Buscar dados paginados da tabela tb_eventos
try {
    $sql = "SELECT * FROM tb_eventos" . $where . " ORDER BY cod_evento ASC LIMIT ? OFFSET ?";
    $stmt = $conexao->prepare($sql);

    $paramIndex = 1;
    foreach ($params as $param) {
        $stmt->bindValue($paramIndex++, $param, PDO::PARAM_STR);
    }

    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $eventos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro na consulta de eventos: " . $e->getMessage());
    $eventos = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Eventos</title>
    <link rel="icon" type="image/x-icon" href="../img/icone.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
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
            <h1>Registros de Eventos</h1>

            <div class="box-search">
                <form method="GET" action="registrar_evento.php" class="form-inline">
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
                <a href="add_evento.php" class="btn btn-success">+</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Código</th>
                            <th scope="col">Tipo</th>
                            <th scope="col">Ativo</th>
                            <th scope="col">Valor</th>
                            <th scope="col">Nome</th>
                            <th scope="col">Data Início</th>
                            <th scope="col">Data Fim</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($eventos as $evento_data): ?>
                            <tr class="selectable-row">
                                <td>
                                    <input type="checkbox" name="select_row" class="select-row"
                                        value="<?php echo htmlspecialchars($evento_data['cod_evento']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($evento_data['cod_evento']); ?></td>
                                <td>
                                    <?php
                                    $tipoExtenso = 'Grande';
                                    if ($evento_data['tipo'] === 'P') $tipoExtenso = 'Pequeno';
                                    elseif ($evento_data['tipo'] === 'M') $tipoExtenso = 'Médio';
                                    echo htmlspecialchars($tipoExtenso);
                                    ?>
                                </td>
                                <td><?php echo ($evento_data['ativo'] === 'S') ? 'Sim' : 'Não'; ?></td>
                                <td><?php echo htmlspecialchars($evento_data['valor']); ?></td>
                                <td><?php echo htmlspecialchars($evento_data['nome']); ?></td>
                                <td><?php echo htmlspecialchars($evento_data['data_inicio']); ?></td>
                                <td><?php echo htmlspecialchars($evento_data['data_fim']); ?></td>
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

                <button id="image-btn" class="btn btn-info" disabled>
                    <i class="fa-regular fa-image"></i> Editar Imagem
                </button>
            </div>

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
            <a class="aba_ativa">Eventos</a>
            <a class="aba" href="registrar_agendamento.php">Agendamentos</a>
            <a class="aba" href="registrar_imagem.php">Imagens</a>
            <div class="abas-direita">
                <a class="aba" href="registrar_evento.php?filter=todos">Todos</a>
                <a class="aba" href="registrar_evento.php?filter=ativos">Ativos</a>
                <a class="aba" href="registrar_evento.php?filter=inativos">Inativos</a>
            </div>
        </div>
    </footer>

    <script>
        const editBtn = document.getElementById('edit-btn');
        const deleteBtn = document.getElementById('delete-btn');
        const imageBtn = document.getElementById('image-btn');
        const rows = document.querySelectorAll('.selectable-row');
        let selectedCheckbox = null;

        rows.forEach(row => {
            row.addEventListener('click', function () {
                const checkbox = this.querySelector('.select-row');

                if (checkbox.checked) {
                    checkbox.checked = false;
                    selectedCheckbox = null;
                    editBtn.disabled = true;
                    deleteBtn.disabled = true;
                    imageBtn.disabled = true;
                } else {
                    if (selectedCheckbox) {
                        selectedCheckbox.checked = false;
                    }
                    checkbox.checked = true;
                    selectedCheckbox = checkbox;
                    editBtn.disabled = false;
                    deleteBtn.disabled = false;
                    imageBtn.disabled = false;
                }
            });
        });

        editBtn.addEventListener('click', function () {
            const selectedId = selectedCheckbox.value;
            window.location.href = 'editar_evento.php?cod_evento=' + selectedId;
        });

        deleteBtn.addEventListener('click', function () {
            const selectedId = selectedCheckbox.value;
            if (confirm("Tem certeza que deseja deletar este evento?")) {
                window.location.href = 'apagar_evento.php?cod_evento=' + selectedId;
            }
        });

        imageBtn.addEventListener('click', function () {
            const selectedId = selectedCheckbox.value;
            window.location.href = 'editar_imagem.php?cod_imagem=' + selectedId;
        });
    </script>
</body>

</html>