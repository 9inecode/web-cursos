<?php
session_start();
require_once 'config/db.php';

// Si el usuario ya está logueado, redirigir al dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Por favor, completa todos los campos.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Inicio de sesión exitoso
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];

                header('Location: dashboard.php');
                exit();
            } else {
                $error = 'Correo electrónico o contraseña incorrectos.';
            }
        } catch (PDOException $e) {
            $error = 'Error al intentar iniciar sesión. Por favor, inténtalo de nuevo.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - CrowDojo Academy</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <form class="auth-form" action="login.php" method="POST">
            <h2>Iniciar Sesión</h2>
            
            <?php if ($error): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="email">Correo electrónico</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required
                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                       autocomplete="email">
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       required
                       autocomplete="current-password">
            </div>

            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>

            <div class="auth-links">
                <p>¿No tienes una cuenta? <a href="register.php">Regístrate</a></p>
                <p><a href="forgot-password.php">¿Olvidaste tu contraseña?</a></p>
            </div>
        </form>
    </div>
</body>
</html>
