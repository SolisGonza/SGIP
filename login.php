<?php
    require_once 'global_functions.php';

    // Si el formulario fue enviado
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $email     = $_POST['email'];
        $password = $_POST['password'];

        if (validar_usuario( $email, $password)) {
            echo "no hay Error al registrar.";
        } else {
            echo "Error al registrar.";
        }
    }
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SGIP | Sistema de Gestión de Inventario Personal</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <a href="index.php" style="text-decoration: none; color: inherit;">
                    <h1>SG<span class="brand-accent">IP</span></h1>
                </a>
            </div>
            <div class="nav-actions">
                <a href="register.php" class="btn btn-outline">Crear Cuenta</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-icon">🔑</div>
                    <h1>Iniciar Sesión</h1>
                    <p>Accede a tu cuenta de SGIP</p>
                </div>

                <form class="auth-form" id="loginForm" method="POST">
                    <div class="form-group">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input" 
                            placeholder="tu@correo.com"
                            required
                        >
                        <div class="form-error" id="emailError"></div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="form-input" 
                            placeholder="Ingresa tu contraseña"
                            required
                        >
                        <div class="form-error" id="passwordError"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        Iniciar Sesión
                    </button>
                </form>

                <div class="auth-footer">
                    <p>¿No tienes una cuenta? <a href="register.php" class="link">Regístrate aquí</a></p>
                </div>
            </div>

            <!-- <div class="auth-features">
                <div class="test-account">
                    <h4>¿Eres Diego?</h4>
                    <p>Usa estas credenciales de prueba:</p>
                    <div class="test-credentials">
                        <p><strong>Email:</strong> demo@sgip.com</p>
                        <p><strong>Contraseña:</strong> Demo1234</p>
                    </div>
                </div>
            </div> -->
        </div>
    </main>

    <script>
        document.getElementById('loginForm').addEventListener('submit', function(e) {

            // Validaciones básicas
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            let isValid = true;

            // Limpiar errores anteriores
            document.querySelectorAll('.form-error').forEach(error => error.textContent = '');

            // Validar email
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').textContent = 'Ingresa un correo electrónico válido';
                isValid = false;
            }

            /* // Validar contraseña
            if (password.length < 6) {
                document.getElementById('passwordError').textContent = 'La contraseña debe tener al menos 6 caracteres';
                isValid = false;
            } */
        });
    </script>
</body>
