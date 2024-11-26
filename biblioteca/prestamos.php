<?php
include 'includes/db_connection.php';
include 'includes/functions.php';
verificar_sesion();

// Obtener el ID del administrador logueado
$id_admin = $_SESSION['usuario_id'];

// Función para limpiar y validar los datos de entrada
function limpiar_dato($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

$mensaje = '';
$error = '';

// Función para obtener la cantidad total de un libro
function obtener_cantidad_total($pdo, $libro_id) {
    $sql = "SELECT Cantidad_Libro FROM Libro WHERE idLibro = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$libro_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? intval($result['Cantidad_Libro']) : 0;
}

// Función para obtener la cantidad prestada de un libro
function obtener_cantidad_prestada($pdo, $libro_id, $prestamo_actual_id = null) {
    $sql = "SELECT SUM(Cantidad) AS total_prestado FROM Detalle_Prestamo WHERE Libro_idLibro = ?";
    $params = [$libro_id];
    
    if ($prestamo_actual_id) {
        $sql .= " AND Prestamo_id_Prestamo != ?";
        $params[] = $prestamo_actual_id;
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? intval($result['total_prestado']) : 0;
}

// Función para obtener la cantidad disponible de un libro
function obtener_cantidad_disponible($pdo, $libro_id, $prestamo_actual_id = null) {
    $cantidad_total = obtener_cantidad_total($pdo, $libro_id);
    $cantidad_prestada = obtener_cantidad_prestada($pdo, $libro_id, $prestamo_actual_id);
    return $cantidad_total - $cantidad_prestada;
}

// Crear o actualizar préstamo
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_prestamo = isset($_POST['id_prestamo']) ? $_POST['id_prestamo'] : null;
    $fecha_prestamo = limpiar_dato($_POST['fecha_prestamo']);
    $fecha_devolucion = limpiar_dato($_POST['fecha_devolucion']);
    $lector_id = limpiar_dato($_POST['lector_id']);
    $libros = isset($_POST['libros']) ? $_POST['libros'] : [];
    $cantidades = isset($_POST['cantidades']) ? $_POST['cantidades'] : [];

    try {
        $pdo->beginTransaction();

        $libros_validos = true;
        $cantidades_actuales = [];

        if ($id_prestamo) {
            // Obtener cantidades actuales para el préstamo existente
            $sql = "SELECT Libro_idLibro, Cantidad FROM Detalle_Prestamo WHERE Prestamo_id_Prestamo = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_prestamo]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $cantidades_actuales[$row['Libro_idLibro']] = $row['Cantidad'];
            }
        }

        foreach ($libros as $index => $libro_id) {
            $cantidad_solicitada = intval($cantidades[$index]);
            $cantidad_actual = isset($cantidades_actuales[$libro_id]) ? $cantidades_actuales[$libro_id] : 0;
            $cantidad_adicional = $cantidad_solicitada - $cantidad_actual;
            $cantidad_disponible = obtener_cantidad_disponible($pdo, $libro_id, $id_prestamo);

            if ($cantidad_adicional > $cantidad_disponible) {
                $libros_validos = false;
                $error = "No hay suficientes ejemplares disponibles del libro con ID $libro_id. Disponibles: $cantidad_disponible";
                break;
            }
        }

        if ($libros_validos) {
            if ($id_prestamo) {
                // Actualizar préstamo
                $sql = "UPDATE Prestamo SET Fecha_Prestamo = ?, Fecha_Devolucion = ?, Lector_idLector = ? 
                        WHERE id_Prestamo = ? AND Administrador_Id_Administrador = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fecha_prestamo, $fecha_devolucion, $lector_id, $id_prestamo, $id_admin]);

                // Actualizar detalles del préstamo
                $sql = "INSERT INTO Detalle_Prestamo (Libro_idLibro, Prestamo_id_Prestamo, Prestamo_Lector_idLector, Prestamo_Administrador_Id_Administrador, Cantidad) 
                        VALUES (?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE Cantidad = Cantidad + VALUES(Cantidad)";
                $stmt = $pdo->prepare($sql);

                foreach ($libros as $index => $libro_id) {
                    $cantidad = $cantidades[$index];
                    $stmt->execute([$libro_id, $id_prestamo, $lector_id, $id_admin, $cantidad]);
                }

                $mensaje = "Préstamo actualizado exitosamente.";
            } else {
                // Crear préstamo
                $sql = "INSERT INTO Prestamo (Fecha_Prestamo, Fecha_Devolucion, Lector_idLector, Administrador_Id_Administrador) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fecha_prestamo, $fecha_devolucion, $lector_id, $id_admin]);
                $id_prestamo = $pdo->lastInsertId();

                // Insertar detalles del préstamo
                $sql = "INSERT INTO Detalle_Prestamo (Libro_idLibro, Prestamo_id_Prestamo, Prestamo_Lector_idLector, Prestamo_Administrador_Id_Administrador, Cantidad) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);

                foreach ($libros as $index => $libro_id) {
                    $cantidad = $cantidades[$index];
                    $stmt->execute([$libro_id, $id_prestamo, $lector_id, $id_admin, $cantidad]);
                }

                $mensaje = "Préstamo creado exitosamente.";
            }

            $pdo->commit();
        } else {
            $pdo->rollBack();
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Eliminar préstamo
if (isset($_GET['eliminar'])) {
    $id_prestamo = $_GET['eliminar'];
    try {
        $pdo->beginTransaction();

        // Eliminar detalles del préstamo
        $sql = "DELETE FROM Detalle_Prestamo WHERE Prestamo_id_Prestamo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_prestamo]);

        // Eliminar préstamo
        $sql = "DELETE FROM Prestamo WHERE id_Prestamo = ? AND Administrador_Id_Administrador = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_prestamo, $id_admin]);

        $pdo->commit();
        $mensaje = "Préstamo eliminado exitosamente.";
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = "Error al eliminar: " . $e->getMessage();
    }
}

// Obtener lista de préstamos
try {
    $sql = "SELECT p.*, l.Primer_Nombre, l.Primer_Apellido FROM Prestamo p 
            JOIN Lector l ON p.Lector_idLector = l.idLector 
            WHERE p.Administrador_Id_Administrador = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_admin]);
    $prestamos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener préstamos: " . $e->getMessage();
}

