<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';

    header('Content-Type: application/json');

    // Validar que el usuario tenga permisos de administrador
    if ($_SESSION['rol'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
        exit;
    }
    $data = json_decode(file_get_contents("php://input"));
    $id_usuario = $data->id_usuario;
    $nombre = $data->nombre;
    $correo = $data->correo;
    $rol = $data->rol;
    $estado = $data->estado;


    // Validaciones básicas
    if (empty($id_usuario) || empty($nombre) || empty($correo)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        exit;
    }

    // Validar que el estado sea válido
    $estado = $estado == '1' ? 1 : 0;

    try {
        if (actualizarUsuario($nombre, $correo, $rol, $estado, $id_usuario )) {
            echo json_encode(['success' => true, 'message' => 'Usuario actulizado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario en la base de datos']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()]);
    }
?>