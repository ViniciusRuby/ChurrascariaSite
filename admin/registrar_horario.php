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
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 8; // Número de registros por página
$offset = ($page - 1) * $limit;

// Construção dinâmica das condições WHERE
$where = " WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (diasemana LIKE ? OR entrada_manha LIKE ? OR entrada_tarde LIKE ? OR saida_manha LIKE ? OR saida_tarde LIKE ? OR entrada_noite LIKE ? OR saida_noite LIKE ?)";
    $searchTerm = '%' . $search . '%';
    for ($i = 0; $i < 7; $i++) {
        $params[] = $searchTerm;
    }
}

// Obter o número total de registros
try {
    $total_sql = "SELECT COUNT(*) FROM tb_horarios" . $where;
    $total_stmt = $conexao->prepare($total_sql);
    $total_stmt->execute($params);
    $total = (int)$total_stmt->fetchColumn();
    $total_pages = ceil($total / $limit);
} catch (PDOException $e) {
    error_log("Erro na contagem de horários: " . $e->getMessage());
    $total = 0;
    $total_pages = 1;
}

// Buscar dados paginados da tabela tb_horarios
try {
    $sql = "SELECT * FROM tb_horarios" . $where . " ORDER BY cod_horario ASC LIMIT ? OFFSET ?";
    $stmt = $conexao->prepare($sql);

    $paramIndex = 1;
    foreach ($params as $param) {
        $stmt->bindValue($paramIndex++, $param, PDO::PARAM_STR);
    }

    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro na consulta de horários: " . $e->getMessage());
    $horarios = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Horários</title>
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
            <h1>Registros de Horários</h1>

            <div class="box-search">
                <form method="GET" action="registrar_horario.php" class="form-inline">
                    <input type="search" name="search" class="form-control w-50" placeholder="Pesquisar" id="pesquisar" value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-dark ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0" />
                        </svg>
                    </button>
                </form>
                <a href="add_horario.php" class="btn btn-success">+</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Código</th>
                            <th scope="col">Entrada Manhã</th>
                            <th scope="col">Entrada Tarde</th>
                            <th scope="col">Saída Manhã</th>
                            <th scope="col">Saída Tarde</th>
                            <th scope="col">Entrada Noite</th>
                            <th scope="col">Saída Noite</th>
                            <th scope="col">Dia da Semana</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($horarios as $horario_data): ?>
                            <tr class="selectable-row">
                                <td>
                                    <input type="checkbox" name="select_row" class="select-row" value="<?php echo htmlspecialchars($horario_data['cod_horario']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($horario_data['cod_horario']); ?></td>
                                <td><?php echo htmlspecialchars($horario_data['entrada_manha']); ?></td>
                                <td><?php echo htmlspecialchars($horario_data['entrada_tarde']); ?></td>
                                <td><?php echo htmlspecialchars($horario_data['saida_manha']); ?></td>
                                <td><?php echo htmlspecialchars($horario_data['saida_tarde']); ?></td>
                                <td><?php echo htmlspecialchars($horario_data['entrada_noite']); ?></td>
                                <td><?php echo htmlspecialchars($horario_data['saida_noite']); ?></td>
                                <td><?php echo htmlspecialchars($horario_data['diasemana']); ?></td>
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

            <!-- Paginação -->
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
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
            <a class="aba_ativa">Horarios</a>
            <a class="aba" href="registrar_evento.php">Eventos</a>
            <a class="aba" href="registrar_agendamento.php">Agendamentos</a>
            <a class="aba" href="registrar_imagem.php">Imagens</a>
            <div class="abas-direita"></div>
        </div>
    </footer>
</body>
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
        window.location.href = 'editar_horario.php?cod_horario=' + selectedId;
    });

    deleteBtn.addEventListener('click', function() {
        const selectedId = selectedCheckbox.value;
        if (confirm("Tem certeza que deseja deletar este horario?")) {
            window.location.href = 'apagar_horario.php?cod_horario=' + selectedId;
        }
    });
</script>
</html>