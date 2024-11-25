<?php
include 'includes/db_connection.php';
include 'includes/functions.php';
verificar_sesion();

// Función para limpiar y validar los datos de entrada
function limpiar_dato($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

// Crear o actualizar lector
if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['crear']) || isset($_POST['actualizar']))) {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $primer_nombre = limpiar_dato($_POST['primer_nombre']);
    $segundo_nombre = limpiar_dato($_POST['segundo_nombre']);
    $primer_apellido = limpiar_dato($_POST['primer_apellido']);
    $segundo_apellido = limpiar_dato($_POST['segundo_apellido']);
    $telefono = limpiar_dato($_POST['telefono']);
    $correo = limpiar_dato($_POST['correo']);
    $identificacion = limpiar_dato($_POST['identificacion']);

    if ($id) {
        // Actualizar
        $sql = "UPDATE Lector SET Primer_Nombre = ?, Segundo_Nombre = ?, Primer_Apellido = ?, Segundo_Apellido = ?, 
                Telefono = ?, Correo = ?, Identificacion = ? WHERE idLector = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido, $telefono, $correo, $identificacion, $id]);
        $mensaje = "Lector actualizado exitosamente.";
    } else {
        // Crear
        $sql = "INSERT INTO Lector (Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido, Telefono, Correo, Identificacion) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido, $telefono, $correo, $identificacion]);
        $mensaje = "Lector creado exitosamente.";
    }
}

// Eliminar lector
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $sql = "DELETE FROM Lector WHERE idLector = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $mensaje = "Lector eliminado exitosamente.";
}

// Listar lectores
$sql = "SELECT * FROM Lector";
$stmt = $pdo->query($sql);
$lectores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Lectores - ShelfWise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4">Gestión de Lectores</h1>
        
        <?php if (isset($mensaje)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#lectorModal">
            Agregar Lector
        </button>

        <!-- Tabla de lectores -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Apellidos</th>
                        <th>Teléfono</th>
                        <th>Correo</th>
                        <th>Identificación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lectores as $lector): ?>
                        <tr>
                            <td><?php echo $lector['idLector']; ?></td>
                            <td><?php echo $lector['Primer_Nombre'] . ' ' . $lector['Segundo_Nombre']; ?></td>
                            <td><?php echo $lector['Primer_Apellido'] . ' ' . $lector['Segundo_Apellido']; ?></td>
                            <td><?php echo $lector['Telefono']; ?></td>
                            <td><?php echo $lector['Correo']; ?></td>
                            <td><?php echo $lector['Identificacion']; ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm editar-lector" data-bs-toggle="modal" data-bs-target="#lectorModal" 
                                        data-lector='<?php echo json_encode($lector); ?>'>Editar</button>
                                <a href="?eliminar=<?php echo $lector['idLector']; ?>" class="btn btn-danger btn-sm" 
                                   onclick="return confirm('¿Está seguro de que desea eliminar este lector?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para crear/editar lector -->
    <div class="modal fade" id="lectorModal" tabindex="-1" aria-labelledby="lectorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="lectorModalLabel">Agregar/Editar Lector</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="lectorForm" method="POST">
                        <input type="hidden" id="idLector" name="id">
                        <div class="mb-3">
                            <label for="primer_nombre" class="form-label">Primer Nombre</label>
                            <input type="text" class="form-control" id="primer_nombre" name="primer_nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="segundo_nombre" class="form-label">Segundo Nombre</label>
                            <input type="text" class="form-control" id="segundo_nombre" name="segundo_nombre">
                        </div>
                        <div class="mb-3">
                            <label for="primer_apellido" class="form-label">Primer Apellido</label>
                            <input type="text" class="form-control" id="primer_apellido" name="primer_apellido" required>
                        </div>
                        <div class="mb-3">
                            <label for="segundo_apellido" class="form-label">Segundo Apellido</label>
                            <input type="text" class="form-control" id="segundo_apellido" name="segundo_apellido">
                        </div>
                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono">
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="correo" name="correo">
                        </div>
                        <div class="mb-3">
                            <label for="identificacion" class="form-label">Identificación</label>
                            <input type="text" class="form-control" id="identificacion" name="identificacion" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" form="lectorForm" class="btn btn-primary" id="guardarLector">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var lectorModal = document.getElementById('lectorModal');
            var lectorForm = document.getElementById('lectorForm');
            var modalTitle = document.getElementById('lectorModalLabel');

            lectorModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var lector = button.getAttribute('data-lector');

                if (lector) {
                    lector = JSON.parse(lector);
                    modalTitle.textContent = 'Editar Lector';
                    document.getElementById('idLector').value = lector.idLector;
                    document.getElementById('primer_nombre').value = lector.Primer_Nombre;
                    document.getElementById('segundo_nombre').value = lector.Segundo_Nombre;
                    document.getElementById('primer_apellido').value = lector.Primer_Apellido;
                    document.getElementById('segundo_apellido').value = lector.Segundo_Apellido;
                    document.getElementById('telefono').value = lector.Telefono;
                    document.getElementById('correo').value = lector.Correo;
                    document.getElementById('identificacion').value = lector.Identificacion;
                    document.getElementById('guardarLector').name = 'actualizar';
                } else {
                    modalTitle.textContent = 'Agregar Lector';
                    lectorForm.reset();
                    document.getElementById('idLector').value = '';
                    document.getElementById('guardarLector').name = 'crear';
                }
            });

            lectorForm.addEventListener('submit', function(event) {
                if (!lectorForm.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                lectorForm.classList.add('was-validated');
            }, false);
        });
    </script>
</body>
</html>