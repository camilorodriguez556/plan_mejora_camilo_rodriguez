<?php

require_once(__DIR__ . "/connection/connect.php");
require_once(__DIR__ . "/functions/funciones.php");

$database = new Database();
$conexion = $database->conectar();

// Actualiza los estados de forma automática al cargar la página
actualizarEstados();

/*
|--------------------------------------------------------------------------
| GUARDAR USUARIO
|--------------------------------------------------------------------------
*/
if (isset($_POST["guardar_usuario"])) {
    guardarUsuario(
        $_POST["nombre"],
        $_POST["cedula"],
        $_POST["telefono"],
        $_POST["correo"]
    );
    header("Location: index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| GUARDAR RESERVA (Unificado para capturar id_cancha correctamente)
|--------------------------------------------------------------------------
*/
if (isset($_POST["guardar_reserva"])) {
    guardarReserva(
        $_POST["usuario"],
        $_POST["cancha"],
        $_POST["fecha"],
        $_POST["hora_inicio"],
        $_POST["hora_fin"]
    );
    header("Location: index.php");
    exit();
}

/*
|--------------------------------------------------------------------------
| ACCIÓN: ELIMINAR RESERVA (Captura el envío del ícono de basura)
|--------------------------------------------------------------------------
*/
if (isset($_POST["accion_eliminar_reserva"])) {
    eliminarReserva($_POST["id_reserva"]);
    header("Location: index.php");
    exit();
}

// Recarga de colecciones actualizadas
$usuarios = listarUsuarios();
$canchas = listarCanchas();
$reservas = listarReservas();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservas Deportivas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
<div class="fondo py-5">
    <div class="container">
        <h1 class="text-center text-white mb-4">Sistema de Reservas Deportivas</h1>
        
        <div class="row g-4 justify-content-center">
            <div class="col-md-4">
                <div class="card p-3 shadow-sm h-100">
                    <h4 class="mb-3 text-secondary">Registrar Usuario</h4>
                    <form method="POST">
                        <input class="form-control mb-2" type="text" name="nombre" placeholder="Nombre" required>
                        <input class="form-control mb-2" type="number" name="cedula" placeholder="Cédula" required> 
                        <input class="form-control mb-2" type="number" name="telefono" placeholder="Teléfono" required>
                        <input class="form-control mb-2" type="email" name="correo" placeholder="Correo">
                        <button class="btn btn-success w-100 mt-2" name="guardar_usuario">
                            Guardar Usuario
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card p-3 shadow-sm h-100">
                    <h4 class="mb-3 text-secondary">Registrar Nueva Reserva</h4>
                    <form method="POST">
                        
                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-bold">Seleccione el Usuario:</label>
                            <select class="form-select" name="usuario" required>
                                <option value="" selected disabled>-- Seleccionar Usuario --</option>
                                <?php foreach ($usuarios as $u): ?>
                                    <option value="<?= $u['id_usuario']; ?>">
                                        <?= htmlspecialchars($u['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-bold">Seleccione la Cancha:</label>
                            <select class="form-select" name="cancha" required>
                                <option value="" selected disabled>-- Seleccionar Cancha --</option>
                                <?php foreach ($canchas as $c): ?>
                                    <option value="<?= $c['id_cancha']; ?>">
                                        <?= htmlspecialchars($c['nombre']); ?> ($<?= number_format($c['precio_hora'], 0, ',', '.'); ?>/h)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small mb-1 fw-bold">Fecha del Evento:</label>
                            <input class="form-control" type="date" name="fecha" required>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small mb-1 fw-bold">Hora Inicio:</label>
                                <input class="form-control" type="time" name="hora_inicio" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small mb-1 fw-bold">Hora Fin:</label>
                                <input class="form-control" type="time" name="hora_fin" required>
                            </div>
                        </div>

                        <button class="btn btn-primary w-100 mt-2" name="guardar_reserva">
                            Reservar Cancha
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="card mt-5 p-3 shadow-sm">
            <h3 class="mb-3 text-secondary">Listado de Reservas</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Cancha</th>
                            <th>Fecha</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Precio/h</th>
                            <th>Total Pago</th>
                            <th>Estado</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($reservas)): ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-3">No hay reservas registradas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reservas as $reserva): 
                                // Cálculo automático del costo
                                $inicio = new DateTime($reserva['hora_inicio']);
                                $fin = new DateTime($reserva['hora_fin']);
                                $diferencia = $inicio->diff($fin);
                                
                                $horas_totales = $diferencia->h + ($diferencia->i / 60);
                                if ($horas_totales <= 0) { $horas_totales = 1; }

                                $precio_por_hora = (!empty($reserva['precio_hora'])) ? $reserva['precio_hora'] : 0; 
                                $total_pago = $horas_totales * $precio_por_hora;
                            ?>
                            <tr>
                                <td><strong>#<?= $reserva['id_reserva']; ?></strong></td>
                                <td><?= htmlspecialchars($reserva['usuario']); ?></td>
                                <td><?= htmlspecialchars($reserva['cancha']); ?></td>
                                <td><?= date("d/m/Y", strtotime($reserva['fecha_reserva'])); ?></td>
                                <td><?= date("g:i a", strtotime($reserva['hora_inicio'])); ?></td>
                                <td><?= date("g:i a", strtotime($reserva['hora_fin'])); ?></td>
                                <td>$<?= number_format($precio_por_hora, 0, ',', '.'); ?></td>
                                <td class="text-success fw-bold">$<?= number_format($total_pago, 0, ',', '.'); ?></td>
                                <td>
                                    <?php if($reserva['estado'] == 'Finalizada'): ?>
                                        <span class="badge bg-secondary">Finalizada</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Activa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <form method="POST" onsubmit="return confirm('¿Estás seguro de que deseas eliminar esta reserva por completo?');">
                                        <input type="hidden" name="id_reserva" value="<?= $reserva['id_reserva']; ?>">
                                        <button type="submit" name="accion_eliminar_reserva" class="btn btn-danger btn-sm" title="Eliminar Reserva">
                                            <i class="bi bi-trash3-fill"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
</body>
</html>