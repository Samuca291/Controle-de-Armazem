<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>GCV - Gestão de Estoque</title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <link rel="stylesheet" href="views/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <style>
    :root {
      --primary-color: #1a237e;
      --secondary-color: #0d47a1;
      --accent-color: #3498db;
      --text-color: #2c3e50;
      --light-gray: #ecf0f1;
      --white: #ffffff;
    }

    body {
      margin: 0;
      padding: 0;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
      font-family: 'Segoe UI', sans-serif;
      position: relative;
      overflow: hidden;
    }

    /* Efeito de partículas no fundo */
    .particles {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
    }

    .particle {
      position: absolute;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      animation: float 8s infinite ease-in-out;
    }

    .login-container {
      width: 100%;
      max-width: 400px;
      margin: 20px;
      background: rgba(255, 255, 255, 0.95);
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
      backdrop-filter: blur(10px);
      transform-style: preserve-3d;
      transform: perspective(1000px) rotateX(0deg);
      transition: all 0.5s ease;
      position: relative;
      z-index: 1;
    }

    .login-container:hover {
      transform: perspective(1000px) rotateX(2deg) translateY(-5px);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .login-header {
      padding: 30px 20px;
      text-align: center;
      position: relative;
    }

    .login-header::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 50%;
      transform: translateX(-50%);
      width: 60%;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--accent-color), transparent);
    }

    .login-header p {
      margin: 0;
      margin-top: 12px;
      color: var(--text-color);
      font-size: 1.1em;
      letter-spacing: 1px;
      padding: 0;
      line-height: 1;
    }

    .logo-text {
      font-size: 2.5em;
      font-weight: 700;
      color: var(--primary-color);
      text-transform: uppercase;
      letter-spacing: 2px;
      margin: 0;
      padding: 0;
      line-height: 1;
      position: relative;
      display: inline-block;
    }

    .logo-text::before {
      content: '';
      position: absolute;
      width: 100%;
      height: 4px;
      bottom: -8px;
      left: 0;
      background: var(--accent-color);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }

    .login-container:hover .logo-text::before {
      transform: scaleX(1);
    }

    .form-container {
      padding: 30px;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    .input-group {
      position: relative;
      margin-bottom: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .input-group input {
      width: 100%;
      padding: 12px 40px;
      border: 2px solid var(--light-gray);
      border-radius: 10px;
      font-size: 16px;
      transition: all 0.3s ease;
      background: var(--white);
      text-align: center;
    }

    .input-group i {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-color);
      transition: all 0.3s ease;
    }

    .input-group input:focus {
      border-color: var(--accent-color);
      box-shadow: 0 0 15px rgba(52, 152, 219, 0.1);
      transform: translateY(-2px);
    }

    .input-group input:focus + i {
      color: var(--accent-color);
    }

    .input-group input::placeholder {
      text-align: center;
      color: #999;
    }

    .input-group input {
      text-align: center;
      padding-left: 40px;
      padding-right: 40px;
    }

    .remember-me {
      display: flex;
      align-items: center;
      margin: 20px 0;
    }

    .remember-me input[type="checkbox"] {
      margin-right: 10px;
      position: relative;
      width: 18px;
      height: 18px;
      cursor: pointer;
    }

    .login-button {
      width: 100%;
      padding: 12px;
      background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
      border: none;
      border-radius: 10px;
      color: var(--white);
      font-size: 16px;
      font-weight: 600;
      letter-spacing: 1px;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .login-button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
      transition: 0.5s;
    }

    .login-button:hover::before {
      left: 100%;
    }

    .login-button:hover {
      transform: translateY(-3px);
      box-shadow: 0 10px 20px rgba(0,0,0,0.2);
    }

    .links {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
      padding-top: 20px;
      border-top: 1px solid var(--light-gray);
    }

    .links a {
      color: var(--text-color);
      text-decoration: none;
      font-size: 14px;
      transition: all 0.3s ease;
      position: relative;
    }

    .links a::after {
      content: '';
      position: absolute;
      width: 100%;
      height: 2px;
      bottom: -4px;
      left: 0;
      background: var(--accent-color);
      transform: scaleX(0);
      transition: transform 0.3s ease;
    }

    .links a:hover::after {
      transform: scaleX(1);
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) rotate(0deg); }
      50% { transform: translateY(-20px) rotate(180deg); }
    }

    @media (max-width: 480px) {
      .login-container {
        margin: 10px;
        padding: 20px;
      }

      .logo-text {
        font-size: 2em;
      }
    }

    .toggle-password {
        position: absolute;
        right: 15px !important;
        left: auto !important;
        cursor: pointer;
        transition: all 0.3s ease;
        padding: 10px;
        z-index: 2;
    }

    .toggle-password:hover {
        color: var(--accent-color);
        transform: translateY(-50%) scale(1.1);
    }

    /* Estilos para o botão de informações e modal */
    .info-icon {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        background: var(--accent-color);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .info-icon:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(52, 152, 219, 0.4);
    }

    .features-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100vh;
        background-color: rgba(0, 0, 0, 0.85);
        z-index: 9999;
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
        overflow-y: auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .features-modal.active {
        opacity: 1;
        visibility: visible;
        display: flex !important;
    }

    .modal-content {
        width: 90%;
        max-width: 1200px;
        max-height: 90vh;
        overflow-y: auto;
        background: linear-gradient(135deg, #1a237e, #0d47a1);
        border-radius: 20px;
        padding: 40px;
        color: white;
        position: relative;
        opacity: 0;
        transform: translateY(20px);
        transition: all 0.3s ease;
        margin: 20px;
    }

    .features-modal.active .modal-content {
        opacity: 1;
        transform: translateY(0);
    }

    .close-modal {
        position: absolute;
        top: 20px;
        right: 20px;
        color: white;
        font-size: 24px;
        cursor: pointer;
        z-index: 1;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transition: all 0.3s ease;
    }

    .close-modal:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: rotate(90deg);
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
        margin-top: 40px;
    }

    .feature-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 15px;
        padding: 25px;
        transition: transform 0.3s ease;
    }

    .feature-card:hover {
        transform: translateY(-10px);
        background: rgba(255, 255, 255, 0.15);
    }

    .feature-icon {
        font-size: 40px;
        margin-bottom: 20px;
        color: var(--accent-color);
    }

    .feature-title {
        font-size: 24px;
        font-weight: 600;
        margin-bottom: 15px;
    }

    .feature-description {
        font-size: 16px;
        line-height: 1.6;
        opacity: 0.9;
    }

    .stats-container {
        display: flex;
        justify-content: space-around;
        margin: 40px 0;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 48px;
        font-weight: 700;
        color: var(--accent-color);
    }

    .stat-label {
        font-size: 16px;
        opacity: 0.9;
    }

    @media (max-width: 768px) {
        .modal-content {
            padding: 20px;
            margin: 20px;
        }
        
        .stats-container {
            flex-direction: column;
            gap: 20px;
        }

        .modal-content {
            margin: 15px auto;
            padding: 20px;
        }

        .features-modal {
            padding: 0 10px; /* Adiciona padding lateral no container */
        }

        .features-grid {
            grid-template-columns: 1fr; /* Uma coluna em telas pequenas */
        }
    }

    /* Estilização da barra de rolagem */
    .features-modal::-webkit-scrollbar {
        width: 8px;
    }

    .features-modal::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    .features-modal::-webkit-scrollbar-thumb {
        background: var(--accent-color);
        border-radius: 4px;
    }

    .features-modal::-webkit-scrollbar-thumb:hover {
        background: #2980b9;
    }

    /* Ajuste para scroll interno do conteúdo */
    .modal-content::-webkit-scrollbar {
        width: 8px;
    }

    .modal-content::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    .modal-content::-webkit-scrollbar-thumb {
        background: var(--accent-color);
        border-radius: 4px;
    }

    @media (max-width: 768px) {
        .modal-content {
            margin: 10px;
            padding: 20px;
            max-height: 85vh;
        }
    }

    /* Melhorias na responsividade */
    @media (max-width: 1200px) {
        .login-container {
            max-width: 380px;
        }
    }

    @media (max-width: 992px) {
        .login-container {
            max-width: 360px;
        }
    }

    @media (max-width: 768px) {
        .login-container {
            max-width: 340px;
            margin: 15px;
        }

        .logo-text {
            font-size: 2.2em;
        }

        .input-group input {
            padding: 10px 35px;
            font-size: 15px;
        }
    }

    @media (max-width: 576px) {
        body {
            padding: 10px;
        }

        .login-container {
            max-width: 100%;
            margin: 10px;
            padding: 15px;
        }

        .logo-text {
            font-size: 2em;
        }

        .login-header {
            padding: 20px 15px;
        }

        .form-container {
            padding: 20px 15px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group input {
            padding: 10px 30px;
            font-size: 14px;
        }

        .remember-me {
            margin: 15px 0;
        }

        .login-button {
            padding: 10px;
            font-size: 15px;
        }

        .links {
            margin-top: 15px;
            padding-top: 15px;
        }

        .links a {
            font-size: 13px;
        }
    }

    @media (max-width: 380px) {
        .login-container {
            margin: 5px;
            padding: 10px;
        }

        .logo-text {
            font-size: 1.8em;
        }

        .input-group {
            margin-bottom: 15px;
        }
    }

    /* Orientação Paisagem em Dispositivos Móveis */
    @media (max-height: 500px) and (orientation: landscape) {
        body {
            align-items: flex-start;
            padding: 10px 0;
        }

        .login-container {
            margin: 5px auto;
            max-height: 95vh;
            overflow-y: auto;
        }

        .login-header {
            padding: 15px 20px;
        }

        .form-container {
            padding: 15px 20px;
        }
    }

    /* Suporte para Telas Muito Grandes */
    @media (min-width: 1600px) {
        .login-container {
            max-width: 450px;
            transform: scale(1.1);
        }

        .logo-text {
            font-size: 2.8em;
        }

        .input-group input {
            padding: 15px 45px;
            font-size: 18px;
        }
    }

    /* Melhorias na Acessibilidade */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation: none !important;
            transition: none !important;
        }
    }

    /* Suporte para Dark Mode */
    @media (prefers-color-scheme: dark) {
        .login-container {
            background: rgba(255, 255, 255, 0.90);
        }

        .input-group input {
            background: rgba(255, 255, 255, 0.95);
        }
    }

    /* Ajustes para Dispositivos de Alta Resolução */
    @media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
        .login-container {
            backdrop-filter: blur(15px);
        }
    }
  </style>
