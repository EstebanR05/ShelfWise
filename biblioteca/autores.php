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

// Crear o actualizar autor
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? $_POST['id'] : null;
    $primer_nombre = limpiar_dato($_POST['primer_nombre']);
    $segundo_nombre = limpiar_dato($_POST['segundo_nombre']);
    $primer_apellido = limpiar_dato($_POST['primer_apellido']);
    $segundo_apellido = limpiar_dato($_POST['segundo_apellido']);

    try {
        if ($id) {
            // Actualizar
            $sql = "UPDATE Autor SET Primer_Nombre = ?, Segundo_Nombre = ?, Primer_Apellido = ?, Segundo_Apellido = ? WHERE idAutor = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido, $id]);
        } else {
            // Crear
            $sql = "INSERT INTO Autor (Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido]);
        }
        $mensaje = "Autor guardado exitosamente.";
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Eliminar autor
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    try {
        $sql = "DELETE FROM Autor WHERE idAutor = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $mensaje = "Autor eliminado exitosamente.";
    } catch (PDOException $e) {
        $error = "Error al eliminar: " . $e->getMessage();
    }
}

// Listar autores
try {
    $sql = "SELECT * FROM Autor";
    $stmt = $pdo->query($sql);
    $autores = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener autores: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Autores - ShelfWise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
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
                        <a class="nav-link active" href="autores.php">Autores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="prestamos.php">Préstamos</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Autores</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#autorModal">
                    Agregar Autor
                </button>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Apellidos</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($autores as $autor): ?>
                            <tr>
                                <td><?php echo $autor['idAutor']; ?></td>
                                <td><?php echo $autor['Primer_Nombre'] . ' ' . $autor['Segundo_Nombre']; ?></td>
                                <td><?php echo $autor['Primer_Apellido'] . ' ' . $autor['Segundo_Apellido']; ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm editar-autor" data-bs-toggle="modal" data-bs-target="#autorModal"
                                        data-autor='<?php echo json_encode($autor); ?>'>Editar</button>
                                    <button class="btn btn-danger btn-sm eliminar-autor"
                                        data-id="<?php echo $autor['idAutor']; ?>">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para crear/editar autor -->
    <div class="modal fade" id="autorModal" tabindex="-1" aria-labelledby="autorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="autorModalLabel">Agregar/Editar Autor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="autorForm" method="POST">
                        <input type="hidden" id="idAutor" name="id">
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
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" form="autorForm" class="btn btn-primary" id="guardarAutor">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var autorModal = document.getElementById('autorModal');
            var autorForm = document.getElementById('autorForm');
            var modalTitle = document.getElementById('autorModalLabel');

            autorModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var autor = button.getAttribute('data-autor');

                if (autor) {
                    autor = JSON.parse(autor);
                    modalTitle.textContent = 'Editar Autor';
                    document.getElementById('idAutor').value = autor.idAutor;
                    document.getElementById('primer_nombre').value = autor.Primer_Nombre;
                    document.getElementById('segundo_nombre').value = autor.Segundo_Nombre;
                    document.getElementById('primer_apellido').value = autor.Primer_Apellido;
                    document.getElementById('segundo_apellido').value = autor.Segundo_Apellido;
                } else {
                    modalTitle.textContent = 'Agregar Autor';
                    autorForm.reset();
                    document.getElementById('idAutor').value = '';
                }
            });

            // Manejar eliminación de autor
            document.querySelectorAll('.eliminar-autor').forEach(button => {
                button.addEventListener('click', function() {
                    const idAutor = this.getAttribute('data-id');
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
                            window.location.href = `autores.php?eliminar=${idAutor}`;
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