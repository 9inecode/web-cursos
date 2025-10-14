<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$stmt = $pdo->prepare("SELECT payment_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$pageTitle = 'Estado del Pago';
require_once 'includes/header.php';
?>

<div class="payment-status-page">
    <div class="status-container">
        <div class="status-card">
            <h2>Estado de tu Inscripción</h2>
            
            <?php if (isset($_SESSION['payment_message'])): ?>
                <div class="success-message">
                    <?php 
                    echo $_SESSION['payment_message'];
                    unset($_SESSION['payment_message']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="status-info">
                <?php if ($user['payment_status'] === 'pending'): ?>
                    <div class="status-pending">
                        <h3>Pago en Revisión</h3>
                        <p>Estamos verificando tu pago. Este proceso puede tomar hasta 24 horas hábiles.</p>
                    </div>
                <?php elseif ($user['payment_status'] === 'completed'): ?>
                    <div class="status-completed">
                        <h3>¡Pago Confirmado!</h3>
                        <p>Tu acceso al curso ha sido activado.</p>
                        <a href="dashboard.php" class="btn btn-primary">Ir al Curso</a>
                    </div>
                <?php elseif ($user['payment_status'] === 'rejected'): ?>
                    <div class="status-rejected">
                        <h3>Pago Rechazado</h3>
                        <p>Tu pago no pudo ser verificado. Por favor, intenta nuevamente.</p>
                        <a href="enroll.php" class="btn btn-primary">Volver a Intentar</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

</body>
</html>
