<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/validar_sesion.php';
// ------------------------------
//  CONSULTAS REALES A LA BD
// ------------------------------
$totalUsuarios = $link->query("SELECT COUNT(*) AS t FROM usuario")->fetch_assoc()['t'];
/* $usuariosActivos = $link->query("SELECT COUNT(*) AS t FROM usuario WHERE estado = 1")->fetch_assoc()['t']; */
$usuariosAdmin = $link->query("SELECT COUNT(*) AS t FROM usuario WHERE rol = 'admin'")->fetch_assoc()['t'];
/* $nuevosHoy = $link->query("SELECT COUNT(*) AS t FROM usuario WHERE DATE(fecha_registro) = CURDATE()")->fetch_assoc()['t']; */

// OBTENER LISTA REAL DE USUARIOS
$queryUsuarios = $link->query("SELECT * FROM usuario ORDER BY id_usuario DESC");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Gestión de Usuarios - SGIP Admin</title>
    <link rel="stylesheet" href="../../css/style.css">
</head>

<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/res/header.php'; ?>

    <main class="admin-container-v2">

        <!-- TÍTULO -->
        <div class="admin-header-v2">
            <div class="admin-title-v2">
                <h1>Gestión de Usuarios</h1>
                <p>Administra y gestiona todos los usuarios del sistema SGIP</p>
            </div>

            <div class="table-actions">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" placeholder="Buscar usuarios..." id="searchInput">
                </div>

                <a href="nuevo-usuario.php" class="btn btn-primary">
                    <span style="font-size: 1.2rem; margin-right: 0.5rem;">+</span>
                    Nuevo Usuario
                </a>
            </div>
        </div>

        <!-- ESTADÍSTICAS -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsuarios; ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <!-- <div class="stat-card">
                <div class="stat-number"><?php echo $usuariosActivos; ?></div>
                <div class="stat-label">Usuarios Activos</div>
            </div> -->
            <div class="stat-card">
                <div class="stat-number"><?php echo $usuariosAdmin; ?></div>
                <div class="stat-label">Administradores</div>
            </div>
            <!-- <div class="stat-card">
                <div class="stat-number"><?php echo $nuevosHoy; ?></div>
                <div class="stat-label">Nuevos Hoy</div>
            </div> -->
        </div>

        <!-- TABLA DE USUARIOS -->
        <div class="users-table-container">
            <div class="table-header">
                <h3>Lista de Usuarios</h3>
                <div class="table-info">Mostrando <?php echo $totalUsuarios; ?> usuarios</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <!-- <th>Estado</th>
                        <th>Fecha Registro</th> -->
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php while ($u = $queryUsuarios->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <div style="display:flex;align-items:center;gap:1rem;">
                                    <div class="user-avatar">
                                        <?php
                                        $ini = strtoupper($u['nombre'][0] . (explode(' ', $u['nombre'])[1][0] ?? ''));
                                        echo $ini;
                                        ?>
                                    </div>
                                    <div>
                                        <strong><?php echo $u['nombre']; ?></strong>
                                        <div style="font-size:0.8rem;color:gray;">ID: <?php echo $u['id_usuario']; ?></div>
                                    </div>
                                </div>
                            </td>

                            <td><?php echo $u['correo']; ?></td>

                            <td>
                                <span class="role-badge <?php echo $u['rol'] == 'admin' ? 'role-admin' : 'role-user'; ?>">
                                    <?php echo $u['rol']; ?>
                                </span>
                            </td>

                            <!--
                            <td>
                                <span class="status-badge <?php echo $u['estado'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $u['estado'] ? 'activo' : 'inactivo'; ?>
                                </span>
                            </td>
                            -->

                            <!-- <td><?php echo $u['fecha_registro']; ?></td> -->

                            <td>
                                <div class="action-buttons">
                                    <a class="btn-action btn-edit" href="editar-usuario.php?id=<?php echo $u['id_usuario']; ?>">✏️ Editar</a>

                                    <!-- <a class="btn-action btn-toggle"
                                        href="toggle-usuario.php?id=<?php echo $u['id_usuario']; ?>"
                                        onclick="return confirm('¿Cambiar estado del usuario?')">
                                        <?php echo $u['estado'] ? '⏸️ Desactivar' : '▶️ Activar'; ?>
                                    </a> -->

                                    <a class="btn-action btn-delete"
                                        href="eliminar-usuario.php?id=<?php echo $u['id_usuario']; ?>"
                                        onclick="return confirm('¿Eliminar usuario permanentemente?')">
                                        🗑️ Eliminar
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>

</html>