// Obtener lista de lectores para el dropdown
try {
    $sql = "SELECT idLector, CONCAT(Primer_Nombre, ' ', Primer_Apellido) AS nombre_completo FROM Lector";
    $stmt = $pdo->query($sql);
    $lectores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener lectores: " . $e->getMessage();
}

// Obtener lista de libros para el dropdown
try {
    $sql = "SELECT l.idLibro, l.Titulo, l.Cantidad_Libro, 
            (l.Cantidad_Libro - COALESCE(SUM(dp.Cantidad), 0)) AS Cantidad_Disponible
            FROM Libro l
            LEFT JOIN Detalle_Prestamo dp ON l.idLibro = dp.Libro_idLibro
            GROUP BY l.idLibro, l.Titulo, l.Cantidad_Libro";
    $stmt = $pdo->query($sql);
    $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener libros: " . $e->getMessage();
}

// Función para obtener detalles de un préstamo
function obtener_detalles_prestamo($pdo, $id_prestamo) {
    $sql = "SELECT dp.*, l.Titulo FROM Detalle_Prestamo dp
            JOIN Libro l ON dp.Libro_idLibro = l.idLibro
            WHERE dp.Prestamo_id_Prestamo = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_prestamo]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Préstamos - ShelfWise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="p-0">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">ShelfWise</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="lectores.php">Lectores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="libros.php">Libros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="autores.php">Autores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="prestamos.php">Préstamos</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Préstamos</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#prestamoModal">
                    Agregar Préstamo
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Lector</th>
                                <th>Fecha Préstamo</th>
                                <th>Fecha Devolución</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($prestamos as $prestamo): ?>
                                <tr>
                                    <td><?php echo $prestamo['id_Prestamo']; ?></td>
                                    <td><?php echo $prestamo['Primer_Nombre'] . ' ' . $prestamo['Primer_Apellido']; ?></td>
                                    <td><?php echo $prestamo['Fecha_Prestamo']; ?></td>
                                    <td><?php echo $prestamo['Fecha_Devolucion']; ?></td>
                                    <td>
                                        <button class="btn btn-warning btn-sm editar-prestamo" data-bs-toggle="modal" data-bs-target="#prestamoModal" 
                                                data-prestamo='<?php echo json_encode($prestamo); ?>'>Editar</button>
                                        <button class="btn btn-danger btn-sm eliminar-prestamo" 
                                                data-id="<?php echo $prestamo['id_Prestamo']; ?>">Eliminar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para crear/editar préstamo -->
    <div class="modal fade" id="prestamoModal" tabindex="-1" aria-labelledby="prestamoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="prestamoModalLabel">Agregar/Editar Préstamo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="prestamoForm" method="POST">
                        <input type="hidden" id="id_prestamo" name="id_prestamo">
                        <div class="mb-3">
                            <label for="lector_id" class="form-label">Lector</label>
                            <select class="form-select" id="lector_id" name="lector_id" required>
                                <option value="">Seleccione un lector</option>
                                <?php foreach ($lectores as $lector): ?>
                                    <option value="<?php echo $lector['idLector']; ?>"><?php echo $lector['nombre_completo']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_prestamo" class="form-label">Fecha de Préstamo</label>
                            <input type="date" class="form-control" id="fecha_prestamo" name="fecha_prestamo" required>
                        </div>
                        <div class="mb-3">
                            <label for="fecha_devolucion" class="form-label">Fecha de Devolución</label>
                            <input type="date" class="form-control" id="fecha_devolucion" name="fecha_devolucion" required>
                        </div>
                        <div class="mb-3">
                            <label for="libros" class="form-label">Libros</label>
                            <select class="form-select" id="libros" name="libros[]" multiple>
                                <?php foreach ($libros as $libro): ?>
                                    <option value="<?php echo $libro['idLibro']; ?>" data-cantidad="<?php echo $libro['Cantidad_Disponible']; ?>">
                                        <?php echo $libro['Titulo']; ?> (Disponibles: <?php echo $libro['Cantidad_Disponible']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="libros-prestados-container" class="mb-3">
                            <!-- Aquí se mostrarán los libros prestados -->
                        </div>
                        <div id="cantidades-container"></div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" form="prestamoForm" class="btn btn-primary" id="guardarPrestamo">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var prestamoModal = document.getElementById('prestamoModal');
            var prestamoForm = document.getElementById('prestamoForm');
            var modalTitle = document.getElementById('prestamoModalLabel');
            var librosSelect = document.getElementById('libros');
            var cantidadesContainer = document.getElementById('cantidades-container');
            var librosPrestadosContainer = document.getElementById('libros-prestados-container');

            // Inicializar Select2 para el dropdown de libros
            $('#libros').select2({
                placeholder: 'Seleccione los libros',
                allowClear: true,
                dropdownParent: $('#prestamoModal')
            });

            // Manejar la selección de libros
            $('#libros').on('change', function() {
                var selectedLibros = $(this).val();
                actualizarCantidadesContainer(selectedLibros);
            });

            function actualizarCantidadesContainer(selectedLibros) {
                cantidadesContainer.innerHTML = '';
                selectedLibros.forEach(function(libroId) {
                    var libroOption = $('#libros option[value="' + libroId + '"]');
                    var libroTitulo = libroOption.text();
                    var cantidadDisponible = parseInt(libroOption.data('cantidad'));
                    var cantidadActual = parseInt($('#cantidad_' + libroId).val()) || 0;
                    var maxCantidad = cantidadDisponible + cantidadActual;
                    cantidadesContainer.innerHTML += `
                        <div class="mb-3">
                            <label for="cantidad_${libroId}" class="form-label">Cantidad para "${libroTitulo}"</label>
                            <input type="number" class="form-control" id="cantidad_${libroId}" name="cantidades[]" value="${cantidadActual}" min="1" max="${maxCantidad}" required>
                            <small class="form-text text-muted">Máximo disponible: ${maxCantidad}</small>
                        </div>
                    `;
                });
            }

            function actualizarLibrosPrestados(detalles) {
                librosPrestadosContainer.innerHTML = '<h6>Libros prestados:</h6>';
                detalles.forEach(function(detalle) {
                    librosPrestadosContainer.innerHTML += `
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <span>${detalle.Titulo} (Cantidad: ${detalle.Cantidad})</span>
                            <div>
                                <button type="button" class="btn btn-sm btn-warning editar-libro-prestado" data-libro-id="${detalle.Libro_idLibro}" data-cantidad="${detalle.Cantidad}">Editar</button>
                                <button type="button" class="btn btn-sm btn-danger eliminar-libro-prestado" data-libro-id="${detalle.Libro_idLibro}">Eliminar</button>
                            </div>
                        </div>
                    `;
                });

                // Agregar eventos a los botones de editar y eliminar
                document.querySelectorAll('.editar-libro-prestado').forEach(button => {
                    button.addEventListener('click', function() {
                        var libroId = this.getAttribute('data-libro-id');
                        var cantidad = this.getAttribute('data-cantidad');
                        $('#libros').val(libroId).trigger('change');
                        setTimeout(() => {
                            document.getElementById(`cantidad_${libroId}`).value = cantidad;
                        }, 100);
                    });
                });

                document.querySelectorAll('.eliminar-libro-prestado').forEach(button => {
                    button.addEventListener('click', function() {
                        var libroId = this.getAttribute('data-libro-id');
                        $('#libros option[value="' + libroId + '"]').prop('selected', false);
                        $('#libros').trigger('change');
                    });
                });
            }

            prestamoModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var prestamo = button.getAttribute('data-prestamo');

                if (prestamo) {
                    prestamo = JSON.parse(prestamo);
                    modalTitle.textContent = 'Editar Préstamo';
                    document.getElementById('id_prestamo').value = prestamo.id_Prestamo;
                    document.getElementById('lector_id').value = prestamo.Lector_idLector;
                    document.getElementById('fecha_prestamo').value = prestamo.Fecha_Prestamo;
                    document.getElementById('fecha_devolucion').value = prestamo.Fecha_Devolucion;
                    
                    // Cargar los libros y cantidades del préstamo
                    $.ajax({
                        url: 'get_prestamo_details.php',
                        method: 'GET',
                        data: { id_prestamo: prestamo.id_Prestamo },
                        dataType: 'json',
                        success: function(response) {
                            var librosIds = response.map(function(item) { return item.Libro_idLibro.toString(); });
                            $('#libros').val(librosIds).trigger('change');
                            
                            // Esperar a que se generen los campos de cantidad
                            setTimeout(function() {
                                response.forEach(function(item) {
                                    $('#cantidad_' + item.Libro_idLibro).val(item.Cantidad);
                                });
                            }, 100);

                            actualizarLibrosPrestados(response);
                        },
                        error: function(xhr, status, error) {
                            console.error("Error al cargar los detalles del préstamo:", error);
                        }
                    });
                } else {
                    modalTitle.textContent = 'Agregar Préstamo';
                    prestamoForm.reset();
                    document.getElementById('id_prestamo').value = '';
                    $('#libros').val(null).trigger('change');
                    cantidadesContainer.innerHTML = '';
                    librosPrestadosContainer.innerHTML = '';
                }
            });

            // Manejar eliminación de préstamo
            document.querySelectorAll('.eliminar-prestamo').forEach(button => {
                button.addEventListener('click', function() {
                    const idPrestamo = this.getAttribute('data-id');
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "No podrás revertir esta acción!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar!',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = `prestamos.php?eliminar=${idPrestamo}`;
                        }
                    });
                });
            });

            // Mostrar mensaje de éxito o error
            <?php if ($mensaje): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: '<?php echo $mensaje; ?>',
                });
            <?php endif; ?>

            <?php if ($error): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: '<?php echo $error; ?>',
                });
            <?php endif; ?>
        });
    </script>
</body>
</html>