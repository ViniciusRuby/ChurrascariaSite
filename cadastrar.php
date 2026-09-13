<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensagem = ''; 
$tipo = 'U';
$nome = $email = $cpf = $fone = $dtnasc = '';

if (isset($_POST['submit'])) {
    require_once('conectar.php');
    $conexao = obterConexao();
    $nome = $_POST['nome']; 
    $email = $_POST['email']; 
    $cpf = $_POST['cpf']; 
    $fone = $_POST['fone'];
    $dtnasc = $_POST['dtnasc'] ?? '';

    if (!$dtnasc) { 
        $mensagem = 'Data de nascimento não informada.'; 
    }
    
    $ativo = $_POST['ativo']; 
    $tipo = $_POST['tipo'];
    $senha = $_POST['senha']; 
    $conf_senha = $_POST['conf_senha'];

    if (empty($fone) || empty($cpf)) {
        $mensagem = 'Telefone e CPF devem estar completamente preenchidos.';
    } elseif ($senha !== $conf_senha) {
        $mensagem = 'Senhas não idênticas';
    } else {
        $idadeMinima = 18;
        $dataNascimento = new DateTime($dtnasc);
        $hoje = new DateTime(); 
        $idade = $hoje->diff($dataNascimento)->y;

        if ($idade < $idadeMinima) {
            $mensagem = 'Idade mínima para cadastro: 18 anos!';
        } else {
            $cpf = preg_replace('/[^0-9]/', '', $cpf);
            $fone = preg_replace('/[^0-9]/', '', $fone);
            
            try {
                // Consulta se e-mail, CPF ou telefone já existem
                $stmt = $conexao->prepare("SELECT email, cpf, fone FROM tb_clientes WHERE email = ? OR cpf = ? OR fone = ?");
                $stmt->execute([$email, $cpf, $fone]);
                
                if ($stmt->fetch()) {
                    $mensagem = 'Email, CPF ou Telefone já cadastrado.';
                } else {
                    $senha_hash = md5($senha);
                    $stmt = $conexao->prepare("INSERT INTO tb_clientes (nome, email, cpf, fone, dtnasc, ativo, tipo, senha) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    if ($stmt->execute([$nome, $email, $cpf, $fone, $dtnasc, $ativo, $tipo, $senha_hash])) {
                        $mensagem = 'Cadastro realizado com sucesso!';
                        // Limpa os dados do formulário
                        $nome = $email = $cpf = $fone = $dtnasc = '';
                    } else {
                        $mensagem = 'Erro ao cadastrar. Tente novamente.';
                    }
                }
            } catch (PDOException $e) {
                $mensagem = 'Erro no banco de dados: ' . addslashes($e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Cadastro</title>
    <link rel="icon" type="image/x-icon" href="img/icone.png">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/cadastro.css">
    <link rel="stylesheet" href="css/alert.css">
    <link rel="stylesheet" href="css/barra_lateral.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script>
        function redirectToLogin() { window.location.href = 'login.php'; }
        window.onload = function () {
            const mensagem = '<?= addslashes($mensagem) ?>';
            if (mensagem.includes("Cadastro realizado com sucesso")) {
                let countdown = 3; const countdownDisplay = document.getElementById("countdown");
                countdownDisplay.innerText = `Redirecionando em ${countdown} segundos...`;
                const interval = setInterval(function () {
                    countdown--; if (countdown <= 0) { clearInterval(interval); redirectToLogin(); }
                    countdownDisplay.innerText = `Redirecionando em ${countdown} segundos...`;
                }, 1000);
            }
        };
    </script>
</head>

<body>
    <div class="d-flex">
        <nav id="sidebar">
            <div id="sidebar_content">
                <div id="user">
                    <img src="img/icone.png" id="user_avatar" alt="Avatar">
                    <p id="user_infos">
                        <span class="item-description">Delicious Churras</span>
                        <span class="item-description">A melhor churrascaria!</span>
                    </p>
                </div>
                <ul id="side_items">
                    <li class="side-item"><a href="index.php"><i class="fa-solid fa-house"></i><span class="item-description">Home</span></a></li>
                    <li class="side-item"><a href="login.php"><i class="fa-solid fa-user"></i><span class="item-description">Logar</span></a></li>
                    <li class="side-item active"><a href=""><i class="fa-solid fa-paperclip"></i><span class="item-description">Cadastrar</span></a></li>
                    <li class="side-item"><a href="sobre_nos.php"><i class="fa-solid fa-drumstick-bite"></i><span class="item-description">Sobre nós</span></a></li>
                    <li class="side-item"><a href="contato.php"><i class="fa-solid fa-phone"></i><span class="item-description">Contato</span></a></li>
                </ul>
                <button id="open_btn"><i id="open_btn_icon" class="fa-solid fa-chevron-right"></i></button>
            </div>
            <div id="logout">
                <button id="logout_btn" onclick="window.location.href='sair.php';"><i class="fa-solid fa-right-from-bracket"></i><span class="item-description">Sair</span></button>
            </div>
        </nav>

        <main class="flex-grow-1 d-flex justify-content-center align-items-center">
            <div class="register-container">
                <div class="register">
                    <h1>Cadastrar-se</h1>
                    <?php if ($mensagem): ?>
                        <div class="alert <?= strpos($mensagem, 'sucesso') !== false ? 'alert-success' : 'alert-error' ?>">
                            <?= $mensagem ?>
                        </div>
                        <div id="countdown"></div>
                    <?php endif; ?>
                    <form action="cadastrar.php" method="post">
                        <input type="hidden" name="ativo" value="S">
                        <input type="hidden" name="tipo" value="U">
                        <div class="input-container">
                            <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($nome) ?>" required>
                            <label class="label" for="nome">Nome</label><span class="underline"></span>
                        </div>
                        <div class="input-container">
                            <input type="email" name="email" id="email" value="<?= htmlspecialchars($email) ?>" required>
                            <label class="label" for="email">Email</label><span class="underline"></span>
                        </div>
                        <div class="input-container">
                            <input type="password" name="senha" id="senha" required>
                            <label class="label" for="senha">Senha</label><span class="underline"></span>
                        </div>
                        <div class="input-container">
                            <input type="password" name="conf_senha" id="conf_senha" required>
                            <label class="label" for="conf_senha">Confirmar Senha</label><span class="underline"></span>
                        </div>
                        <div class="input-container">
                            <input type="text" name="cpf" id="cpf" maxlength="14" value="<?= htmlspecialchars($cpf) ?>" onkeyup="formatarCampo(this);" required>
                            <label class="label" for="cpf">CPF</label><span class="underline"></span>
                        </div>
                        <div class="input-container">
                            <input type="tel" name="fone" id="fone" maxlength="15" value="<?= htmlspecialchars($fone) ?>" onkeyup="formatarTelefone(this);" required>
                            <label class="label" for="fone">Telefone</label><span class="underline"></span>
                        </div>
                        <div class="input-container">
                            <input type="date" name="dtnasc" id="dtnasc" value="<?= htmlspecialchars($dtnasc) ?>" required>
                            <label class="label" for="dtnasc" style="top: -20px; font-size: 14px;">Data de Nascimento </label>
                        </div>
                        <button type="submit" name="submit">Cadastrar</button>
                    </form>
                </div>
            </div>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="js/barra_lateral.js"></script>
    <script src="js/cpf_funcao.js"></script>
    <script src="js/numero_funcao.js"></script>
</body>
</html>