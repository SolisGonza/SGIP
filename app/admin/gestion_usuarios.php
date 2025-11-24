<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/validar_sesion.php';
// ------------------------------
//  CONSULTAS REALES A LA BD
// ------------------------------
$totalUsuarios = $link->query("SELECT COUNT(*) AS t FROM usuario")->fetch_assoc()['t'];
$usuariosActivos = $link->query("SELECT COUNT(*) AS t FROM usuario WHERE estado = 1")->fetch_assoc()['t'];
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script src="/SGIP/js/sgip_global.js"></script>
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

                <button class="btn btn-primary" onclick="nuevoUsuario()">
                    <span style="font-size: 1.2rem; margin-right: 0.5rem;">+</span>
                    Nuevo Usuario
                </button>
            </div>
        </div>

        <!-- ESTADÍSTICAS -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalUsuarios; ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $usuariosActivos; ?></div>
                <div class="stat-label">Usuarios Activos</div>
            </div>
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
                <div class="table-info">Mostrando <span id="totalMostrados"><?php echo $totalUsuarios; ?></span> usuarios</div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <!-- <th>Fecha Registro</th> -->
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody id="tablaUsuarios">
                    <?php while ($u = $queryUsuarios->fetch_assoc()): ?>
                        <tr data-nombre="<?php echo htmlspecialchars(strtolower($u['nombre'])); ?>"
                            data-correo="<?php echo htmlspecialchars(strtolower($u['correo'])); ?>"
                            data-rol="<?php echo htmlspecialchars(strtolower($u['rol'])); ?>"
                            data-estado="<?php echo $u['estado']; ?>">
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


                            <td>
                                <span class="status-badge <?php echo $u['estado'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $u['estado'] ? 'activo' : 'inactivo'; ?>
                                </span>
                            </td>


                            <!-- <td><?php echo $u['fecha_registro']; ?></td> -->

                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" onclick="editarUsuario(<?php echo $u['id_usuario']; ?>)">
                                        ✏️ Editar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Función para nuevo usuario con SweetAlert
        function nuevoUsuario() {
            Swal.fire({
                title: 'Crear Nuevo Usuario',
                html: `
                    <form id="nuevoUsuarioForm">
                        <div class="form-group">
                            <label>Nombre completo:</label>
                            <input type="text" name="nombre" class="swal2-input" placeholder="Ingrese el nombre completo" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Correo electrónico:</label>
                            <input type="email" name="correo" class="swal2-input" placeholder="Ingrese el correo electrónico" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Contraseña:</label>
                            <input type="password" name="password" class="swal2-input" placeholder="Ingrese la contraseña" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label>Confirmar contraseña:</label>
                            <input type="password" name="confirm_password" class="swal2-input" placeholder="Confirme la contraseña" required minlength="6">
                        </div>
                        
                        <div class="form-group">
                            <label>Rol:</label>
                            <select name="rol" class="swal2-input" required>
                                <option value="usuario">Usuario</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Estado:</label>
                            <select name="estado" class="swal2-input" required>
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </form>
                `,
                showCancelButton: true,
                confirmButtonText: 'Crear Usuario',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                preConfirm: () => {
                    const form = document.getElementById('nuevoUsuarioForm');
                    const formData = new FormData(form);
                    const data = Object.fromEntries(formData);

                    // Validaciones
                    if (!data.nombre.trim()) {
                        Swal.showValidationMessage('El nombre es requerido');
                        return false;
                    }

                    if (!data.correo.trim()) {
                        Swal.showValidationMessage('El correo es requerido');
                        return false;
                    }

                    if (!data.correo.match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/)) {
                        Swal.showValidationMessage('El correo electrónico no es válido');
                        return false;
                    }

                    if (data.password.length < 6) {
                        Swal.showValidationMessage('La contraseña debe tener al menos 6 caracteres');
                        return false;
                    }

                    if (data.password !== data.confirm_password) {
                        Swal.showValidationMessage('Las contraseñas no coinciden');
                        return false;
                    }

                    return data;
                }
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    const userData = result.value;

                    // Enviar datos al servidor
                    ajaxRequest({
                        url: "procesar-nuevo-usuario.php",
                        data: JSON.stringify(userData),
                        successCallback: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Éxito!',
                                    text: response.message || 'Usuario creado correctamente',
                                    confirmButtonColor: '#3085d6'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Error al crear el usuario',
                                    confirmButtonColor: '#d33'
                                });
                            }
                        },
                        errorCallback: function(error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error de conexión: ' + error,
                                confirmButtonColor: '#d33'
                            });
                        }
                    });
                }
            });
        }

        // Función para búsqueda en tiempo real
        function inicializarBusqueda() {
            const searchInput = document.getElementById('searchInput');
            const tablaUsuarios = document.getElementById('tablaUsuarios');
            const filas = tablaUsuarios.getElementsByTagName('tr');
            const totalMostrados = document.getElementById('totalMostrados');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                let contador = 0;

                for (let fila of filas) {
                    const nombre = fila.getAttribute('data-nombre') || '';
                    const correo = fila.getAttribute('data-correo') || '';
                    const rol = fila.getAttribute('data-rol') || '';
                    const estado = fila.getAttribute('data-estado') || '';

                    const coincideNombre = nombre.includes(searchTerm);
                    const coincideCorreo = correo.includes(searchTerm);
                    const coincideRol = rol.includes(searchTerm);

                    // También buscar por estado (activo/inactivo)
                    const textoEstado = estado === '1' ? 'activo' : 'inactivo';
                    const coincideEstado = textoEstado.includes(searchTerm);

                    if (coincideNombre || coincideCorreo || coincideRol || coincideEstado || searchTerm === '') {
                        fila.style.display = '';
                        contador++;
                    } else {
                        fila.style.display = 'none';
                    }
                }

                totalMostrados.textContent = contador;
            });
        }

        // Inicializar búsqueda cuando el documento esté listo
        document.addEventListener('DOMContentLoaded', function() {
            inicializarBusqueda();
        });

        function editarUsuario(idUsuario) {
            // Primero pedimos los datos reales del usuario
            ajaxRequest({
                url: "obtener-usuario.php",
                data: idUsuario,

                successCallback: function(response) {
                    if (response.status !== "success") {
                        Swal.fire("Error", "No se pudieron obtener los datos del usuario.", "error");
                        return;
                    }

                    const u = response.data;

                    // Abrimos el SweetAlert con los datos precargados
                    Swal.fire({
                        title: 'Editar Usuario',
                        html: `
                    <form id="editarUsuarioForm">
                        <input type="hidden" name="id_usuario" value="${idUsuario}">
                        
                        <div class="form-group">
                            <label>Nombre:</label>
                            <input type="text" id="nombre" name="nombre" class="swal2-input" value="${u.nombre}" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Correo:</label>
                            <input type="email" id="correo" name="correo" class="swal2-input" value="${u.correo}" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Rol:</label>
                            <select id="rol" name="rol" class="swal2-input" required>
                                <option value="usuario" ${u.rol === 'usuario' ? 'selected' : ''}>Usuario</option>
                                <option value="admin" ${u.rol === 'admin' ? 'selected' : ''}>Administrador</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Estado:</label>
                            <select id="estado" name="estado" class="swal2-input" required>
                                <option value="1" ${u.estado == 1 ? 'selected' : ''}>Activo</option>
                                <option value="0" ${u.estado == 0 ? 'selected' : ''}>Inactivo</option>
                            </select>
                        </div>
                    </form>
                `,
                        showCancelButton: true,
                        confirmButtonText: 'Guardar Cambios',
                        cancelButtonText: 'Cancelar',

                        preConfirm: () => {
                            const form = document.getElementById('editarUsuarioForm');
                            return new FormData(form);
                        }

                    }).then((result) => {

                        if (!result.isConfirmed) return;
                        const formData = result.value;

                        // Guardar cambios usando tu propia estructura AJAX
                        ajaxRequest({
                            url: "procesar-editar-usuario.php",
                            data: JSON.stringify(Object.fromEntries(formData)),
                            successCallback: function(resp) {
                                if (resp.success === true) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Actualizado",
                                        text: resp.message
                                    }).then(() => location.reload());
                                } else {
                                    Swal.fire("Error", resp.message || "No se pudo actualizar", "error");
                                }
                            },

                            errorCallback: function(err) {
                                Swal.fire("Error", err, "error");
                            }
                        });
                    });
                },

                errorCallback: function() {
                    Swal.fire("Error", "No se pudo obtener la información del usuario.", "error");
                }
            });
        }

        // Estilos para el formulario dentro del SweetAlert
        const style = document.createElement('style');
        style.textContent = `
        .form-group {
            margin-bottom: 1rem;
            text-align: left;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #555;
        }
        .swal2-input, .swal2-select {
            width: 100% !important;
            margin: 0.5rem 0 !important;
        }
    `;
        document.head.appendChild(style);
    </script>

</body>

</html>