</head>
<body>
  <div class="particles" id="particles"></div>
  
  <div class="login-container">
    <div class="login-header">
      <h1 class="logo-text">GESTÃO</h1>
      <p>DE ESTOQUE</p>
    </div>
    
    <div class="form-container">
      <form action="App/session.php" method="post">
        <div class="input-group">
          <i class="fa fa-user"></i>
          <input type="text" name="username" placeholder="Usuário" required>
        </div>
        
        <div class="input-group">
          <i class="fa fa-lock"></i>
          <input type="password" name="password" id="password" placeholder="Senha" required>
          <i class="fa fa-eye toggle-password" id="togglePassword" style="right: 15px; left: auto; cursor: pointer;"></i>
        </div>
        
        <div class="remember-me">
          <input type="checkbox" id="remember" name="remember">
          <label for="remember">Lembrar-me</label>
        </div>
        
        <button type="submit" class="login-button">
          Entrar
        </button>
        
        <div class="links">
          <a href="#">Esqueci minha senha</a>
          <a href="#">Registrar-se</a>
        </div>
      </form>
    </div>
  </div>

  <div class="info-icon" id="infoButton">
    <i class="fa fa-lightbulb-o"></i>
  </div>

  <div class="features-modal" id="featuresModal">
    <div class="modal-content">
        <div class="close-modal" id="closeModal">
            <i class="fa fa-times"></i>
        </div>

        <h1>Sistema de Gestão de Estoque</h1>
        <p>Uma solução completa para o controle eficiente do seu negócio</p>

        <div class="stats-container">
            <div class="stat-item">
                <div class="stat-number">99%</                <div class="stat-label">Precisão no Controle</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">50%</                <div class="stat-label">Redução de Custos</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">2x</                <div class="stat-label">Mais Produtividade</div>
            </div>
        </div>

        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa fa-dashboard"></i>
                </div>
                <div class="feature-title">Dashboard Inteligente</div>
                <div class="feature-description">
                    Visualize todos os indicadores importantes em tempo real, com gráficos interativos e análises detalhadas.
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa fa-barcode"></i>
                </div>
                <div class="feature-title">Controle de Estoque</div>
                <div class="feature-description">
                    Gerencie seu estoque com precisão, com alertas automáticos e previsão de demanda.
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa fa-line-chart"></i>
                </div>
                <div class="feature-title">Relatórios Avançados</div>
                <div class="feature-description">
                    Gere relatórios personalizados com insights valiosos para tomada de decisões.
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa fa-users"></i>
                </div>
                <div class="feature-title">Multiusuários</div>
                <div class="feature-description">
                    Controle de acesso por níveis e registro de todas as operações realizadas.
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa fa-mobile"></i>
                </div>
                <div class="feature-title">Acesso Mobile</div>
                <div class="feature-description">
                    Acesse suas informações de qualquer lugar, com interface responsiva.
                </div>
            </div>

            <div class="feature-card">
                <div class="feature-icon">
                    <i class="fa fa-cloud"></i>
                </div>
                <div class="feature-title">Backup Automático</div>
                <div class="feature-description">
                    Seus dados sempre seguros com backup automático na nuvem.
                </div>
            </div>
        </div>
    </div>
  </div>

  <script>
    // Criar partículas
    document.addEventListener('DOMContentLoaded', function() {
      const particles = document.getElementById('particles');
      for(let i = 0; i < 50; i++) {
        const particle = document.createElement('div');
        particle.className = 'particle';
        particle.style.width = Math.random() * 5 + 'px';
        particle.style.height = particle.style.width;
        particle.style.left = Math.random() * 100 + 'vw';
        particle.style.top = Math.random() * 100 + 'vh';
        particle.style.animationDelay = Math.random() * 5 + 's';
        particles.appendChild(particle);
      }
    });

    document.addEventListener('DOMContentLoaded', function() {
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('password');

        togglePassword.addEventListener('click', function() {
            // Alterna o tipo do input entre 'password' e 'text'
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Alterna o ícone entre 'eye' e 'eye-slash'
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    });

    // Adicione este código no final do script
    document.addEventListener('DOMContentLoaded', function() {
        const infoButton = document.getElementById('infoButton');
        const featuresModal = document.getElementById('featuresModal');
        const closeModal = document.getElementById('closeModal');
        const modalContent = document.querySelector('.modal-content');

        function openModal() {
            featuresModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
            // Força reflow antes de adicionar a classe active
            featuresModal.offsetHeight;
            
            requestAnimationFrame(() => {
                featuresModal.classList.add('active');
                animateFeatureCards();
            });
        }

        function closeModalHandler() {
            featuresModal.classList.remove('active');
            document.body.style.overflow = 'auto';
            setTimeout(() => {
                featuresModal.style.display = 'none';
            }, 300);
        }

        infoButton.addEventListener('click', openModal);

        closeModal.addEventListener('click', function(e) {
            e.stopPropagation();
            closeModalHandler();
        });

        featuresModal.addEventListener('click', function(e) {
            if (e.target === featuresModal) {
                closeModalHandler();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && featuresModal.classList.contains('active')) {
                closeModalHandler();
            }
        });

        // Função para animar os cards
        function animateFeatureCards() {
            const cards = document.querySelectorAll('.feature-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        }
    });

    // Adicione este código para melhor suporte mobile
    document.addEventListener('DOMContentLoaded', function() {
        // Previne zoom em inputs em dispositivos iOS
        const metaViewport = document.querySelector('meta[name=viewport]');
        metaViewport.setAttribute('content', 'width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0');

        // Ajusta altura em dispositivos móveis quando o teclado virtual aparece
        const originalHeight = window.innerHeight;
        window.addEventListener('resize', () => {
            if (window.innerHeight < originalHeight) {
                document.body.style.height = 'auto';
            } else {
                document.body.style.height = '100vh';
            }
        });

        // Fecha teclado virtual ao pressionar enter
        document.querySelectorAll('input').forEach(input => {
            input.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    input.blur();
                }
            });
        });
    });
  </script>
</body>
</html>



