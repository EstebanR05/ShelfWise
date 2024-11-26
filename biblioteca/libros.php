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

// Crear o actualizar libro
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : null;
    $editorial = limpiar_dato($_POST['editorial']);
    $titulo = limpiar_dato($_POST['titulo']);
    $autor_id = intval($_POST['autor_id']);
    $genero = limpiar_dato($_POST['genero']);
    $cantidad = intval($_POST['cantidad']);

    try {
        if ($id) {
            // Actualizar
            $sql = "UPDATE Libro SET Editorial = ?, Titulo = ?, Autor_idAutor = ?, Genero = ?, Cantidad_Libro = ? WHERE idLibro = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$editorial, $titulo, $autor_id, $genero, $cantidad, $id]);
            $mensaje = "Libro actualizado exitosamente.";
        } else {
            // Crear
            $sql = "INSERT INTO Libro (Editorial, Titulo, Autor_idAutor, Genero, Cantidad_Libro) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$editorial, $titulo, $autor_id, $genero, $cantidad]);
            $mensaje = "Libro creado exitosamente.";
        }
    } catch (PDOException $e) {
        $error = "Error en la base de datos: " . $e->getMessage();
    }
}

// Eliminar libro
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    try {
        $sql = "DELETE FROM Libro WHERE idLibro = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $mensaje = "Libro eliminado exitosamente.";
    } catch (PDOException $e) {
        $error = "Error al eliminar: " . $e->getMessage();
    }
}

// Listar libros
try {
    $sql = "SELECT l.*, CONCAT(a.Primer_Nombre, ' ', a.Primer_Apellido) as nombre_autor 
            FROM Libro l 
            JOIN Autor a ON l.Autor_idAutor = a.idAutor 
            ORDER BY l.Titulo";
    $stmt = $pdo->query($sql);
    $libros = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al obtener libros: " . $e->getMessage();
}

// Obtener lista de autores para el dropdown
try {
    $sql = "SELECT a.idAutor, CONCAT(a.Primer_Nombre, ' - ', a.Primer_Apellido) as nombre FROM Autor as a ORDER BY a.Primer_Apellido, a.Primer_Nombre";
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
    <title>Gestión de Libros - ShelfWise</title>
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="lectores.php">Lectores</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="libros.php">Libros</a>
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
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Libros</h5>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#libroModal">
                    Agregar Libro
                </button>
            </div>
            <div class="card-body">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Autor</th>
                            <th>Editorial</th>
                            <th>Género</th>
                            <th>Cantidad</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($libros as $libro): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($libro['idLibro']); ?></td>
                                <td><?php echo htmlspecialchars($libro['Titulo']); ?></td>
                                <td><?php echo htmlspecialchars($libro['nombre_autor']); ?></td>
                                <td><?php echo htmlspecialchars($libro['Editorial']); ?></td>
                                <td><?php echo htmlspecialchars($libro['Genero']); ?></td>
                                <td><?php echo htmlspecialchars($libro['Cantidad_Libro']); ?></td>
                                <td>
                                    <button class="btn btn-warning btn-sm editar-libro" data-bs-toggle="modal" data-bs-target="#libroModal"
                                        data-libro='<?php echo json_encode($libro, JSON_HEX_APOS | JSON_HEX_QUOT); ?>'>Editar</button>
                                    <button class="btn btn-danger btn-sm eliminar-libro"
                                        data-id="<?php echo $libro['idLibro']; ?>">Eliminar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para crear/editar libro -->
    <div class="modal fade" id="libroModal" tabindex="-1" aria-labelledby="libroModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="libroModalLabel">Agregar/Editar Libro</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="libroForm" method="POST">
                        <input type="hidden" id="idLibro" name="id">
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título</label>
                            <input type="text" class="form-control" id="titulo" name="titulo" required>
                        </div>
                        <div class="mb-3">
                            <label for="autor_id" class="form-label">Autor</label>
                            <select class="form-select" id="autor_id" name="autor_id" required>
                                <option value="">Seleccione un autor</option>
                                <?php foreach ($autores as $autor): ?>
                                    <option value="<?php echo $autor['idAutor']; ?>"><?php echo htmlspecialchars($autor['nombre']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="editorial" class="form-label">Editorial</label>
                            <input type="text" class="form-control" id="editorial" name="editorial">
                        </div>
                        <div class="mb-3">
                            <label for="genero" class="form-label">Género</label>
                            <input type="text" class="form-control" id="genero" name="genero" required>
                        </div>
                        <div class="mb-3">
                            <label for="cantidad" class="form-label">Cantidad</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" required min="0">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" form="libroForm" class="btn btn-primary" id="guardarLibro">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var libroModal = document.getElementById('libroModal');
            var libroForm = document.getElementById('libroForm');
            var modalTitle = document.getElementById('libroModalLabel');

            libroModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var libro = button.getAttribute('data-libro');

                if (libro) {
                    libro = JSON.parse(libro);
                    modalTitle.textContent = 'Editar Libro';
                    document.getElementById('idLibro').value = libro.idLibro;
                    document.getElementById('titulo').value = libro.Titulo;
                    document.getElementById('autor_id').value = libro.Autor_idAutor;
                    document.getElementById('editorial').value = libro.Editorial;
                    document.getElementById('genero').value = libro.Genero;
                    document.getElementById('cantidad').value = libro.Cantidad_Libro;
                } else {
                    modalTitle.textContent = 'Agregar Libro';
                    libroForm.reset();
                    document.getElementById('idLibro').value = '';
                }
            });

            // Manejar eliminación de libro
            document.querySelectorAll('.eliminar-libro').forEach(button => {
                button.addEventListener('click', function() {
                    const idLibro = this.getAttribute('data-id');
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
                            window.location.href = `libros.php?eliminar=${idLibro}`;
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