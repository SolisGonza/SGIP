<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGIP - Controla tu Inventario Personal</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <!-- Header -->
    <head class="header">
        <nav class="nav">
            <div class="nav-brand">
                <h1>S<span class="brand-accent">GIP</span></h1>
            </div>
            <div class="nav-actions">
                <button class="btn btn-outline" onclick="scrollToSection('features')">Cómo funciona</button>
            </div>
        </nav>
    </head>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    Controla tu <span class="text-gradient">Inventario Personal</span> de Manera Inteligente
                </h1>
                <p class="hero-description">
                    Organiza, gestiona y optimiza todos tus productos y pertenencias en un solo lugar.
                    Simplifica tu vida con nuestro sistema de inventario personalizado.
                </p>
                <div class="hero-actions">
                    <button class="btn btn-primary" onclick="scrollToSection('auth')">
                        Comenzar Ahora
                    </button>
                    <button class="btn btn-secondary" onclick="scrollToSection('features')">
                        Saber Más
                    </button>
                </div>
            </div>
            <div class="hero-visual">
                <div class="floating-cards">
                    <div class="card card-1">
                        <div class="card-icon">📱</div>
                        <h4>Acceso Móvil</h4>
                    </div>
                    <div class="card card-2">
                        <div class="card-icon">📊</div>
                        <h4>Reportes Detallados</h4>
                    </div>
                    <div class="card card-3">
                        <div class="card-icon">🔒</div>
                        <h4>Seguro</h4>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <div class="container">
            <h2 class="section-title">¿Por qué elegir SGIP?</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🎯</div>
                    <h3>Fácil de Usar</h3>
                    <p>Interfaz intuitiva diseñada para que cualquier persona pueda gestionar su inventario sin complicaciones.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⚡</div>
                    <h3>Rápido y Eficiente</h3>
                    <p>Gestiona tus productos en segundos con búsquedas instantáneas y operaciones optimizadas.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">📈</div>
                    <h3>Análisis en Tiempo Real</h3>
                    <p>Obtén insights valiosos sobre tu inventario con reportes y estadísticas actualizadas.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🔔</div>
                    <h3>Alertas Inteligentes</h3>
                    <p>Recibe notificaciones cuando tus productos estén por agotarse o necesiten atención.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌐</div>
                    <h3>Acceso desde Cualquier Lugar</h3>
                    <p>Gestiona tu inventario desde tu computadora, tablet o smartphone.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🛡️</div>
                    <h3>Seguridad Garantizada</h3>
                    <p>Tus datos están protegidos con los más altos estándares de seguridad.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Auth Section -->
    <section id="auth" class="auth-section">
        <div class="container">
            <div class="auth-container">
                <div class="auth-header">
                    <h2>Únete a SGIP</h2>
                    <p>Comienza a gestionar tu inventario en minutos</p>
                </div>
                <div class="auth-options">
                    <div class="auth-card">
                        <div class="auth-icon">🚀</div>
                        <h3>Crear Cuenta</h3>
                        <p>Regístrate y comienza a organizar tu inventario personal</p>
                        <a href="register.php" class="btn btn-primary auth-btn">
                            Registrarse
                        </a>
                    </div>
                    <div class="auth-card">
                        <div class="auth-icon">🔑</div>
                        <h3>Acceder</h3>
                        <p>Ya tienes una cuenta? Inicia sesión aquí</p>
                        <a href="login.php" class="btn btn-outline auth-btn">
                            Iniciar Sesión
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h3>S<span class="brand-accent">GIP</span></h3>
                    <p>Gestiona tu inventario personal de manera inteligente</p>
                </div>
                <!-- <div class="footer-links">
                    <a href="#">Términos de Servicio</a>
                    <a href="#">Privacidad</a>
                    <a href="#">Contacto</a>
                </div> -->
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 SGIP. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <script>
        function scrollToSection(sectionId) {
            document.getElementById(sectionId).scrollIntoView({
                behavior: 'smooth'
            });
        }

        function showRegister() {
            alert('Funcionalidad de registro - Próximamente');
            // Aquí irá la lógica para mostrar el formulario de registro
        }

        function showLogin() {
            alert('Funcionalidad de login - Próximamente');
            // Aquí irá la lógica para mostrar el formulario de login
        }
    </script>
</body>

</html>