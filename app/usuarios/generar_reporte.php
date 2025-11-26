<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/validar_sesion.php';

// Configurar zona horaria de México
date_default_timezone_set('America/Mexico_City');

// Obtener el ID del usuario logueado
$idUsuario = $_SESSION['id_usuario'];
$nombreUsuario = $_SESSION['nombre_usuario'] ?? 'Usuario';
$correoUsuario = $_SESSION['correo'] ?? '';

// Consultar artículos del usuario
$query = "SELECT a.id_articulo, a.nombre, a.descripcion, a.precio, a.estado, a.fecha_registro,
                 c.nombre AS categoriaNombre,
                 (SELECT COUNT(*) FROM facturas f WHERE f.id_articulo = a.id_articulo) AS tiene_factura,
                 (SELECT COUNT(*) FROM imagenes i WHERE i.id_articulo = a.id_articulo) AS cantidad_imagenes
            FROM articulo a
            LEFT JOIN categoria c ON c.id_categoria = a.id_categoria
            WHERE a.id_usuario = $idUsuario
            ORDER BY a.fecha_registro DESC";

$result = mysqli_query($link, $query);

if (!$result) {
    die('Error al consultar los artículos: ' . mysqli_error($link));
}

$articulos = [];
$totalArticulos = 0;
$valorTotal = 0;
$articulosActivos = 0;
$conFactura = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $articulos[] = $row;
    $totalArticulos++;
    $valorTotal += floatval($row['precio']);
    if ($row['estado'] == 1) {
        $articulosActivos++;
    }
    if ($row['tiene_factura'] > 0) {
        $conFactura++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Inventario - SGIP</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: #ffffff;
        }

        .report-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: white;
        }

        .report-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 3px solid var(--primary-color);
        }

        .report-logo {
            font-size: 2.5rem;
            font-weight: 700;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.25rem;
        }

        .report-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.75rem;
        }

        .report-info {
            display: flex;
            justify-content: center;
            gap: 2rem;
            flex-wrap: wrap;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .report-info-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .report-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stats-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 1rem;
            padding-left: 0.75rem;
            border-left: 4px solid var(--primary-color);
        }

        .stats-grid {
            display: flex;
            justify-content: space-between;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .stat-card {
            background: white;
            color: var(--text-primary);
            padding: 1rem 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--border-color);
            flex: 1;
            min-width: 200px;
            text-align: center;
        }

        .stat-card:nth-child(1) {
            border-left: 4px solid #667eea;
        }

        .stat-card:nth-child(2) {
            border-left: 4px solid #f093fb;
        }

        .stat-card:nth-child(3) {
            border-left: 4px solid #4facfe;
        }

        .stat-card:nth-child(4) {
            border-left: 4px solid #43e97b;
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        /* Estilos específicos para impresión de estadísticas */
        @media print {
            .stats-grid {
                display: flex;
                gap: 0.5rem;
                margin-bottom: 1rem;
            }

            .stat-card {
                padding: 0.5rem 1rem;
                box-shadow: none;
                border: 1px solid #ddd;
                page-break-inside: avoid;
                min-width: auto;
            }

            .stat-label {
                font-size: 0.65rem;
                margin-bottom: 0.25rem;
            }

            .stat-value {
                font-size: 1rem;
            }

            .stats-section {
                margin-bottom: 1rem;
            }

            .section-title {
                font-size: 1rem;
                margin-bottom: 0.5rem;
            }
        }

        .articles-section {
            margin-bottom: 3rem;
        }

        .report-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        .report-table thead {
            background: var(--primary-color);
            color: white;
        }

        .report-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
        }

        .report-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .report-table tbody tr:hover {
            background: var(--background-alt);
        }

        .report-table tbody tr:last-child td {
            border-bottom: none;
        }

        .empty-message {
            text-align: center;
            padding: 3rem;
            color: var(--text-secondary);
            font-style: italic;
        }

        .report-footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 2px solid var(--border-color);
            text-align: center;
            color: var(--text-secondary);
            font-size: 0.85rem;
        }

        .legend {
            background: var(--background-alt);
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        /* Estilos para impresión */
        @media print {
            .report-actions {
                display: none !important;
            }

            body {
                background: white;
            }

            .report-container {
                padding: 0;
                max-width: 100%;
            }

            .report-table {
                box-shadow: none;
            }

            .report-header {
                margin-bottom: 1rem;
                padding-bottom: 1rem;
                page-break-after: avoid;
            }

            .report-logo {
                font-size: 2rem;
                margin-bottom: 0.25rem;
                color: #667eea !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .report-title {
                font-size: 1.25rem;
                margin-bottom: 0.5rem;
            }

            .report-info {
                font-size: 0.75rem;
                gap: 1rem;
            }

            @page {
                margin: 1.5cm;
            }
        }

        @media (max-width: 768px) {
            .report-container {
                padding: 1rem;
            }

            .report-table {
                font-size: 0.8rem;
            }

            .report-table th,
            .report-table td {
                padding: 0.5rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="report-container">
        <!-- Botones de acción -->
        <div class="report-actions">
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Imprimir Reporte
            </button>
            <button onclick="window.location.href='inventario.php'" class="btn btn-secondary">
                ← Regresar al Inventario
            </button>
        </div>

        <!-- Encabezado del reporte -->
        <div class="report-header">
            <div class="report-logo">SGIP</div>
            <div class="report-title">Reporte de Inventario Personal</div>
            <div class="report-info">
                <div class="report-info-item">
                    <strong>👤 Usuario:</strong> <?php echo htmlspecialchars($nombreUsuario); ?>
                </div>
                <div class="report-info-item">
                    <strong>📧 Correo:</strong> <?php echo htmlspecialchars($correoUsuario); ?>
                </div>
                <div class="report-info-item">
                    <strong>📅 Fecha:</strong> <?php echo date('d/m/Y H:i:s'); ?>
                </div>
            </div>
        </div>

        <!-- Sección de estadísticas -->
        <div class="stats-section">
            <h2 class="section-title">Resumen Estadístico</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total de Artículos</div>
                    <div class="stat-value"><?php echo $totalArticulos; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Valor Total del Inventario</div>
                    <div class="stat-value">$<?php echo number_format($valorTotal, 2); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Artículos Activos</div>
                    <div class="stat-value"><?php echo $articulosActivos; ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Artículos con Factura</div>
                    <div class="stat-value"><?php echo $conFactura; ?></div>
                </div>
            </div>
        </div>

        <!-- Sección de artículos -->
        <div class="articles-section">
            <h2 class="section-title">Detalle de Artículos</h2>
            
            <?php if (count($articulos) > 0): ?>
                <div class="legend">
                    <strong>Leyenda:</strong> 📄 = Tiene factura | 📷 = Número de imágenes adjuntas
                </div>

                <table class="report-table">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Extras</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($articulos as $index => $articulo): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($articulo['nombre']); ?></strong>
                                    <?php if ($articulo['descripcion']): ?>
                                        <br><small style="color: var(--text-secondary);">
                                            <?php echo htmlspecialchars(substr($articulo['descripcion'], 0, 60)); ?>
                                            <?php echo strlen($articulo['descripcion']) > 60 ? '...' : ''; ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($articulo['categoriaNombre'] ?? 'Sin categoría'); ?></td>
                                <td><strong>$<?php echo number_format($articulo['precio'], 2); ?></strong></td>
                                <td>
                                    <span class="status-badge <?php echo $articulo['estado'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                                        <?php echo $articulo['estado'] == 1 ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($articulo['fecha_registro'])); ?></td>
                                <td>
                                    <?php if ($articulo['tiene_factura'] > 0): ?>
                                        📄
                                    <?php endif; ?>
                                    <?php if ($articulo['cantidad_imagenes'] > 0): ?>
                                        📷 (<?php echo $articulo['cantidad_imagenes']; ?>)
                                    <?php endif; ?>
                                    <?php if ($articulo['tiene_factura'] == 0 && $articulo['cantidad_imagenes'] == 0): ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-message">
                    No hay artículos registrados en el inventario.
                </div>
            <?php endif; ?>
        </div>

        <!-- Pie del reporte -->
        <div class="report-footer">
            <p>
                <strong>SGIP - Sistema de Gestión de Inventario Personal</strong><br>
                Este reporte contiene información confidencial del inventario personal.<br>
                Generado automáticamente el <?php echo date('d/m/Y'); ?> a las <?php echo date('H:i:s'); ?>
            </p>
        </div>
    </div>
</body>

</html>
