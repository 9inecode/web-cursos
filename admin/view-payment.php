<?php
session_start();
require_once '../config/db.php';

// Verificar si es admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_GET['id'] ?? '';
if (!$user_id) {
    header('Location: payment-management.php');
    exit();
}

// Obtener información del usuario y su pago
$stmt = $pdo->prepare("
    SELECT id, username, email, payment_status, payment_reference, payment_date, created_at 
    FROM users 
    WHERE id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    header('Location: payment-management.php');
    exit();
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'approve_payment') {
        $stmt = $pdo->prepare("UPDATE users SET payment_status = 'completed', payment_date = NOW() WHERE id = ?");
        $stmt->execute([$user_id]);
        $success_message = "Pago aprobado exitosamente";
        // Recargar datos del usuario
        $stmt = $pdo->prepare("SELECT id, username, email, payment_status, payment_reference, payment_date, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } elseif ($action === 'reject_payment') {
        $stmt = $pdo->prepare("UPDATE users SET payment_status = 'failed' WHERE id = ?");
        $stmt->execute([$user_id]);
        $success_message = "Pago rechazado";
        // Recargar datos del usuario
        $stmt = $pdo->prepare("SELECT id, username, email, payment_status, payment_reference, payment_date, created_at FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Comprobante de Pago - CrowDojo Academy</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, -apple-system, sans-serif;
            background: #f7fafc;
            color: #2d3748;
        }

        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .admin-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
        }

        .payment-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .user-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background: #f7fafc;
            padding: 1rem;
            border-radius: 8px;
        }

        .info-label {
            font-size: 0.875rem;
            color: #4a5568;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 600;
            color: #2d3748;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-completed {
            background: #dcfce7;
            color: #166534;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .payment-proof {
            text-align: center;
            margin: 2rem 0;
        }

        .payment-proof img {
            max-width: 100%;
            max-height: 600px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .payment-proof iframe {
            width: 100%;
            height: 600px;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.2s ease;
        }

        .btn-approve {
            background: #48bb78;
            color: white;
        }

        .btn-reject {
            background: #f56565;
            color: white;
        }

        .btn-back {
            background: #667eea;
            color: white;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        .no-proof {
            text-align: center;
            color: #a0aec0;
            font-style: italic;
            padding: 2rem;
            background: #f7fafc;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>🐦‍⬛ Comprobante de Pago</h1>
        <p>Usuario: <?php echo htmlspecialchars($user['username']); ?></p>
    </div>

    <div class="admin-container">
        <?php if (isset($success_message)): ?>
            <div class="success-message">
                ✅ <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <div class="payment-card">
            <h2>Información del Usuario</h2>
            <div class="user-info">
                <div class="info-item">
                    <div class="info-label">ID de Usuario</div>
                    <div class="info-value"><?php echo $user['id']; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Nombre de Usuario</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['username']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Estado del Pago</div>
                    <div class="info-value">
                        <span class="status-badge status-<?php echo $user['payment_status']; ?>">
                            <?php echo ucfirst($user['payment_status']); ?>
                        </span>
                    </div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Registro</div>
                    <div class="info-value"><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Fecha de Pago</div>
                    <div class="info-value">
                        <?php echo $user['payment_date'] ? date('d/m/Y H:i', strtotime($user['payment_date'])) : 'Pendiente'; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="payment-card">
            <h2>Comprobante de Pago</h2>
            <?php if ($user['payment_reference']): ?>
                <div class="payment-proof">
                    <?php
                    $file_path = '../' . $user['payment_reference'];
                    $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                    
                    if (file_exists($file_path)):
                        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])):
                    ?>
                        <img src="<?php echo htmlspecialchars($file_path); ?>" alt="Comprobante de Pago">
                    <?php elseif ($file_extension === 'pdf'): ?>
                        <iframe src="<?php echo htmlspecialchars($file_path); ?>"></iframe>
                        <br><br>
                        <a href="<?php echo htmlspecialchars($file_path); ?>" target="_blank" class="btn btn-back">
                            📄 Abrir PDF en nueva ventana
                        </a>
                    <?php else: ?>
                        <div class="no-proof">
                            <p>Tipo de archivo no soportado para vista previa</p>
                            <a href="<?php echo htmlspecialchars($file_path); ?>" target="_blank" class="btn btn-back">
                                📄 Descargar Archivo
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php else: ?>
                        <div class="no-proof">
                            <p>❌ Archivo no encontrado en el servidor</p>
                            <p>Ruta: <?php echo htmlspecialchars($user['payment_reference']); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="no-proof">
                    <p>No se ha subido ningún comprobante de pago</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="actions">
            <?php if ($user['payment_status'] === 'pending'): ?>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="action" value="approve_payment" class="btn btn-approve">
                        ✅ Aprobar Pago
                    </button>
                </form>
                <form method="POST" style="display: inline;">
                    <button type="submit" name="action" value="reject_payment" class="btn btn-reject">
                        ❌ Rechazar Pago
                    </button>
                </form>
            <?php endif; ?>
            <a href="payment-management.php" class="btn btn-back">
                ← Volver a Gestión de Pagos
            </a>
        </div>
    </div>
</body>
</html>