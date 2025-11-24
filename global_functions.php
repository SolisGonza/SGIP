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

// Función para ejecutar consultas
function ejecutarQuery($sql)
{
    global $link;
    $result = mysqli_query($link, $sql);
    if (!$result) {
        die("Error en consulta: " . mysqli_error($link) . " - SQL: " . $sql);
    }
    return $result;
}

// Función para consultas preparadas 
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

// Función para limpiar y sanitizar datos
function limpiarDato($dato)
{
    global $link;
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return mysqli_real_escape_string($link, $dato);
}

// Función para obtener el último ID insertado
function obtenerUltimoId()
{
    global $link;
    return mysqli_insert_id($link);
}

// Función para contar filas afectadas
function filasAfectadas()
{
    global $link;
    return mysqli_affected_rows($link);
}

// Función para verificar si un usuario existe
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


// Cerrar conexión al final del script
register_shutdown_function(function () {
    global $link;
    if ($link) {
        mysqli_close($link);
    }
});

function registrarUsuario($nombre, $correo, $password, $rol = 'usuario', $estado = 1)
{
    global $link;

    // Encriptar contraseña
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    // Consulta preparada
    $sql = "INSERT INTO usuario (nombre, correo, contraseña, rol, estado)
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return false;
    }

    // Bind (s = string, i = integer)
    $stmt->bind_param("ssssi", $nombre, $correo, $passwordHash, $rol, $estado);

    return $stmt->execute();
}


function actualizarUsuario($nombre, $correo, $rol, $estado, $id_usuario)
{
    global $link;

    // Consulta preparada
    $sql = "UPDATE usuario SET nombre = ?, correo = ?, rol = ?, estado = ? WHERE id_usuario = ?";

    $stmt = $link->prepare($sql);
    if (!$stmt) {
        return false;
    }

    // Bind (s = string, i = integer)
    $stmt->bind_param("sssii", $nombre, $correo, $rol, $estado, $id_usuario);

    return $stmt->execute();
}

function validar_usuario($email, $password)
{

    // Verificar si los campos están vacíos
    if (empty($email) || empty($password)) {
        header("Location: login.php?error=campos_vacios");
        exit();
    }

    // Consulta preparada para mayor seguridad
    $sql = "SELECT id_usuario, correo, contraseña, nombre, rol FROM usuario WHERE correo = ?";
    $result = ejecutarConsultaPreparada($sql, "s", [$email]);

    if ($row = mysqli_fetch_assoc($result)) {
        // Verificar contraseña
        if (password_verify($password, $row['contraseña'])) {
            // Iniciar sesión
            session_start();
            $_SESSION["id_usuario"] = $row['id_usuario'];
            $_SESSION["correo"] = $row['correo'];
            $_SESSION["nombre"] = $row['nombre'];
            $_SESSION["rol"] = $row['rol'];
            $_SESSION["loggedin"] = true;

            // Redirigir según el rol
            if ($row['rol'] == 'admin') {
                header("Location: app/admin/index.php");
            } else {
                header("Location: app/usuarios/inventario.php");
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
