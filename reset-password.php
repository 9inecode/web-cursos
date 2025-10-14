<?php
session_start();
require_once 'config/db.php';

$errors = [];
$success = false;
$token = $_GET['token'] ?? '';

// Verificar si el token es válido y no ha expirado
if (!empty($token)) {
    try {
        $stmt = $pdo->prepare("
            SELECT pr.*, u.username, u.email 
            FROM password_resets pr 
            JOIN users u ON pr.user_id = u.id 
            WHERE pr.token = ? 
            AND pr.expiry > NOW() 
            AND pr.used = FALSE
        ");
        $stmt->execute([$token]);
        $reset = $stmt->fetch();

        if (!$reset) {
            $errors[] = "El enlace ha expirado o no es válido.";
        }
    } catch (PDOException $e) {
        $errors[] = "Error al verificar el token.";
    }
}

// Procesar el formulario de cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($token)) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validaciones
    if (strlen($password) < 6) {
        $errors[] = "La contraseña debe tener al menos 6 caracteres.";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    // Si no hay errores, actualizar la contraseña
    if (empty($errors) && isset($reset)) {
        try {
            // Iniciar transacción
            $pdo->beginTransaction();

            // Actualizar la contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashedPassword, $reset['user_id']]);

            // Marcar el token como usado
            $stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE id = ?");
            $stmt->execute([$reset['id']]);

            // Confirmar transacción
            $pdo->commit();

            $success = true;
            $_SESSION['success'] = "Tu contraseña ha sido actualizada correctamente.";
            
            // Redirigir después de 2 segundos
            header("refresh:2;url=login.php");
        } catch (PDOException $e) {
            $pdo->rollBack();
            $errors[] = "Error al actualizar la contraseña.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Hackademia</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-form">
            <h2>Restablecer Contraseña</h2>

            <?php if ($success): ?>
                <div class="success-message">
                    <p>Tu contraseña ha sido actualizada correctamente.</p>
                    <p>Serás redirigido al inicio de sesión...</p>
                </div>
            <?php elseif (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php if ($errors[0] === "El enlace ha expirado o no es válido."): ?>
                    <div class="auth-links">
                        <p><a href="forgot-password.php">Solicitar un nuevo enlace</a></p>
                    </div>
                <?php endif; ?>
            <?php elseif (!empty($token) && isset($reset)): ?>
                <form method="POST">
                    <div class="form-group">
                        <label for="password">Nueva Contraseña</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               minlength="6"
                               placeholder="Mínimo 6 caracteres"
                               autocomplete="new-password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña</label>
                        <input type="password" 
                               id="confirm_password" 
                               name="confirm_password" 
                               required
                               placeholder="Repite tu contraseña"
                               autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary">Cambiar Contraseña</button>
                </form>
            <?php endif; ?>

            <div class="auth-links">
                <p><a href="login.php">Volver al inicio de sesión</a></p>
            </div>
        </div>
    </div>
</body>
</html>
