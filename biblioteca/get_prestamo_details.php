<?php
include 'includes/db_connection.php';
include 'includes/functions.php';
verificar_sesion();

if (isset($_GET['id_prestamo'])) {
    $id_prestamo = $_GET['id_prestamo'];
    
    try {
        $sql = "SELECT dp.*, l.Titulo, 
                (l.Cantidad_Libro - COALESCE(SUM(dp2.Cantidad), 0) + dp.Cantidad) AS Cantidad_Disponible
                FROM Detalle_Prestamo dp
                JOIN Libro l ON dp.Libro_idLibro = l.idLibro
                LEFT JOIN Detalle_Prestamo dp2 ON l.idLibro = dp2.Libro_idLibro AND dp2.Prestamo_id_Prestamo != dp.Prestamo_id_Prestamo
                WHERE dp.Prestamo_id_Prestamo = ?
                GROUP BY dp.Libro_idLibro, dp.Cantidad, l.Titulo, l.Cantidad_Libro";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_prestamo]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($detalles);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al obtener detalles del préstamo: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID de préstamo no proporcionado']);
}