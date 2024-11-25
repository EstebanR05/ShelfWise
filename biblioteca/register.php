<?php
include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $primer_nombre = $_POST['primer_nombre'];
    $primer_apellido = $_POST['primer_apellido'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("INSERT INTO Administrador (Primer_Nombre, Primer_Apellido, Email, Contraseña, Rol_idRol) VALUES (?, ?, ?, ?, 1)");
    if ($stmt->execute([$primer_nombre, $primer_apellido, $email, $password])) {
        header("Location: login.php");
        exit();
    } else {
        $error = "Error al registrar el usuario";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - ShelfWise</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Registrarse</h1>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
    <form method="POST">
        <input type="text" name="primer_nombre" required placeholder="Primer nombre">
        <input type="text" name="primer_apellido" required placeholder="Primer apellido">
        <input type="email" name="email" required placeholder="Correo electrónico">
        <input type="password" name="password" required placeholder="Contraseña">
        <button type="submit">Registrarse</button>
    </form>
</body>
</html>