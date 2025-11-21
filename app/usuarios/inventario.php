<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/validar_sesion.php';

// 1. OBTENER ARTÍCULOS DEL USUARIO LOGUEADO
$idUsuario = $_SESSION['id_usuario'];

$query = "SELECT a.id_articulo, a.nombre, a.precio, a.estado, c.nombre AS categoriaNombre
            FROM articulo a
            LEFT JOIN categoria c ON c.id_articulo = a.id_categoria
            WHERE a.id_usuario = $idUsuario";

$result = mysqli_query($link, $query);

$articulos = [];

while ($row = mysqli_fetch_assoc($result)) {
    $articulos[] = $row;
}

// Convertir el array PHP a JSON para usarlo en JS
$jsonArticulos = json_encode($articulos);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta content="charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta charset="utf-8">
    <title>Mi Inventario - SGIP</title>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/res/common.php'; ?>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    </style>
</head>

<body>
    <?php require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/res/header.php'; ?>

    <!-- Main Content -->
    <main class="user-container">
        <div class="user-header">
            <div class="user-title">
                <h1>Mi Inventario Personal</h1>
                <p>Gestiona todos tus artículos y productos</p>
            </div>
            <div class="user-actions">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" class="search-input" placeholder="Buscar artículos..." id="searchInput">
                </div>
                <button class="btn btn-primary" onclick="agregarArticulo()">
                    <span style="font-size: 1.2rem; margin-right: 0.5rem;">+</span>
                    Agregar Artículo
                </button>
                <button class="btn btn-secondary" onclick="generarReporte()">
                    📊 Generar Reporte
                </button>
            </div>
        </div>

        <!-- Estadísticas -->
        <div class="stats-cards">
            <div class="stat-card">
                <div class="stat-number" id="totalArticulos">15</div>
                <div class="stat-label">Total Artículos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="valorTotal">$4,250</div>
                <div class="stat-label">Valor Total</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="articulosActivos">12</div>
                <div class="stat-label">Artículos Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="conFactura">8</div>
                <div class="stat-label">Con Factura</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filter-section">
            <div class="filter-grid">
                <div class="filter-group">
                    <label class="filter-label">Categoría</label>
                    <select class="filter-select" id="filterCategoria">
                        <option value="">Todas las categorías</option>
                        <option value="electronica">Electrónicos</option>
                        <option value="hogar">Hogar</option>
                        <option value="ropa">Ropa</option>
                        <option value="libros">Libros</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Estado</label>
                    <select class="filter-select" id="filterEstado">
                        <option value="">Todos los estados</option>
                        <option value="activo">Activo</option>
                        <option value="vendido">Vendido</option>
                        <option value="donado">Donado</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label">Precio Mínimo</label>
                    <input type="number" class="filter-input" id="filterPrecioMin" placeholder="$0">
                </div>
                <div class="filter-group">
                    <label class="filter-label">Precio Máximo</label>
                    <input type="number" class="filter-input" id="filterPrecioMax" placeholder="$10000">
                </div>
                <div class="filter-group">
                    <button class="btn btn-outline" onclick="aplicarFiltros()" style="margin-top: 1.2rem;">
                        🔄 Aplicar Filtros
                    </button>
                </div>
            </div>
        </div>

        <!-- Tabla de artículos -->
        <div class="inventory-table-container">
            <div class="table-header">
                <h3 style="margin: 0; color: var(--text-primary);">Mis Artículos</h3>
                <div class="table-info" style="color: var(--text-secondary);">
                    Mostrando <span id="articleCount" style="font-weight: 600;">8</span> de 15 artículos
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Artículo</th>
                        <th>Categoría</th>
                        <th>Precio</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Factura</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="articlesTable">
                    <!-- Los artículos se cargarán aquí con JavaScript -->
                </tbody>
            </table>

            <div class="pagination">
                <button class="page-btn">← Anterior</button>
                <button class="page-btn active">1</button>
                <button class="page-btn">2</button>
                <button class="page-btn">Siguiente →</button>
            </div>
        </div>
    </main>

    <script>
        // Cargar artículos reales desde PHP
        const articulos = <?php echo $jsonArticulos; ?>;


        function cargarArticulos() {
            const tbody = document.getElementById('articlesTable');
            tbody.innerHTML = '';

            articulos.forEach(articulo => {
                const tr = document.createElement('tr');

                tr.innerHTML = `
                    <td>${articulo.nombre}</td>
                    <td>${articulo.categoriaNombre ?? 'Sin categoría'}</td>
                    <td>$${parseFloat(articulo.precio).toFixed(2)}</td>
                    <td><span class="status-badge ${getStatusClass(articulo.estado)}">${articulo.estado}</span></td>
                    <td>${articulo.fecha_registro}</td>
                    <td>${articulo.factura == 1 ? 'Sí' : 'No'}</td>
                    <td>
                        <button onclick="verArticulo(${articulo.id})">Ver</button>
                        <button onclick="editarArticulo(${articulo.id})">Editar</button>
                        <button onclick="eliminarArticulo(${articulo.id})">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function getStatusClass(estado) {
            switch (estado) {
                case 'activo':
                    return 'status-active';
                case 'vendido':
                    return 'status-sold';
                case 'donado':
                    return 'status-donated';
                default:
                    return 'status-active';
            }
        }

        function agregarArticulo() {
            alert('Funcionalidad de agregar artículo - Próximamente');
            // Aquí abrirías un modal o redirigirías a un formulario
        }

        function editarArticulo(id) {
            alert('Editar artículo ID: ' + id);
            // Aquí abrirías un modal de edición
        }

        function verArticulo(id) {
            alert('Ver detalles artículo ID: ' + id);
            // Aquí mostrarías un modal con todos los detalles
        }

        function agregarFactura(id) {
            alert('Agregar factura al artículo ID: ' + id);
            // Aquí abrirías un formulario para subir factura
        }

        function verFactura(id) {
            alert('Ver factura del artículo ID: ' + id);
            // Aquí mostrarías la factura
        }

        function eliminarArticulo(id) {
            if (confirm('¿Estás seguro de que quieres eliminar este artículo? Esta acción no se puede deshacer.')) {
                alert('Artículo eliminado: ' + id);
                // Aquí harías la petición al servidor
            }
        }

        function generarReporte() {
            alert('Generando reporte de inventario...');
            // Aquí generarías y descargarías el reporte
        }

        function aplicarFiltros() {
            alert('Aplicando filtros...');
            // Aquí implementarías la lógica de filtrado
        }

        // Búsqueda en tiempo real
        document.getElementById('searchInput').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            // Aquí implementarías la lógica de búsqueda
        });

        // Cargar artículos al iniciar
        document.addEventListener('DOMContentLoaded', cargarArticulos);
    </script>
</body>

</html>