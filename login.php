<?php
require_once 'config.php';

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'register') {
        $nome = $_POST['nome'];
        $cpf = $_POST['cpf'];
        $celular = $_POST['celular'];
        $email = strtolower($_POST['email']);
        $senha = password_hash($_POST['senha'], PASSWORD_DEFAULT);
        
        // Emails dos donos do site que terão permissão de administrador
        $emails_admin = ['admin@rockettcg.com', 'dono@rockettcg.com'];
        $tipo = in_array($email, $emails_admin) ? 'admin' : 'cliente';

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nome, cpf, celular, email, senha, tipo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nome, $cpf, $celular, $email, $senha, $tipo]);
            
            // Auto login after register
            $_SESSION['usuario_id'] = $pdo->lastInsertId();
            $_SESSION['usuario_nome'] = $nome;
            $_SESSION['usuario_tipo'] = $tipo;
            
            header("Location: index.php");
            exit;
        } catch(PDOException $e) {
            $erro = "Erro ao registrar: Email já pode estar em uso.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'login') {
        $email = strtolower($_POST['email']);
        $senha = $_POST['senha'];

        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            // Garante privilégio de admin no login caso o email do usuário seja de um dono
            $emails_admin = ['admin@rockettcg.com', 'dono@rockettcg.com'];
            $tipo = in_array($email, $emails_admin) ? 'admin' : $user['tipo'];
            
            if ($tipo === 'admin' && $user['tipo'] !== 'admin') {
                $pdo->prepare("UPDATE usuarios SET tipo = 'admin' WHERE id = ?")->execute([$user['id']]);
            }

            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_tipo'] = $tipo;
            header("Location: index.php");
            exit;
        } else {
            $erro = "E-mail ou senha incorretos!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RocketTCG - Login</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <!-- Ionicons for icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

    <div class="background-elements">
        <div class="circle circle-1"></div>
        <div class="circle circle-2"></div>
        <div class="circle circle-3"></div>
    </div>

    <div class="container" id="container">
        
        <?php if($erro): ?>
            <div style="position:absolute; top:20px; left:50%; transform:translateX(-50%); background:#ff0055; color:white; padding:10px 20px; border-radius:8px; z-index:1000; font-weight:600;">
                <?php echo $erro; ?>
            </div>
        <?php endif; ?>

        <!-- Painel de Cadastro (Sign Up) -->
        <div class="form-container sign-up-container">
            <form action="login.php" method="POST">
                <input type="hidden" name="action" value="register">
                <h1>Criar Conta</h1>
                <p class="subtitle">Junte-se à maior comunidade de TCG</p>
                
                <div class="input-group">
                    <ion-icon name="person-outline"></ion-icon>
                    <input type="text" name="nome" placeholder="Nome Completo" required />
                </div>

                <div class="input-group">
                    <ion-icon name="card-outline"></ion-icon>
                    <input type="text" name="cpf" placeholder="CPF" required />
                </div>
                
                <div class="input-group">
                    <ion-icon name="call-outline"></ion-icon>
                    <input type="text" name="celular" placeholder="Celular" required />
                </div>

                <div class="input-group">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="email" name="email" placeholder="E-mail" required />
                </div>
                
                <div class="input-group">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="senha" placeholder="Senha" required />
                </div>


                <button type="submit" class="btn primary-btn">Registrar</button>
            </form>
        </div>

        <!-- Painel de Login (Sign In) -->
        <div class="form-container sign-in-container">
            <form action="login.php" method="POST">
                <input type="hidden" name="action" value="login">
                <a href="index.php" class="brand-logo" style="text-decoration: none;">
                    <img src="assets/Rocket_foto_de_perfil_png.png" alt="RocketTCG" style="height: 50px; width: auto; object-fit: contain;">
                </a>
                <h1>Bem-vindo de volta</h1>
                <p class="subtitle">Acesse sua coleção e batalhas</p>
                
                <div class="input-group">
                    <ion-icon name="mail-outline"></ion-icon>
                    <input type="email" name="email" placeholder="E-mail" required />
                </div>
                
                <div class="input-group">
                    <ion-icon name="lock-closed-outline"></ion-icon>
                    <input type="password" name="senha" placeholder="Senha" required />
                </div>
                
                <a href="#" class="forgot-password">Esqueceu sua senha?</a>
                <button type="submit" class="btn primary-btn">Entrar</button>
            </form>
        </div>

        <!-- Painel de Sobreposição (Overlay) para Efeito Visual -->
        <div class="overlay-container">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <h2>Já possui uma conta?</h2>
                    <p>Faça login para acessar sua coleção e continuar suas negociações.</p>
                    <button class="btn ghost-btn" id="signIn">Fazer Login</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <h2>Novo por aqui?</h2>
                    <p>Crie sua conta agora e mergulhe no melhor mercado de TCG.</p>
                    <button class="btn ghost-btn" id="signUp">Criar Conta</button>
                </div>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>
