<?php
session_start();
require_once '../config/db.php';

// Verificar si es admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Cambiar rol de usuario
if (isset($_POST['change_role'])) {
    $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->execute([$_POST['new_role'], $_POST['user_id']]);
    header('Location: manage-users.php');
    exit();
}

// Aprobar pago
if (isset($_POST['approve_payment'])) {
    $stmt = $pdo->prepare("UPDATE users SET payment_status = 'completed', enrolled = TRUE WHERE id = ?");
    $stmt->execute([$_POST['user_id']]);
    header('Location: manage-users.php');
    exit();
}

// Rechazar pago
if (isset($_POST['reject_payment'])) {
    $stmt = $pdo->prepare("UPDATE users SET payment_status = 'rejected' WHERE id = ?");
    $stmt->execute([$_POST['user_id']]);
    header('Location: manage-users.php');
    exit();
}

// Eliminar usuario
if (isset($_POST['delete_user'])) {
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND id != ?");
    $stmt->execute([$_POST['delete_user'], $_SESSION['user_id']]);
    header('Location: manage-users.php');
    exit();
}

// Obtener todos los usuarios con su estado de pago
$stmt = $pdo->query("
    SELECT id, username, email, role, created_at, payment_status, payment_reference, payment_date 
    FROM users 
    ORDER BY 
        CASE 
            WHEN payment_status = 'pending' THEN 1
            WHEN payment_status = 'completed' THEN 2
            ELSE 3
        END,
        created_at DESC
");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Usuarios - CrowDojo Academy</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .admin-navbar {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .nav-content {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 1rem;
        }

        .nav-content h1 {
            color: white;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .nav-buttons {
            display: flex;
            gap: 1rem;
        }

        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .users-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h2 {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .filter-tabs {
            display: flex;
            gap: 0.5rem;
        }

        .filter-tab {
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .filter-tab:hover, .filter-tab.active {
            background: rgba(255, 255, 255, 0.3);
        }

        .users-table {
            width: 100%;
            border-collapse: collapse;
        }

        .users-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e9ecef;
        }

        .users-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .users-table tr:hover {
            background-color: #f8f9fa;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .user-details h4 {
            margin: 0;
            color: #333;
            font-size: 1rem;
        }

        .user-details p {
            margin: 0;
            color: #666;
            font-size: 0.85rem;
        }

        .role-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .role-admin {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
        }

        .role-user {
            background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%);
            color: white;
        }

        .payment-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-align: center;
            min-width: 100px;
        }

        .status-pending {
            background: linear-gradient(135deg, #fdcb6e 0%, #e17055 100%);
            color: white;
        }

        .status-completed {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
        }

        .status-rejected {
            background: linear-gradient(135deg, #d63031 0%, #74b9ff 100%);
            color: white;
        }

        .status-none {
            background: #e9ecef;
            color: #6c757d;
        }

        .payment-proof {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            cursor: pointer;
            object-fit: cover;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .payment-proof:hover {
            transform: scale(1.1);
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .btn-approve {
            background: linear-gradient(135deg, #00b894 0%, #00a085 100%);
            color: white;
        }

        .btn-reject {
            background: linear-gradient(135deg, #d63031 0%, #b71c1c 100%);
            color: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            backdrop-filter: blur(5px);
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-content img {
            max-width: 100%;
            max-height: 90vh;
            display: block;
        }

        .modal-close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.9);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .empty-state h3 {
            margin-bottom: 1rem;
            color: #333;
        }

        @media (max-width: 768px) {
            .nav-content {
                flex-direction: column;
                gap: 1rem;
            }

            .stats-bar {
                grid-template-columns: 1fr;
            }

            .users-table {
                font-size: 0.85rem;
            }

            .users-table th,
            .users-table td {
                padding: 0.75rem 0.5rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .filter-tabs {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <div class="nav-content">
            <h1>👥 Gestionar Usuarios</h1>
            <div class="nav-buttons">
                <a href="dashboard.php" class="btn btn-secondary">← Panel Admin</a>
                <a href="payment-management.php" class="btn btn-primary">💳 Gestión de Pagos</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php
        // Calcular estadísticas
        $total_users = count($users);
        $pending_payments = count(array_filter($users, fn($u) => $u['payment_status'] === 'pending'));
        $completed_payments = count(array_filter($users, fn($u) => $u['payment_status'] === 'completed'));
        $admin_users = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
        ?>

        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Usuarios</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pending_payments; ?></div>
                <div class="stat-label">Pagos Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $completed_payments; ?></div>
                <div class="stat-label">Pagos Completados</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $admin_users; ?></div>
                <div class="stat-label">Administradores</div>
            </div>
        </div>

        <div class="users-container">
            <div class="table-header">
                <h2>📋 Lista de Usuarios</h2>
                <div class="filter-tabs">
                    <button class="filter-tab active" onclick="filterUsers('all')">Todos</button>
                    <button class="filter-tab" onclick="filterUsers('pending')">Pendientes</button>
                    <button class="filter-tab" onclick="filterUsers('completed')">Pagados</button>
                    <button class="filter-tab" onclick="filterUsers('admin')">Admins</button>
                </div>
            </div>

            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <h3>No hay usuarios registrados</h3>
                    <p>Los usuarios aparecerán aquí cuando se registren en el sistema.</p>
                </div>
            <?php else: ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Estado de Pago</th>
                            <th>Comprobante</th>
                            <th>Fecha de Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr class="user-row" 
                                data-status="<?php echo $user['payment_status'] ?? 'none'; ?>" 
                                data-role="<?php echo $user['role']; ?>">
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                        </div>
                                        <div class="user-details">
                                            <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                                            <p><?php echo htmlspecialchars($user['email']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="role-badge role-<?php echo $user['role']; ?>">
                                        <?php echo $user['role'] === 'admin' ? '👑 Admin' : '👤 Usuario'; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="payment-status status-<?php echo $user['payment_status'] ?? 'none'; ?>">
                                        <?php 
                                            switch($user['payment_status']) {
                                                case 'pending':
                                                    echo '⏳ Pendiente';
                                                    break;
                                                case 'completed':
                                                    echo '✅ Pagado';
                                                    break;
                                                case 'rejected':
                                                    echo '❌ Rechazado';
                                                    break;
                                                default:
                                                    echo '⚪ Sin pago';
                                            }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['payment_reference']): ?>
                                        <img src="../<?php echo htmlspecialchars($user['payment_reference']); ?>" 
                                             class="payment-proof" 
                                             onclick="showImage(this.src)" 
                                             alt="Comprobante"
                                             title="Click para ver comprobante">
                                    <?php else: ?>
                                        <span style="color: #999;">Sin comprobante</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="font-size: 0.9rem;">
                                        <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                                        <br>
                                        <small style="color: #666;">
                                            <?php echo date('H:i', strtotime($user['created_at'])); ?>
                                        </small>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($user['payment_status'] === 'pending'): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="approve_payment" 
                                                        class="btn btn-approve"
                                                        onclick="return confirm('¿Aprobar el pago de este usuario?')">
                                                    ✅ Aprobar
                                                </button>
                                            </form>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <button type="submit" name="reject_payment" 
                                                        class="btn btn-reject"
                                                        onclick="return confirm('¿Rechazar el pago de este usuario?')">
                                                    ❌ Rechazar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['role'] !== 'admin' && $user['id'] != $_SESSION['user_id']): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="new_role" value="admin">
                                                <button type="submit" name="change_role" 
                                                        class="btn btn-primary"
                                                        onclick="return confirm('¿Hacer administrador a este usuario?')">
                                                    👑 Hacer Admin
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                            <form method="post" style="display: inline;">
                                                <input type="hidden" name="delete_user" value="<?php echo $user['id']; ?>">
                                                <button type="submit" 
                                                        class="btn btn-danger"
                                                        onclick="return confirm('¿ELIMINAR este usuario? Esta acción no se puede deshacer.')">
                                                    🗑️ Eliminar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para mostrar imagen -->
    <div id="imageModal" class="modal" onclick="closeModal()">
        <button class="modal-close" onclick="closeModal()">×</button>
        <div class="modal-content" onclick="event.stopPropagation()">
            <img id="modalImage" src="" alt="Comprobante de pago">
        </div>
    </div>

    <script>
        function showImage(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        function filterUsers(filter) {
            // Actualizar tabs activos
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            event.target.classList.add('active');

            // Filtrar filas
            const rows = document.querySelectorAll('.user-row');
            rows.forEach(row => {
                const status = row.dataset.status;
                const role = row.dataset.role;
                
                let show = false;
                
                switch(filter) {
                    case 'all':
                        show = true;
                        break;
                    case 'pending':
                        show = status === 'pending';
                        break;
                    case 'completed':
                        show = status === 'completed';
                        break;
                    case 'admin':
                        show = role === 'admin';
                        break;
                }
                
                row.style.display = show ? '' : 'none';
            });
        }

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeModal();
            }
        });

        // Animaciones de entrada
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = '0';
                    card.style.transform = 'translateY(20px)';
                    card.style.transition = 'all 0.5s ease';
                    
                    setTimeout(() => {
                        card.style.opacity = '1';
                        card.style.transform = 'translateY(0)';
                    }, 100);
                }, index * 100);
            });
        });
    </script>
</body>
</html>
