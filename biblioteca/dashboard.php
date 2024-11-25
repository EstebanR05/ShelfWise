<?php
include 'includes/db_connection.php';
include 'includes/functions.php';
verificar_sesion();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de control - ShelfWise</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Panel de control</h1>
    <nav>
        <a href="lectores.php">Gestionar Lectores</a>
        <a href="libros.php">Gestionar Libros</a>
        <a href="autores.php">Gestionar Autores</a>
        <a href="prestamos.php">Gestionar Préstamos</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</body>
</html>