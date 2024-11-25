<?php
include 'includes/db_connection.php';
include 'includes/functions.php';
verificar_sesion();

// Función para ejecutar consultas y obtener resultados
function ejecutar_consulta($pdo, $sql) {
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error en la consulta: " . $e->getMessage());
        return null;
    }
}

// Ejecutar consultas
$libro_mas_prestado = ejecutar_consulta($pdo, "
    SELECT COUNT(DISTINCT dp.Prestamo_Lector_idLector) AS cantidad_lectores, l.Titulo
    FROM Detalle_Prestamo dp
    JOIN Libro l ON dp.Libro_idLibro = l.idLibro
    WHERE l.idLibro = (
        SELECT dp.Libro_idLibro
        FROM Detalle_Prestamo dp
        GROUP BY dp.Libro_idLibro
        ORDER BY COUNT(*) DESC
        LIMIT 1
    );
");

$libro_mas_leido_rango = ejecutar_consulta($pdo, "
    SELECT COUNT(DISTINCT dp.Prestamo_Lector_idLector) AS cantidad_lectores, l.Titulo
    FROM Detalle_Prestamo dp
    JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
    JOIN Libro l ON dp.Libro_idLibro = l.idLibro
    WHERE l.idLibro = (
        SELECT dp.Libro_idLibro
        FROM Detalle_Prestamo dp
        JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
        WHERE p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20'
        GROUP BY dp.Libro_idLibro
        ORDER BY COUNT(*) DESC
        LIMIT 1
    )
    AND p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20';
");

$veces_prestado_libro = ejecutar_consulta($pdo, "
    SELECT COUNT(*) AS cantidad_prestamos, l.Titulo
    FROM Detalle_Prestamo dp
    JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
    JOIN Libro l ON dp.Libro_idLibro = l.idLibro
    WHERE l.idLibro = (
        SELECT dp.Libro_idLibro
        FROM Detalle_Prestamo dp
        JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
        WHERE p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20'
        GROUP BY dp.Libro_idLibro
        ORDER BY COUNT(*) DESC
        LIMIT 1
    )
    AND p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20';
");

// Consulta para el gráfico de libros y número de lectores
$stmt = $pdo->query("
    SELECT l.Titulo, COUNT(DISTINCT dp.Prestamo_Lector_idLector) AS numero_lectores
    FROM Libro l
    JOIN Detalle_Prestamo dp ON l.idLibro = dp.Libro_idLibro
    GROUP BY l.idLibro, l.Titulo
    HAVING numero_lectores > 0;
");
$libros_lectores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para la tabla de lectores y sus libros más leídos
$stmt = $pdo->query("
    WITH LectoresLibros AS (
        SELECT 
            le.idLector,
            le.Primer_Nombre,
            le.Primer_Apellido,
            l.idLibro,
            l.Titulo,
            COUNT(*) AS veces_leido
        FROM Lector le
        JOIN Prestamo p ON le.idLector = p.Lector_idLector
        JOIN Detalle_Prestamo dp ON p.id_Prestamo = dp.Prestamo_id_Prestamo
        JOIN Libro l ON dp.Libro_idLibro = l.idLibro
        WHERE p.Fecha_Prestamo BETWEEN '2024-01-01' AND '2024-11-20'
        GROUP BY le.idLector, le.Primer_Nombre, le.Primer_Apellido, l.idLibro, l.Titulo
    ),
    MaxLecturas AS (
        SELECT 
            idLector,
            MAX(veces_leido) AS max_leido
        FROM LectoresLibros
        GROUP BY idLector
    )
    SELECT 
        ll.Primer_Nombre,
        ll.Primer_Apellido,
        ll.Titulo,
        ll.veces_leido
    FROM LectoresLibros ll
    JOIN MaxLecturas ml ON ll.idLector = ml.idLector AND ll.veces_leido = ml.max_leido;
");
$lectores_libros = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para libros a devolver pronto
$stmt = $pdo->query("
    SELECT DISTINCT l.Titulo, p.Fecha_Devolucion
    FROM Detalle_Prestamo dp
    JOIN Libro l ON dp.Libro_idLibro = l.idLibro
    JOIN Prestamo p ON dp.Prestamo_id_Prestamo = p.id_Prestamo
    WHERE p.Fecha_Devolucion BETWEEN DATE_SUB(CURDATE(), INTERVAL 3 DAY) AND CURDATE();
");
$libros_devolver = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para lectores con libros vencidos
$stmt = $pdo->query("
    SELECT l.Primer_Nombre, l.Primer_Apellido, p.Fecha_Devolucion
    FROM Prestamo p
    JOIN Lector l ON p.Lector_idLector = l.idLector
    WHERE p.Fecha_Devolucion < CURDATE()
      AND NOT EXISTS (
          SELECT 1
          FROM Detalle_Prestamo dp
          WHERE dp.Prestamo_id_Prestamo = p.id_Prestamo
      );
");
$lectores_vencidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ShelfWise</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <h1 class="mb-4">Dashboard</h1>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Libro más prestado</h5>
                        <p class="card-text">
                            <?php echo $libro_mas_prestado['Titulo']; ?><br>
                            Lectores: <?php echo $libro_mas_prestado['cantidad_lectores']; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Libro más leído (2024)</h5>
                        <p class="card-text">
                            <?php echo $libro_mas_leido_rango['Titulo']; ?><br>
                            Lectores: <?php echo $libro_mas_leido_rango['cantidad_lectores']; ?>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Préstamos del libro más popular (2024)</h5>
                        <p class="card-text">
                            <?php echo $veces_prestado_libro['Titulo']; ?><br>
                            Préstamos: <?php echo $veces_prestado_libro['cantidad_prestamos']; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Libros y número de lectores</h5>
                        <canvas id="librosLectoresChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Lectores y sus libros más leídos (2024)</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Lector</th>
                                        <th>Libro más leído</th>
                                        <th>Veces leído</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lectores_libros as $lector): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($lector['Primer_Nombre'] . ' ' . $lector['Primer_Apellido']); ?></td>
                                            <td><?php echo htmlspecialchars($lector['Titulo']); ?></td>
                                            <td><?php echo $lector['veces_leido']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Libros a devolver pronto</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Título</th>
                                        <th>Fecha de devolución</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($libros_devolver as $libro): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($libro['Titulo']); ?></td>
                                            <td><?php echo $libro['Fecha_Devolucion']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Lectores con libros vencidos</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Lector</th>
                                        <th>Fecha de vencimiento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lectores_vencidos as $lector): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($lector['Primer_Nombre'] . ' ' . $lector['Primer_Apellido']); ?></td>
                                            <td><?php echo $lector['Fecha_Devolucion']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico de libros y número de lectores
        var ctx = document.getElementById('librosLectoresChart').getContext('2d');
        var chart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($libros_lectores, 'Titulo')); ?>,
                datasets: [{
                    label: 'Número de lectores',
                    data: <?php echo json_encode(array_column($libros_lectores, 'numero_lectores')); ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html>