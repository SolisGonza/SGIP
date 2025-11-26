<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/validar_sesion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idUsuario = $_SESSION['id_usuario'];
    
    // Verificar si es una edición, inserción o agregar factura
    $accion = $_POST['accion'] ?? 'agregar';
    
    // Rutas de almacenamiento
    $rutaImagenes = $_SERVER['DOCUMENT_ROOT'] . '/SGIP/uploads/articulos/imagenes/';
    $rutaFacturas = $_SERVER['DOCUMENT_ROOT'] . '/SGIP/uploads/articulos/facturas/';
    
    // Crear carpetas si no existen
    if (!file_exists($rutaImagenes)) {
        mkdir($rutaImagenes, 0777, true);
    }
    if (!file_exists($rutaFacturas)) {
        mkdir($rutaFacturas, 0777, true);
    }
    
    try {
        // Si es eliminar artículo
        if ($accion === 'eliminar') {
            $idArticulo = intval($_POST['id_articulo']);
            
            // Verificar que el artículo pertenece al usuario
            $queryVerificar = "SELECT id_articulo FROM articulo WHERE id_articulo = $idArticulo AND id_usuario = $idUsuario";
            $resultVerificar = mysqli_query($link, $queryVerificar);
            
            if (mysqli_num_rows($resultVerificar) === 0) {
                throw new Exception("No tienes permiso para eliminar este artículo");
            }
            
            // Obtener las rutas de las imágenes y facturas para eliminarlas físicamente
            $queryImagenes = "SELECT ruta_imagen FROM imagenes WHERE id_articulo = $idArticulo";
            $resultImagenes = mysqli_query($link, $queryImagenes);
            $rutasImagenes = [];
            while ($row = mysqli_fetch_assoc($resultImagenes)) {
                $rutasImagenes[] = $row['ruta_imagen'];
            }
            
            $queryFacturas = "SELECT ruta_factura FROM facturas WHERE id_articulo = $idArticulo";
            $resultFacturas = mysqli_query($link, $queryFacturas);
            $rutasFacturas = [];
            while ($row = mysqli_fetch_assoc($resultFacturas)) {
                $rutasFacturas[] = $row['ruta_factura'];
            }
            
            // Iniciar transacción
            mysqli_begin_transaction($link);
            
            try {
                // Eliminar registros de imágenes
                $queryEliminarImagenes = "DELETE FROM imagenes WHERE id_articulo = $idArticulo";
                mysqli_query($link, $queryEliminarImagenes);
                
                // Eliminar registros de facturas
                $queryEliminarFacturas = "DELETE FROM facturas WHERE id_articulo = $idArticulo";
                mysqli_query($link, $queryEliminarFacturas);
                
                // Eliminar el artículo
                $queryEliminarArticulo = "DELETE FROM articulo WHERE id_articulo = $idArticulo";
                mysqli_query($link, $queryEliminarArticulo);
                
                // Confirmar transacción
                mysqli_commit($link);
                
                // Eliminar archivos físicos después de confirmar la transacción
                foreach ($rutasImagenes as $rutaImagen) {
                    $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . str_replace('/SGIP/', '/SGIP/', parse_url($rutaImagen, PHP_URL_PATH));
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }
                }
                
                foreach ($rutasFacturas as $rutaFactura) {
                    $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . str_replace('/SGIP/', '/SGIP/', parse_url($rutaFactura, PHP_URL_PATH));
                    if (file_exists($rutaCompleta)) {
                        unlink($rutaCompleta);
                    }
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Artículo eliminado exitosamente'
                ]);
                exit;
                
            } catch (Exception $e) {
                mysqli_rollback($link);
                throw new Exception("Error al eliminar el artículo: " . $e->getMessage());
            }
        }
        
        // Si es agregar factura a un artículo existente
        if ($accion === 'agregar_factura') {
            $idArticulo = intval($_POST['id_articulo']);
            
            // Verificar que el artículo pertenece al usuario
            $queryVerificar = "SELECT id_articulo FROM articulo WHERE id_articulo = $idArticulo AND id_usuario = $idUsuario";
            $resultVerificar = mysqli_query($link, $queryVerificar);
            
            if (mysqli_num_rows($resultVerificar) === 0) {
                throw new Exception("No tienes permiso para agregar factura a este artículo");
            }
            
            // Verificar si ya tiene factura
            $queryFacturaExistente = "SELECT id_factura FROM facturas WHERE id_articulo = $idArticulo";
            $resultFacturaExistente = mysqli_query($link, $queryFacturaExistente);
            
            if (mysqli_num_rows($resultFacturaExistente) > 0) {
                throw new Exception("Este artículo ya tiene una factura asociada");
            }
            
            // Procesar factura
            if (isset($_FILES['factura']) && $_FILES['factura']['error'] === UPLOAD_ERR_OK) {
                $nombreOriginal = $_FILES['factura']['name'];
                $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                $nombreNuevo = 'factura_' . $idArticulo . '_' . time() . '.' . $extension;
                $rutaCompleta = $rutaFacturas . $nombreNuevo;
                
                // Validar que sea PDF
                if (strtolower($extension) !== 'pdf') {
                    throw new Exception("Solo se permiten archivos PDF");
                }
                
                // Validar tamaño (10MB máximo)
                if ($_FILES['factura']['size'] > 10 * 1024 * 1024) {
                    throw new Exception("El archivo es demasiado grande (máx. 10MB)");
                }
                
                if (move_uploaded_file($_FILES['factura']['tmp_name'], $rutaCompleta)) {
                    $rutaRelativa = '/SGIP/uploads/articulos/facturas/' . $nombreNuevo;
                    
                    // Insertar factura en la tabla facturas
                    $queryFactura = "INSERT INTO facturas (fecha, ruta_factura, id_articulo) 
                                    VALUES (CURDATE(), '$rutaRelativa', $idArticulo)";
                    
                    if (!mysqli_query($link, $queryFactura)) {
                        throw new Exception("Error al guardar la factura en la base de datos");
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Factura agregada exitosamente',
                        'id_articulo' => $idArticulo
                    ]);
                } else {
                    throw new Exception("Error al subir el archivo");
                }
            } else {
                throw new Exception("No se recibió ningún archivo o hubo un error al subirlo");
            }
            
            exit;
        }
        
        // Obtener datos del formulario
        $nombre = mysqli_real_escape_string($link, $_POST['nombre']);
        $descripcion = mysqli_real_escape_string($link, $_POST['descripcion'] ?? '');
        $precio = floatval($_POST['precio']);
        $idCategoria = intval($_POST['id_categoria']);
        $estado = intval($_POST['estado']);
    
        // Si es edición
        if ($accion === 'editar') {
            $idArticulo = intval($_POST['id_articulo']);
            
            // Verificar que el artículo pertenece al usuario
            $queryVerificar = "SELECT id_articulo FROM articulo WHERE id_articulo = $idArticulo AND id_usuario = $idUsuario";
            $resultVerificar = mysqli_query($link, $queryVerificar);
            
            if (mysqli_num_rows($resultVerificar) === 0) {
                throw new Exception("No tienes permiso para editar este artículo");
            }
            
            // Actualizar artículo
            $query = "UPDATE articulo SET 
                     nombre = '$nombre',
                     descripcion = '$descripcion',
                     precio = $precio,
                     id_categoria = $idCategoria,
                     estado = $estado
                     WHERE id_articulo = $idArticulo AND id_usuario = $idUsuario";
            
            if (!mysqli_query($link, $query)) {
                throw new Exception("Error al actualizar artículo: " . mysqli_error($link));
            }
            
            // Procesar nuevas imágenes si se agregaron
            $imagenesGuardadas = 0;
            if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
                foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                    if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                        $nombreOriginal = $_FILES['imagenes']['name'][$key];
                        $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                        $nombreNuevo = 'articulo_' . $idArticulo . '_img_' . uniqid() . '_' . $key . '.' . $extension;
                        $rutaCompleta = $rutaImagenes . $nombreNuevo;
                        
                        // Validar tipo de archivo
                        $tiposPermitidos = ['jpg', 'jpeg', 'png'];
                        if (!in_array(strtolower($extension), $tiposPermitidos)) {
                            continue;
                        }
                        
                        // Validar tamaño (5MB máximo)
                        if ($_FILES['imagenes']['size'][$key] > 5 * 1024 * 1024) {
                            continue;
                        }
                        
                        if (move_uploaded_file($tmpName, $rutaCompleta)) {
                            $rutaRelativa = '/SGIP/uploads/articulos/imagenes/' . $nombreNuevo;
                            
                            // Guardar en base de datos
                            $queryImagen = "INSERT INTO imagenes (id_articulo, ruta_imagen, nombre_archivo) 
                                           VALUES ($idArticulo, '$rutaRelativa', '$nombreNuevo')";
                            if (mysqli_query($link, $queryImagen)) {
                                $imagenesGuardadas++;
                            }
                        }
                    }
                }
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Artículo actualizado exitosamente',
                'id_articulo' => $idArticulo,
                'imagenes_agregadas' => $imagenesGuardadas
            ]);
            
        } else {
            // Insertar nuevo artículo
            $query = "INSERT INTO articulo (nombre, descripcion, precio, id_categoria, estado, id_usuario, fecha_registro) 
                      VALUES ('$nombre', '$descripcion', $precio, $idCategoria, '$estado', $idUsuario, NOW())";
            
            if (!mysqli_query($link, $query)) {
                throw new Exception("Error al insertar artículo: " . mysqli_error($link));
            }
            
            $idArticulo = mysqli_insert_id($link);
        
        // Procesar imágenes
        $imagenesGuardadas = [];
        
        // Debug: registrar información de $_FILES
        error_log("=== DEBUG IMAGENES ===");
        error_log("isset imagenes: " . (isset($_FILES['imagenes']) ? 'SI' : 'NO'));
        if (isset($_FILES['imagenes'])) {
            error_log("Nombre[0]: " . (isset($_FILES['imagenes']['name'][0]) ? $_FILES['imagenes']['name'][0] : 'VACIO'));
            error_log("Total archivos: " . count($_FILES['imagenes']['name']));
            error_log("Nombres: " . print_r($_FILES['imagenes']['name'], true));
        }
        
        if (isset($_FILES['imagenes']) && !empty($_FILES['imagenes']['name'][0])) {
            foreach ($_FILES['imagenes']['tmp_name'] as $key => $tmpName) {
                error_log("Procesando imagen key=$key, nombre=" . $_FILES['imagenes']['name'][$key]);
                if ($_FILES['imagenes']['error'][$key] === UPLOAD_ERR_OK) {
                    $nombreOriginal = $_FILES['imagenes']['name'][$key];
                    $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
                    $nombreNuevo = 'articulo_' . $idArticulo . '_img_' . uniqid() . '_' . $key . '.' . $extension;
                    $rutaCompleta = $rutaImagenes . $nombreNuevo;
                    
                    error_log("Nombre nuevo generado: $nombreNuevo");
                    error_log("Ruta completa: $rutaCompleta");
                    
                    // Validar tipo de archivo
                    $tiposPermitidos = ['jpg', 'jpeg', 'png'];
                    if (!in_array(strtolower($extension), $tiposPermitidos)) {
                        error_log("Imagen rechazada: extensión no permitida ($extension)");
                        continue;
                    }
                    
                    // Validar tamaño (5MB máximo)
                    if ($_FILES['imagenes']['size'][$key] > 5 * 1024 * 1024) {
                        error_log("Imagen rechazada: tamaño excede 5MB");
                        continue;
                    }
                    
                    if (move_uploaded_file($tmpName, $rutaCompleta)) {
                        error_log("Archivo movido exitosamente");
                        $rutaRelativa = '/SGIP/uploads/articulos/imagenes/' . $nombreNuevo;
                        
                        // Guardar en base de datos (tabla: imagenes)
                        $queryImagen = "INSERT INTO imagenes (id_articulo, ruta_imagen, nombre_archivo) 
                                       VALUES ($idArticulo, '$rutaRelativa', '$nombreNuevo')";
                        $resultInsert = mysqli_query($link, $queryImagen);
                        
                        if ($resultInsert) {
                            error_log("Imagen guardada en BD exitosamente");
                            $imagenesGuardadas[] = $rutaRelativa;
                        } else {
                            error_log("ERROR al insertar en BD: " . mysqli_error($link));
                        }
                    } else {
                        error_log("ERROR al mover archivo de $tmpName a $rutaCompleta");
                    }
                } else {
                    error_log("Error en upload: " . $_FILES['imagenes']['error'][$key]);
                }
            }
        }
        
        // Procesar factura
        $facturaGuardada = false;
        if (isset($_FILES['factura']) && $_FILES['factura']['error'] === UPLOAD_ERR_OK) {
            $nombreOriginal = $_FILES['factura']['name'];
            $extension = pathinfo($nombreOriginal, PATHINFO_EXTENSION);
            $nombreNuevo = 'factura_' . $idArticulo . '_' . time() . '.' . $extension;
            $rutaCompleta = $rutaFacturas . $nombreNuevo;
            
            // Validar que sea PDF
            if (strtolower($extension) === 'pdf' && $_FILES['factura']['size'] <= 10 * 1024 * 1024) {
                if (move_uploaded_file($_FILES['factura']['tmp_name'], $rutaCompleta)) {
                    $rutaRelativa = '/SGIP/uploads/articulos/facturas/' . $nombreNuevo;
                    
                    // Insertar factura en la tabla facturas
                    $queryFactura = "INSERT INTO facturas (fecha, ruta_factura, id_articulo) 
                                    VALUES (CURDATE(), '$rutaRelativa', $idArticulo)";
                    mysqli_query($link, $queryFactura);
                    $facturaGuardada = true;
                }
            }
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Artículo guardado exitosamente',
            'id_articulo' => $idArticulo,
            'imagenes_guardadas' => count($imagenesGuardadas),
            'factura_guardada' => $facturaGuardada
        ]);
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Método no permitido'
    ]);
}
?>
