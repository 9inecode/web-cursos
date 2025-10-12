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
    <title>Gestionar Usuarios - Bug Bounty</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .admin-navbar {
            background-color: #2c3e50;
            padding: 1rem;
            color: white;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .users-table {
            width: 100%;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border: 1px solid #ddd;
        }

        .users-table th, .users-table td {
            text-align: left;
            padding: 12px;
        }

        .users-table th {
            background-color: #f8f9fa;
        }

        .users-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .users-table tr:hover {
            background-color: #e9e9e9;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }

        .btn:hover {
            background-color: #45a049;
        }

        .payment-status {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-none {
            background-color: #e2e3e5;
            color: #383d41;
        }

        .btn-approve {
            background-color: #28a745;
        }

        .btn-reject {
            background-color: #dc3545;
        }

        .payment-proof {
            max-width: 100px;
            cursor: pointer;
        }

        /* Modal para la imagen */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 1000;
        }

        .modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            max-width: 90%;
            max-height: 90%;
        }

        .modal-content img {
            max-width: 100%;
            max-height: 90vh;
        }
    </style>
</head>
<body>
    <div class="admin-navbar">
        <div class="container">
            <h1>Gestionar Usuarios</h1>
        </div>
    </div>

    <div class="container">
        <table class="users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado de Pago</th>
                    <th>Comprobante</th>
                    <th>Fecha de Pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['role']); ?></td>
                        <td>
                            <span class="payment-status status-<?php echo $user['payment_status'] ?? 'none'; ?>">
                                <?php 
                                    switch($user['payment_status']) {
                                        case 'pending':
                                            echo 'Pendiente';
                                            break;
                                        case 'completed':
                                            echo 'Pagado';
                                            break;
                                        case 'rejected':
                                            echo 'Rechazado';
                                            break;
                                        default:
                                            echo 'Sin pago';
                                    }
                                ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($user['payment_reference']): ?>
                                <img src="../<?php echo htmlspecialchars($user['payment_reference']); ?>" 
                                     class="payment-proof" 
                                     onclick="showImage(this.src)" 
                                     alt="Comprobante">
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td><?php echo $user['payment_date'] ? date('d/m/Y H:i', strtotime($user['payment_date'])) : '-'; ?></td>
                        <td>
                            <?php if ($user['payment_status'] === 'pending'): ?>
                                <form action="manage-users.php" method="post" style="display: inline;">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" name="approve_payment" class="btn btn-approve">Aprobar Pago</button>
                                    <button type="submit" name="reject_payment" class="btn btn-reject">Rechazar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para mostrar imagen -->
    <div id="imageModal" class="modal" onclick="this.style.display='none'">
        <div class="modal-content">
            <img id="modalImage" src="" alt="Comprobante de pago">
        </div>
    </div>

    <script>
        function showImage(src) {
            document.getElementById('modalImage').src = src;
            document.getElementById('imageModal').style.display = 'block';
        }
    </script>
</body>
</html>
