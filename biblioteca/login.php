<?php
include 'includes/db_connection.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM Administrador WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $password == $user['Contraseña']) {
        $_SESSION['usuario_id'] = $user['Id_Administrador'];
        $_SESSION['usuario_nombre'] = $user['Primer_Nombre'] . ' ' . $user['Primer_Apellido'];
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-hover: #4338ca;
            --text-primary: #1f2937;
            --text-secondary: #6b7280;
            --background-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        body {
            min-height: 100vh;
            background: var(--background-gradient);
            display: flex;
            align-items: center;
            font-family: 'Inter', sans-serif;
            padding: 2rem 0;
        }

        .login-container {
            animation: fadeIn 0.5s ease-out;
        }

        .card {
            border: none;
            border-radius: 1.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            overflow: hidden;
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            padding: 2.5rem 2rem 1.5rem;
        }

        .card-header h2 {
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.75rem;
        }

        .card-header p {
            color: var(--text-secondary);
            font-size: 0.975rem;
        }

        .card-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1.5px solid #e5e7eb;
            font-size: 1rem;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .input-group {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-secondary);
            z-index: 10;
            background: none;
            border: none;
            padding: 0;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.875rem 1rem;
            font-weight: 500;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            font-size: 1rem;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 0.75rem;
            font-weight: 500;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-logo {
            width: 120px;
            height: auto;
            margin-bottom: 1.5rem;
        }

        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .register-link p {
            color: var(--text-secondary);
            margin-bottom: 0;
        }

        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .register-link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .card {
                margin: 0;
            }
            
            .card-header {
                padding: 1.5rem 1rem;
            }
            
            .card-body {
                padding: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="row justify-content-center">
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Bienvenido a ShelfWise</h2>
                        <p class="text-muted">Ingresa tus credenciales para continuar</p>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-circle-fill"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" id="loginForm">
                            <div class="mb-4">
                                <label for="email" class="form-label">
                                    <i class="bi bi-envelope-fill"></i>
                                    Correo electrónico
                                </label>
                                <input type="email" 
                                       class="form-control" 
                                       id="email" 
                                       name="email" 
                                       placeholder="nombre@ejemplo.com"
                                       required
                                       autocomplete="email">
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock-fill"></i>
                                    Contraseña
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="password" 
                                           name="password" 
                                           placeholder="Ingresa tu contraseña"
                                           required
                                           autocomplete="current-password">
                                    <button type="button" class="password-toggle" onclick="togglePassword()">
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Iniciar sesión
                                </button>
                            </div>
                        </form>
                        <div class="register-link">
                            <p>¿No tienes una cuenta? <a href="register.php" class="ms-1">Regístrate aquí</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleButton = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleButton.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
            } else {
                passwordInput.type = 'password';
                toggleButton.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
            }
        }

        // Auto-dismiss alerts after 3 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.opacity = '0';
                    alert.style.transition = 'opacity 0.3s ease-out';
                    setTimeout(() => alert.remove(), 300);
                }, 3000);
            });
        });
    </script>
</body>
</html>