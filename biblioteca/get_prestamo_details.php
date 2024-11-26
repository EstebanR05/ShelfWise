<?php
include 'includes/db_connection.php';
include 'includes/functions.php';
verificar_sesion();

if (isset($_GET['id_prestamo'])) {
    $id_prestamo = $_GET['id_prestamo'];
    
    try {
        $sql = "SELECT dp.*, l.Titulo FROM Detalle_Prestamo dp
                JOIN Libro l ON dp.Libro_idLibro = l.idLibro
                WHERE dp.Prestamo_id_Prestamo = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_prestamo]);
        $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener la cantidad disponible para cada libro
        foreach ($detalles as &$detalle) {
            $detalle['Cantidad_Disponible'] = obtener_cantidad_disponible($pdo, $detalle['Libro_idLibro'], $id_prestamo);
        }
        
        echo json_encode($detalles);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al obtener detalles del préstamo: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'ID de préstamo no proporcionado']);
}

function obtener_cantidad_disponible($pdo, $libro_id, $prestamo_actual_id) {
    $cantidad_total = obtener_cantidad_total($pdo, $libro_id);
    $cantidad_prestada = obtener_cantidad_prestada($pdo, $libro_id, $prestamo_actual_id);
    return $cantidad_total - $cantidad_prestada;
}

function obtener_cantidad_total($pdo, $libro_id) {
    $sql = "SELECT Cantidad_Libro FROM Libro WHERE idLibro = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$libro_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? intval($result['Cantidad_Libro']) : 0;
}

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