<?php
   require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';
   require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/validar_sesion.php';
   
   validar_rol('usuario');

   // 1. OBTENER ARTÍCULOS DEL USUARIO LOGUEADO
   $idUsuario = $_SESSION['id_usuario'];

   $query = "SELECT a.id_articulo, a.nombre, a.descripcion, a.precio, a.estado, a.fecha_registro, a.id_categoria,
                  c.nombre AS categoriaNombre,
                  (SELECT COUNT(*) FROM facturas f WHERE f.id_articulo = a.id_articulo) AS tiene_factura,
                  (SELECT ruta_factura FROM facturas f WHERE f.id_articulo = a.id_articulo LIMIT 1) AS ruta_factura,
                  (SELECT COUNT(*) FROM imagenes i WHERE i.id_articulo = a.id_articulo) AS cantidad_imagenes,
                  (SELECT GROUP_CONCAT(i.ruta_imagen SEPARATOR '|') FROM imagenes i WHERE i.id_articulo = a.id_articulo) AS rutas_imagenes
               FROM articulo a
               LEFT JOIN categoria c ON c.id_categoria = a.id_categoria
               WHERE a.id_usuario = $idUsuario
               ORDER BY a.fecha_registro DESC";

   $result = mysqli_query($link, $query);

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
            <a href="/SGIP/app/usuarios/index.php" class="home-link" title="Ir al Inicio">
               <span class="home-icon">🏠</span>
            </a>
         </div>

         <div class="nav-actions">
            <a href="../../logout.php" class="btn btn-outline">Cerrar Sesión</a>
         </div>
      </nav>
   </header>

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
            <div class="stat-number" id="totalArticulos"><?php echo $totalArticulos; ?></div>
            <div class="stat-label">Total Artículos</div>
         </div>
         <div class="stat-card">
            <div class="stat-number" id="valorTotal">$<?php echo number_format($valorTotal, 2); ?></div>
            <div class="stat-label">Valor Total</div>
         </div>
         <div class="stat-card">
            <div class="stat-number" id="articulosActivos"><?php echo $articulosActivos; ?></div>
            <div class="stat-label">Artículos Activos</div>
         </div>
         <div class="stat-card">
            <div class="stat-number" id="conFactura"><?php echo $conFactura; ?></div>
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
                  <?php
                  $queryCategoriasFiltro = "SELECT DISTINCT c.nombre 
                                                   FROM categoria c 
                                                   INNER JOIN articulo a ON c.id_categoria = a.id_categoria 
                                                   WHERE a.id_usuario = $idUsuario 
                                                   ORDER BY c.nombre";
                  $resultCategoriasFiltro = mysqli_query($link, $queryCategoriasFiltro);
                  while ($catFiltro = mysqli_fetch_assoc($resultCategoriasFiltro)) {
                     echo "<option value='{$catFiltro['nombre']}'>{$catFiltro['nombre']}</option>";
                  }
                  ?>
               </select>
            </div>
            <div class="filter-group">
               <label class="filter-label">Estado</label>
               <select class="filter-select" id="filterEstado">
                  <option value="">Todos los estados</option>
                  <option value="1">Activo</option>
                  <option value="0">Inactivo</option>
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
            <div class="filter-group">
               <button class="btn btn-outline" onclick="limpiarFiltros()" style="margin-top: 1.2rem;">
                  ✖️ Limpiar Filtros
               </button>
            </div>
         </div>
      </div>

      <!-- Tabla de artículos -->
      <div class="inventory-table-container">
         <div class="table-header">
            <h3 style="margin: 0; color: var(--text-primary);">Mis Artículos</h3>
            <div class="table-info" style="color: var(--text-secondary);">
               Mostrando <span id="articleCount" style="font-weight: 600;"><?php echo $totalArticulos; ?></span> de <?php echo $totalArticulos; ?> artículos
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
                  <th>Factura / Imágenes</th>
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

   <!-- Modal para Ver Artículo -->
   <div class="modal-overlay" id="modalVerArticulo">
      <div class="modal-container">
         <div class="modal-header">
            <h2 class="modal-title">Detalles del Artículo</h2>
            <button class="modal-close" onclick="cerrarModalVer()">&times;</button>
         </div>
         <div class="modal-body">
            <div id="detalleArticulo" class="detalle-articulo">
               <!-- Los detalles se cargarán aquí con JavaScript -->
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="cerrarModalVer()">Cerrar</button>
         </div>
      </div>
   </div>

   <!-- Modal para Editar Artículo -->
   <div class="modal-overlay" id="modalEditarArticulo">
      <div class="modal-container">
         <div class="modal-header">
            <h2 class="modal-title">Editar Artículo</h2>
            <button class="modal-close" onclick="cerrarModalEditar()">&times;</button>
         </div>
         <form id="formEditarArticulo" class="modal-body" enctype="multipart/form-data">
            <input type="hidden" name="id_articulo" id="edit_id_articulo">
            <div class="modal-form">
               <div class="form-row">
                  <div class="form-group">
                     <label class="form-label">Nombre del Artículo <span class="required">*</span></label>
                     <input type="text" name="nombre" id="edit_nombre" class="form-input" placeholder="Ej: Laptop HP" required>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Precio <span class="required">*</span></label>
                     <input type="number" name="precio" id="edit_precio" class="form-input" step="0.01" min="0" placeholder="0.00" required>
                  </div>
               </div>

               <div class="form-row">
                  <div class="form-group">
                     <label class="form-label">Categoría <span class="required">*</span></label>
                     <select name="id_categoria" id="edit_id_categoria" class="form-select" required>
                        <option value="">Selecciona una categoría</option>
                        <?php
                        $queryCategorias2 = "SELECT id_categoria, nombre FROM categoria ORDER BY nombre";
                        $resultCategorias2 = mysqli_query($link, $queryCategorias2);
                        while ($cat = mysqli_fetch_assoc($resultCategorias2)) {
                           echo "<option value='{$cat['id_categoria']}'>{$cat['nombre']}</option>";
                        }
                        ?>
                     </select>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Estado <span class="required">*</span></label>
                     <select name="estado" id="edit_estado" class="form-select" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                     </select>
                  </div>
               </div>

               <div class="form-group full-width">
                  <label class="form-label">Descripción</label>
                  <textarea name="descripcion" id="edit_descripcion" class="form-textarea" placeholder="Describe el artículo (opcional)"></textarea>
               </div>

               <div class="form-group full-width">
                  <label class="form-label">Añadir Imágenes</label>
                  <input type="file" id="edit_imagenes" name="imagenes[]" class="form-file-input" accept="image/*" multiple onchange="previsualizarImagenesEditar(event)">
                  <label for="edit_imagenes" class="file-upload-area">
                     <div class="file-upload-icon">📷</div>
                     <div class="file-upload-text">
                        <strong>Click para seleccionar</strong> nuevas imágenes<br>
                        <small>PNG, JPG o JPEG (máx. 5MB por imagen)</small>
                     </div>
                  </label>
                  <div id="editImagenesPreview" class="file-preview"></div>
               </div>
            </div>
         </form>
         <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="cerrarModalEditar()">Cancelar</button>
            <button type="submit" form="formEditarArticulo" class="btn btn-primary">Actualizar Artículo</button>
         </div>
      </div>
   </div>

   <!-- Modal para Agregar Factura -->
   <div class="modal-overlay" id="modalAgregarFactura">
      <div class="modal-container" style="max-width: 600px;">
         <div class="modal-header">
            <h2 class="modal-title">Agregar Factura</h2>
            <button class="modal-close" onclick="cerrarModalFactura()">&times;</button>
         </div>
         <form id="formAgregarFactura" class="modal-body" enctype="multipart/form-data">
            <input type="hidden" name="id_articulo" id="factura_id_articulo">
            <div class="modal-form">
               <div class="form-group full-width">
                  <label class="form-label">Artículo</label>
                  <input type="text" id="factura_nombre_articulo" class="form-input" readonly>
               </div>
               <div class="form-group full-width">
                  <label class="form-label">Factura (PDF) <span class="required">*</span></label>
                  <input type="file" id="factura_archivo" name="factura" class="form-file-input" accept=".pdf" required onchange="previsualizarFacturaModal(event)">
                  <label for="factura_archivo" class="file-upload-area">
                     <div class="file-upload-icon">📄</div>
                     <div class="file-upload-text">
                        <strong>Click para seleccionar</strong> la factura<br>
                        <small>Solo archivos PDF (máx. 10MB)</small>
                     </div>
                  </label>
                  <div id="facturaModalPreview"></div>
               </div>
            </div>
         </form>
         <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="cerrarModalFactura()">Cancelar</button>
            <button type="submit" form="formAgregarFactura" class="btn btn-primary">Guardar Factura</button>
         </div>
      </div>
   </div>

   <!-- Modal para Agregar Artículo -->
   <div class="modal-overlay" id="modalAgregarArticulo">
      <div class="modal-container">
         <div class="modal-header">
            <h2 class="modal-title">Agregar Nuevo Artículo</h2>
            <button class="modal-close" onclick="cerrarModal()">&times;</button>
         </div>
         <form id="formAgregarArticulo" class="modal-body" enctype="multipart/form-data">
            <div class="modal-form">
               <div class="form-row">
                  <div class="form-group">
                     <label class="form-label">Nombre del Artículo <span class="required">*</span></label>
                     <input type="text" name="nombre" class="form-input" placeholder="Ej: Laptop HP" required>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Precio <span class="required">*</span></label>
                     <input type="number" name="precio" class="form-input" step="0.01" min="0" placeholder="0.00" required>
                  </div>
               </div>

               <div class="form-row">
                  <div class="form-group">
                     <label class="form-label">Categoría <span class="required">*</span></label>
                     <select name="id_categoria" class="form-select" required>
                        <option value="">Selecciona una categoría</option>
                        <?php
                        $queryCategorias = "SELECT id_categoria, nombre FROM categoria ORDER BY nombre";
                        $resultCategorias = mysqli_query($link, $queryCategorias);
                        while ($cat = mysqli_fetch_assoc($resultCategorias)) {
                           echo "<option value='{$cat['id_categoria']}'>{$cat['nombre']}</option>";
                        }
                        ?>
                     </select>
                  </div>
                  <div class="form-group">
                     <label class="form-label">Estado <span class="required">*</span></label>
                     <select name="estado" class="form-select" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                     </select>
                  </div>
               </div>

               <div class="form-group full-width">
                  <label class="form-label">Descripción</label>
                  <textarea name="descripcion" class="form-textarea" placeholder="Describe el artículo (opcional)"></textarea>
               </div>

               <div class="form-group full-width">
                  <label class="form-label">Imágenes del Artículo</label>
                  <input type="file" id="imagenes" name="imagenes[]" class="form-file-input" accept="image/*" multiple onchange="previsualizarImagenes(event)">
                  <label for="imagenes" class="file-upload-area">
                     <div class="file-upload-icon">📷</div>
                     <div class="file-upload-text">
                        <strong>Click para seleccionar</strong> o arrastra las imágenes aquí<br>
                        <small>PNG, JPG o JPEG (máx. 5MB por imagen)</small>
                     </div>
                  </label>
                  <div id="imagenesPreview" class="file-preview"></div>
               </div>

               <div class="form-group full-width">
                  <label class="form-label">Factura (PDF)</label>
                  <input type="file" id="factura" name="factura" class="form-file-input" accept=".pdf" onchange="previsualizarFactura(event)">
                  <label for="factura" class="file-upload-area">
                     <div class="file-upload-icon">📄</div>
                     <div class="file-upload-text">
                        <strong>Click para seleccionar</strong> la factura<br>
                        <small>Solo archivos PDF (máx. 10MB)</small>
                     </div>
                  </label>
                  <div id="facturaPreview"></div>
               </div>
            </div>
         </form>
         <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="cerrarModal()">Cancelar</button>
            <button type="submit" form="formAgregarArticulo" class="btn btn-primary">Guardar Artículo</button>
         </div>
      </div>
   </div>

   <script>
      // Constantes de configuración para tamaños de archivos
      const MAX_IMAGE_SIZE_MB = 5; // Tamaño máximo por imagen en MB
      const MAX_INVOICE_SIZE_MB = 10; // Tamaño máximo por factura en MB
      const MAX_IMAGE_SIZE = MAX_IMAGE_SIZE_MB * 1024 * 1024; // Convertir a bytes
      const MAX_INVOICE_SIZE = MAX_INVOICE_SIZE_MB * 1024 * 1024; // Convertir a bytes

      // Variables de paginación
      const ITEMS_POR_PAGINA = 10;
      let paginaActual = 1;
      let articulosFiltrados = [];

      // Cargar artículos reales desde PHP
      const articulos = <?php echo $jsonArticulos; ?>;


      function cargarArticulos() {
         articulosFiltrados = articulos;
         paginaActual = 1;
         mostrarArticulos();
      }

      function mostrarArticulos() {
         const tbody = document.getElementById('articlesTable');
         tbody.innerHTML = '';

         if (articulosFiltrados.length === 0) {
            tbody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-secondary);">
                            No se encontraron artículos con los filtros aplicados
                        </td>
                    </tr>
                `;
            actualizarPaginacion();
            return;
         }

         // Calcular índices para la página actual
         const inicio = (paginaActual - 1) * ITEMS_POR_PAGINA;
         const fin = inicio + ITEMS_POR_PAGINA;
         const articulosPagina = articulosFiltrados.slice(inicio, fin);

         articulosPagina.forEach(articulo => {
            const tr = document.createElement('tr');

            tr.innerHTML = `
                    <td>
                        <div style="font-weight: 500;">${articulo.nombre}</div>
                        ${articulo.descripcion ? `<div style="font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem;">${articulo.descripcion.substring(0, 50)}${articulo.descripcion.length > 50 ? '...' : ''}</div>` : ''}
                    </td>
                    <td>${articulo.categoriaNombre ?? 'Sin categoría'}</td>
                    <td>$${parseFloat(articulo.precio).toFixed(2)}</td>
                    <td><span class="status-badge ${getStatusClass(articulo.estado)}">${articulo.estado == 1 ? 'Activo' : 'Inactivo'}</span></td>
                    <td>${articulo.fecha_registro ? new Date(articulo.fecha_registro).toLocaleDateString('es-ES') : 'N/A'}</td>
                    <td>
                        <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            ${articulo.tiene_factura > 0 
                                ? `<button class="btn-mini btn-success" onclick="verFactura(${articulo.id_articulo})" title="Ver factura">📄 Ver Factura</button>` 
                                : `<button class="btn-mini btn-secondary" onclick="agregarFactura(${articulo.id_articulo})" title="Agregar factura">➕ Agregar Factura</button>`
                            }
                            ${articulo.cantidad_imagenes > 0 ? `<span class="badge-info" title="${articulo.cantidad_imagenes} imagen(es)">📷 ${articulo.cantidad_imagenes}</span>` : ''}
                        </div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn-action btn-view" onclick="verArticulo(${articulo.id_articulo})">
                                <span class="btn-icon">👁️</span>
                                <span class="btn-text">Ver</span>
                            </button>
                            <button class="btn-action btn-edit" onclick="editarArticulo(${articulo.id_articulo})">
                                <span class="btn-icon">✏️</span>
                                <span class="btn-text">Editar</span>
                            </button>
                            <button class="btn-action btn-delete" onclick="eliminarArticulo(${articulo.id_articulo})">
                                <span class="btn-icon">🗑️</span>
                                <span class="btn-text">Eliminar</span>
                            </button>
                        </div>
                    </td>
                `;
            tbody.appendChild(tr);
         });

         // Actualizar contador y paginación
         actualizarContador();
         actualizarPaginacion();
      }

      function actualizarContador() {
         const inicio = (paginaActual - 1) * ITEMS_POR_PAGINA + 1;
         const fin = Math.min(paginaActual * ITEMS_POR_PAGINA, articulosFiltrados.length);
         const totalMostrado = articulosFiltrados.length > 0 ? `${inicio}-${fin}` : '0';

         document.getElementById('articleCount').textContent = totalMostrado;
         document.querySelector('.table-info').innerHTML = `Mostrando <span id="articleCount" style="font-weight: 600;">${totalMostrado}</span> de ${articulosFiltrados.length} artículos`;
      }

      function actualizarPaginacion() {
         const totalPaginas = Math.ceil(articulosFiltrados.length / ITEMS_POR_PAGINA);
         const paginacion = document.querySelector('.pagination');

         if (totalPaginas <= 1) {
            paginacion.style.display = 'none';
            return;
         }

         paginacion.style.display = 'flex';
         paginacion.innerHTML = '';

         // Botón Anterior
         const btnAnterior = document.createElement('button');
         btnAnterior.className = 'page-btn';
         btnAnterior.textContent = '← Anterior';
         btnAnterior.disabled = paginaActual === 1;
         btnAnterior.onclick = () => cambiarPagina(paginaActual - 1);
         paginacion.appendChild(btnAnterior);

         // Botones de páginas
         const maxBotones = 5;
         let inicioPagina = Math.max(1, paginaActual - Math.floor(maxBotones / 2));
         let finPagina = Math.min(totalPaginas, inicioPagina + maxBotones - 1);

         if (finPagina - inicioPagina < maxBotones - 1) {
            inicioPagina = Math.max(1, finPagina - maxBotones + 1);
         }

         // Botón primera página si no está visible
         if (inicioPagina > 1) {
            const btn1 = document.createElement('button');
            btn1.className = 'page-btn';
            btn1.textContent = '1';
            btn1.onclick = () => cambiarPagina(1);
            paginacion.appendChild(btn1);

            if (inicioPagina > 2) {
               const btnDots = document.createElement('button');
               btnDots.className = 'page-btn';
               btnDots.textContent = '...';
               btnDots.disabled = true;
               paginacion.appendChild(btnDots);
            }
         }

         // Botones de páginas visibles
         for (let i = inicioPagina; i <= finPagina; i++) {
            const btn = document.createElement('button');
            btn.className = `page-btn ${i === paginaActual ? 'active' : ''}`;
            btn.textContent = i;
            btn.onclick = () => cambiarPagina(i);
            paginacion.appendChild(btn);
         }

         // Botón última página si no está visible
         if (finPagina < totalPaginas) {
            if (finPagina < totalPaginas - 1) {
               const btnDots = document.createElement('button');
               btnDots.className = 'page-btn';
               btnDots.textContent = '...';
               btnDots.disabled = true;
               paginacion.appendChild(btnDots);
            }

            const btnUltima = document.createElement('button');
            btnUltima.className = 'page-btn';
            btnUltima.textContent = totalPaginas;
            btnUltima.onclick = () => cambiarPagina(totalPaginas);
            paginacion.appendChild(btnUltima);
         }

         // Botón Siguiente
         const btnSiguiente = document.createElement('button');
         btnSiguiente.className = 'page-btn';
         btnSiguiente.textContent = 'Siguiente →';
         btnSiguiente.disabled = paginaActual === totalPaginas;
         btnSiguiente.onclick = () => cambiarPagina(paginaActual + 1);
         paginacion.appendChild(btnSiguiente);
      }

      function cambiarPagina(nuevaPagina) {
         const totalPaginas = Math.ceil(articulosFiltrados.length / ITEMS_POR_PAGINA);
         if (nuevaPagina < 1 || nuevaPagina > totalPaginas) return;

         paginaActual = nuevaPagina;
         mostrarArticulos();

         // Scroll suave hacia arriba de la tabla
         document.querySelector('.inventory-table-container').scrollIntoView({
            behavior: 'smooth',
            block: 'start'
         });
      }

      function getStatusClass(estado) {
         return estado == 1 ? 'status-active' : 'status-inactive';
      }

      // Variables para almacenar archivos seleccionados
      let imagenesSeleccionadas = [];
      let facturaSeleccionada = null;

      function agregarArticulo() {
         document.getElementById('modalAgregarArticulo').classList.add('active');
         document.body.style.overflow = 'hidden';
      }

      function cerrarModal() {
         document.getElementById('modalAgregarArticulo').classList.remove('active');
         document.body.style.overflow = 'auto';
         document.getElementById('formAgregarArticulo').reset();
         document.getElementById('imagenesPreview').innerHTML = '';
         document.getElementById('facturaPreview').innerHTML = '';

         // Restablecer el estado de la factura
         document.getElementById('factura').disabled = false;
         const facturaLabel = document.querySelector('label[for="factura"]');
         if (facturaLabel) {
            facturaLabel.style.opacity = '1';
            facturaLabel.style.pointerEvents = 'auto';
            facturaLabel.style.cursor = 'pointer';
         }

         imagenesSeleccionadas = [];
         facturaSeleccionada = null;
      }

      function previsualizarImagenes(event) {
         const files = Array.from(event.target.files);
         const preview = document.getElementById('imagenesPreview');

         // Validar tamaño de cada archivo
         const archivosValidos = [];
         const archivosRechazados = [];

         files.forEach(file => {
            if (file.size > MAX_IMAGE_SIZE) {
               archivosRechazados.push({
                  nombre: file.name,
                  tamano: (file.size / 1024 / 1024).toFixed(2)
               });
            } else {
               archivosValidos.push(file);
            }
         });

         // Mostrar errores si hay archivos rechazados
         if (archivosRechazados.length > 0) {
            const mensajesError = archivosRechazados.map(archivo =>
               `❌ "${archivo.nombre}" (${archivo.tamano} MB)`
            ).join('\n');

            Swal.fire({
               icon: "warning",
               title: "Archivos demasiado grandes",
               text: `Los siguientes archivos exceden el tamaño máximo de ${MAX_IMAGE_SIZE_MB}MB y no se pueden adjuntar:\n\n${mensajesError}\n\n${archivosValidos.length > 0 ? `Se agregarán ${archivosValidos.length} archivo(s) válido(s).` : 'Por favor, selecciona archivos más pequeños.'}`
            });

         }

         // Solo agregar archivos válidos
         if (archivosValidos.length > 0) {
            imagenesSeleccionadas = imagenesSeleccionadas.concat(archivosValidos);
         }

         // Si no hay archivos válidos, limpiar el input y salir
         if (archivosValidos.length === 0) {
            event.target.value = '';
            return;
         }

         // Limpiar y regenerar la vista previa con todas las imágenes
         preview.innerHTML = '';

         imagenesSeleccionadas.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
               const reader = new FileReader();
               reader.onload = function(e) {
                  const div = document.createElement('div');
                  div.className = 'file-preview-item';
                  div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="file-remove" onclick="eliminarImagen(${index})">&times;</button>
                        `;
                  preview.appendChild(div);
               }
               reader.readAsDataURL(file);
            }
         });

         // Limpiar el input para permitir seleccionar el mismo archivo nuevamente
         event.target.value = '';
      }

      function eliminarImagen(index) {
         imagenesSeleccionadas.splice(index, 1);

         // Regenerar la vista previa
         const preview = document.getElementById('imagenesPreview');
         preview.innerHTML = '';

         imagenesSeleccionadas.forEach((file, idx) => {
            if (file.type.startsWith('image/')) {
               const reader = new FileReader();
               reader.onload = function(e) {
                  const div = document.createElement('div');
                  div.className = 'file-preview-item';
                  div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <button type="button" class="file-remove" onclick="eliminarImagen(${idx})">&times;</button>
                        `;
                  preview.appendChild(div);
               }
               reader.readAsDataURL(file);
            }
         });
      }

      function previsualizarImagenesEditar(event) {
         const files = Array.from(event.target.files);
         const preview = document.getElementById('editImagenesPreview');

         // Validar tamaño de cada archivo
         const archivosValidos = [];
         const archivosRechazados = [];

         files.forEach(file => {
            if (file.size > MAX_IMAGE_SIZE) {
               archivosRechazados.push({
                  nombre: file.name,
                  tamano: (file.size / 1024 / 1024).toFixed(2)
               });
            } else {
               archivosValidos.push(file);
            }
         });

         // Mostrar errores si hay archivos rechazados
         if (archivosRechazados.length > 0) {
            const mensajesError = archivosRechazados.map(archivo =>
               `❌ "${archivo.nombre}" (${archivo.tamano} MB)`
            ).join('\n');

            swal(
               "⚠️ Archivos demasiado grandes",
               `Los siguientes archivos exceden el tamaño máximo de ${MAX_IMAGE_SIZE_MB}MB:\n\n${mensajesError}\n\n${
                  archivosValidos.length > 0
                     ? `${archivosValidos.length} archivo(s) válido(s) se agregará(n).`
                     : "Por favor, selecciona archivos más pequeños."
               }`,
               "warning"
            );

         }

         // Si no hay archivos válidos, limpiar el input y salir
         if (archivosValidos.length === 0) {
            event.target.value = '';
            return;
         }

         preview.innerHTML = '';

         archivosValidos.forEach((file, index) => {
            if (file.type.startsWith('image/')) {
               const reader = new FileReader();
               reader.onload = function(e) {
                  const div = document.createElement('div');
                  div.className = 'file-preview-item';
                  div.innerHTML = `
                            <img src="${e.target.result}" alt="Preview">
                            <div class="file-preview-name">${file.name}</div>
                        `;
                  preview.appendChild(div);
               }
               reader.readAsDataURL(file);
            }
         });
      }

      function previsualizarFactura(event) {
         const file = event.target.files[0];
         const preview = document.getElementById('facturaPreview');

         if (!file) return;

         // Validar tipo de archivo
         if (file.type !== 'application/pdf') {
            Swal.fire({
               icon: 'error',
               title: 'Archivo no permitido',
               text: 'Solo se permiten archivos PDF',
               confirmButtonText: 'Ok'
            });

            event.target.value = '';
            return;
         }

         // Validar tamaño
         if (file.size > MAX_INVOICE_SIZE) {
            const tamanoMB = (file.size / 1024 / 1024).toFixed(2);
            alert(`❌ El archivo "${file.name}" (${tamanoMB} MB) excede el tamaño máximo permitido de ${MAX_INVOICE_SIZE_MB}MB.\n\nPor favor, selecciona un archivo más pequeño.`);
            event.target.value = '';
            return;
         }

         if (file && file.type === 'application/pdf') {
            facturaSeleccionada = file;
            preview.innerHTML = `
                    <div style="margin-top: 1rem; padding: 1rem; background: var(--background-alt); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.5rem;">📄</span>
                            <div>
                                <div style="font-weight: 500; color: var(--text-primary);">${file.name}</div>
                                <div style="font-size: 0.875rem; color: var(--text-secondary);">${(file.size / 1024).toFixed(2)} KB</div>
                            </div>
                        </div>
                        <button type="button" class="file-remove" onclick="eliminarFactura()" style="position: static;">&times;</button>
                    </div>
                `;

            // Deshabilitar el input de archivo y el área de clic
            document.getElementById('factura').disabled = true;
            document.querySelector('label[for="factura"]').style.opacity = '0.5';
            document.querySelector('label[for="factura"]').style.pointerEvents = 'none';
            document.querySelector('label[for="factura"]').style.cursor = 'not-allowed';
         }
      }

      function eliminarFactura() {
         facturaSeleccionada = null;
         document.getElementById('factura').value = '';
         document.getElementById('factura').disabled = false;
         document.querySelector('label[for="factura"]').style.opacity = '1';
         document.querySelector('label[for="factura"]').style.pointerEvents = 'auto';
         document.querySelector('label[for="factura"]').style.cursor = 'pointer';
         document.getElementById('facturaPreview').innerHTML = '';
      }

      // Manejar envío del formulario de agregar factura
      document.getElementById('formAgregarFactura').addEventListener('submit', function(e) {
         e.preventDefault();

         const formData = new FormData(this);
         formData.append('accion', 'agregar_factura');

         const submitBtn = document.querySelector('#modalAgregarFactura .modal-footer button[type="submit"]');
         const originalText = submitBtn.innerHTML;
         submitBtn.disabled = true;
         submitBtn.innerHTML = '⏳ Guardando...';

         fetch('procesar_articulo.php', {
               method: 'POST',
               body: formData
            })
            .then(response => response.text())
            .then(text => {
               console.log('Respuesta del servidor:', text);
               cerrarModalFactura();
               try {
                  const data = JSON.parse(text);

                  if (data.success) {
                     Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Factura agregada exitosamente'
                     }).then(() => {
                        // Se ejecuta SOLO después de que el usuario cierre el Swal
                        location.reload();
                     });

                  } else {
                     Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                     });
                  }

               } catch (e) {
                  console.error('Error al parsear JSON:', e);
                  console.error('Respuesta recibida:', text);
                  Swal.fire({
                     icon: 'error',
                     title: 'Error',
                     text: 'Error: El servidor no devolvió una respuesta válida.'
                  });
               }
            })
            .catch(error => {
               console.error('Error:', error);
               Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: '❌ Error al agregar la factura: ' + error.message
               });

            })
            .finally(() => {
               submitBtn.disabled = false;
               submitBtn.innerHTML = originalText;
            });
      });

      // Manejar envío del formulario de edición
      document.getElementById('formEditarArticulo').addEventListener('submit', function(e) {
         e.preventDefault();

         const formData = new FormData(this);
         formData.append('accion', 'editar');

         const submitBtn = document.querySelector('#modalEditarArticulo .modal-footer button[type="submit"]');
         const originalText = submitBtn.innerHTML;
         submitBtn.disabled = true;
         submitBtn.innerHTML = '⏳ Actualizando...';

         fetch('procesar_articulo.php', {
               method: 'POST',
               body: formData
            })
            .then(response => response.text())
            .then(text => {
               console.log('Respuesta del servidor:', text);
               cerrarModalEditar();
               try {
                  const data = JSON.parse(text);
                  if (data.success) {

                     Swal.fire({
                        icon: 'success',
                        title: 'Artículo actualizado',
                        text: 'Artículo actualizado exitosamente'
                     }).then(() => {
                        // SOLO después de cerrar el swal
                        location.reload();
                     });

                  } else {
                     Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                     });
                  }
               } catch (e) {
                  console.error('Error al parsear JSON:', e);
                  console.error('Respuesta recibida:', text);
                  alert('❌ Error: El servidor no devolvió una respuesta válida.');
               }
            })
            .catch(error => {
               console.error('Error:', error);
               alert('❌ Error al actualizar el artículo: ' + error.message);
            })
            .finally(() => {
               submitBtn.disabled = false;
               submitBtn.innerHTML = originalText;
            });
      });

      // Manejar envío del formulario
      document.getElementById('formAgregarArticulo').addEventListener('submit', function(e) {
         e.preventDefault();

         const formData = new FormData(this);

         // Limpiar el campo de imágenes del formulario
         formData.delete('imagenes[]');

         // Agregar todas las imágenes seleccionadas manualmente
         imagenesSeleccionadas.forEach((file, index) => {
            formData.append('imagenes[]', file);
         });

         // Mostrar mensaje de carga
         const submitBtn = document.querySelector('.modal-footer button[type="submit"]');
         const originalText = submitBtn.innerHTML;
         submitBtn.disabled = true;
         submitBtn.innerHTML = '⏳ Guardando...';

         // Enviar al servidor
         fetch('procesar_articulo.php', {
               method: 'POST',
               body: formData
            })
            .then(response => {
               // Verificar si la respuesta es exitosa
               if (!response.ok) {
                  throw new Error('Error en el servidor: ' + response.status);
               }
               // Obtener el texto de la respuesta primero
               return response.text();
            })
            .then(text => {
               console.log('Respuesta del servidor:', text); // Para debug
               cerrarModal();
               try {
                  const data = JSON.parse(text);
                  if (data.success) {
                     Swal.fire({
                        icon: 'success',
                        title: 'Artículo agregado',
                        text: 'Artículo agregado exitosamente'
                     }).then(() => {
                        location.reload();
                     });
                  } else {
                     alert('❌ Error: ' + data.message);
                  }
               } catch (e) {
                  console.error('Error al parsear JSON:', e);
                  console.error('Respuesta recibida:', text);
                  alert('❌ Error: El servidor no devolvió una respuesta válida.\nRevisa la consola para más detalles.');
               }
            })
            .catch(error => {
               console.error('Error:', error);
               alert('❌ Error al guardar el artículo: ' + error.message);
            })
            .finally(() => {
               submitBtn.disabled = false;
               submitBtn.innerHTML = originalText;
            });
      });

      // Cerrar modal al hacer clic fuera
      document.getElementById('modalAgregarArticulo').addEventListener('click', function(e) {
         if (e.target === this) {
            cerrarModal();
         }
      });

      // Cerrar modal con tecla ESC
      document.addEventListener('keydown', function(e) {
         if (e.key === 'Escape') {
            cerrarModal();
            cerrarModalVer();
            cerrarModalEditar();
            cerrarModalFactura();
         }
      });

      // Cerrar modales al hacer clic fuera
      document.getElementById('modalVerArticulo').addEventListener('click', function(e) {
         if (e.target === this) {
            cerrarModalVer();
         }
      });

      document.getElementById('modalEditarArticulo').addEventListener('click', function(e) {
         if (e.target === this) {
            cerrarModalEditar();
         }
      });

      document.getElementById('modalAgregarFactura').addEventListener('click', function(e) {
         if (e.target === this) {
            cerrarModalFactura();
         }
      });

      function verArticulo(id) {
         const articulo = articulos.find(a => a.id_articulo == id);
         if (!articulo) return;

         // Crear galería de imágenes si existen
         let galeriaHTML = '';
         if (articulo.rutas_imagenes) {
            const imagenes = articulo.rutas_imagenes.split('|');
            galeriaHTML = `
                    <div class="detalle-item full-width">
                        <label class="detalle-label">Imágenes (${imagenes.length}):</label>
                        <div class="galeria-imagenes">
                            ${imagenes.map(ruta => `
                                <div class="galeria-item">
                                    <img src="${ruta}" alt="Imagen del artículo" onclick="verImagenCompleta('${ruta}')">
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
         }

         const detalleHTML = `
                <div class="detalle-grid">
                    <div class="detalle-item">
                        <label class="detalle-label">Nombre:</label>
                        <div class="detalle-valor">${articulo.nombre}</div>
                    </div>
                    <div class="detalle-item">
                        <label class="detalle-label">Precio:</label>
                        <div class="detalle-valor">$${parseFloat(articulo.precio).toFixed(2)}</div>
                    </div>
                    <div class="detalle-item">
                        <label class="detalle-label">Categoría:</label>
                        <div class="detalle-valor">${articulo.categoriaNombre ?? 'Sin categoría'}</div>
                    </div>
                    <div class="detalle-item">
                        <label class="detalle-label">Estado:</label>
                        <div class="detalle-valor"><span class="status-badge ${getStatusClass(articulo.estado)}">${articulo.estado == 1 ? 'Activo' : 'Inactivo'}</span></div>
                    </div>
                    <div class="detalle-item">
                        <label class="detalle-label">Fecha de Registro:</label>
                        <div class="detalle-valor">${articulo.fecha_registro ? new Date(articulo.fecha_registro).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' }) : 'N/A'}</div>
                    </div>
                    <div class="detalle-item">
                        <label class="detalle-label">Factura:</label>
                        <div class="detalle-valor">
                            ${articulo.tiene_factura > 0 
                                ? `<button class="btn-mini btn-success" onclick="verFactura(${articulo.id_articulo})">📄 Ver Factura</button>` 
                                : '❌ No'}
                        </div>
                    </div>
                    ${articulo.descripcion ? `
                    <div class="detalle-item full-width">
                        <label class="detalle-label">Descripción:</label>
                        <div class="detalle-valor">${articulo.descripcion}</div>
                    </div>
                    ` : ''}
                    ${galeriaHTML}
                </div>
            `;

         document.getElementById('detalleArticulo').innerHTML = detalleHTML;
         document.getElementById('modalVerArticulo').classList.add('active');
         document.body.style.overflow = 'hidden';
      }

      function verImagenCompleta(ruta) {
         window.open(ruta, '_blank');
      }

      function cerrarModalVer() {
         document.getElementById('modalVerArticulo').classList.remove('active');
         document.body.style.overflow = 'auto';
      }

      function editarArticulo(id) {
         const articulo = articulos.find(a => a.id_articulo == id);
         if (!articulo) return;

         // Llenar el formulario con los datos del artículo
         document.getElementById('edit_id_articulo').value = articulo.id_articulo;
         document.getElementById('edit_nombre').value = articulo.nombre;
         document.getElementById('edit_precio').value = articulo.precio;
         document.getElementById('edit_id_categoria').value = articulo.id_categoria || '';
         document.getElementById('edit_estado').value = articulo.estado;
         document.getElementById('edit_descripcion').value = articulo.descripcion || '';

         document.getElementById('modalEditarArticulo').classList.add('active');
         document.body.style.overflow = 'hidden';
      }

      function cerrarModalEditar() {
         document.getElementById('modalEditarArticulo').classList.remove('active');
         document.body.style.overflow = 'auto';
         document.getElementById('formEditarArticulo').reset();
         document.getElementById('editImagenesPreview').innerHTML = '';
      }

      function agregarFactura(id) {
         const articulo = articulos.find(a => a.id_articulo == id);
         if (!articulo) return;

         // Validar que no tenga ya una factura
         if (articulo.tiene_factura > 0) {
            alert('⚠️ Este artículo ya tiene una factura asociada');
            return;
         }

         document.getElementById('factura_id_articulo').value = articulo.id_articulo;
         document.getElementById('factura_nombre_articulo').value = articulo.nombre;
         document.getElementById('modalAgregarFactura').classList.add('active');
         document.body.style.overflow = 'hidden';
      }

      function cerrarModalFactura() {
         document.getElementById('modalAgregarFactura').classList.remove('active');
         document.body.style.overflow = 'auto';
         document.getElementById('formAgregarFactura').reset();
         document.getElementById('facturaModalPreview').innerHTML = '';
      }

      function previsualizarFacturaModal(event) {
         const file = event.target.files[0];
         const preview = document.getElementById('facturaModalPreview');

         if (!file) return;

         // Validar tipo de archivo
         if (file.type !== 'application/pdf') {
            alert('⚠️ Solo se permiten archivos PDF');
            event.target.value = '';
            return;
         }

         // Validar tamaño
         if (file.size > MAX_INVOICE_SIZE) {
            const tamanoMB = (file.size / 1024 / 1024).toFixed(2);
            alert(`❌ El archivo "${file.name}" (${tamanoMB} MB) excede el tamaño máximo permitido de ${MAX_INVOICE_SIZE_MB}MB.\n\nPor favor, selecciona un archivo más pequeño.`);
            event.target.value = '';
            return;
         }

         if (file && file.type === 'application/pdf') {
            preview.innerHTML = `
                    <div style="margin-top: 1rem; padding: 1rem; background: var(--background-alt); border-radius: 8px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="font-size: 1.5rem;">📄</span>
                            <div>
                                <div style="font-weight: 500; color: var(--text-primary);">${file.name}</div>
                                <div style="font-size: 0.875rem; color: var(--text-secondary);">${(file.size / 1024).toFixed(2)} KB</div>
                            </div>
                        </div>
                    </div>
                `;
         }
      }

      function verFactura(id) {
         const articulo = articulos.find(a => a.id_articulo == id);
         if (!articulo || !articulo.ruta_factura) {
            alert('❌ No se encontró la factura');
            return;
         }

         // Abrir factura en nueva ventana
         window.open(articulo.ruta_factura, '_blank');
      }

      function eliminarArticulo(id) {
         const articulo = articulos.find(a => a.id_articulo == id);
         if (!articulo) {
            Swal.fire({
               icon: 'error',
               title: 'No encontrado',
               text: 'No se encontró el artículo'
            });

            return;
         }
         Swal.fire({
            title: '¿Estás seguro?',
            html: `
        ¿Estás seguro de que quieres eliminar el artículo <b>"${articulo.nombre}"</b>?<br><br>
        <span style="color:#d33; font-weight:bold;">⚠️ Esta acción eliminará:</span><br>
        • El artículo<br>
        • Todas sus imágenes<br>
        • Su factura (si tiene)<br><br>
        <b>Esta acción no se puede deshacer.</b>
    `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33',
         }).then((result) => {
            if (result.isConfirmed) {
               // Crear FormData para enviar la petición
               const formData = new FormData();
               formData.append('accion', 'eliminar');
               formData.append('id_articulo', id);

               // Enviar petición al servidor
               fetch('procesar_articulo.php', {
                     method: 'POST',
                     body: formData
                  })
                  .then(response => response.text())
                  .then(text => {
                     console.log('Respuesta del servidor:', text);
                     try {
                        const data = JSON.parse(text);
                        if (data.success) {
                           Swal.fire({
                              icon: "success",
                              title: "Artículo actualizado",
                              text: "Artículo actualizado exitosamente"
                           });

                           location.reload();
                        } else {
                           Swal.fire({
                              icon: 'error',
                              title: 'No encontrado',
                              text: data.message
                           });

                        }
                     } catch (e) {
                        console.error('Error al parsear JSON:', e);
                        console.error('Respuesta recibida:', text);
                        Swal.fire({
                           icon: 'error',
                           title: 'Error',
                           text: 'El servidor no devolvio un valor válido '
                        });

                     }
                  })
                  .catch(error => {
                     console.error('Error:', error);
                     Swal.fire({
                        icon: 'error',
                        title: 'Error al eliminar el artículo',
                        text: error.message
                     });
                  });
            }
         });
      }


      function generarReporte() {
         // Confirmar antes de generar
         Swal.fire({
            title: '¿Deseas generar el reporte de tu inventario?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, generar',
            cancelButtonText: 'Cancelar'
         }).then((result) => {
            if (result.isConfirmed) {
               window.open('generar_reporte.php', '_blank');
            }
         });

      }

      function aplicarFiltros() {
         const categoria = document.getElementById('filterCategoria').value.toLowerCase();
         const estado = document.getElementById('filterEstado').value;
         const precioMin = parseFloat(document.getElementById('filterPrecioMin').value) || 0;
         const precioMax = parseFloat(document.getElementById('filterPrecioMax').value) || Infinity;

         articulosFiltrados = articulos.filter(articulo => {
            // Filtro de categoría
            if (categoria && articulo.categoriaNombre && !articulo.categoriaNombre.toLowerCase().includes(categoria)) {
               return false;
            }

            // Filtro de estado
            if (estado !== '' && articulo.estado != estado) {
               return false;
            }

            // Filtro de precio
            const precio = parseFloat(articulo.precio);
            if (precio < precioMin || precio > precioMax) {
               return false;
            }

            return true;
         });

         paginaActual = 1;
         mostrarArticulos();
      }

      function limpiarFiltros() {
         document.getElementById('filterCategoria').value = '';
         document.getElementById('filterEstado').value = '';
         document.getElementById('filterPrecioMin').value = '';
         document.getElementById('filterPrecioMax').value = '';
         document.getElementById('searchInput').value = '';
         cargarArticulos();
      }

      // Búsqueda en tiempo real
      document.getElementById('searchInput').addEventListener('input', function(e) {
         const searchTerm = e.target.value.toLowerCase();

         if (searchTerm === '') {
            cargarArticulos();
            return;
         }

         articulosFiltrados = articulos.filter(articulo => {
            return articulo.nombre.toLowerCase().includes(searchTerm) ||
               (articulo.descripcion && articulo.descripcion.toLowerCase().includes(searchTerm)) ||
               (articulo.categoriaNombre && articulo.categoriaNombre.toLowerCase().includes(searchTerm));
         });

         paginaActual = 1;
         mostrarArticulos();
      });

      // Cargar artículos al iniciar
      document.addEventListener('DOMContentLoaded', cargarArticulos);
   </script>
</body>

</html>