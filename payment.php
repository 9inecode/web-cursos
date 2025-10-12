<?php
session_start();
require_once 'config/db.php';
require_once 'config/notifications.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Verificar si ya está inscrito o pendiente
$stmt = $pdo->prepare("SELECT enrolled, payment_status FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if ($user['payment_status'] === 'completed') {
    header('Location: dashboard.php');
    exit();
}

// Notificar acceso a página de pago alternativo (sistema simplificado como backup)
try {
    require_once 'config/notifications.php';
    notify_payment_page_access($pdo, $_SESSION['user_id'], 'Pago por Transferencia Bancaria');
} catch (Exception $e) {
    // Usar sistema simplificado como backup
    require_once 'config/simple-notifications.php';
    notify_simple_payment_access($pdo, $_SESSION['user_id'], 'Pago por Transferencia Bancaria');
}

$upload_error = '';
$upload_success = false;

// Procesar el envío del comprobante
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar que se subió un archivo
    if (!isset($_FILES['payment_proof'])) {
        $upload_error = 'No se seleccionó ningún archivo.';
    } else {
        $file = $_FILES['payment_proof'];
        
        // Verificar errores de upload
        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                $upload_error = 'No se seleccionó ningún archivo.';
                break;
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $upload_error = 'El archivo es demasiado grande. Máximo 2MB.';
                break;
            default:
                $upload_error = 'Error desconocido al subir el archivo.';
                break;
        }
        
        if (empty($upload_error)) {
            $allowed = ['jpg', 'jpeg', 'png', 'pdf'];
            $filename = $file['name'];
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            // Verificar extensión
            if (!in_array($ext, $allowed)) {
                $upload_error = 'Formato no permitido. Solo se aceptan: JPG, PNG, PDF.';
            } else {
                // Verificar tamaño (2MB máximo según configuración PHP)
                if ($file['size'] > 2 * 1024 * 1024) {
                    $upload_error = 'El archivo es demasiado grande. Máximo 2MB.';
                } else {
                    // Crear directorio si no existe
                    $upload_dir = 'uploads/payments/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    $newname = uniqid() . '.' . $ext;
                    $destination = $upload_dir . $newname;
                    
                    // Intentar mover el archivo
                    if (move_uploaded_file($file['tmp_name'], $destination)) {
                        try {
                            $stmt = $pdo->prepare("
                                UPDATE users 
                                SET payment_reference = ?,
                                    payment_status = 'pending',
                                    payment_date = NOW()
                                WHERE id = ?
                            ");
                            $result = $stmt->execute([$destination, $_SESSION['user_id']]);
                            
                            if ($result) {
                                $upload_success = true;
                                
                                // Notificar al admin sobre el comprobante subido
                                try {
                                    notify_payment_proof_uploaded($pdo, $_SESSION['user_id'], $newname);
                                } catch (Exception $e) {
                                    // Usar sistema simplificado como backup
                                    require_once 'config/simple-notifications.php';
                                    notify_simple_proof_uploaded($pdo, $_SESSION['user_id'], $newname);
                                }
                                
                                // No redirigir, mantener en la página para mostrar mensaje de éxito
                            } else {
                                $upload_error = 'Error al actualizar la base de datos - Query falló.';
                                if (file_exists($destination)) {
                                    unlink($destination);
                                }
                            }
                        } catch (Exception $e) {
                            $upload_error = 'Error al actualizar la base de datos: ' . $e->getMessage();
                            // Eliminar archivo si falló la BD
                            if (file_exists($destination)) {
                                unlink($destination);
                            }
                        }
                    } else {
                        $upload_error = 'Error al guardar el archivo. Verifique los permisos.';
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desbloquear Dojo - CrowDojo Academy</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .payment-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        .payment-card {
            background: white;
            border-radius: 16px;
            padding: 3rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #2d3748;
            text-align: center;
            margin: 0 0 2rem 0;
            font-size: 2rem;
        }

        .price-box {
            background: #f7fafc;
            border-radius: 12px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 2rem;
        }

        .price-amount {
            font-size: 3rem;
            font-weight: bold;
            color: #667eea;
        }

        .price-detail {
            color: #4a5568;
            margin-top: 0.5rem;
        }

        .bank-details {
            background: #f7fafc;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .bank-details h2 {
            color: #2d3748;
            margin: 0 0 1.5rem 0;
            font-size: 1.5rem;
        }

        .bank-info {
            display: grid;
            gap: 0.75rem;
        }

        .bank-info p {
            margin: 0;
            padding: 1rem;
            background: #f7fafc;
            border-radius: 8px;
            color: #4a5568;
            display: flex;
            align-items: center;
        }

        .bank-info strong {
            color: #2d3748;
            display: inline-block;
            width: 100px;
            flex-shrink: 0;
        }

        .upload-section {
            background: #f7fafc;
            border-radius: 12px;
            padding: 2rem;
        }

        .upload-section h2 {
            color: #2d3748;
            margin: 0 0 1.5rem 0;
            font-size: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #4a5568;
        }

        .form-group input[type="file"] {
            width: 100%;
            padding: 0.5rem;
            background: white;
            border-radius: 8px;
        }

        .form-text {
            font-size: 0.875rem;
            color: #718096;
            margin-top: 0.5rem;
        }

        .btn-submit {
            background: #667eea;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .payment-card {
                padding: 2rem 1.5rem;
            }

            .bank-info p {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }

            .bank-info strong {
                width: auto;
            }
        }

        .payment-option {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
        }

        .payment-option:last-child {
            margin-bottom: 0;
        }

        .payment-option h3 {
            color: #667eea;
            margin: 0 0 1.5rem 0;
            font-size: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .payment-option h3::before {
            content: "";
            display: inline-block;
            width: 24px;
            height: 24px;
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        .payment-option:first-child h3::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23667eea'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'%3E%3C/path%3E%3C/svg%3E");
        }

        .payment-option:last-child h3::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%23667eea'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z'%3E%3C/path%3E%3C/svg%3E");
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .alert-error {
            background: #fed7d7;
            color: #c53030;
            border: 1px solid #feb2b2;
        }

        .alert-success {
            background: #c6f6d5;
            color: #2f855a;
            border: 1px solid #9ae6b4;
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <h1>🐦‍⬛ Desbloquear Dojo Completo</h1>
            <p style="text-align: center; color: #4a5568; font-size: 1.1rem; margin-bottom: 2rem;">
                Únete a la élite de guerreros cibernéticos y domina el arte del hacking ético
            </p>

            <div class="price-box">
                <div class="price-amount">GS. 80.000</div>
                <div class="price-detail">🥋 Acceso de por vida al CrowDojo Academy completo</div>
                <div style="margin-top: 1rem; font-size: 0.9rem; color: #718096;">
                    ✅ Todos los módulos • ✅ Laboratorios prácticos • ✅ Comunidad premium • ✅ Soporte 24/7
                </div>
            </div>

            <div class="bank-details">
                <h2>Opciones de Pago</h2>
                
                <div class="payment-option">
                    <h3>🏦 Transferencia Bancaria</h3>
                    <div class="bank-info">
                        <p><strong>Titular:</strong> Fidel Acevedo Gonzalez</p>
                        <p><strong>CI:</strong> 4082736</p>
                        <p><strong>Entidad:</strong> Ueno Bank</p>
                        <p><strong>N° Cuenta:</strong> 6191108212</p>
                        <p><strong>Moneda:</strong> GS</p>
                    </div>
                </div>

                <div class="payment-option">
                    <h3>📱 Personal Pay</h3>
                    <div class="bank-info">
                        <p><strong>Número:</strong> 0985 185 604</p>
                        <p><strong>CI:</strong> 4082736</p>
                        <p><strong>Titular:</strong> Fidel Acevedo Gonzalez</p>
                    </div>
                </div>
            </div>

            <div class="upload-section">
                <h2>Subir Comprobante</h2>
                
                <?php if (!empty($upload_error)): ?>
                    <div class="alert alert-error">
                        ❌ <?php echo htmlspecialchars($upload_error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($upload_success): ?>
                    <div class="alert alert-success">
                        ✅ <strong>¡Comprobante enviado exitosamente!</strong><br>
                        📄 Tu comprobante ha sido recibido y está siendo revisado<br>
                        📧 Recibirás una confirmación por email una vez aprobado<br>
                        🕐 El proceso de revisión toma entre 1-24 horas<br><br>
                        <a href="dashboard.php" style="background: #667eea; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; display: inline-block; margin-top: 0.5rem;">
                            ← Volver al Dashboard
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if (!$upload_success): ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="MAX_FILE_SIZE" value="2097152">
                    <div class="form-group">
                        <label for="payment_proof">Comprobante de Pago</label>
                        <input type="file" 
                               id="payment_proof" 
                               name="payment_proof" 
                               accept=".jpg,.jpeg,.png,.pdf" 
                               required>
                        <div class="form-text">
                            Formatos aceptados: JPG, PNG, PDF. Tamaño máximo: 2MB
                        </div>
                    </div>

                    <button type="submit" class="btn-submit">
                        Enviar Comprobante
                    </button>
                </form>
                <?php else: ?>
                <div style="text-align: center; padding: 2rem; background: #f7fafc; border-radius: 8px;">
                    <h3 style="color: #2d3748; margin-bottom: 1rem;">¿Necesitas enviar otro comprobante?</h3>
                    <p style="color: #4a5568; margin-bottom: 1.5rem;">
                        Si tienes un comprobante adicional o necesitas reemplazar el anterior, puedes hacerlo aquí.
                    </p>
                    <a href="payment.php" style="background: #667eea; color: white; padding: 0.75rem 1.5rem; text-decoration: none; border-radius: 6px; display: inline-block;">
                        📎 Subir Otro Comprobante
                    </a>
                </div>
                <?php endif; ?>
                
                <div style="margin-top: 1rem; text-align: center;">
                    <a href="debug_payment.php" style="color: #667eea; font-size: 0.9rem;">Ver estado de pago (Debug)</a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($upload_success): ?>
    <script>
        // Scroll suave al mensaje de éxito
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.querySelector('.alert-success');
            if (successAlert) {
                successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                
                // Efecto de highlight
                successAlert.style.animation = 'highlight 2s ease-in-out';
            }
        });
    </script>
    <style>
        @keyframes highlight {
            0% { box-shadow: 0 0 0 0 rgba(72, 187, 120, 0.7); }
            50% { box-shadow: 0 0 0 10px rgba(72, 187, 120, 0.3); }
            100% { box-shadow: 0 0 0 0 rgba(72, 187, 120, 0); }
        }
    </style>
    <?php endif; ?>
</body>
</html>
