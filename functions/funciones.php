<?php

/*
|--------------------------------------------------------------------------
| INSERTAR USUARIO
|--------------------------------------------------------------------------
*/

function guardarUsuario($nombre, $cedula, $telefono, $correo)
{
    global $conexion;

    $sql = "INSERT INTO usuarios
    (nombre, cedula, telefono, correo)
    VALUES (?, ?, ?, ?)";

    $consulta = $conexion->prepare($sql);

    return $consulta->execute([
        $nombre,
        $cedula,
        $telefono,
        $correo
    ]);
}

/*
|--------------------------------------------------------------------------
| INSERTAR CANCHA
|--------------------------------------------------------------------------
*/

function guardarCancha($nombre, $tipo, $precio)
{
    global $conexion;

    $sql = "INSERT INTO canchas
    (nombre, tipo, precio_hora)
    VALUES (?, ?, ?)";

    $consulta = $conexion->prepare($sql);

    return $consulta->execute([
        $nombre,
        $tipo,
        $precio
    ]);
}

/*
|--------------------------------------------------------------------------
| INSERTAR RESERVA
|--------------------------------------------------------------------------
*/

function guardarReserva(
    $usuario,
    $cancha,
    $fecha,
    $hora_inicio,
    $hora_fin
) {

    global $conexion;

    $sql = "INSERT INTO reservas
    (
        id_usuario,
        id_cancha,
        fecha_reserva,
        hora_inicio,
        hora_fin,
        estado
    )
    VALUES (?, ?, ?, ?, ?, 'Pendiente')";

    $consulta = $conexion->prepare($sql);

    return $consulta->execute([
        $usuario,
        $cancha,
        $fecha,
        $hora_inicio,
        $hora_fin
    ]);
}

/*
|--------------------------------------------------------------------------
| LISTAR RESERVAS
|--------------------------------------------------------------------------
*/

function listarReservas()
{
    global $conexion;

    $sql = "
        SELECT
        reservas.id_reserva,
        usuarios.nombre AS usuario,
        canchas.nombre AS cancha,
        canchas.precio_hora,
        reservas.fecha_reserva,
        reservas.hora_inicio,
        reservas.hora_fin,
        reservas.estado
        FROM reservas
        INNER JOIN usuarios
        ON reservas.id_usuario = usuarios.id_usuario
        INNER JOIN canchas
        ON reservas.id_cancha = canchas.id_cancha
        ORDER BY reservas.id_reserva DESC
    ";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| LISTAR USUARIOS
|--------------------------------------------------------------------------
*/

function listarUsuarios()
{
    global $conexion;

    $sql = "SELECT * FROM usuarios";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| LISTAR CANCHAS
|--------------------------------------------------------------------------
*/

function listarCanchas()
{
    global $conexion;

    $sql = "SELECT * FROM canchas";

    $consulta = $conexion->prepare($sql);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

/*
|--------------------------------------------------------------------------
| ACTUALIZAR RESERVAS FINALIZADAS AUTOMÁTICAMENTE
|--------------------------------------------------------------------------
*/

function actualizarEstados()
{
    global $conexion;

    date_default_timezone_set("America/Bogota");

    $horaActual = date("H:i:s");
    $fechaActual = date("Y-m-d");

    $sql = "
        UPDATE reservas
        SET estado = 'Finalizada'
        WHERE fecha_reserva = ?
        AND hora_fin < ?
    ";

    $consulta = $conexion->prepare($sql);
    $consulta->execute([
        $fechaActual,
        $horaActual
    ]);
}

/*
|--------------------------------------------------------------------------
| ELIMINAR RESERVA (NUEVA FUNCIÓN)
|--------------------------------------------------------------------------
*/
function eliminarReserva($id_reserva)
{
    global $conexion;

    $sql = "DELETE FROM reservas WHERE id_reserva = ?";
    $consulta = $conexion->prepare($sql);
    
    return $consulta->execute([$id_reserva]);
}