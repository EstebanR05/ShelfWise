<?php
include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM Administrador WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $password == $user['Contraseña']) {
        session_start();
        $_SESSION['usuario_id'] = $user['Id_Administrador'];
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Credenciales incorrectas";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - ShelfWise</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <h1>Iniciar sesión</h1>
    <?php if (isset($error)) echo "<p>$error</p>"; ?>
    <form method="POST">
        <input type="email" name="email" required placeholder="Correo electrónico">
        <input type="password" name="password" required placeholder="Contraseña">
        <button type="submit">Iniciar sesión</button>
    </form>
</body>
</html>