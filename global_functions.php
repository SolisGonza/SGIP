<?php

// global-function.php - Archivo de configuración global para SGIP

// Configuración de la base de datos
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "sgip";

// Establecer conexión
$link = mysqli_connect($db_host, $db_user, $db_pass);

// Verificar conexión al servidor
if (!$link) {
    die("Error: No se puede conectar con el servidor - " . mysqli_connect_error());
}

// Seleccionar base de datos
if (!mysqli_select_db($link, $db_name)) {
    die("Error: No se puede seleccionar la base de datos - " . mysqli_error($link));
}

// Establecer charset utf8
mysqli_set_charset($link, "utf8");

/**
 * Ejecuta una consulta SQL directa
 * 
 * @param string $sql Consulta SQL a ejecutar
 * @return mysqli_result Resultado de la consulta
 */
function ejecutarQuery($sql)
{
    global $link;
    $result = mysqli_query($link, $sql);
    if (!$result) {
        die("Error en consulta: " . mysqli_error($link) . " - SQL: " . $sql);
    }
    return $result;
}

/**
 * Ejecuta una consulta preparada
 * 
 * @param string $sql Consulta SQL con placeholders
 * @param string $tipos Tipos de datos para bind_param
 * @param array $parametros Valores a vincular
 * @return mysqli_result Resultado de la consulta
 */
function ejecutarConsultaPreparada($sql, $tipos = "", $parametros = [])
{
    global $link;
    $stmt = mysqli_prepare($link, $sql);

    if (!$stmt) {
        die("Error en preparación: " . mysqli_error($link));
    }

    if (!empty($tipos) && !empty($parametros)) {
        mysqli_stmt_bind_param($stmt, $tipos, ...$parametros);
    }

    if (!mysqli_stmt_execute($stmt)) {
        die("Error en ejecución: " . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    mysqli_stmt_close($stmt);

    return $result;
}

/**
 * Limpia y sanitiza un dato
 * 
 * @param string $dato Dato a limpiar
 * @return string Dato limpio y escapado
 */
function limpiarDato($dato)
{
    global $link;
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return mysqli_real_escape_string($link, $dato);
}

/**
 * Obtiene el último ID insertado en la base de datos
 * 
 * @return int Último ID insertado
 */
function obtenerUltimoId()
{
    global $link;
    return mysqli_insert_id($link);
}

/**
 * Retorna la cantidad de filas afectadas por la última operación
 * 
 * @return int Número de filas afectadas
 */
function filasAfectadas()
{
    global $link;
    return mysqli_affected_rows($link);
}

/**
 * Verifica si un usuario existe por su correo
 * 
 * @param string $email Correo del usuario
 * @return bool True si existe, false si no
 */
function usuarioExiste($email)
{
    $email = limpiarDato($email);
    $sql = "SELECT id_usuario FROM usuario WHERE correo = '$email'";
    $result = ejecutarQuery($sql);
    return mysqli_num_rows($result) > 0;
}

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Registra un nuevo usuario usando procedimiento almacenado
 * 
 * @param string $nombre Nombre del usuario
 * @param string $correo Correo del usuario
 * @param string $password Contraseña del usuario
 * @param string $rol Rol del usuario (por defecto 'usuario')
 * @param int $estado Estado del usuario (por defecto 1)
 * @return bool True si se ejecutó correctamente, false si hubo error
 */
function registrarUsuario($nombre, $correo, $password, $rol = 'usuario', $estado = 1)
{
    global $link;
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "CALL registrarUsuario('$nombre', '$correo', '$passwordHash', '$rol', $estado)";
    return mysqli_query($link, $sql);
}

/**
 * Actualiza un usuario existente usando procedimiento almacenado
 * 
 * @param string $nombre Nombre del usuario
 * @param string $correo Correo del usuario
 * @param string $rol Rol del usuario
 * @param int $estado Estado del usuario
 * @param int $id_usuario ID del usuario a actualizar
 * @return bool True si se ejecutó correctamente, false si hubo error
 */
function actualizarUsuario($nombre, $correo, $rol, $estado, $id_usuario)
{
    global $link;
    $sql = "CALL actualizarUsuario('$nombre', '$correo', '$rol', $estado, $id_usuario)";
    return $link->query($sql);
}

/**
 * Obtiene los datos de un usuario
 * 
 * @param int $id_usuario ID del usuario
 * @return array Array con 'status' y 'data' (datos del usuario o null)
 */
function obtenerUsuario($id_usuario)
{
    global $link;

    $sql = "SELECT * FROM usuario WHERE id_usuario = ?";
    $stmt = $link->prepare($sql);
    $stmt->bind_param("i", $id_usuario);

    if (!$stmt->execute()) {
        $stmt->close();
        return [
            "status" => "error",
            "message" => "Error al ejecutar consulta: " . $stmt->error
        ];
    }

    $query = $stmt->get_result();
    $usuario = $query->fetch_assoc();
    $stmt->close();

    if (!$usuario) {
        return [
            "status" => "success",
            "data" => null,
            "message" => "Usuario no encontrado"
        ];
    }

    return [
        "status" => "success",
        "data" => $usuario
    ];
}

/**
 * Valida las credenciales de un usuario
 * 
 * @param string $email Correo del usuario
 * @param string $password Contraseña ingresada
 * @return void Redirige según el resultado
 */
function validar_usuario($email, $password)
{
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=campos_vacios");
        exit();
    }

    $sql = "SELECT id_usuario, correo, contraseña, nombre, rol FROM usuario WHERE correo = ?";
    $result = ejecutarConsultaPreparada($sql, "s", [$email]);

    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['contraseña'])) {
            session_start();
            $_SESSION["id_usuario"] = $row['id_usuario'];
            $_SESSION["correo"] = $row['correo'];
            $_SESSION["nombre"] = $row['nombre'];
            $_SESSION["rol"] = $row['rol'];
            $_SESSION["loggedin"] = true;

            if ($row['rol'] == 'admin') {
                header("Location: app/admin/index.php");
            } else {
                header("Location: app/usuarios/index.php");
            }
            exit();
        } else {
            header("Location: login.php?error=password_incorrecto");
            exit();
        }
    } else {
        header("Location: login.php?error=usuario_no_existe");
        exit();
    }
}

/**
 * Valida el rol del usuario actual
 * 
 * @param string $rol Rol requerido
 * @return void Cierra sesión y redirige si no coincide
 */
function validar_rol($rol)
{
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== $rol) {
        session_unset();
        session_destroy();
        header('Location: /SGIP/index.php?missing_permissions=1', true, 302);
        exit;
    }
}

// Cerrar conexión al final del script
register_shutdown_function(function () {
    global $link;
    if ($link) {
        mysqli_close($link);
    }
});
