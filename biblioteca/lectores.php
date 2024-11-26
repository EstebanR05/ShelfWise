<?php
include 'includes/db_connection.php';
include 'includes/functions.php';
verificar_sesion();

// Función para limpiar y validar los datos de entrada
function limpiar_dato($dato)
{
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

$mensaje = '';
$error = '';

// Crear o actualizar lector
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $primer_nombre = limpiar_dato($_POST['primer_nombre']);
    $segundo_nombre = limpiar_dato($_POST['segundo_nombre']);
    $primer_apellido = limpiar_dato($_POST['primer_apellido']);
    $segundo_apellido = limpiar_dato($_POST['segundo_apellido']);
    $telefono = limpiar_dato($_POST['telefono']);
    $correo = limpiar_dato($_POST['correo']);
    $identificacion = limpiar_dato($_POST['identificacion']);

    try {
        if ($id) {
            // Actualizar
            $sql = "UPDATE Lector SET Primer_Nombre = ?, Segundo_Nombre = ?, Primer_Apellido = ?, Segundo_Apellido = ?, 
                    Telefono = ?, Correo = ?, Identificacion = ? WHERE idLector = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido, $telefono, $correo, $identificacion, $id]);
        } else {
            // Crear
            $sql = "INSERT INTO Lector (Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido, Telefono, Correo, Identificacion) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido, $telefono, $correo, $identificacion]);
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Eliminar lector
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    try {
        $sql = "DELETE FROM Lector WHERE idLector = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
    } catch (PDOException $e) {
        $error = "Error al eliminar: " . $e->getMessage();
    }
}

// Listar lectores
try {
    $sql = "SELECT * FROM Lector";
    $stmt = $pdo->query($sql);
    $lectores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener lectores: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Lectores - ShelfWise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="lectores.php">Lectores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="libros.php">Libros</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="autores.php">Autores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prestamos.php">Préstamos</a>
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
                <h5 class="mb-0">Lista de Lectores</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#lectorModal">
                    Agregar Lector
                </button>
            </div>
            <div class="card-body">
                <table class="table">
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
                                    <button class="btn btn-danger btn-sm eliminar-lector"
                                        data-id="<?php echo $lector['idLector']; ?>">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
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
                } else {
                    modalTitle.textContent = 'Agregar Lector';
                    lectorForm.reset();
                    document.getElementById('idLector').value = '';
                }
            });

            // Manejar eliminación de lector
            document.querySelectorAll('.eliminar-lector').forEach(button => {
                button.addEventListener('click', function() {
                    const idLector = this.getAttribute('data-id');
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
                            window.location.href = `lectores.php?eliminar=${idLector}`;
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