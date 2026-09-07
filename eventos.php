<?php
require_once 'config.php';

$stmt = $pdo->query("SELECT * FROM eventos ORDER BY data_evento ASC");
$eventos = $stmt->fetchAll();

$presencas = [];
if (isset($_SESSION['usuario_id'])) {
    $stmtP = $pdo->prepare("SELECT evento_id FROM eventos_presencas WHERE usuario_id = ?");
    $stmtP->execute([$_SESSION['usuario_id']]);
    $presencas = $stmtP->fetchAll(PDO::FETCH_COLUMN, 0);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventos e Torneios - RocketTCG</title>
    <link rel="stylesheet" href="home.css?v=<?php echo time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        .eventos-container { max-width: 1200px; margin: 40px auto; padding: 20px; }
        .eventos-header { text-align: center; margin-bottom: 40px; }
        .eventos-header h1 { font-size: 2.5rem; margin-bottom: 10px; }
        .eventos-header p { color: var(--text-muted); font-size: 1.1rem; }
        
        .evento-card { background: var(--card-bg); border-radius: 15px; overflow: hidden; border: 1px solid var(--glass-border); display: flex; flex-direction: column; md:flex-row; margin-bottom: 30px; transition: 0.3s; }
        .evento-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.5); }
        .evento-banner { width: 100%; height: 300px; object-fit: cover; }
        @media(min-width: 768px) {
            .evento-card { flex-direction: row; }
            .evento-banner { width: 40%; height: auto; }
            .evento-content { width: 60%; }
        }
        .evento-content { padding: 30px; display: flex; flex-direction: column; justify-content: center; }
        .evento-title { font-size: 2rem; margin-bottom: 10px; color: var(--accent-color); }
        .evento-date { display: inline-flex; align-items: center; gap: 5px; color: #aaa; margin-bottom: 20px; font-weight: 600; }
        .evento-desc { line-height: 1.6; margin-bottom: 25px; color: #ddd; }
        
        .evento-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .info-item { background: rgba(0,0,0,0.3); padding: 15px; border-radius: 10px; border: 1px solid var(--glass-border); text-align: center; }
        .info-item span { display: block; font-size: 0.9rem; color: var(--text-muted); margin-bottom: 5px; }
        .info-item strong { font-size: 1.2rem; color: #fff; }
        
        .btn-presenca { background: var(--primary-color); color: #fff; border: none; padding: 12px 25px; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; gap: 8px; text-decoration: none; }
        .btn-presenca:hover { background: var(--primary-hover); }
        .btn-presenca.marcado { background: #4caf50; cursor: default; }
    </style>
</head>
<body>

    <!-- Navbar simplificada -->
    <header class="navbar">
        <a href="index.php" class="logo" style="text-decoration:none;">
            <img src="assets/Rocket_foto_de_perfil_png.png" alt="RocketTCG" style="height: 50px; width: auto; object-fit: contain;">
        </a>
        <nav class="nav-links">
            <a href="index.php"><ion-icon name="home-outline"></ion-icon> Início</a>
            <a href="eventos.php" style="color: var(--accent-color);"><ion-icon name="calendar-outline"></ion-icon> Eventos</a>
        </nav>
        <div class="nav-actions">
            <?php if(isset($_SESSION['usuario_id'])): ?>
                <a href="logout.php" class="login-link"><ion-icon name="log-out-outline"></ion-icon> Sair</a>
            <?php else: ?>
                <a href="login.php" class="login-link"><ion-icon name="person-circle-outline"></ion-icon> Login</a>
            <?php endif; ?>
        </div>
    </header>

    <main class="eventos-container">
        <div class="eventos-header">
            <h1>Torneios e Eventos</h1>
            <p>Participe dos nossos torneios e prove que você é o melhor duelista!</p>
        </div>

        <?php if(empty($eventos)): ?>
            <div style="text-align: center; padding: 50px; background: var(--card-bg); border-radius: 15px; border: 1px solid var(--glass-border);">
                <h2>Nenhum evento no momento.</h2>
                <p style="color: var(--text-muted);">Fique de olho, em breve teremos novos torneios!</p>
            </div>
        <?php else: ?>
            <?php foreach($eventos as $evento): 
                $ja_marcado = in_array($evento['id'], $presencas);
            ?>
                <div class="evento-card">
                    <img src="<?php echo htmlspecialchars($evento['banner']); ?>" alt="Banner" class="evento-banner" onerror="this.src='https://images.unsplash.com/photo-1511512578047-dfb367046420?q=80&w=1000&auto=format&fit=crop'">
                    <div class="evento-content">
                        <h2 class="evento-title"><?php echo htmlspecialchars($evento['titulo']); ?></h2>
                        <div class="evento-date">
                            <ion-icon name="time-outline"></ion-icon> 
                            <?php echo date('d/m/Y \à\s H:i', strtotime($evento['data_evento'])); ?>
                        </div>
                        <div class="evento-desc">
                            <?php echo nl2br(htmlspecialchars($evento['descricao'])); ?>
                        </div>
                        
                        <div class="evento-info-grid">
                            <div class="info-item">
                                <span>Ingresso</span>
                                <strong>R$ <?php echo number_format($evento['preco_ingresso'], 2, ',', '.'); ?></strong>
                            </div>
                            <div class="info-item">
                                <span>Premiação</span>
                                <strong>R$ <?php echo number_format($evento['valor_premio'], 2, ',', '.'); ?></strong>
                            </div>
                        </div>

                        <div>
                            <?php if(!isset($_SESSION['usuario_id'])): ?>
                                <a href="login.php" class="btn-presenca">Faça login para marcar presença</a>
                            <?php elseif($ja_marcado): ?>
                                <button class="btn-presenca marcado" disabled><ion-icon name="checkmark-circle-outline"></ion-icon> Presença Confirmada</button>
                            <?php else: ?>
                                <form action="evento_action.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="evento_id" value="<?php echo $evento['id']; ?>">
                                    <button type="submit" class="btn-presenca"><ion-icon name="hand-right-outline"></ion-icon> Marcar Presença</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
    
    <?php include 'footer.php'; ?>
</body>
</html>
