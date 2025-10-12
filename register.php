<?php
session_start();
require_once 'config/db.php';

// Verificar si viene de un email válido
$email = isset($_GET['email']) ? filter_var($_GET['email'], FILTER_SANITIZE_EMAIL) : '';
$emailVerified = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

// Inicializar variables
$username = ''; // Inicializar vacío, no con el usuario de la BD

// Procesar el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? $email; // Mantener el email de la URL si no viene en POST
    
    // Validaciones básicas
    $errors = [];
    
    if (empty($username) || strlen($username) < 4) {
        $errors[] = "El usuario debe tener al menos 4 caracteres";
    }
    
    // Validación de contraseña
    if (empty($password)) {
        $errors[] = "La contraseña es requerida";
    } else {
        // Verificar longitud mínima
        if (strlen($password) < 8) {
            $errors[] = "La contraseña debe tener al menos 8 caracteres";
        }
        
        // Verificar mayúsculas
        if (!preg_match('/[A-Z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra mayúscula";
        }
        
        // Verificar minúsculas
        if (!preg_match('/[a-z]/', $password)) {
            $errors[] = "La contraseña debe contener al menos una letra minúscula";
        }
        
        // Verificar números
        if (!preg_match('/[0-9]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un número";
        }
        
        // Verificar caracteres especiales
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
            $errors[] = "La contraseña debe contener al menos un carácter especial (!@#$%^&*(),.?\":{}|<>)";
        }
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden";
    }
    
    // Si no hay errores, registrar
    if (empty($errors)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            require_once 'config/mail.php';
            
            // Email al admin
            $adminSubject = 'Nuevo guerrero en CrowDojo Academy';
            $adminBody = "
                <h2>🐦‍⬛ Nuevo guerrero registrado en CrowDojo Academy</h2>
                <p><strong>Usuario:</strong> {$username}</p>
                <p><strong>Email:</strong> {$email}</p>
                <p><strong>Fecha:</strong> " . date('d/m/Y H:i:s') . "</p>
            ";
            sendMail('fidelgnzf@gmail.com', $adminSubject, $adminBody);
            
            // Email de bienvenida al usuario
            $userSubject = '🥋 Bienvenido al CrowDojo Academy';
            $userBody = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #4a5568;'>🐦‍⬛ ¡Bienvenido al CrowDojo Academy, {$username}!</h2>
                    <p>Gracias por unirte a nuestro dojo. Estás a punto de comenzar tu entrenamiento como guerrero cibernético.</p>
                    <div style='background: #f7fafc; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p><strong>🥋 Tus credenciales del dojo:</strong></p>
                        <p>Usuario: {$username}</p>
                        <p>Email: {$email}</p>
                    </div>
                    <p>Ya puedes comenzar a explorar tu entrenamiento y empezar tu camino en el hacking ético y bug bounty.</p>
                    <p>Si tienes alguna pregunta, no dudes en contactarnos respondiendo a este email.</p>
                    <br>
                    <p style='color: #4a5568;'><strong>¡Que comience tu entrenamiento!</strong></p>
                    <p>🐦‍⬛ El equipo de CrowDojo Academy</p>
                </div>
            ";
            sendMail($email, $userSubject, $userBody);
            
            // Iniciar sesión y redirigir
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['username'] = $username;
            $_SESSION['success'] = "¡Registro exitoso! Redirigiendo...";
            
            ?>
            <script>
                setTimeout(function() {
                    window.location.href = 'dashboard.php';
                }, 2000);
            </script>
            <?php
            exit();
            
        } catch (PDOException $e) {
            $errors[] = "Error al crear el usuario";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - CrowDojo Academy</title>
    <style>
        body.auth-page {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: system-ui, -apple-system, sans-serif;
        }

        .register-container {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            margin: 2rem;
        }

        h2 {
            color: #2d3748;
            margin: 0 0 1.5rem 0;
            text-align: center;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            color: #4a5568;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .auth-button {
            width: 100%;
            padding: 0.75rem;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .auth-button:hover {
            background: #5a67d8;
            transform: translateY(-1px);
        }

        .error-message {
            background: #fff5f5;
            color: #c53030;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
        }

        .error-message p {
            margin: 0;
            font-size: 0.875rem;
        }

        .success-message {
            background: #f0fff4;
            color: #2f855a;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #4a5568;
            font-size: 0.875rem;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        input::placeholder {
            color: #a0aec0;
        }

        .password-requirements {
            background: #f8fafc;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }

        .password-requirements h3 {
            color: #4a5568;
            font-size: 0.9rem;
            margin: 0 0 0.5rem 0;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 1.2rem;
            color: #718096;
            font-size: 0.85rem;
        }

        .password-requirements li {
            margin: 0.25rem 0;
        }
    </style>
</head>
<body class="auth-page">
    <div class="register-container">
        <h2>🐦‍⬛ Registro en CrowDojo Academy</h2>
        
        <div class="password-requirements">
            <h3>Requisitos de la contraseña:</h3>
            <ul>
                <li>Mínimo 8 caracteres</li>
                <li>Al menos una letra mayúscula</li>
                <li>Al menos una letra minúscula</li>
                <li>Al menos un número</li>
                <li>Al menos un carácter especial (!@#$%^&*(),.?":{}|<>)</li>
            </ul>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success-message">
                <?php 
                echo htmlspecialchars($_SESSION['success']);
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Usuario:</label>
                <input type="text" name="username" required minlength="4" 
                       value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>"
                       placeholder="Elige un nombre de usuario">
            </div>

            <div class="form-group">
                <label>Email:</label>
                <input type="email" name="email" required 
                       value="<?php echo htmlspecialchars($email); ?>" 
                       <?php echo !empty($email) ? 'readonly' : ''; ?>
                       placeholder="Tu correo electrónico">
            </div>

            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="password" required minlength="8"
                       placeholder="Ingresa tu contraseña">
            </div>

            <div class="form-group">
                <label>Confirmar Contraseña:</label>
                <input type="password" name="confirm_password" required
                       placeholder="Repite tu contraseña">
            </div>

            <button type="submit" class="auth-button">Registrarse</button>
        </form>

        <div class="login-link">
            ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
        </div>
    </div>
</body>
</html>
