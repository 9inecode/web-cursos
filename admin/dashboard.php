<?php
session_start();
require_once '../config/db.php';

// Verificar si es admin (usuario ID = 1)
if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: ../login.php');
    exit();
}

// Obtener estadísticas
$stats = [
    'total_videos' => $pdo->query("SELECT COUNT(*) FROM videos")->fetchColumn(),
    'total_users' => $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
    'total_modules' => $pdo->query("SELECT COUNT(DISTINCT module) FROM videos")->fetchColumn()
];

// Obtener los últimos videos agregados
$recent_videos = $pdo->query("SELECT * FROM videos ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Hackademia</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-dashboard">
        <nav class="admin-nav">
            <div class="nav-content">
                <h2>Panel de Administración</h2>
                <div class="admin-controls">
                    <span class="welcome-text">Admin: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="nav-buttons">
                        <a href="../dashboard.php" class="btn btn-secondary">Ver Sitio</a>
                        <a href="../logout.php" class="btn btn-primary">Cerrar Sesión</a>
                    </div>
                </div>
            </div>
        </nav>

        <div class="admin-container">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_videos']; ?></div>
                    <div class="stat-label">Videos Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    <div class="stat-label">Usuarios Registrados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $stats['total_modules']; ?></div>
                    <div class="stat-label">Módulos</div>
                </div>
            </div>

            <div class="admin-section">
                <h3>Acciones Rápidas</h3>
                <div class="quick-actions">
                    <a href="add-video.php" class="action-card">
                        <h4>Agregar Video</h4>
                        <p>Subir nuevo contenido al curso</p>
                    </a>
                    <a href="manage-videos.php" class="action-card">
                        <h4>Gestionar Videos</h4>
                        <p>Editar o eliminar videos existentes</p>
                    </a>
                    <a href="manage-users.php" class="action-card">
                        <h4>Gestionar Usuarios</h4>
                        <p>Administrar usuarios del sistema</p>
                    </a>
                    <a href="payment-management.php" class="action-card">
                        <h4>Gestionar Pagos</h4>
                        <p>Aprobar o rechazar comprobantes de pago</p>
                    </a>
                </div>
            </div>

            <div class="admin-section">
                <h3>Videos Recientes</h3>
                <div class="videos-list">
                    <?php foreach ($recent_videos as $video): ?>
                        <div class="video-item">
                            <div class="video-info">
                                <div class="video-title"><?php echo htmlspecialchars($video['title']); ?></div>
                                <div class="video-module">Módulo <?php echo htmlspecialchars($video['module']); ?></div>
                            </div>
                            <div class="video-actions">
                                <a href="edit-video.php?id=<?php echo $video['id']; ?>" class="btn btn-secondary">Editar</a>
                                <a href="manage-videos.php" class="btn btn-primary">Ver Todos</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
