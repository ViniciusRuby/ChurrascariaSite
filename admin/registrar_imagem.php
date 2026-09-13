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
$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
if ($page < 1) $page = 1;

$limit = 8;
$offset = ($page - 1) * $limit;

// Construção dinâmica das condições WHERE
$where = " WHERE 1=1";
$params = [];

if (!empty($search)) {
    $where .= " AND (nome LIKE ? OR diretorio LIKE ?)";
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// Obter o número total de registros
try {
    $total_sql = "SELECT COUNT(*) FROM tb_imagens" . $where;
    $total_stmt = $conexao->prepare($total_sql);
    $total_stmt->execute($params);
    $total = (int) $total_stmt->fetchColumn();
    $total_pages = ceil($total / $limit);
} catch (PDOException $e) {
    error_log("Erro na contagem de imagens: " . $e->getMessage());
    $total = 0;
    $total_pages = 1;
}

// Ordenação
$orderBy = " ORDER BY cod_imagem ASC";
if ($filter === 'recentes') {
    $orderBy = " ORDER BY cod_imagem DESC";
} elseif ($filter === 'antigos') {
    $orderBy = " ORDER BY cod_imagem ASC";
}

// Buscar dados paginados da tabela tb_imagens
try {
    $sql = "SELECT * FROM tb_imagens" . $where . $orderBy . " LIMIT ? OFFSET ?";
    $stmt = $conexao->prepare($sql);

    $paramIndex = 1;
    foreach ($params as $param) {
        $stmt->bindValue($paramIndex++, $param, PDO::PARAM_STR);
    }

    $stmt->bindValue($paramIndex++, $limit, PDO::PARAM_INT);
    $stmt->bindValue($paramIndex++, $offset, PDO::PARAM_INT);

    $stmt->execute();
    $imagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Erro na consulta de imagens: " . $e->getMessage());
    $imagens = [];
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registros de Imagens</title>
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
            <h1>Registros de Imagens</h1>

            <div class="box-search">
                <form method="GET" action="registrar_imagem.php" class="form-inline">
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
                <a href="add_imagem.php" class="btn btn-success">+</a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th scope="col"></th>
                            <th scope="col">Código</th>
                            <th scope="col">Nome</th>
                            <th scope="col">Diretório</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imagens as $imagem_data): ?>
                            <tr class="selectable-row" data-diretorio="<?php echo htmlspecialchars($imagem_data['diretorio'], ENT_QUOTES); ?>">
                                <td>
                                    <input type="checkbox" name="select_row" class="select-row" value="<?php echo htmlspecialchars($imagem_data['cod_imagem']); ?>">
                                </td>
                                <td><?php echo htmlspecialchars($imagem_data['cod_imagem']); ?></td>
                                <td><?php echo htmlspecialchars($imagem_data['nome']); ?></td>
                                <td><?php echo htmlspecialchars($imagem_data['diretorio']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="action-buttons text-center mt-4">
                <a id="edit-btn" class="btn btn-primary" href="editar_imagem.php?cod_imagem=" disabled>
                    <i class="fas fa-pencil-alt"></i> Editar
                </a>

                <button id="delete-btn" class="btn btn-danger" disabled>
                    <i class="fas fa-trash-alt"></i> Excluir
                </button>

                <button id="image-btn" class="btn btn-warning" disabled onclick="visualizarImagem(selectedImageDir)">
                    <i class="fa-solid fa-eye"></i> Visualizar Imagem
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
            <a class="aba" href="registrar_evento.php">Eventos</a>
            <a class="aba" href="registrar_agendamento.php">Agendamentos</a>
            <a class="aba_ativa">Imagens</a>
            <div class="abas-direita"></div>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>

    <script>
let selectedImageDir = '';
const editBtn = document.getElementById('edit-btn');
const deleteBtn = document.getElementById('delete-btn');
const imageBtn = document.getElementById('image-btn');
const rows = document.querySelectorAll('.selectable-row');
let selectedCheckbox = null;

rows.forEach(row => {
    row.addEventListener('click', function () {
        const checkbox = this.querySelector('.select-row');

        selectedImageDir = this.getAttribute('data-diretorio');

        if (checkbox.checked) {
            checkbox.checked = false;
            selectedCheckbox = null;
        } else {
            if (selectedCheckbox) {
                selectedCheckbox.checked = false;
            }
            checkbox.checked = true;
            selectedCheckbox = checkbox;
        }

        // Habilitar ou desabilitar os botões com base na seleção
        editBtn.disabled = !selectedCheckbox;
        deleteBtn.disabled = !selectedCheckbox;
        imageBtn.disabled = !selectedCheckbox;
    });
});

function visualizarImagem(diretorio) {
    if (diretorio) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Visualizar Imagem</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <img src="${diretorio}" class="img-fluid" alt="Imagem" />
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
        $(modal).modal('show');
    } else {
        alert("Por favor, selecione uma imagem para visualizar.");
    }
}

deleteBtn.addEventListener('click', function () {
    if (selectedCheckbox) {
        const codImagem = selectedCheckbox.value;
        if (confirm('Tem certeza que deseja excluir a imagem selecionada?')) {
            window.location.href = `apagar_imagem.php?cod_imagem=${codImagem}`;
        }
    } else {
        alert("Selecione uma imagem para excluir.");
    }
});

editBtn.addEventListener('click', function (event) {
    if (selectedCheckbox) {
        const codImagem = selectedCheckbox.value;
        this.href = `editar_imagem.php?cod_imagem=${codImagem}`;
    } else {
        alert("Selecione uma imagem para editar.");
        event.preventDefault();
        window.location.href = `registrar_imagem.php`;
    }
});

</script>

</body>

</html>