
<?php
    require_once 'global_functions.php';

    // Si el formulario fue enviado
    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        $name     = $_POST['name'];
        $email    = $_POST['email'];
        $password = $_POST['password'];

        if (registrarUsuario($name, $email, $password)) {
            header("Location: login.php");
        } else {
            echo "Error al registrar.";
        }
    }
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - SGIP | Sistema de Gestión de Inventario Personal</title>
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
                <a href="login.php" class="btn btn-outline">Iniciar Sesión</a>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="auth-main">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <div class="auth-icon">🚀</div>
                    <h1>Crear Cuenta</h1>
                    <p>Únete a SGIP y comienza a gestionar tu inventario</p>
                </div>

                <form class="auth-form" id="registerForm" method="POST">
                    <div class="form-group">
                        <label for="name" class="form-label">Nombre Completo</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            placeholder="Ingresa tu nombre completo"
                            required>
                        <div class="form-error" id="nameError"></div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Correo Electrónico</label>
                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-input"
                            placeholder="tu@correo.com"
                            required>
                        <div class="form-error" id="emailError"></div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Contraseña</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="Crea una contraseña segura"
                            required>
                        <div class="password-requirements">
                            <small>La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números</small>
                        </div>
                        <div class="form-error" id="passwordError"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        Crear Cuenta
                    </button>
                </form>
            </div>

        </div>
    </main>

    <script>
        document.getElementById('registerForm').addEventListener('submit', function(e) {

            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            let isValid = true;

            document.querySelectorAll('.form-error').forEach(error => error.textContent = '');

            if (name.length < 2) {
                document.getElementById('nameError').textContent = 'El nombre debe tener al menos 2 caracteres';
                isValid = false;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                document.getElementById('emailError').textContent = 'Ingresa un correo electrónico válido';
                isValid = false;
            }

            

            if (!isValid) {
                e.preventDefault(); // Solo bloquea si hay errores
            }
        });
    </script>
</body>