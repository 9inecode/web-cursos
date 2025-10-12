<?php
session_start();
require_once 'config/db.php';
require_once 'config/mail.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Por favor, introduce un email válido";
    } else {
        try {
            // Verificar si el email existe
            $stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Generar token único
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Guardar el token en la base de datos
                $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expiry) VALUES (?, ?, ?)");
                $stmt->execute([$user['id'], $token, $expiry]);
                
                // Preparar el correo
                $resetLink = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
                $subject = "Recuperación de contraseña - Hackademia";
                $body = "
                    <h2>Recuperación de contraseña</h2>
                    <p>Hola {$user['username']},</p>
                    <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                    <p><a href='{$resetLink}'>{$resetLink}</a></p>
                    <p>Este enlace expirará en 1 hora.</p>
                    <p>Si no solicitaste este cambio, puedes ignorar este correo.</p>
                    <br>
                    <p>Saludos,<br>Equipo de Hackademia</p>
                ";
                
                if (sendMail($email, $subject, $body)) {
                    $success = true;
                } else {
                    $errors[] = "Error al enviar el correo. Por favor, intenta más tarde.";
                }
            } else {
                // Por seguridad, simulamos éxito incluso si el email no existe
                $success = true;
            }
        } catch (PDOException $e) {
            $errors[] = "Error al procesar la solicitud";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - Hackademia</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <form class="auth-form" method="POST">
            <h2>Recuperar Contraseña</h2>

            <?php if ($success): ?>
                <div class="success-message">
                    <p>Si el email existe en nuestra base de datos, recibirás un enlace para restablecer tu contraseña.</p>
                    <p>Por favor, revisa tu bandeja de entrada y la carpeta de spam.</p>
                </div>
            <?php else: ?>
                <?php if (!empty($errors)): ?>
                    <div class="error-message">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo htmlspecialchars($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email">Correo electrónico</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required
                           placeholder="Tu correo electrónico"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           autocomplete="email">
                </div>

                <button type="submit" class="btn btn-primary">Enviar enlace de recuperación</button>
            <?php endif; ?>

            <div class="auth-links">
                <p>¿Recordaste tu contraseña? <a href="login.php">Iniciar sesión</a></p>
                <p>¿No tienes una cuenta? <a href="register.php">Regístrate</a></p>
            </div>
        </form>
    </div>
</body>
</html>
