<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/validar_sesion.php';

// CONSULTAS REALES A LA BD
$totalUsuarios   = $link->query("SELECT COUNT(*) AS total FROM usuario")->fetch_assoc()['total'];
$totalArticulos  = $link->query("SELECT COUNT(*) AS total FROM articulo")->fetch_assoc()['total'];
$totalCategorias = $link->query("SELECT COUNT(*) AS total FROM categoria")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<meta content="charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<head>
    <meta charset="utf-8">
    <title>Panel de Administración - SGIP</title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/res/common.php'; ?>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <header class="header">
        <nav class="nav">
            <div class="nav-brand">
                <a href="index.php" style="text-decoration: none; color: inherit;">
                    <h1>SG<span class="brand-accent">IP</span></h1>
                </a>
            </div>

            <div class="nav-home">
                <a href="/SGIP/app/admin/index.php" class="home-link" title="Ir al Inicio">
                    <span class="home-icon">🏠</span>
                </a>
            </div>

            <div class="nav-actions">
                <a href="../../logout.php" class="btn btn-outline">Cerrar Sesión</a>
            </div>
        </nav>
    </header>
    <main class="admin-dashboard">
        <div class="admin-container">
            <div class="admin-header">
                <h1>Panel de Administración</h1>
                <p>Gestiona y supervisa el sistema SGIP</p>
            </div>

            <div class="admin-cards">
                <div class="admin-card">
                    <div class="admin-icon">👥</div>
                    <h3>Administrar Usuarios</h3>
                    <p>Gestiona los usuarios del sistema, crea, edita o desactiva cuentas de usuario.</p>
                    <a href="gestion_usuarios.php" class="btn btn-primary">Gestionar Usuarios</a>
                </div>

                <!-- <div class="admin-card">
                    <div class="admin-icon">📊</div>
                    <h3>Estadísticas del Sistema</h3>
                    <p>Visualiza reportes y métricas generales del uso de la plataforma.</p>
                    <button class="btn btn-secondary" onclick="mostrarEstadisticas()">Ver Estadísticas</button>
                </div>-->
            </div>

            <!-- Estadísticas rápidas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" id="totalUsuarios">0</div>
                    <div class="stat-label">Usuarios Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="totalArticulos">0</div>
                    <div class="stat-label">Artículos Registrados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" id="categorias">0</div>
                    <div class="stat-label">Categorías</div>
                </div>
            </div>

            <!-- Información del usuario admin -->
            <div class="user-info">
                <h4>Tu Información de Administrador</h4>
                <div class="user-details">
                    <div class="user-detail">
                        <div class="label">Nombre</div>
                        <div class="value"><?php echo $_SESSION['nombre']; ?></div>
                    </div>
                    <div class="user-detail">
                        <div class="label">Correo</div>
                        <div class="value"><?php echo $_SESSION['correo']; ?></div>
                    </div>
                    <div class="user-detail">
                        <div class="label">Rol</div>
                        <div class="value"><?php echo ucfirst($_SESSION['rol']); ?></div>
                    </div>
                    <div class="user-detail">
                        <div class="label">ID Usuario</div>
                        <div class="value">#<?php echo $_SESSION['id_usuario']; ?></div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Simular carga de estadísticas
        function cargarEstadisticas() {
            setTimeout(() => {
                document.getElementById('totalUsuarios').textContent = <?php echo json_encode($totalUsuarios); ?>;
                document.getElementById('totalArticulos').textContent = <?php echo json_encode($totalArticulos); ?>;
                document.getElementById('categorias').textContent = <?php echo json_encode($totalCategorias); ?>;
            }, 1000);
        }


        function mostrarEstadisticas() {
            Swal.fire({
                icon: 'info',
                title: 'Estadísticas',
                text: 'Funcionalidad de estadísticas - Próximamente'
            });
        }

        function mostrarConfiguracion() {
            Swal.fire({
                icon: 'info',
                title: 'Configuración',
                text: 'Funcionalidad de configuración - Próximamente'
            });
        }


        // Cargar estadísticas al iniciar
        document.addEventListener('DOMContentLoaded', cargarEstadisticas);
    </script>
</body>

</html>