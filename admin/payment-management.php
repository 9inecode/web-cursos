<?php
session_start();
require_once '../config/db.php';

// Verificar si es admin (simplificado)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $user_id = $_POST['user_id'] ?? '';
    
    if ($action === 'approve_payment' && $user_id) {
        $stmt = $pdo->prepare("UPDATE users SET payment_status = 'completed', payment_date = NOW() WHERE id = ?");
        $stmt->execute([$user_id]);
        $success_message = "Pago aprobado para el usuario ID: $user_id";
    } elseif ($action === 'reject_payment' && $user_id) {
        $stmt = $pdo->prepare("UPDATE users SET payment_status = 'failed' WHERE id = ?");
        $stmt->execute([$user_id]);
        $success_message = "Pago rechazado para el usuario ID: $user_id";
    }
}

// Obtener usuarios con pagos pendientes
$stmt = $pdo->query("
    SELECT id, username, email, payment_status, payment_date, created_at 
    FROM users 
    WHERE payment_status IN ('pending', 'failed') 
    ORDER BY created_at DESC
");
$pending_users = $stmt->fetchAll();

// Obtener usuarios con pagos completados (últimos 10)
$stmt = $pdo->query("
    SELECT id, username, email, payment_status, payment_date 
    FROM users 
    WHERE payment_status = 'completed' 
    ORDER BY payment_date DESC 
    LIMIT 10
");
$completed_users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pagos - CrowDojo Academy</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .section h2 {
            margin: 0 0 1.5rem 0;
            color: #2d3748;
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }

        .users-table th,
        .users-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        .users-table th {
            background: #f7fafc;
            font-weight: 600;
            color: #4a5568;
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
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

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 0.25rem;
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

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .success-message {
            background: #dcfce7;
            color: #166534;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #4a5568;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>🐦‍⬛ Panel de Gestión de Pagos</h1>
        <p>CrowDojo Academy - Administración</p>
    </div>

    <div class="admin-container">
        <?php if (isset($success_message)): ?>
            <div class="success-message">
                ✅ <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Estadísticas -->
        <div class="stats-grid">
            <?php
            $stats = $pdo->query("
                SELECT 
                    COUNT(CASE WHEN payment_status = 'pending' THEN 1 END) as pending,
                    COUNT(CASE WHEN payment_status = 'completed' THEN 1 END) as completed,
                    COUNT(CASE WHEN payment_status = 'failed' THEN 1 END) as failed,
                    COUNT(*) as total
                FROM users
            ")->fetch();
            ?>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['pending']; ?></div>
                <div class="stat-label">Pagos Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['completed']; ?></div>
                <div class="stat-label">Pagos Completados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['failed']; ?></div>
                <div class="stat-label">Pagos Fallidos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $stats['total']; ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
        </div>

        <!-- Pagos Pendientes -->
        <div class="section">
            <h2>⏳ Pagos Pendientes de Aprobación</h2>
            <?php if (empty($pending_users)): ?>
                <p style="color: #718096;">No hay pagos pendientes.</p>
            <?php else: ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Fecha Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['payment_status']; ?>">
                                        <?php echo ucfirst($user['payment_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="action" value="approve_payment" class="btn btn-approve">
                                            ✅ Aprobar
                                        </button>
                                        <button type="submit" name="action" value="reject_payment" class="btn btn-reject">
                                            ❌ Rechazar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Pagos Completados Recientes -->
        <div class="section">
            <h2>✅ Pagos Completados Recientes</h2>
            <?php if (empty($completed_users)): ?>
                <p style="color: #718096;">No hay pagos completados aún.</p>
            <?php else: ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Fecha Pago</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($completed_users as $user): ?>
                            <tr>
                                <td><?php echo $user['id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td>
                                    <span class="status-badge status-completed">
                                        Completado
                                    </span>
                                </td>
                                <td><?php echo $user['payment_date'] ? date('d/m/Y H:i', strtotime($user['payment_date'])) : 'N/A'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div style="text-align: center; margin-top: 2rem;">
            <a href="../dashboard.php" class="btn" style="background: #667eea; color: white; padding: 1rem 2rem;">
                ← Volver al Dashboard
            </a>
        </div>
    </div>
</body>
</html>