<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . '/SGIP/global_functions.php';
    // Obtener entrada JSON
    $id = json_decode(file_get_contents("php://input"));

    $query = $link->query("SELECT * FROM usuario WHERE id_usuario = $id");

    if ($query && $query->num_rows > 0) {
        echo json_encode([
            "status" => "success",
            "data" => $query->fetch_assoc()
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Usuario no encontrado"
        ]);
    }
