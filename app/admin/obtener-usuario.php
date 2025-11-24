
<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';

    header('Content-Type: application/json');

    // Validar que el usuario tenga permisos de administrador
    if ($_SESSION['rol'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
        exit;
    }
    $id_usuario = json_decode(file_get_contents("php://input"));

    // Validacion
    if (empty($id_usuario)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        exit;
    }

    try {
        $data = obtenerUsuario($id_usuario);
        if (!empty($id_usuario)) {
            echo json_encode($data);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al obtener los datos del usuario']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()]);
    }
?>