<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';

    header('Content-Type: application/json');

    // Validar que el usuario tenga permisos de administrador
    if ($_SESSION['rol'] !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
        exit;
    }

    $data = json_decode(file_get_contents('php://input'));

    $nombre = $data->nombre;
    $correo = $data->correo;
    $password = $data->password;
    $rol = $data->rol;
    $estado = $data->estado;


    // Validar que el rol sea válido
    if (!in_array($rol, ['admin', 'usuario'])) {
        echo json_encode(['success' => false, 'message' => 'Rol no válido']);
        exit;
    }

    // Validar que el estado sea válido
    $estado = $estado == '1' ? 1 : 0;
    try {
        if (usuarioExiste($correo)) {
            echo json_encode(['success' => false, 'message' => 'El correo electrónico ya está en uso']);
            exit;
        }
        if (registrarUsuario($nombre, $correo, $password, $rol, $estado )) {
            echo json_encode(['success' => true, 'message' => 'Usuario creado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al crear el usuario en la base de datos']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error del sistema: ' . $e->getMessage()]);
    }
