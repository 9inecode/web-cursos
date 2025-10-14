<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar estado del usuario
$stmt = $pdo->prepare("SELECT enrolled, payment_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['enrolled'] && $user['payment_status'] === 'completed') {
    header('Location: dashboard.php');
    exit();
}

// Procesar el envío del comprobante
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reference = trim($_POST['payment_reference']);
    
    if (!empty($reference)) {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET payment_reference = ?, 
                payment_status = 'pending',
                payment_date = NOW() 
            WHERE id = ?
        ");
        $stmt->execute([$reference, $_SESSION['user_id']]);
        
        $_SESSION['payment_message'] = 'Tu comprobante ha sido enviado. Revisaremos tu pago y activaremos tu cuenta pronto.';
        header('Location: payment-status.php');
        exit();
    }
}

$pageTitle = 'Inscripción al Curso';
require_once 'includes/header.php';
?>

<div class="enroll-page">
    <div class="enroll-container">
        <div class="course-info">
            <h1>Bug Bounty: De Cero a Experto</h1>
            
            <!-- Información del curso -->
            <div class="course-highlights">
                <div class="highlight-item">
                    <span class="highlight-icon">📚</span>
                    <h3>Contenido Completo</h3>
                    <p>Más de 50 horas de contenido actualizado</p>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon">🎯</span>
                    <h3>Prácticas Reales</h3>
                    <p>Casos de estudio y laboratorios prácticos</p>
                </div>
                <div class="highlight-item">
                    <span class="highlight-icon">🏆</span>
                    <h3>Certificación</h3>
                    <p>Certificado al completar el curso</p>
                </div>
            </div>

            <!-- Detalles de pago -->
            <div class="payment-section">
                <h2>Información de Pago</h2>
                <div class="price-box">
                    <div class="price">$99.99 USD</div>
                    <div class="price-details">Acceso de por vida al curso completo</div>
                </div>

                <div class="bank-info">
                    <h3>Datos Bancarios</h3>
                    <div class="bank-details">
                        <p><strong>Banco:</strong> Tu Banco</p>
                        <p><strong>Titular:</strong> Tu Nombre</p>
                        <p><strong>Cuenta:</strong> 1234-5678-9012-3456</p>
                        <p><strong>Tipo:</strong> Cuenta Corriente</p>
                    </div>
                </div>

                <form method="POST" class="payment-form">
                    <div class="form-group">
                        <label for="payment_reference">Número de Referencia del Pago</label>
                        <input type="text" 
                               id="payment_reference" 
                               name="payment_reference" 
                               required 
                               placeholder="Ingresa el número de referencia de tu pago"
                               class="form-control">
                        <small class="form-text">Por favor, ingresa el número de referencia o captura de tu transferencia</small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-large">
                        Enviar Comprobante de Pago
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
