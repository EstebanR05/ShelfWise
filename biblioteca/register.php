<?php
include 'includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger todos los campos del formulario
    $primer_nombre = filter_input(INPUT_POST, 'primer_nombre', FILTER_SANITIZE_STRING);
    $segundo_nombre = filter_input(INPUT_POST, 'segundo_nombre', FILTER_SANITIZE_STRING);
    $primer_apellido = filter_input(INPUT_POST, 'primer_apellido', FILTER_SANITIZE_STRING);
    $segundo_apellido = filter_input(INPUT_POST, 'segundo_apellido', FILTER_SANITIZE_STRING);
    $telefono = filter_input(INPUT_POST, 'telefono', FILTER_SANITIZE_STRING);
    $edad = filter_input(INPUT_POST, 'edad', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $errors = [];

    // Validaciones
    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden";
    }

    // Verificar si el correo ya existe
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM Administrador WHERE Email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Este correo electrónico ya está registrado";
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO Administrador (Primer_Nombre, Segundo_Nombre, Primer_Apellido, Segundo_Apellido, Telefono, Edad, Email, Contraseña, Rol_idRol) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)");
        if ($stmt->execute([$primer_nombre, $segundo_nombre, $primer_apellido, $segundo_apellido, $telefono, $edad, $email, $password])) {
            header("Location: login.php?registered=true");
            exit();
        } else {
            $errors[] = "Error al registrar el usuario";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - ShelfWise</title>
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

        .register-container {
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

        .accordion-button:not(.collapsed) {
            background-color: var(--primary-color);
            color: white;
        }

        .accordion-button:focus {
            box-shadow: none;
            border-color: rgba(0,0,0,.125);
        }

        .accordion-button::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
        }

        .accordion-button:not(.collapsed)::after {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23fff'%3e%3cpath fill-rule='evenodd' d='M1.646 4.646a.5.5 0 0 1 .708 0L8 10.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z'/%3e%3c/svg%3e");
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

        .btn-next {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .btn-next:hover {
            background-color: var(--primary-hover);
            color: white;
        }

        .alert {
            border-radius: 0.75rem;
            font-weight: 500;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border: none;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid rgba(0, 0, 0, 0.1);
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .step {
            width: 2rem;
            height: 2rem;
            border-radius: 50%;
            background-color: #e5e7eb;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .step.active {
            background-color: var(--primary-color);
            color: white;
        }

        .step.completed {
            background-color: #10B981;
            color: white;
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
    <div class="container register-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Crear cuenta</h2>
                        <p class="text-muted">Complete el formulario en dos simples pasos</p>
                        <div class="step-indicator">
                            <div class="step active" id="step1-indicator">1</div>
                            <div class="step" id="step2-indicator">2</div>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="bi bi-exclamation-circle-fill me-2"></i>
                                <ul class="mb-0">
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="registerForm" novalidate>
                            <div class="accordion" id="registrationAccordion">
                                <!-- Paso 1: Información básica -->
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header" id="headingOne">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                            Paso 1: Información de acceso
                                        </button>
                                    </h2>
                                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="email" class="form-label">
                                                        <i class="bi bi-envelope-fill"></i>
                                                        Correo electrónico
                                                    </label>
                                                    <input type="email" class="form-control" id="email" name="email" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="telefono" class="form-label">
                                                        <i class="bi bi-telephone-fill"></i>
                                                        Teléfono
                                                    </label>
                                                    <input type="tel" class="form-control" id="telefono" name="telefono">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="password" class="form-label">
                                                        <i class="bi bi-lock-fill"></i>
                                                        Contraseña
                                                    </label>
                                                    <input type="password" class="form-control" id="password" name="password" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="confirm_password" class="form-label">
                                                        <i class="bi bi-lock-fill"></i>
                                                        Confirmar contraseña
                                                    </label>
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                                </div>
                                            </div>
                                            <div class="d-flex justify-content-end mt-3">
                                                <button type="button" class="btn btn-next" onclick="nextStep()">
                                                    Siguiente paso
                                                    <i class="bi bi-arrow-right ms-2"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Paso 2: Información personal -->
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header" id="headingTwo">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                            Paso 2: Información personal
                                        </button>
                                    </h2>
                                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo">
                                        <div class="accordion-body">
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="primer_nombre" class="form-label">
                                                        <i class="bi bi-person-fill"></i>
                                                        Primer nombre
                                                    </label>
                                                    <input type="text" class="form-control" id="primer_nombre" name="primer_nombre" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="segundo_nombre" class="form-label">
                                                        <i class="bi bi-person-fill"></i>
                                                        Segundo nombre
                                                    </label>
                                                    <input type="text" class="form-control" id="segundo_nombre" name="segundo_nombre">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <label for="primer_apellido" class="form-label">
                                                        <i class="bi bi-person-fill"></i>
                                                        Primer apellido
                                                    </label>
                                                    <input type="text" class="form-control" id="primer_apellido" name="primer_apellido" required>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label for="segundo_apellido" class="form-label">
                                                        <i class="bi bi-person-fill"></i>
                                                        Segundo apellido
                                                    </label>
                                                    <input type="text" class="form-control" id="segundo_apellido" name="segundo_apellido">
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="edad" class="form-label">
                                                    <i class="bi bi-calendar-fill"></i>
                                                    Edad
                                                </label>
                                                <input type="number" class="form-control" id="edad" name="edad">
                                            </div>
                                            <div class="d-grid gap-2 mt-4">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-person-plus-fill me-2"></i>
                                                    Completar registro
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>

                        <div class="login-link">
                            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function nextStep() {
            // Validar campos del primer paso
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            if (!email || !password || !confirmPassword) {
                alert('Por favor, complete todos los campos obligatorios');
                return;
            }

            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden');
                return;
            }

            // Cambiar al siguiente paso
            const collapseOne = document.getElementById('collapseOne');
            const collapseTwo = document.getElementById('collapseTwo');
            const bsCollapseOne = new bootstrap.Collapse(collapseOne, { toggle: false });
            const bsCollapseTwo = new bootstrap.Collapse(collapseTwo, { toggle: false });

            bsCollapseOne.hide();
            bsCollapseTwo.show();

            // Actualizar indicadores de paso
            document.getElementById('step1-indicator').classList.remove('active');
            document.getElementById('step1-indicator').classList.add('completed');
            document.getElementById('step2-indicator').classList.add('active');
        }

        // Validación del formulario antes de enviar
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const requiredFields = ['email', 'password', 'confirm_password', 'primer_nombre', 'primer_apellido'];
            let isValid = true;

            requiredFields.forEach(field => {
                if (!document.getElementById(field).value) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Por favor, complete todos los campos obligatorios');
            }
        });

        // Auto-dismiss alerts
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