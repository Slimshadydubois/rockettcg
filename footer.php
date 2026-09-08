    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-col">
                <h3>Atendimento</h3>
                <ul>
                    <li><a href="contato.php">Fale Conosco</a></li>
                    <li><a href="#">Perguntas Frequentes</a></li>
                    <li><a href="#">Busca Avançada</a></li>
                    <li><a href="#">Navegue pelo Site</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Contato</h3>
                <ul class="contact-info">
                    <li><ion-icon name="location-outline"></ion-icon> <span>Endereço Genérico, 000 - Bairro<br> (Loja Exemplo)<br> Cidade/UF</span></li>
                    <li><ion-icon name="call-outline"></ion-icon> <span>(00) 0000-0000</span></li>
                    <li><ion-icon name="logo-whatsapp"></ion-icon> <span>+55 51 8151-3325</span></li>
                    <li><ion-icon name="mail-outline"></ion-icon> <span>rockettcgg@gmail.com</span></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Funcionamento</h3>
                <ul class="contact-info">
                    <li><ion-icon name="time-outline"></ion-icon> <span>Segunda a Sexta: 09:00 - 20:00</span></li>
                    <li><ion-icon name="calendar-outline"></ion-icon> <span>Sábado: 09:00 - 20:00</span></li>
                </ul>
            </div>
            <div class="footer-col">
                <h3>Pagamento</h3>
                <div style="display: flex; gap: 15px; align-items: center; margin-top: 10px;">
                    <img src="assets/logo_mercado_pago.png" alt="Mercado Pago" style="height: 30px; border-radius: 5px;">
                    <img src="assets/logo_pix.png" alt="Pix" style="height: 30px; border-radius: 5px;">
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 RocketTCG. Todos os direitos reservados.</p>
        </div>
    </footer>

    <!-- Theme Toggle -->
    <button class="theme-toggle-btn" id="themeToggleBtn" title="Alternar Tema">
        <ion-icon name="moon-outline" id="themeIcon"></ion-icon>
    </button>

    <style>
    /* Dark Mode Overrides */
    [data-theme="dark"] {
        --bg-color: #0b0f19 !important;
        --secondary-color: #1a2332 !important;
        --text-main: #ffffff !important;
        --text-muted: #a0aab2 !important;
        --glass-bg: rgba(11, 15, 25, 0.85) !important;
        --glass-border: rgba(255, 255, 255, 0.1) !important;
        --card-bg: #151c28 !important;
        --input-bg: rgba(255, 255, 255, 0.05) !important;
    }

    .theme-toggle-btn {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: var(--primary-color);
        color: #fff;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
        display: flex;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        z-index: 9999;
        transition: 0.3s;
    }
    .theme-toggle-btn:hover {
        transform: scale(1.1);
        background: var(--primary-hover);
    }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const themeBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');
        
        let savedTheme = localStorage.getItem('rockettcg_theme');
        if (savedTheme === null) {
            savedTheme = 'dark';
        }

        if (savedTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeIcon.setAttribute('name', 'sunny-outline');
        } else {
            themeIcon.setAttribute('name', 'moon-outline');
        }

        themeBtn.addEventListener('click', () => {
            const currentTheme = document.documentElement.getAttribute('data-theme');
            if (currentTheme === 'dark') {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('rockettcg_theme', 'light');
                themeIcon.setAttribute('name', 'moon-outline');
            } else {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('rockettcg_theme', 'dark');
                themeIcon.setAttribute('name', 'sunny-outline');
            }
        });
    });
    </script>
